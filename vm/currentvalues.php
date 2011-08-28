<?

require "config_tool.php";
include "config.php";
require "rrd.php";

	if (isset($_GET['config'])) {
		$configfile=urldecode($_GET['config']);
	} else {
			error_display("Incorrect call to currentvalues.php. Config variable is not defined");
			exit;
	}
	read_config($configfile);

	if (isset($_GET["link"])) {
		$link=urldecode($_GET["link"]);
	} else {
		error_display("Incorrect call to currentvalues.php. Link variable is not defined");
		exit;
	}
	
	$in=rrdtool_get_last_value($targetin[$link],$inpos[$link],$rrdstep);
	$out=rrdtool_get_last_value($targetout[$link],$outpos[$link],$rrdstep);
	
	if ($in >=125000) {
		$coefdisplay=8/(1000*1000);
		$unitdisplay="Mbits";
	} else {
		$coefdisplay=8/1000;
		$unitdisplay="Kbits";
	}

	echo "Input : ".round($in*$coefdisplay,1)." $unitdisplay";
	echo "<br>";
	if ($out >=125000) {
		$coefdisplay=8/(1000*1000);
		$unitdisplay="Mbits";
	} else {
		$coefdisplay=8/1000;
		$unitdisplay="Kbits";
	}
	echo "Output : ".round($out*$coefdisplay,1)." $unitdisplay";
?>
