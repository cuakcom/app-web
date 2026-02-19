#!/bin/sh
### Copyright 1999-2022. Plesk International GmbH. All rights reserved.

# If env variable PLESK_INSTALLER_ERROR_REPORT=path_to_file is specified then in case of error
# repository_check.sh writes single line json report into it with the following fields:
# - "stage": "repositorycheck"
# - "level": "error"
# - "errtype" is one of the following:
#   * "reponotenabled" - required repository is not enabled.
#   * "reponotsupported" - unsupported repository is enabled.
#   * "configmanagernotinstalled" - dnf config-manager is disabled.
# - "repo": repository name.
# - "date": time of error occurance ("2020-03-24T06:59:43,127545441+0000")
# - "error": human readable error message.

[ -z "$PLESK_INSTALLER_DEBUG" ] || set -x
[ -z "$PLESK_INSTALLER_STRICT_MODE" ] || set -e

export LC_ALL=C
unset GREP_OPTIONS

SKIP_FLAG="/tmp/plesk-installer-skip-repository-check.flag"
RET_WARN=1
RET_FATAL=2

# @params are tags in format "key=value"
# Report body (human readable information) is read from stdin
# and copied to stderr.
make_error_report()
{
	local report_file="${PLESK_INSTALLER_ERROR_REPORT:-}"

	local python_bin=
	for bin in "/opt/psa/bin/python" "/usr/local/psa/bin/python" "/usr/bin/python2" "/opt/psa/bin/py3-python" "/usr/local/psa/bin/py3-python" "/usr/libexec/platform-python" "/usr/bin/python3"; do
		if [ -x "$bin" ]; then
			python_bin="$bin"
			break
		fi
	done

	if [ -n "$report_file" -a -x "$python_bin" ]; then
		"$python_bin" -c 'import sys, json
report_file = sys.argv[1]
error = sys.stdin.read()

sys.stderr.write(error)

data = {
    "error": error,
}

for tag in sys.argv[2:]:
    k, v = tag.split("=", 1)
    data[k] = v

with open(report_file, "a") as f:
    json.dump(data, f)
    f.write("\n")
' "$report_file" "date=$(date --utc --iso-8601=ns)" "$@"
	else
		cat - >&2
	fi
}

report_no_repo()
{
	local repo="$1"

	make_error_report 'stage=repositorycheck' 'level=error' 'errtype=reponotenabled' "repo=$repo" <<-EOL
		Plesk installation requires '$repo' OS repository to be enabled.
		Make sure it is available and enabled, then try again.
	EOL
}

report_unsupported_repo()
{
	local repo="$1"

	make_error_report 'stage=repositorycheck' 'level=error' 'errtype=reponotsupported' "repo=$repo" <<-EOL
		Plesk installation doesn't support '$repo' OS repository.
		Make sure it is disabled, then try again.
	EOL
}

report_rh_no_config_manager()
{
	local target
	case "$package_manager" in
		yum)
			target="yum-utils package"
		;;
		dnf)
			target="config-manager dnf plugin"
		;;
	esac

	make_error_report 'stage=repositorycheck' 'level=error' 'errtype=configmanagernotinstalled' <<-EOL
		Failed to install $target.
		Make sure repositories configuration of $package_manager package manager is correct
		(use '$package_manager repolist --verbose' to get its actual state), then try again.
	EOL
}

has_rh_enabled_repo()
{
	local repo="$1"

	case "$package_manager" in
		yum)
			# Repo-id may end with OS version and/or architecture
			# if baseurl of the repo refers to $releasever and/or $basearch variables
			# eg 'epel/7/x86_64', 'epel/7', 'epel/x86_64'
			yum repolist enabled --verbose --cacheonly -q | egrep -q "^Repo-id\s*: $repo(/.+)?\s*$"
		;;
		dnf)
			# note: --noplugins may cause failure and empty output on RedHat
			dnf repoinfo --enabled --cacheonly -q | egrep -q "^Repo-id\s*: $repo\s*$"
		;;
	esac
}

has_rh_config_manager()
{
	case "$package_manager" in
		yum) yum-config-manager --help >/dev/null 2>&1 ;;
		dnf) dnf config-manager --help >/dev/null 2>&1 ;;
	esac
}

install_rh_config_manager()
{
	case "$package_manager" in
		yum) yum install --disablerepo 'PLESK_*' -q -y 'yum-utils' ;;
		dnf) dnf install --disablerepo 'PLESK_*' -q -y 'dnf-command(config-manager)' ;;
	esac
}

check_rh_config_manager()
{
	if ! has_rh_config_manager && ! install_rh_config_manager; then
		report_rh_no_config_manager
		return $RET_FATAL
	fi
}

