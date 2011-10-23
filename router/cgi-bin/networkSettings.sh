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
#along with NetworkManagerApp Router.  If not, see \"http://www.gnu.org/licenses/>. 
#!/bin/sh
path=$(uci get uhttpd.main.home)
jsonPath=$path"/json/networkSettings.json"
echo "[" > $jsonPath
echo "{" >> $jsonPath
echo "\"name\" : \"SSID (Name)\"," >> $jsonPath
echo "\"value\" : " >> $jsonPath
grep ssid /etc/config/wireless | awk '{print substr($3, 2, length($3) - 2)}' >> $jsonPath
echo "\"value\"" >> $jsonPath
echo "}," >> $jsonPath
echo "{" >> $jsonPath
echo "\"name\" : \"Security Type\"," >> $jsonPath
echo "\"value\" : " >> $jsonPath
grep encryption /etc/config/wireless | awk '{print substr($3, 2, length($3) - 2)}' >> $jsonPath
echo "\"value\"" >> $jsonPath
echo "}," >> $jsonPath
echo "{" >> $jsonPath
echo "\"name\" : \"Password\"," >> $jsonPath
echo "\"value\" : " >> $jsonPath
grep key /etc/config/wireless | awk '{print substr($3, 2, length($3) - 2)}' >> $jsonPath
echo "\"value\"" >> $jsonPath
echo "}," >> $jsonPath
echo "{" >> $jsonPath
echo "\"name\" : \"Channel\"," >> $jsonPath
echo "\"value\"" >> $jsonPath
grep channel /etc/config/wireless | awk '{print substr($3, 2, length($3) - 2)}' >> $jsonPath
echo "\"value\"" >> $jsonPath
echo "}" >> $jsonPath
echo "]" >> $jsonPath
echo "Success"

