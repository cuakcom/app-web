#!/bin/bash
# jp 2022/09

# CONSTANTES
FICH_A_ANALIZAR="/srv/log/httpd/access_log"
# opcion valida tambien: CADENA_A_BUSCAR="cadenachunga1|cadenachunga2"
CADENA_A_BUSCAR="ninguna"
COLUMNA_CON_IP=2
IPS_BUENAS="/tmp/ips_buenas.txt"
PAISES_CHUNGOS="/tmp/paises_chungos.txt"
# establecer un máximo número de reglas para que no afecte al rendimiento del servidor
LINEAS_IPTABLES_BASE=65
LINEAS_IPTABLES_MAX=500
LINEAS_IPTABLES_TOTAL=$((LINEAS_IPTABLES_MAX + LINEAS_IPTABLES_BASE))
PUERTO_A_FILTRAR="80:443"
# CORTAR=0 -> no corta
# CORTAR=1 -> si corta
CORTAR=0
# DEBUG=0 no debug
# DEBUG=1 debug básico
# DEBUG=2 debug + verboso
# DEBUG=3 debug mucho mas verboso
DEBUG=1
FW=""
CORTAR_RED=1
IP="1.1.1.1"
IP2="1.1.0.0"
PAIS=""
ES_CHUNGA=0

if [[ $(which geoiplookup > /dev/null 2>&1) ]];then
        GEOIP=1
else
        GEOIP=0
        echo -n "NOTA: no está el comando geoiplookup, te recomiendo que lo instales: yum install GeoIP"
fi
if [[ "$DEBUG" -ne 0 ]];then echo "GEOIP: $GEOIP";fi


#############
# FUNCIONES #
#############

