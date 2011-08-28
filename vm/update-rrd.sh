#!/bin/bash
cd /var/www/html/weathermap
echo "#Weathermap configuration File" > /var/www/html/weathermap/weathermap-2.conf
echo "HEIGHT 600" >> /var/www/html/weathermap/weathermap-2.conf
echo "WIDTH 740" >> /var/www/html/weathermap/weathermap-2.conf
echo "FONT 4" >> /var/www/html/weathermap/weathermap-2.conf
echo "LEGENDPOS 300 460" >> /var/www/html/weathermap/weathermap-2.conf
echo "LEGENDSTYLE line" >> /var/www/html/weathermap/weathermap-2.conf
echo "TITLE \"Home Network Map\"" >> /var/www/html/weathermap/weathermap-2.conf
echo "TITLEPOS 4 470" >> /var/www/html/weathermap/weathermap-2.conf
echo "TITLEBACKGROUND 255 255 128" >> /var/www/html/weathermap/weathermap-2.conf
echo "TITLEFOREGROUND 0 0 0" >> /var/www/html/weathermap/weathermap-2.conf
echo "REFRESH 60" >> /var/www/html/weathermap/weathermap-2.conf
echo "OUTPUTFILE /var/www/html/weathermap/weathermap.png" >> /var/www/html/weathermap/weathermap-2.conf
echo "HTMLFILE /var/www/html/weathermap/weathermap.html" >> /var/www/html/weathermap/weathermap-2.conf
echo "SCALE  1  10 140   0  255" >> /var/www/html/weathermap/weathermap-2.conf
echo "SCALE 10  25  32  32  255" >> /var/www/html/weathermap/weathermap-2.conf
echo "SCALE 25  40   0 192  255" >> /var/www/html/weathermap/weathermap-2.conf
echo "SCALE 40  55   0 240    0" >> /var/www/html/weathermap/weathermap-2.conf
echo "SCALE 55  70 240 240    0" >> /var/www/html/weathermap/weathermap-2.conf
echo "SCALE 70  85 255 192    0" >> /var/www/html/weathermap/weathermap-2.conf
echo "SCALE 85  90 255 100    0" >> /var/www/html/weathermap/weathermap-2.conf
echo "SCALE 90 100 255   0    0" >> /var/www/html/weathermap/weathermap-2.conf 
echo "NODE router" >> /var/www/html/weathermap/weathermap-2.conf
echo "POSITION 60 40" >> /var/www/html/weathermap/weathermap-2.conf
echo "LABEL Router" >> /var/www/html/weathermap/weathermap-2.conf
echo "ICON icons/wireless_router.png" >> /var/www/html/weathermap/weathermap-2.conf
for f in *.txt 
do
name=${f%.*}".rrd"
bytesin=$(head -n 1 $f)
bytesout=$(head -n 2 $f | tail -n 1)
labeltext=$(head -n 3 $f | tail -n 1)
x=$(($RANDOM%600))
x=$(($x + 60))
y=$(($RANDOM%450))
y=$(($y + 60))
if [ ! -e $name ] 
then
rrdtool create $name --start N --step=60 DS:out:GAUGE:60:U:U DS:in:GAUGE:60:U:U RRA:AVERAGE:0.5:1:24
fi
rrdtool update $name N:$bytesout:$bytesin
echo "NODE "${f%.*} >> /var/www/html/weathermap/weathermap-2.conf
echo "POSITION " $x $y >> /var/www/html/weathermap/weathermap-2.conf
echo "LABEL "$labeltext >> /var/www/html/weathermap/weathermap-2.conf
echo "LINK" ${f%.*}"-router" >> /var/www/html/weathermap/weathermap-2.conf
echo "NODES router ${f%.*}" >> /var/www/html/weathermap/weathermap-2.conf
echo "TARGET /var/www/html/weathermap/"$name >> /var/www/html/weathermap/weathermap-2.conf
echo "INPOS 1" >> /var/www/html/weathermap/weathermap-2.conf
echo "OUTPOS 2" >> /var/www/html/weathermap/weathermap-2.conf
echo "UNIT bytes" >> /var/www/html/weathermap/weathermap-2.conf
echo "BANDWIDTH 50000" >> /var/www/html/weathermap/weathermap-2.conf
echo "DISPLAYVALUE 1" >> /var/www/html/weathermap/weathermap-2.conf
echo "ARROW normal" >> /var/www/html/weathermap/weathermap-2.conf

done
rm -f /var/www/html/weathermap/*.txt
