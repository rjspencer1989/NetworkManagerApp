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
path=$(uci get uhttpd.main.home)
jsonPath=$path"/json/devices.json"
echo "["  > $jsonPath
macAddresses=$(iw dev wlan0 station dump | awk '/Station/ {print $2}')
for MAC in $macAddresses
do
dhcp=$(cat /tmp/dhcp.leases | grep $MAC | awk '{print $2; print $3; if($4 == "*"){print "Unknown"} else{print $4}}')
hw=$(echo $dhcp | awk '{print $1}')
ip=$(echo $dhcp | awk '{print $2}')
name=$(echo $dhcp | awk '{print $3}')
dns=$(cat $path"dns/log" | grep query | grep [A] | grep $ip | tail -n 50 | awk '{print $6}')
echo "{"  >> $jsonPath
echo "\"name\" : \""$name"\","  >> $jsonPath
echo "\"macAddress\" : \""$hw"\","  >> $jsonPath
echo "\"ipAddress\" : \""$ip"\"," >> $jsonPath
echo "\"dns\" : \""$dns"\"" >> $jsonPath
echo "}," >> $jsonPath
done
echo "]" >> $jsonPath
echo "Success"
