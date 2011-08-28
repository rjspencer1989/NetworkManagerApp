#!/bin/sh
cd /mnt/usbdisk/www/cgi-bin
macAddresses=$(/usr/sbin/iw dev wlan0 station dump | /usr/bin/awk '/Station/ {print $2}')
for MAC in $macAddresses
do
byte_in=$(/usr/sbin/iw dev wlan0 station get $MAC | /usr/bin/awk '/rx bytes:/ {print $3}')
byte_out=$(/usr/sbin/iw dev wlan0 station get $MAC | /usr/bin/awk '/tx bytes:/ {print $3}')
name=$(/bin/cat /tmp/dhcp.leases | grep $MAC | /usr/bin/awk '{if($4 == "*"){print "Unknown"} else{print $4}}')
file=$(/bin/echo $MAC | /bin/sed 's/:/-/g')
filename=/mnt/usbdisk/www/$file".txt"
/bin/echo $byte_in > $filename
/bin/echo $byte_out >> $filename
/bin/echo $name >> $filename
/usr/bin/scp -i /root/id_rsa $filename root@192.168.1.225:/var/www/html/weathermap
done