function pedir_datos(){
        echo "Configurando app..."
        echo

        echo -n "Introduce puerto/s a cortar [$PUERTO_A_FILTRAR]: "
        read VAR
        if [[ -n "$VAR" ]];then
                PUERTO_A_FILTRAR=$VAR
        fi


        echo -n "Introduce fichero a analizar [$FICH_A_ANALIZAR]: "
        read VAR
        if [[ -n "$VAR" ]];then
                FICH_A_ANALIZAR=$VAR
        fi

        echo -n "Introduce la cadena de caracteres a filtrar en el fichero $FICH_A_ANALIZAR [$CADENA_A_BUSCAR]: "
        read VAR
        if [ -z "$VAR" ] || [ "$VAR" = "ninguna" ];then
                CADENA_A_BUSCAR=""
        else
                CADENA_A_BUSCAR=$VAR
        fi


        #echo;echo;echo
        #echo "Este es el log de $FICH_A_ANALIZAR"
        #echo
        #tail -2 $FICH_A_ANALIZAR
        #echo
        #echo -n "Introduce la columna de $FICH_A_ANALIZAR donde aparece la posible IP a cortar [$COLUMNA_CON_IP]: "
        #read VAR
        #if [ -n "$VAR" ];then
        #       COLUMNA_CON_IP=$VAR
        #fi




        if [[ -f $IPS_BUENAS ]];then
                echo -n "Existe el fichero whitelist de IPs: $IPS_BUENAS, ¿deseas sobreescribirlo? (s/n) [n]: "
                read VAR
                if [ -z "$VAR" ] || [ "$VAR" = "n" ];then
                        echo "Se mantiene el fichero $IPS_BUENAS"
                else
                        echo "Se sobreescribe el fichero $IPS_BUENAS"
                        wget http://reclinux.net/ips_buenas.txt -O $IPS_BUENAS 2> /dev/null
                fi
        else
                wget http://reclinux.net/ips_buenas.txt -O $IPS_BUENAS 2> /dev/null
        fi

        if [[ -f $PAISES_CHUNGOS ]];then
                echo -n "Existe el fichero blacklist de paises: $PAISES_CHUNGOS, ¿deseas sobreescribirlo? (s/n) [n]: "
                read VAR
                if [ -z "$VAR" ] || [ "$VAR" = "n" ];then
                        echo "Se mantiene el fichero $PAISES_CHUNGOS"
                else
                        echo "Se sobreescribe el fichero $PAISES_CHUNGOS"
                        wget http://reclinux.net/paises_chungos.txt -O $PAISES_CHUNGOS 2> /dev/null
                fi
        else
                wget http://reclinux.net/paises_chungos.txt -O $PAISES_CHUNGOS 2> /dev/null
        fi


        echo -n "¿Deseas hacer debug antes de cortar realmente? (s/n) [s]: "
        read VAR
        if [ -z "$VAR" ] || [ "$VAR" = "s" ];then
                CORTAR=0
        else
                CORTAR=1
        fi


	if [ $CORTAR -eq 1 ];then
        	echo -n "¿Deseas cortar la IP o la red (ip/16)? (ip/red) [red]: "
        	read VAR
        	if [[ "$VAR" = "ip" ]];then
        	        CORTAR_RED=0
        	fi
	fi



	if [ $CORTAR -eq 1 ];then
		which ipset
	        if [[ $? -eq 0 ]];then
	                if [[ "$DEBUG" -ne 0 ]];then echo "Existe ipset";fi
	                IPSET=1
	        else
	                if [[ "$DEBUG" -ne 0 ]];then echo "No existe ipset";fi
	        fi
	        which iptables
	        if [[ $? -eq 0 ]];then
	                if [[ "$DEBUG" -ne 0 ]];then echo "Existe iptables";fi
	                IPTABLES=1
	        else
	                if [[ "$DEBUG" -ne 0 ]];then echo "No existe iptables";fi
	        fi
	        if [ -z "$IPSET" ] && [ -z "$IPTABLES" ];then
	                echo;echo;echo "     ¡¡ERROR!!   No existe ni iptables ni ipset, instala alguno de ellos antes de continuar"
	                exit 0
	        fi
	        if [ -n "$IPSET" ] && [ -n "$IPTABLES" ];then
	                echo -n "Existe iptables e ipset, ¿que programa deseas usar (iptables/ipset)? [ipset]: "
	                read VAR
	                if [ -z "$VAR" ] || [ "$VAR" = "ipset" ];then
	                        FW="ipset"
	                else
	                        FW="iptables"
	                fi
	        else
	                if [ -n "$IPSET" ];then
	                        FW="ipset"
	                else
	                        FW="iptables"
	                fi
	        fi
	fi


	echo -n "Nivel de log [0: sin log, 3: mucho log], [default: 1]: "
	read VAR
	if [ -z "$VAR" ] || [ "$VAR" = "1" ];then
		DEBUG=1
	else
		DEBUG=$VAR
	fi

}

function iniciar_fw(){
        if [[ "$FW" = "iptables" ]];then
                LINEAS_IPTABLES_BASE=65
                LINEAS_IPTABLES_MAX=500
                LINEAS_IPTABLES_TOTAL=$((LINEAS_IPTABLES_MAX + LINEAS_IPTABLES_BASE))
        else
                # FW = "ipset"
                if [[ "$CORTAR_RED" -eq 1 ]];then
                        ipset create blacklist_wiki hash:net hashsize 4096 2>/dev/null
                else
                        ipset create blacklist_wiki hash:ip hashsize 4096 2>/dev/null
                fi
		iptables -L -n |grep blacklist_wiki || iptables -I INPUT 1 -m set --match-set blacklist_wiki src -j DROP
        fi
}

