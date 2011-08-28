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
																												