enable_rh_repo()
{
	case "$package_manager" in
		yum) yum-config-manager --enable "$@"; has_rh_enabled_repo "$@" ;;
		dnf) dnf config-manager --set-enabled "$@" ;;
	esac
}

check_epel()
{
	! enable_rh_repo "epel" || return 0

	# try to install epel-release from centos/extras or plesk/thirdparty repo
	# and then try to update it to last version shipped by epel itself
	# to make package upgradable with pum
	"$package_manager" install --disablerepo 'PLESK_*' -q -y 'epel-release' 2>/dev/null \
		|| "$package_manager" install --enablerepo 'PLESK_18_*-thirdparty' -q -y 'epel-release' \
		&& "$package_manager" update -q -y 'epel-release'

	! has_rh_enabled_repo "epel" || return 0

	report_no_repo "epel"
	return $RET_FATAL
}

check_repos_centos8()
{
	check_rh_config_manager || return $?

	local rc=0
	check_epel || rc=$?

	# names of repos are lowercased since 8.3
	! enable_rh_repo "powertools" || return $rc
	! enable_rh_repo "PowerTools" || return $rc

	report_no_repo "powertools"
	return $RET_FATAL
}

check_repos_cloudlinux8()
{
	check_rh_config_manager || return $?

	local rc=0
	check_epel || rc=$?

	# names of repos are changed since 8.5
	! enable_rh_repo "powertools" || return $rc
	! enable_rh_repo "cloudlinux-PowerTools" || return $rc

	report_no_repo "powertools"
	return $RET_FATAL
}

check_repos_rhel8()
{
	check_rh_config_manager || return $?

	local rc=0
	check_epel || rc=$?

	[ "$1" = "install" ] || return $rc

	local arch="`/usr/bin/arch`"

	for repo in "codeready-builder-for-rhel-8-$arch-rpms"; do
		if ! subscription-manager repos --enable "$repo"; then
			! has_rh_enabled_repo "$repo" || continue
			report_no_repo "$repo"
			rc=$RET_FATAL
		fi
	done
	return $rc
}

check_repos_almalinux8()
{
	check_repos_centos8 "$@"
}

check_repos_rocky8()
{
	check_repos_centos8 "$@"
}

check_repos_rhel7()
{
	check_rh_config_manager && check_epel
}

check_repos_centos7()
{
	check_repos_rhel7
}

check_repos_cloudlinux7()
{
	check_repos_rhel7
}

check_repos_virtuozzo7()
{
	check_repos_rhel7
}

find_apt_repo()
{
	local repo="$1"

	local dist_tag=
	! [ "$os_name" = "ubuntu" ] || dist_tag="a"
	! [ "$os_name" = "debian" ] || dist_tag="n"

	if [ -z "$_apt_cache_policy" ]; then
		# extract info of each available release as a string which consists of 'tag=value'
		# filter out releases with priority less or equal to 100
		_apt_cache_policy="$(
			apt-cache policy \
			| grep "b=$pkg_arch" \
			| grep -Eo '([a-z]=[^,]+,?)*' \
		)"
	fi

	local l="$(echo "$repo" | cut -f1 -d'/')"
	local d="$(echo "$repo" | cut -f2 -d'/')"
	local c="$(echo "$repo" | cut -f3 -d'/')"

	# try to find releases by distribution and component
	echo "$_apt_cache_policy" \
		| grep -E "(^|,)l=$l(,|$)" \
		| grep -E "(^|,)$dist_tag=$d(,|$)" \
		| grep -E "(^|,)c=$c(,|$)" \
		| while IFS=$(printf '\n') read rel && [ -n "$rel" ]; do
			l="$(echo "$rel" | grep -Eo "(^|,)l=[^,]+"         | cut -f2 -d"=")"
			d="$(echo "$rel" | grep -Eo "(^|,)$dist_tag=[^,]+" | cut -f2 -d"=")"
			c="$(echo "$rel" | grep -Eo "(^|,)c=[^,]+"         | cut -f2 -d"=")"
			echo "$l/$d/$c"
		done
}

disable_apt_repo()
{
	local repos_to_disable=$(find_apt_repo "$1" | cut -d '/' -f 2,3 | sort | uniq)
	if [ -z "$repos_to_disable" ]; then
		return 0
	fi

	echo "$repos_to_disable" \
		| while IFS= read -r repo_to_disable && [ -n "$repo_to_disable" ]; do
			local distrib=${repo_to_disable%%/*}
			local component=${repo_to_disable##*/}
			find /etc/apt -name "*.list" -exec \
				sed -i -e "/^\s*#/! s/.*\s$distrib\s\+$component\b/# &/" {} +
		done

	# Since we have changed the repositories list, we should re-read _apt_cache_policy on a next call
	# of the find_apt_repo function. Hence we have to reset the value of the variable
	_apt_cache_policy=""

	return 0
}