function cortar(){
        IP_O_RED_CHUNGA=$1
        if [[ "$CORTAR" -eq 0 ]];then
                echo "     [DEBUG] Estoy en la funcion cortar: Voy a cortar: $IP_O_RED_CHUNGA ( IP: $IP, Red: $IP2, Pais: $PAIS )"
        else
                if [[ "$DEBUG" -gt 1 ]];then echo "   Voy a cortar: $IP_O_RED_CHUNGA ( IP: $IP, Red: $IP2, Pais: $PAIS )";fi
                if [[ "$FW" = "ipset" ]];then
                        if [[ "$CORTAR_RED" -eq 1 ]];then
                                # Se usa ip2, se podría usar: ii=$(echo $IP| sed -e 's/\(.*[0-9]*\.[0-9]*\.\)[0-9]*\.[0-9]*\(.*\)/\10.0\2/g')
                                if [[ $(ipset list | grep "^$IP2/16") ]];then
                                        if [[ "$DEBUG" -gt 1 ]];then echo "        Nota: La red $IP2/16 ya está filtrada";fi
                                else
                                        echo "$(date) Filtro la Red: $IP2/16 (ip: $IP, pais: $PAIS)";
                                        ipset add blacklist_wiki $IP2/16
                                fi
                        else
                                if [ $(ipset list | grep "^$IP$") ]];then
                                        if [[ "$DEBUG" -gt 1 ]];then echo "        Nota: La IP $IP ya esta filtrada";fi
                                else
                                        echo "$(date) Filtro la IP: $IP (pais: $PAIS)";
                                        ipset add blacklist_wiki $IP
                                fi
                        fi
                else
                        # usar iptables
                        if [[ "$CORTAR_RED" -eq 1 ]];then
                                if [[ $(iptables -L -n|grep " $IP2/16") ]];then
                                        echo "   Nota: La red $IP2/16 ya esta filtrada"
                                else
                                        echo "   Filtro la red: $IP2/16"
                                        iptables -I INPUT 1 -p tcp --dport $PUERTO_A_FILTRAR -s $IP2/16 -j DROP
                                        if [[ $(iptables -L -n|wc -l) -gt $LINEAS_IPTABLES_TOTAL ]];then
                                                echo "    Borro la linea $LINEAS_IPTABLES_MAX de iptables para que no haya muchas"
                                                iptables -D INPUT $LINEAS_IPTABLES_MAX
                                        fi
                                fi
                        else
                                # cortar ip con iptables
                                if [[ $(iptables -L -n|grep " $IP") ]];then
                                        echo "   Nota: La ip $IP ya esta filtrada"
                                else
                                        echo "   Filtro la ip $IP"
                                        iptables -I INPUT 1 -p tcp --dport $PUERTO_A_FILTRAR -s $IP -j DROP
                                        if [[ $(iptables -L -n|wc -l) -gt $LINEAS_IPTABLES_TOTAL ]];then
                                                echo "    Borro la linea $LINEAS_IPTABLES_MAX de iptables para que no haya muchas"
                                                iptables -D INPUT $LINEAS_IPTABLES_MAX
                                        fi
                                fi
                        fi
                fi
        fi
}

