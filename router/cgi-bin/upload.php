//Copyright 2011 Robert Spencer
//This file is part of NetworkManagerApp Router
//NetworkManagerApp Router is free software: you can redistribute it and/or modify
//it under the terms of the GNU General Public License as published by
//the Free Software Foundation, either version 3 of the License, or
//(at your option) any later version.
//
//NetworkManagerApp Router is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details.
//
//You should have received a copy of the GNU General Public License
//along with NetworkManagerApp Router.  If not, see <http://www.gnu.org/licenses/>.
#!/usr/bin/php-cgi
<?php
date_default_timezone_set("Europe/London");
$uploaddir = "../uploads/";
print_r($_FILES);
$filename = $uploaddir . basename($_FILES['file']['name']);
echo($filename);
$fileContents = array();
move_uploaded_file($_FILES['file']['tmp_name'], $filename);
echo $filename;
if($filename != ""){
$fileContents = parse_ini_file($filename);
}
	
$settingName = $fileContents['Name'];
$value = $fileContents['Value'];
	
$handle = fopen("/etc/config/wireless", "r");
$new = fopen("/mnt/usbdisk/www/uploads/wireless_update", "w");
	
if($handle){
	while(($buffer = fgets($handle)) != false){
		$pos = strpos($buffer, $settingName);
		if($pos === false){
			fwrite($new, $buffer);
		}else{
			preg_match_all( '/(\w+|"[\w\s]*")+/', $buffer, $matches);
			fwrite($new, $matches[1][0]);
			fwrite($new, " '" . $matches[1][1]. "'");
			fwrite($new, " '" . $value . "'\n");
		}
	}
	fclose($new);
	fclose($handle);
}
																												
exec("mv ../uploads/wireless_update /etc/config/wireless");
?>
																												
