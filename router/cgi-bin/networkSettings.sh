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
path=$(uci get uhttpd.main.home)"/"
echo "<root>" > $path"xml/networkSettings.xml"
echo "<item>" >> $path"xml/networkSettings.xml"
echo "<name>SSID (Name)</name>" >> $path"xml/networkSettings.xml"
echo "<value>" >> $path"xml/networkSettings.xml"
grep ssid /etc/config/wireless | awk '{print substr($3, 2, length($3) - 2)}' >> $path"xml/networkSettings.xml"
echo "</value>" >> $path"xml/networkSettings.xml"
echo "</item>" >> $path"xml/networkSettings.xml"
echo "<item>" >> $path"xml/networkSettings.xml"
echo "<name>Security Type</name>" >> $path"xml/networkSettings.xml"
echo "<value>" >> $path"xml/networkSettings.xml"
grep encryption /etc/config/wireless | awk '{print substr($3, 2, length($3) - 2)}' >> $path"xml/networkSettings.xml"
echo "</value>" >> $path"xml/networkSettings.xml"
echo "</item>" >> $path"xml/networkSettings.xml"
echo "<item>" >> $path"xml/networkSettings.xml"
echo "<name>Password</name>" >> $path"xml/networkSettings.xml"
echo "<value>" >> $path"xml/networkSettings.xml"
grep key /etc/config/wireless | awk '{print substr($3, 2, length($3) - 2)}' >> $path"xml/networkSettings.xml"
echo "</value>" >> $path"xml/networkSettings.xml"
echo "</item>" >> $path"xml/networkSettings.xml"
echo "<item>" >> $path"xml/networkSettings.xml"
echo "<name>Channel</name>" >> $path"xml/networkSettings.xml"
echo "<value>" >> $path"xml/networkSettings.xml"
grep channel /etc/config/wireless | awk '{print substr($3, 2, length($3) - 2)}' >> $path"xml/networkSettings.xml"
echo "</value>" >> $path"xml/networkSettings.xml"
echo "</item>" >> $path"xml/networkSettings.xml"
echo "</root>" >> $path"xml/networkSettings.xml"
echo "Success"

