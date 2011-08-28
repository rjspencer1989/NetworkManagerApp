<?

	require "config_tool.php";
	require "rrd.php";

	if (isset($_GET["link"])) {
		$link=$_GET["link"];
	} else {
		error_display("Incorrect call to graph.php. Link variable is not defined");
		exit;
	}

	if (isset($_GET['config'])) {
		$configfile=$_GET['config'];
	} else {
		error_display("Incorrect call to graph.php. Config variable is not defined");
		exit;
	}



	echo "<HTML><HEAD><TITLE>Traffic Graphs</TITLE></HEAD><BODY>";
	echo "<img src=\"graph.php?link=$link&period=1day&config=$configfile\">";
	echo "<br>";
	echo "<img src=\"graph.php?link=$link&period=1week&config=$configfile\">";
	echo "<br>";
	echo "<img src=\"graph.php?link=$link&period=1month&config=$configfile\">";
	echo "<br>";
	echo "<img src=\"graph.php?link=$link&period=1year&config=$configfile\">";
	echo "<br>";

	echo "</BODY></HTML>";

?>
