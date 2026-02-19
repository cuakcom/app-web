apt -y update && apt -y upgrade && apt -y dist-upgrade && apt -y autoremove
systemctl stop redis-server.service
systemctl enable redis-server.service
systemctl start redis-server.service
apt -y update && apt -y upgrade && apt -y dist-upgrade && apt -y autoremove
apt -y install redis-server
systemctl stop redis-server.service
systemctl enable redis-server.service
systemctl start redis-server.service
systemctl status redis-server.service
php -v
plesk bin php_handler --list
/opt/plesk/php/7.4/bin/php -v
plesk bin extension --install wp-toolkit
curl -q -O "https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar" && mv wp-cli.phar /usr/sbin/ && chmod +x /usr/sbin/wp-cli.phar && if [[ -f /bin/php-cli ]]; then alias wp='/bin/php-cli /usr/sbin/wp-cli.phar --allow-root'; else alias wp='/opt/plesk/php/7.4/bin/php /usr/sbin/wp-cli.phar --allow-root'; fi
wp --info
timedatectl set-timezone 'UTC'
timedatectl set-ntp on
lsb_release -a
apt -y update && apt -y upgrade && apt -y dist-upgrade && apt -y autoremove
utc
time
timedate
date
lsb_release -a
plesk
plesk login
wp-cli
wp --info
wp-toolkit
plesk ext wp-toolkit
ll /usr/local/psa/var/certificates/
pwd
ll
./cortar.sh
chmod 755 cortar.sh
ll
./cortar.sh
yum install GeoIPGEOIP
apt install GeoIPGEOIP
ipset
iptables -L
cat /etc/mysql/my.cnf
pwd
plesk bin dns -info jorge.eu.org > /tmp/jorge.eu.org.txt
plesk bin dns
plesk bin dns --info jorge.eu.org > /tmp/jorge.eu.org.txt
cat /tmp/jorge.eu.org.txt
who
ll /var/log/maillog
cat /var/log/maillog
ll /var/log/maillog
curl
curl http://trufas.dyndns.org:9994 
cat /var/www/html/index.html 
vi /var/www/html/index.html 
mv /var/www/html/index.html /var/www/html/_index.html
ll /var/www/html/
curl https://pdc.arsys.es/
curl https://ka.plesk.com
curl htts://arsys.es
curl https://arsys.es
curl https://www.arsys.es
telnet ka.plesk.com 443
postconf -d|grep mAil_version
postconf -d|grep mail_version
ll /var/log/
ll /var/log/my
ll /var/log/
ll /etc/
ll /var/log/
ll /var/log/apache2/
lsb_release -a
apt -y update && apt -y upgrade && apt -y dist-upgrade && apt -y autoremove
apt -y install software-properties-common curl vim zip unzip apt-transport-https
apt -y install unattended-upgrades
dpkg-reconfigure -plow unattended-upgradesapt -y install unattended-upgrades
apt -y install unattended-upgrades
dpkg-reconfigure -plow unattended-upgrades
cat /etc/mysql/mariadb.conf.d/50-server.cnf
echo "hola"
hola="esto es lo que va a salir ahora"
echo $hola
cat /var/www/vhosts/jorgefernandez.net/logs/access_log
cat /var/www/vhosts/jorgefernandez.net/logs/access_ssl_log
postqueue -f
mailq
ls /sys/class/scsi_disk/
echo 1 > /sys/class/scsi_device/2:0:0:0/device/rescan
/sys/class/scsi_device/2:0:0:0/device/rescan
fdisk -l
df -h
plesk bin http3_pref --enable -panel -nginx
df -h
history
top
apt list --upgradable
apt
apt update
apt list --upgradable
grub2-editenv list
awk -F\' '$1=="menuentry " {print i++ " : " $2}' /etc/grub2.cfg
uname -r
grubby --info=ALL | grep title
grubby --info=ALL
ll /usr/src/
cat /etc/debian_version 
cat /etc/cloud/cloud.cfg.d/99_ui.cfg 
uname -a
uname
w
w -m
who -m
who -T
who -u
who -p
who -l
who -H
who -a
who -s
who -d
who -b
who -u
who -b -H
who -d -H
who -l -H
who -q -H
who -a
w
last
finger
who -q
who -A
wp
cd /var/www/vhosts/jorgefernandez.net/
wp
ll
cd .wp-cli/
wp
plesk ext wp-toolkit --wp-cli 2
plesk ext wp-toolkit --wp-cli -instance-id 2
ll
cd ..
ll
cd /var/www/vhosts/jorgefernandez.net/
ll
cd /httpdocs
ll
cd httpdocs/
ll
plesk ext wp-toolkit --wp-cli -instance-id 2 core verify-checksums
plesk ext wp-toolkit --wp-cli -instance-id 2
plesk ext wp-toolkit --wp-cli -instance-id 2 wp core verify-checksums
plesk ext wp-toolkit --wp-cli -instance-id 2
plesk ext wp-toolkit --wp-cli -instance-id 1
plesk ext wp-toolkit --wp-cli -instance-id 3
plesk ext wp-toolkit --wp-cli -instance-id 4
cd /
plesk ext wp-toolkit --wp-cli -instance-id 2
plesk ext wp-toolkit --wp-cli -instance-id 2 wp core verify-checksums
plesk ext wp-toolkit --wp-cli
plesk ext wp-toolkit --wp-cli -instance-id 2
wp
plesk ext wp-toolkit --list
plesk ext wp-toolkit --wp-cli -instance-id 12
plesk ext wp-toolkit --list
plesk ext wp-toolkit --wp-cli -instance-id 14
plesk ext wp-toolkit --list
plesk ext wp-toolkit --wp-cli -main-domain-id 2
plesk ext wp-toolkit --wp-cli -main-domain-id 2 core verify-checksums
plesk ext wp-toolkit --wp-cli -main-domain-id 2 -path /var/www/vhost/jorgefernandez.net/httpdocs core verify-checksums
plesk ext wp-toolkit --wp-cli -main-domain-id 2 --core verify-checksums
cd /var/www/vhosts/jorgefernandez.net/httpdocs/
plesk ext wp-toolkit --wp-cli -main-domain-id 2 -- core verify-checksums
plesk ext wp-toolkit --wp-cli -main-domain-id 2 -path / core verify-checksums
plesk ext wp-toolkit --wp-cli -instance-id 2 -path / core verify-checksums
plesk ext wp-toolkit --wp-cli -main-domain-id 2 -path / core verify-checksums
plesk ext wp-toolkit --wp-cli
plesk ext wp-toolkit --list
plesk ext wp-toolkit --wp-cli -main-domain-id 2 -path /var/www/vhosts/jorgefernandez.net/httpdocs core verify-checksums
plesk ext wp-toolkit --wp-cli -main-domain-id 2
plesk ext wp-toolkit --wp-cli -main-domain-id 2 -instance-id 12
plesk ext wp-toolkit --info
plesk ext wp-toolkit --wp-cli -main-domain-id 2 --info
plesk ext wp-toolkit --info -instance-id 1
plesk ext wp-toolkit --info -instance-id 2
plesk ext wp-toolkit core verify-checksums -main-domain-id 2
plesk ext wp-toolkit core verify-checksums -main-domain-id 2 | more
plesk ext wp-toolkit --info -main-domain-id 2
plesk ext wp-toolkit --list
plesk ext wp-toolkit --wp-cli -intance-id 2
plesk ext wp-toolkit --wp-cli -intance-id 2 -- plugin list
plesk ext wp-toolkit --wp-cli -main-domain-id 2 -- plugin list
cd /
plesk ext wp-toolkit --wp-cli -main-domain-id 2 -- plugin list
plesk ext wp-toolkit --wp-cli -instance-id 2 -- plugin list
plesk ext wp-toolkit --wp-cli -instance-id 2 -- core verify-checksums
plesk ext wp-toolkit --wp-cli -instance-id 2 --info
plesk ext wp-toolkit --wp-cli -instance-id 2 --list
plesk ext wp-toolkit --wp-cli --list
plesk ext wp-toolkit --list
plesk ext wp-toolkit --wp-cli -instance-id 2 -- core verify-checksums
plesk ext wp-toolkit --wp-cli -instance-id 2 -- plugin verify-checksums
plesk ext wp-toolkit --wp-cli -instance-id 2 -- plugin verify-checksums -all
cd /var/www/vhosts/jorgefernandez.net/httpdocs/
plesk ext wp-toolkit --wp-cli -instance-id 2 -- plugin verify-checksums -all
ll
plesk ext wp-toolkit --wp-cli -instance-id 2 -- plugin verify-checksums -all
plesk ext wp-toolkit --wp-cli -instance-id 2 -- plugin verify-checksums
cd ..
plesk ext wp-toolkit --wp-cli -instance-id 2 -- plugin verify-checksums
plesk ext wp-toolkit --wp-cli -main-domain-id 2 -- plugin verify-checksums
plesk ext wp-toolkit --wp-cli -main-domain-id 2 -- plugin verify-checksums -all
cd httpdocs/
plesk ext wp-toolkit --wp-cli -main-domain-id 2 -- plugin verify-checksums -all
plesk ext wp-toolkit --info
plesk ext wp-toolkit --list
plesk ext wp-toolkit --wp-cli -main-domain-id 2 -- plugin verify-checksums -all
plesk ext wp-toolkit --wp-cli -main-domain-id 2 -path /var/www/vhost/jorgefernandez.net/httpdocs/ -- plugin verify-checksums -all
cd /
plesk ext wp-toolkit --wp-cli -main-domain-id 2 -path /var/www/vhost/jorgefernandez.net/httpdocs/ -- plugin verify-checksums -all
plesk ext wp-toolkit --wp-cli -instance-id 2 -path /var/www/vhost/jorgefernandez.net/httpdocs/ -- plugin verify-checksums -all
plesk ext wp-toolkit --wp-cli -instance-id 12 -path /var/www/vhost/jorgefernandez.net/httpdocs/ -- plugin verify-checksums -all
plesk ext wp-toolkit --wp-cli -instance-id 12 -path /var/www/vhost/jorgefernandez.net/httpdocs/ -- plugin verify-checksums
plesk ext wp-toolkit --wp-cli -instance-id 12 -path /var/www/vhost/jorgefernandez.net/httpdocs/wp-content/plugins -- plugin verify-checksums -all
plesk ext wp-toolkit --wp-cli -instance-id 12 -path /var/www/vhost/jorgefernandez.net/httpdocs/wp-content/plugins/akismet -- plugin verify-checksums -all
ll /var/www/vhost/jorgefernandez.net/httpdocs/wp-content/plugins/akismet
ll /var/www/vhost/jorgefernandez.net/httpdocs/wp-content/plugins/
ll /var/www/vhost/jorgefernandez.net/httpdocs/wp-content/
ll /var/www/vhosts/jorgefernandez.net/httpdocs/
plesk ext wp-toolkit --wp-cli -instance-id 12 -path /var/www/vhosts/jorgefernandez.net/httpdocs/ -- plugin verify-checksums -all
plesk ext wp-toolkit --wp-cli -instance-id 12 -path /var/www/vhosts/jorgefernandez.net/httpdocs/ -- plugin verify-checksums
plesk ext wp-toolkit --wp-cli -instance-id 12 -path /var/www/vhosts/jorgefernandez.net/httpdocs/ -- plugin verify-checksums --all
root@s1:/# plesk ext wp-toolkit --wp-cli -instance-id 12 -path /var/www/vhosts/jorgefernandez.net/httpdocs/ -- plugin verify-checksums --all
Success: Verified 6 of 6 plugins.
plesk ext wp-toolkit --wp-cli -instance-id 12 -- core verify-checksums
plesk ext wp-toolkit --wp-cli -instance-id 12 --info
plesk ext wp-toolkit --wp-cli -instance-id 12 -- admin-name
plesk ext wp-toolkit --wp-cli -instance-id 12 -- core verify-checksums
plesk ext wp-toolkit --wp-cli -instance-id 12 -- config get
plesk ext wp-toolkit --wp-cli -instance-id 12 -- user list
plesk ext wp-toolkit --wp-cli -instance-id 12 -- db tables
plesk ext wp-toolkit --wp-cli -instance-id 12 -- rewrite list
telnet mx.serviciodecorreo.es 465
telnet smpt.serviciodecorreo.es 465
telnet smtp.serviciodecorreo.es 465
ping smtp.serviciodecorreo.es
telnet smtp.serviciodecorreo.es 25
telnet smtp.serviciodecorreo.es 465
plesk repair
plesk repair all