function es_chunga(){
        if [[ "$DEBUG" -ge 2 ]];then echo "   Estoy en la funcion: es_chunga(), estoy revisando la IP: $IP";fi
	PAIS=""
        IP2=$( echo $IP | sed 's/[0-9]\+/0/3g' );
        if [[ $(grep ^$IP2$ $IPS_BUENAS) ]];then
                if [[ "$DEBUG" -gt 1 ]];then echo "        Nota: La IP $IP está whitelisted";fi
                ES_CHUNGA=0
        else
                if [[ "$GEOIP" -eq 1 ]]; then
                        PAIS=$(geoiplookup $IP|grep -i country |awk '{print $4}'|sed 's/,//g')
                        if [[ "$PAIS" == "IP" ]];then
                                # no se ha encontrado, la bbdd puede que este desactualizada
                                echo "     WARNING: puede ser que la bbdd esté desactualizada, no se ha encontrado geoiplookup $IP, uso el curl ..."
                                #PAIS=$(curl --silent https://who.is/whois-ip/ip-address/$IP|grep Country| awk '{print $NF}')
                                #PAIS=$(curl --silent https://ipinfo.io/$IP|grep -i country|awk -F '"' '{print $(NF-1)}')
                                PAIS=$(curl --silent https://ip-whois-lookup.com/lookup.php?ip=$IP|grep "Country Code"|awk '{print $NF}'|sed 's/<\/p>//g' )
                                if [[ "$DEBUG" -ge 2 ]];then echo "   PAIS: $PAIS";fi
                        fi
                else
                        #PAIS=$(curl --silent https://who.is/whois-ip/ip-address/$IP|grep Country| awk '{print $NF}')
                        #PAIS=$(curl --silent https://ipinfo.io/$IP|grep -i country|awk -F '"' '{print $(NF-1)}')
                        PAIS=$(curl --silent https://ip-whois-lookup.com/lookup.php?ip=$IP|grep "Country Code"|awk '{print $NF}'|sed 's/<\/p>//g' )
                fi
                if [[ ! $PAIS ]];then
                        PAIS="desconocido"
                fi
                if [[ "$DEBUG" -gt 2 ]];then echo "          ---> Pais: $PAIS";fi
                if [[ $(grep $PAIS $PAISES_CHUNGOS) ]];then
                        if [[ "$DEBUG" -gt 0 ]];then echo "   La IP: $IP (del pais: $PAIS) es chunga";fi
                        ES_CHUNGA=1
                else
                        if [[ "$DEBUG" -ge 2 ]];then echo "   Nota: El pais $PAIS de la IP $IP es bueno";fi
                        ES_CHUNGA=0
                fi
        fi
}

################
##### main #####
################

pedir_datos

if [[ "$CORTAR" -eq 1 ]];then
        iniciar_fw
else
        echo "FICH_A_ANALIZAR: $FICH_A_ANALIZAR"
        echo "CADENA_A_BUSCAR: $CADENA_A_BUSCAR"
        echo "Columna con IP: $COLUMNA_CON_IP"
        echo "Cortar red: $CORTAR_RED"
        echo "Puerto a filtrar: $PUERTO_A_FILTRAR"
        echo "Nivel de DEBUG: $DEBUG"
        echo "FW: $FW"
fi

if [[ $(which geoiplookup >/dev/null 2>&1) ]];then GEOIP=1; else GEOIP=0; fi


if [[ "$DEBUG" -ne 0 ]];then echo "GEOIP: $GEOIP";fi


echo "Terminada configuracion de la app. Empiezo a analizar $FICH_A_ANALIZAR... "
tail -f $FICH_A_ANALIZAR | grep "$CADENA_A_BUSCAR" |

while read LINE
    do
        #IP=$( echo $LINE | cut -d' ' -f$COLUMNA_CON_IP )
        IP=$( echo $LINE | grep -Eo '([0-9]{1,3}\.){3}[0-9]{1,3}' | head -1 )
        if [[ "$DEBUG" -gt 2 ]];then echo "      IP: $IP";fi
	echo "    Analizando $IP"
        es_chunga
        if [[ "$DEBUG" -gt 1 ]];then echo "      ES_CHUNGA: $ES_CHUNGA";fi
        if [[ "$ES_CHUNGA" = "1" ]];then
                if [[ "$DEBUG" -gt 1 ]];then echo "     Voy a analizar la IP: $IP, parece chunga. Pais: $PAIS";fi
                cortar $IP
        else
                if [[ "$DEBUG" -gt 1 ]];then echo "     Nota: La IP $IP no es chunga, no hago nada";fi
        fi
done

echo "Borrando $IPS_BUENAS y $PAISES_CHUNGOS"
rm -f $IPS_BUENAS
rm -f $PAISES_CHUNGOS
# ipset flush blacklist_wiki (vaciar)
# ipset destroy blacklist_wiki (borrar)
# service iptables restart

