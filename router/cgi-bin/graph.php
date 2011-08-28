#!/usr/bin/php-cgi
<?php

require "config_tool.php";
include "config.php";
require "rrd.php";

	if (isset($_GET['config'])) {
		$configfile=urldecode($_GET['config']);
	} else {
			error_display("Incorrect call to graph.php. Config variable is not defined");
			exit;
	}
	read_config($configfile);

	if (isset($_GET["link"])) {
		$link=urldecode($_GET["link"]);
	} else {
		error_display("Incorrect call to graph.php. Link variable is not defined");
		exit;
	}
	
	$period=urldecode($_GET["period"]);
	$type=urldecode($_GET["type"]);

	$rrain=get_rra_name($targetin[$link],$inpos[$link]);
	$rraout=get_rra_name($targetout[$link],$outpos[$link]);

	if ($type == "global") { 
		rrdtool_graph($targetin[$link],$rrain,$targetout[$link],$rraout,$nodea[$link],$nodeb[$link],"1hour",$coef[$link],$coef[$link]);
		rrdtool_graph($targetin[$link],$rrain,$targetout[$link],$rraout,$nodea[$link],$nodeb[$link],"1day",$coef[$link],$coef[$link]);
		rrdtool_graph($targetin[$link],$rrain,$targetout[$link],$rraout,$nodea[$link],$nodeb[$link],"1week",$coef[$link],$coef[$link]);
		rrdtool_graph($targetin[$link],$rrain,$targetout[$link],$rraout,$nodea[$link],$nodeb[$link],"1month",$coef[$link],$coef[$link]);
	} else {
		rrdtool_graph($targetin[$link],$rrain,$targetout[$link],$rraout,$nodea[$link],$nodeb[$link],$period,$coef[$link],$coef[$link]);
	}
?>