check_requred_apt_repo()
{
	local repo="$1"
	[ -z "$(find_apt_repo "$repo")" ] || return 0
	report_no_repo "$repo"
	return $RET_FATAL
}

check_unsupported_apt_repos_ubuntu()
{
	[ -n "$os_codename" ] || return 0
	local mode="$1"

	local repos="$(
		find_apt_repo "Ubuntu/[^,]+/[^,]+" | grep -v "Ubuntu/$os_codename.*/.*"
		find_apt_repo "Debian[^,]*/[^,]+/[^,]+"
	)"
	[ -n "$repos" ] || return 0

	echo "$repos" | while IFS=$(printf '\n') read repo; do
		report_unsupported_repo "$repo"
	done

	[ "$mode" = "install" ] || return $RET_WARN
	return $RET_FATAL
}

check_repos_ubuntu18()
{
	[ -n "$os_codename" ] || return 0
	local mode="$1"
	local rc=0

	check_requred_apt_repo "Ubuntu/$os_codename/main" || rc=$?
	check_requred_apt_repo "Ubuntu/$os_codename/universe" || rc=$?
	check_requred_apt_repo "Ubuntu/$os_codename-updates/main" || rc=$?
	check_requred_apt_repo "Ubuntu/$os_codename-updates/universe" || rc=$?
	check_unsupported_apt_repos_ubuntu "$mode" || rc=$?

	return $rc
}


check_repos_ubuntu()
{
	[ -n "$os_codename" ] || return 0
	local mode="$1"
	local rc=0

	check_requred_apt_repo "Ubuntu/$os_codename/main" || rc=$?
	check_requred_apt_repo "Ubuntu/$os_codename/universe" || rc=$?
	check_unsupported_apt_repos_ubuntu "$mode" || rc=$?

	return $rc
}

check_unsupported_apt_repos_debian()
{
	[ -n "$os_codename" ] || return 0
	local mode="$1"

	local repos="$(
		find_apt_repo "Debian Backports/$os_codename-backports/[^,]+"
		find_apt_repo "Debian[^,]*/[^,]+/[^,]+" | grep -v "Debian.*/$os_codename.*/.*"
		find_apt_repo "Ubuntu/[^,]+/[^,]+"
	)"
	[ -n "$repos" ] || return 0

	echo "$repos" | while IFS=$(printf '\n') read repo; do
		report_unsupported_repo "$repo"
	done

	[ "$mode" = "install" ] || return $RET_WARN
	return $RET_FATAL
}

check_repos_debian()
{
	[ -n "$os_codename" ] || return 0
	local mode="$1"
	local rc=0

	disable_apt_repo "Debian Backports/$os_codename-backports/[^,]+"

	check_requred_apt_repo "Debian/$os_codename/main" || rc=$?
	check_unsupported_apt_repos_debian "$mode" || rc=$?

	return $rc
}

detect_platform()
{
	. /etc/os-release
	os_name="$ID"
	os_version="${VERSION_ID%%.*}"
	os_arch=$(uname -m)
	if [ -e /etc/debian_version ]; then
		case "$os_arch" in
			x86_64)  pkg_arch="amd64" ;;
			aarch64) pkg_arch="arm64" ;;
		esac
		if [ -n "$VERSION_CODENAME" ]; then
			os_codename="$VERSION_CODENAME"
		else
			case "$os_name$os_version" in
				debian9)  os_codename="stretch"  ;;
				debian10) os_codename="buster"   ;;
				debian11) os_codename="bullseye" ;;
				ubuntu18) os_codename="bionic"   ;;
				ubuntu20) os_codename="focal"    ;;
				ubuntu22) os_codename="jammy"    ;;
			esac
		fi
	fi

	case "$os_name$os_version" in
		rhel7|centos7|cloudlinux7|virtuozzo7)
			package_manager="yum"
		;;
		rhel8|centos8|cloudlinux8|almalinux8|rocky8)
			package_manager="dnf"
		;;
		debian*|ubuntu*)
			package_manager="apt"
		;;
	esac
}

check_repos()
{
	detect_platform

	# try to execute checker only if all attributes are detected
	[ -n "$os_name" -a -n "$os_version" ] || return 0

	local mode="$1"
	local prefix="check_repos"
	for checker in "${prefix}_${os_name}${os_version}" "${prefix}_${os_name}"; do
		case "`type "$checker" 2>/dev/null`" in
			*function*)
				"$checker" "$mode"
				return $?
			;;
		esac
	done
	return 0
}

# ---

if [ -f "$SKIP_FLAG" ]; then
	echo "Repository check was skipped due to flag file." >&2
	exit 0
fi

check_repos "$1"
