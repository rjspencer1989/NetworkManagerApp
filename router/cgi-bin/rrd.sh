#Copyright 2011 Robert Spencer
#This file is part of NetworkManagerApp Router
#NetworkManagerApp Router is free software: you can redistribute it and/or modify
#it under the terms of the GNU General Public License as published by
#the Free Software Foundation, either version 3 of the License, or
#(at your option) any later version.
#
#NetworkManagerApp Router is distributed in the hope that it will be useful,
#but WITHOUT ANY WARRANTY; without even the implied warranty of
#MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#GNU General Public License for more details.
#
#You should have received a copy of the GNU General Public License
#along with NetworkManagerApp Router.  If not, see <http://www.gnu.org/licenses/>. 
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