dmidecode
dmidecode -t memory
dmidecode -h
curl http://ip-api.com/json/
telnet smtp.serviciodecorreo.es 25
telnet smtp.serviciodecorreo.es 465
telnet smtp.telefonica.es 25
telnet mail.diffusion-bridging.top 25
cat /var/www/vhosts/system/asuntospropios.com/conf/httpd.conf

ipset
iptables
ipset -L
ipset -L | grep 82.223
iptables -L | grep 82.223
ll /var/log/
ll -h /var/log/
cd /var/log/
cat syslog
tail -f syslog
cat /root/.ssh/authorized_keys 
vi /root/.ssh/authorized_keys 
cat /root/.ssh/authorized_keys 
vi /root/.ssh/authorized_keys 
cat /root/.ssh/authorized_keys 
ll /var/log/modsec_audit.log
grep 52.210.126.224 /var/log/modsec_audit.log
grep 52.210.126.224 /var/log/*
grep 52.210.126.224 /var/log/*.log
grep -R 52.210.126.224 /var/log/*
grep -r "123.45.67.89" /var/log
grep -r "52.210.126.224" /var/log
grep 52.210.126.224 /var/log/auth.log
grep 52.210.126.224 /var/log/modsec_audit.log
tail /var/log/modsec_audit.log
tail -100 /var/log/modsec_audit.log
cat /var/lib/dhcp/dhclient6.leases 
cat /var/lib/dhcp/dhclient.ens192.leases 
ll /var/www/vhosts/jorgefernandez.net/httpdocs/wp-content/themes/
cat /var/www/vhosts/jorgefernandez.net/httpdocs/wp-content/themes/twentytwentyfour/functions.php 
ll /var/www/vhosts/jorgefernandez.net/httpdocs/wp-content/themes/twentytwentyfour/
ll /var/www/vhosts/jorgefernandez.net/httpdocs/
ll -H /var/www/vhosts/jorgefernandez.net/httpdocs/
ll -h /var/www/vhosts/jorgefernandez.net/httpdocs/
pro status
sudo apt upgrade -s
cat /etc/psa/.psa.shadow 
mysql -uadmin -p`cat /etc/psa/.psa.shadow`
mysql
exit
whoami
pwd
sudo
history
vi /root/.ssh/authorized_keys 
ll /var/www/vhosts/jorgefernandez.net/
mkdir /var/www/vhosts/jorgefernandez.net/httpdocs/ciudad
vi index.html
ll /var/www/vhosts/jorgefernandez.net/httpdocs/ciudad
ll
mv index.html /var/www/vhosts/jorgefernandez.net/httpdocs/ciudad/
ll
cd /var/www/vhosts/jorgefernandez.net/httpdocs/ciudad
vi index.html 
history
pro status
ip a
curl http://169.254.169.254
https://api.ionos.com/docs/activitylog/v1https://api.ionos.com/docs/activitylog/v1https://api.ionos.com/docs/activitylog/v1https://api.ionos.com/docs/activitylog/v1
https://api.ionos.com/docs/activitylog/v1
curl https://api.ionos.com/docs/activitylog/v1
curl https://api.ionos.com/activitylog/v1/
sudo apt update
sudo apt install whois
git init
cd /var/www/vhosts/inteligenciageneral.com/httpdocs/app/
git init
add .
git add .
ll /root/.ssh/
ll /root/.ssh/authorized_keys 
cat /root/.ssh/authorized_keys 
vi /root/.ssh/authorized_keys 
cat /root/.ssh/authorized_keys 
cd /var/www/vhosts/inteligenciageneral.com/httpdocs/app/
git init
git add .
git commit -m "Versión 1.0: Estructura modular, captura Thum.io y rueda giratoria"
git config --global --add safe.directory /var/www/vhosts/inteligenciageneral.com/httpdocs/app
git add .
git commit -m "Versión 1.0: Estructura modular, captura Thum.io y rueda giratoria"
git config --global user.name "cuakcom"
git config --global user.email github@cuak.com
git commit --amend --reset-author
git push -u origin main
git add .
git commit -m "Añadida seguridad .htaccess, gitignore y documentación README"
git push origin main
git branch
# 1. Cambiamos el nombre de la rama local 'master' a 'main'
git branch -M main
# 2. Aseguramos que todo esté añadido y confirmado
git add .
git commit -m "Versión 1.0: Estructura modular, captura Thum.io y rueda giratoria"
# 3. Subimos a GitHub (usando -u para vincular las ramas)
git push -u origin main
git branch -M main
git add .
git commit -m "Versión 1.0: Estructura modular, captura Thum.io y rueda giratoria"
git push -u origin main
git remote add origin https://github.com/cuakcom/app-web.git
git remote -v
git push -u origin main
git config --global credential.helper store
ll /etc/
vi /etc/motd
plesk
ipset
ipset help
