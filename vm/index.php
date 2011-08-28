<?php
	if (ini_get('register_globals')){
	    echo "<font color=red>Register global is ON</a><br>It could be a security issue, please be sure you need it or change settings inside your php.ini file</font>";
	}
	$HTMLFILE = ""; // To be sure that it won't be a problem even if register_globals is set to ON

	include "config.php";
	require "config_tool.php";
	read_config($configfile);
	$configfile=$_GET['config'];


	echo'
		<HTML><HEAD><META HTTP-EQUIV="REFRESH" CONTENT="'.$refresh_display.'" />
		<TITLE></TITLE>
		</HEAD>
		<BODY>
	';

if ($ROLLOVERLIB="overlib") {
	echo '	
		<DIV id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></DIV>
		<SCRIPT language="JavaScript" src="overlib_mini.js">
		<!-- overLIB (c) Erik Bosrup --></SCRIPT>
		';
}

	echo '
		<IMG SRC="w4rrd.php?config='.$configfile.'" BORDER=0 ';
		if (file_exists($HTMLFILE)) { 
			echo 'USEMAP="#weathermap_imap" '; 
		}
		echo '/>';
	
	if (file_exists($HTMLFILE)) { include $HTMLFILE; }

	echo '
		<br>
		<font size=1>if you don\'t see any map, please click <a href="w4rrd.php?config='.$configfile.'">here</a> to see error message.</font>
		</BODY>
		</HTML>
	';

?>
