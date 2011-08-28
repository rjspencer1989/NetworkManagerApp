#!/usr/bin/php-cgi
<?php

$rrdtool = "/usr/bin/rrdtool";
//$rrdtool = "/usr/src/rrdtool-1.3.6/src/rrdtool";

# You have to set up this variable to your weathermap4rrd path
# it will be used to read config file
$weathermap4php="/mnt/usbdisk/www/cgi-bin/";
#$configfile=$weathermap4php."/weathermap.conf";
$configfile=$_GET['config'];
if (empty($configfile)) {
	if (empty($weathermap4php)) {
	    $configfile = "weathermap.conf";
	} else {
	    $configfile = $weathermap4php."/weathermap.conf";
	}
} else {
	if (empty($weathermap4php)) {
	    $configfile = $configfile;
	} else {
	    $configfile = $weathermap4php."/".$configfile;
	}
} 
#echo "configfile = $configfile";
	
$adminpage="index.php";
$VERSION="1.2b";
$DEBUG=0;

# Size of map if nothing defined in configuration file
$width=600;
$height=700;

# Default font used if none is defined in configuration file (FONT directive)
$font=4; 

# Default width base arrow if none is defined in configuration file (ARROW directive)
$width_arrow=4;

# Default colors of title background and forecolor if non are specified in configuration file (TITLEBACKGROUND and TITLEFOREGROUND directives)

# White background
$titlebackground_red=255;
$titlebackground_green=255;
$titlebackground_blue=255;

# Black foreground writing
$titleforeground_red=0;
$titleforeground_green=100;
$titleforeground_blue=0;

# Default background color if non is specified in configuration file (BACKGROUNDCOLOR directive)
$backgroundcolor_red=255;
$backgroundcolor_green=255;
$backgroundcolor_blue=255;

# Specifies the base interval in seconds with which data have been fed into the RRD file
# This value is only used for RRDTool 1.2 binaries
$rrdstep=300; # = 5 minutes

# Period between refresh of display (in seconds) if none is defined in weathermap.conf 
$refresh_display=60;

# Type of javascript library used of rollover above map elements
$ROLLOVERLIB="overlib";


?>
