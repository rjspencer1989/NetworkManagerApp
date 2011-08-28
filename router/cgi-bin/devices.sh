#!/bin/sh
path=$(uci get uhttpd.main.home)"/"
echo "<root>"  > $path"xml/devices.xml"
macAddresses=$(iw dev wlan0 station dump | awk '/Station/ {print $2}')
for MAC in $macAddresses
do
dhcp=$(cat /tmp/dhcp.leases | grep $MAC | awk '{print $2; print $3; if($4 == "*"){print "Unknown"} else{print $4}}')
hw=$(echo $dhcp | awk '{print $1}')
ip=$(echo $dhcp | awk '{print $2}')
name=$(echo $dhcp | awk '{print $3}')
dns=$(cat $path"dns/log" | grep query | grep [A] | grep $ip | tail -n 50 | awk '{print $6}')
echo "<item>"  >> $path"xml/devices.xml"
echo "<name>"  >> $path"xml/devices.xml"
echo "$name"  >> $path"xml/devices.xml"
echo "</name>"  >> $path"xml/devices.xml"
echo "<macAddress>"  >> $path"xml/devices.xml"
echo $hw >> $path"xml/devices.xml"
echo "</macAddress>" >> $path"xml/devices.xml"
echo "<ipAddress>" >> $path"xml/devices.xml"
echo $ip >> $path"xml/devices.xml"
echo "</ipAddress>" >> $path"xml/devices.xml"
echo "<dns>" >> $path"xml/devices.xml"
echo $dns >> $path"xml/devices.xml"
echo "</dns>" >> $path"xml/devices.xml"
echo "</item>" >> $path"xml/devices.xml"
done
echo "</root>" >> $path"xml/devices.xml"
echo "Success"
