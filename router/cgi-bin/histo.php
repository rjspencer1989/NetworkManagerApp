#!/usr/bin/php-cgi
<?php

include "config.php";

if ( ! ($step=$_REQUEST['step']) ) {
	$step=$rrdstep;
}

$unixtime=$_REQUEST['unixtime'];

if (! $unixtime) {
	$unixtime=time()-2*$step;
}

$nexttime=$unixtime-$step;

echo '<meta http-equiv="refresh" content="1;url=histo.php?unixtime='.$nexttime.'">';
echo "<img src=\"w4rrd.php?unixtime=$unixtime\">";

?>
