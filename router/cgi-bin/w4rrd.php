#!/usr/bin/php-cgi
<?php

$html_map = "";

include "config.php";
require "rrd.php";
require "config_tool.php";
require "graphic.php";
require "net.php";

$unixtime=$_REQUEST['unixtime'];

if (isset($_GET['config'])) {
	$configfile=$_GET['config'];
	if ($configfile=="") { $configfile="weathermap.conf"; }
}

if ($unixtime) {
	$month=date("m",$unixtime);
	$day=date("d",$unixtime);
	$year=date("Y",$unixtime);
	$hours=date("H",$unixtime);
	$minutes=date("i",$unixtime);
	$seconds=date("s",$unixtime);
} elseif ($month && $day && $year) {
	$month=$_REQUEST['month'];
	$day=$_REQUEST['day'];
	$year=$_REQUEST['year'];
	$hours=$_REQUEST['hours'];
	$minutes=$_REQUEST['minutes'];
	$seconds=$_REQUEST['seconds'];

	$unixtime=mktime($hours,$minutes,$seconds,$month,$day,$year);
}

if ($unixtime) { 
	$date=$unixtime;
}

	# If no filter parameter is send to the script to only display link belonging to GROUP defined
	$filter=$_REQUEST["group"];
	if ((! $filter) || $filter=="all") { $filter=""; }
	

	# Check if GD module is installed with PHP
    if (! extension_loaded('gd')) {
		error_display("PHP GD module is needed for Weathermap4RRD.");
		exit;
	}

	# Read configuration from file defined in $configfile (see config.php)
	read_config($configfile);

	if (empty($target)) {
		error_display("No link has been defined. Unable to generate graph.");
		exit;
	}


	# Create global GD image for network map
	# Size :  X = $width+8
	#         Y = $height+4
	
	if (empty($background)) {
			$im = imagecreate($width+8,$height+4) or die ("Cannot Initialize new GD image stream");
	} else {
		if (file_exists($background)) {
			$im = @imagecreatefrompng($background);
			if ($DEBUG) echo "Number of colors existing in $background : ".imagecolorstotal($im)."<br>";
		} else {
			error_display("$background specified in configuration file has not been found. Check use of BACKGROUND directive.");
			exit;
		}
	}

	# Define main colors of GD image
	define_colors($im);
	# Display icons
	if ($iconpng) {
		foreach ($iconpng as $node => $i) {
			if ($iconpng[$node]) {
				if (file_exists($iconpng[$node])) {
					$icone = @imagecreatefrompng($iconpng[$node]);
					$iconwidth=imagesx($icone);
					$iconheight=imagesy($icone);
					$factor=$iconresize[$node]/100;
					if (! $factor ) {
						$factor=1;
					}
					$icone2=imagecreate($iconwidth*$factor,$iconheight*$factor);
					$white2=imagecolorallocate($icone2, 255, 255, 255);
					imagecolortransparent($icone2,$white2);
					imagecopyresized($icone2,$icone,0,0,0,0,$iconwidth*$factor,$iconheight*$factor,$iconwidth,$iconheight);

					if ($iconx[$node]==0) {
						$iconx[$node] = $posx[$node]-$iconwidth*$factor/2;
						$icony[$node] = $posy[$node]-$iconheight*$factor/2;
					}
				
					if ($icon_transparent[$node]==0) {
						$icon_transparent[$node]= 100;
					}
					imagecopymerge($im,$icone2,$iconx[$node],$icony[$node],0,0,$iconwidth*$factor,$iconheight*$factor,$icon_transparent[$node]);
				} else {
					error_display("File \"".$iconpng[$node]."\" not found. Icon won't be displayed on graph. Please check use of ICON directive.");
					exit;
				}
			}
		}
	}

	# Draw as many links as defined in configuration file
	foreach ($target as $link => $i) {
	if ( (! $filter) || ($filter == $group_name[$link]) ) {

		# input link
		if (  (basename($targetin[$link],".htm") != basename($targetin[$link]))  || (basename($targetin[$link],".html")!=basename($targetin[$link])) || $forcemrtg[$link]) {
			if ($DEBUG) echo "MRTG Target in reading...$targetin[$link]";
			$handle = fopen($targetin[$link], "r") or die("File $targetin[$link] not found or unreachable");

			while (!feof($handle)) {
		        $line=fgets($handle);
				if (preg_match("/^<\!-- cuin d (\d+) -->/i",$line,$out)) {
					$input[$link]=$out[1]*$coef[$link];
				}
			}
			fclose($handle);
		} else {
			# Read RRDTool values for current link
			# If no date are specified, use last date stored in RRD database. 
			if ($DEBUG) echo "RRDTool binary version detected : ".rrdtool_getversion()."<br>";

			if (empty($date) ) {
				$start=rrdtool_last($targetin[$link]);
				$unixtime=$start;
				if ($DEBUG) echo "Last time value detected in rrd file : ".$start."<br>";
				if (rrdtool_getversion() >= 1.2) { 
					$end=$start;
					$start=$start-$rrdstep; 
				} else {
					$end=$start;
				}
			} else {
				$start=$date;
				$end=$date;
			}
			$result=rrdtool_function_fetch($targetin[$link],$start,$end);

			# Get values from array read before and convert values in bytes
			$input[$link]=$result["values"][$inpos[$link]-1][0]*$coef[$link];
		}
		
		# output link
		if (  (basename($targetout[$link],".htm") != basename($targetout[$link]))  || (basename($targetout[$link],".html")!=basename($targetout[$link])) || $forcemrtg[$link]) {
			if ($DEBUG) echo "MRTG Target out reading...$targetout[$link]";
			$handle = fopen($targetout[$link], "r") or die("File $targetout[$link] not found or unreachable");

			while (!feof($handle)) {
		        $line=fgets($handle);
				if (preg_match("/^<\!-- cuout d (\d+) -->/i",$line,$out)) {
					$output[$link]=$out[1]*$coef[$link];
				}
			}
			fclose($handle);


			
		} else {
			# Read RRDTool values for current link
			# If no date are specified, use last date stored in RRD database. 
			if ($DEBUG) echo "RRDTool binary version detected : ".rrdtool_getversion()."<br>";

			if (empty($date) ) {
				$start=rrdtool_last($targetout[$link]);
				$unixtime=$start;
				if ($DEBUG) echo "Last time value detected in rrd file : ".$start."<br>";
				if (rrdtool_getversion() >= 1.2) { 
					$end=$start;
					$start=$start-$rrdstep; 
				} else {
					$end=$start;
				}
#				echo "****************** last time used=$start  ************************ <br>";
			} else {
				$start=$date;
				$end=$date;
			}
			$result=rrdtool_function_fetch($targetout[$link],$start,$end);

			# Get values from array read before and convert values in bytes
			$output[$link]=$result["values"][$outpos[$link]-1][0]*$coef[$link];
		}


		# # # #

		#echo "input=$input[$link]<br>";
		#echo "output=$output[$link]<br>";
		if ( (int)(($output[$link]/$maxbytesout[$link]+0.005)*100) > 100 ) {
			$outrate=100;
		} else {
			$outrate=(int)(($output[$link]/$maxbytesout[$link]+0.005)*100);
		}

		if ( (int)(($input[$link]/$maxbytesin[$link]+0.005)*100) > 100 ) {
			$inrate=100;
		} else {
			$inrate=(int)(($input[$link]/$maxbytesin[$link]+0.005)*100);
		}

		# Not to display 0% if it is not exactly 0. It will display 1%
		if($output[$link] != 0 && $outrate == 0)  $outrate=1; 
		if($input[$link] != 0 && $inrate == 0) $inrate=1;

		if ($DEBUG) echo $targetin[$link].": in=".$input[$link]."/".$maxbytesin[$link]." out=".$output[$link]."/".$maxbytesout[$link]."<br>";
		if ($DEBUG) echo $targetout[$link].": outrate=".$outrate."%, inrate=".$inrate."%<br>";

		# Display first arrow from node A to node B
				# If any internodes are defined, we have to go thru
				if ($internodes[$link]) {
					switch ($arrow[$link]):
						case "circle":
							if ($internodes[$link]==1) {
								draw_arrow_circle3($posx[$nodea[$link]],$posy[$nodea[$link]],
									$internodex[$link][$internodes[$link]],$internodey[$link][$internodes[$link]],
									$width_arrow,0,1,select_color($im,$outrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue));
								draw_arrow_circle3($posx[$nodea[$link]],$posy[$nodea[$link]],
									$internodex[$link][$internodes[$link]],$internodey[$link][$internodes[$link]],
									$width_arrow,0,0,$black);
								if ($linkoverlibgraph[$link] || $linkinfourl[$link]) {
									draw_arrow_map(
										$posx[$nodea[$link]],$posy[$nodea[$link]],
										$internodex[$link][$internodes[$link]],$internodey[$link][$internodes[$link]],
										$width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
								}

							} else {
								draw_circle3(
											   $internodex[$link][1],$internodey[$link][1],
											   $posx[$nodea[$link]],$posy[$nodea[$link]],
											   $width_arrow,0,1,select_color($im,$outrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue));
								draw_circle3(
											   $internodex[$link][1],$internodey[$link][1],
											   $posx[$nodea[$link]],$posy[$nodea[$link]],
											   $width_arrow,0,0,$black);
								if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) {
									draw_arrow_map(
									   $internodex[$link][1],$internodey[$link][1],
									   $posx[$nodea[$link]],$posy[$nodea[$link]],
	   								   $width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
								}

											   
	
								for ($i=1; $i<floor($internodes[$link]/2);$i++) { 
									draw_circle3(
											   $internodex[$link][$i],$internodey[$link][$i],
											   $internodex[$link][$i+1],$internodey[$link][$i+1],
											   $width_arrow,0,1,select_color($im,$outrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue));
									draw_circle3(
											   $internodex[$link][$i],$internodey[$link][$i],
											   $internodex[$link][$i+1],$internodey[$link][$i+1],
											   $width_arrow,0,0,$black);
									if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) {
										draw_arrow_map(
											$internodex[$link][$i],$internodey[$link][$i],
											$internodex[$link][$i+1],$internodey[$link][$i+1],
											$width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
									}

								}

								if ($internodes[$link]%2) {
									# Draw arrow to middle internode
									draw_arrow_circle3(
												$internodex[$link][$i],$internodey[$link][$i],
												$internodex[$link][ceil($internodes[$link]/2)],$internodey[$link][ceil($internodes[$link]/2)],
												$width_arrow,0,1,select_color($im,$outrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue));
									draw_arrow_circle3(
												$internodex[$link][$i],$internodey[$link][$i],
												$internodex[$link][ceil($internodes[$link]/2)],$internodey[$link][ceil($internodes[$link]/2)],
												$width_arrow,0,0,$black);
									if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) {
										draw_arrow_map(
											$internodex[$link][$i],$internodey[$link][$i],
											$internodex[$link][ceil($internodes[$link]/2)],$internodey[$link][ceil($internodes[$link]/2)],
											$width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
									}

								} else {
									# Draw arrow to middle of central internodes
									draw_arrow_circle3(
										$internodex[$link][$i],$internodey[$link][$i],
										middle($internodex[$link][$i],$internodex[$link][$i+1]),
										middle($internodey[$link][$i],$internodey[$link][$i+1]),
										$width_arrow,0,1,select_color($im,$outrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue));
									draw_arrow_circle3(
										$internodex[$link][$i],$internodey[$link][$i],
										middle($internodex[$link][$i],$internodex[$link][$i+1]),
										middle($internodey[$link][$i],$internodey[$link][$i+1]),
										$width_arrow,0,0,$black);
									if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) {
										draw_arrow_map(
											$internodex[$link][$i],$internodey[$link][$i],
											middle($internodex[$link][$i],$internodex[$link][$i+1]),
											middle($internodey[$link][$i],$internodey[$link][$i+1]),
											$width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
									}

								}
							}
						break;
						case "halfarrow":	
								draw_rectangle_half(
									$posx[$nodea[$link]],$posy[$nodea[$link]],
									$internodex[$link][1],$internodey[$link][1],
									$width_arrow,1,select_color($im,$outrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue));
									if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) {
											draw_rectangle_half_map(
											$posx[$nodea[$link]],$posy[$nodea[$link]],
											$internodex[$link][1],$internodey[$link][1],
											$width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
									}

						
								for ($i=1; $i<$internodes[$link];$i++) { 
									draw_rectangle_half(
										$internodex[$link][$i],$internodey[$link][$i],
										$internodex[$link][$i+1],$internodey[$link][$i+1],
										$width_arrow,1,select_color($im,$outrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue));
										if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) {
												draw_rectangle_half_map(
												$internodex[$link][$i],$internodey[$link][$i],
												$internodex[$link][$i+1],$internodey[$link][$i+1],
												$width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
										}

								}
								draw_arrow_half(
									$internodex[$link][$i],$internodey[$link][$i],
									$posx[$nodeb[$link]],$posy[$nodeb[$link]],
									$width_arrow,1,select_color($im,$outrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue));
									if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) {
										draw_arrow_half_map(
										$internodex[$link][$i],$internodey[$link][$i],
										$posx[$nodeb[$link]],$posy[$nodeb[$link]],
										$width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
									}


						break;

						default:
							if ($internodes[$link]==1) {
								draw_arrow($posx[$nodea[$link]],$posy[$nodea[$link]],
									$internodex[$link][$internodes[$link]],$internodey[$link][$internodes[$link]],
									$width_arrow,1,select_color($im,$outrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue));
								draw_arrow($posx[$nodea[$link]],$posy[$nodea[$link]],
									$internodex[$link][$internodes[$link]],$internodey[$link][$internodes[$link]],
									$width_arrow,0,$black);
								if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) {
									draw_arrow_map($posx[$nodea[$link]],$posy[$nodea[$link]],
									$internodex[$link][$internodes[$link]],$internodey[$link][$internodes[$link]],
									$width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
								}
							} else {
								# Default mode is "normal" arrow type
								draw_rectangle($posx[$nodea[$link]],$posy[$nodea[$link]],
											   $internodex[$link][1],$internodey[$link][1],
											   $width_arrow,1,select_color($im,$outrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue));
								draw_rectangle($posx[$nodea[$link]],$posy[$nodea[$link]],
											   $internodex[$link][1],$internodey[$link][1],
											   $width_arrow,0,$black);
								if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) { 
											   draw_rectangle_map($posx[$nodea[$link]],$posy[$nodea[$link]],
											   $internodex[$link][1],$internodey[$link][1],
											   $width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
								}

	
								for ($i=1; $i<floor($internodes[$link]/2);$i++) { 
									draw_rectangle(
											   $internodex[$link][$i],$internodey[$link][$i],
											   $internodex[$link][$i+1],$internodey[$link][$i+1],
											   $width_arrow,1,select_color($im,$outrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue));
									draw_rectangle(
											   $internodex[$link][$i],$internodey[$link][$i],
											   $internodex[$link][$i+1],$internodey[$link][$i+1],
											   $width_arrow,0,$black);
									if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) { 
											   draw_rectangle_map(
											   $internodex[$link][$i],$internodey[$link][$i],
											   $internodex[$link][$i+1],$internodey[$link][$i+1],
											   $width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
									}
								}

								if ($internodes[$link]%2) {
									# Draw arrow to middle internode
									draw_arrow(
												$internodex[$link][$i],$internodey[$link][$i],
												$internodex[$link][ceil($internodes[$link]/2)],$internodey[$link][ceil($internodes[$link]/2)],
												$width_arrow,1,select_color($im,$outrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue));
									draw_arrow(
												$internodex[$link][$i],$internodey[$link][$i],
												$internodex[$link][ceil($internodes[$link]/2)],$internodey[$link][ceil($internodes[$link]/2)],
												$width_arrow,0,$black);
									if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) {
										draw_arrow_map(
												$internodex[$link][$i],$internodey[$link][$i],
												$internodex[$link][ceil($internodes[$link]/2)],$internodey[$link][ceil($internodes[$link]/2)],
												$width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
									}
								} else {
									# Draw arrow to middle of central internodes
									draw_arrow(
										$internodex[$link][$i],$internodey[$link][$i],
										middle($internodex[$link][$i],$internodex[$link][$i+1]),
										middle($internodey[$link][$i],$internodey[$link][$i+1]),
										$width_arrow,1,select_color($im,$outrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue));
									draw_arrow(
										$internodex[$link][$i],$internodey[$link][$i],
										middle($internodex[$link][$i],$internodex[$link][$i+1]),
										middle($internodey[$link][$i],$internodey[$link][$i+1]),
										$width_arrow,0,$black);
									if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) {
										draw_arrow_map(
										$internodex[$link][$i],$internodey[$link][$i],
										middle($internodex[$link][$i],$internodex[$link][$i+1]),
										middle($internodey[$link][$i],$internodey[$link][$i+1]),
										$width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
									}
								}
						}
						break;
					endswitch;
					# Display bandwidth % links from node A to node B
					if ($arrow[$link]=="halfarrow") {
						label($im,"$outrate%",
							$posx[$nodeb[$link]]+newx(
												$posx[$nodeb[$link]]-$internodex[$link][$internodes[$link]],
												$posy[$nodeb[$link]]-$internodey[$link][$internodes[$link]],
												-10*$width_arrow,-4*$width_arrow),
							$posy[$nodeb[$link]]+newy(
												$posx[$nodeb[$link]]-$internodex[$link][$internodes[$link]],
												$posy[$nodeb[$link]]-$internodey[$link][$internodes[$link]],
												-10*$width_arrow,-4*$width_arrow),
							$font-1,$black,$white);
					} else {
						label($im,"$outrate%",
								middle($posx[$nodea[$link]],$internodex[$link][1]),
								middle($posy[$nodea[$link]],$internodey[$link][1]),
								$font,$black,$white);
					}
					if ($DEBUG) echo "displayvalue[$link]=".$displayvalue[$link]."<br>";

					if ($displayvalue[$link]) {
							if ($output[$link] >=125000) {
								$coefdisplay=8/(1000*1000);
								$unitdisplay="Mbits";
							} else {
								$coefdisplay=8/1000;
								$unitdisplay="Kbits";
							}
							$todisplay=round($output[$link]*$coefdisplay,1). "$unitdisplay";

							if ($arrow[$link]=="halfarrow") {
								if ($posy[$nodeb[$link]] < $internodey[$link][$internodes[$link]]) {
									$factor=1; 
								} else {
									$factor=-1;
								}
								
								label($im,"$todisplay",
									$posx[$nodeb[$link]]+newx(
												$posx[$nodeb[$link]]-$internodex[$link][$internodes[$link]],
												$posy[$nodeb[$link]]-$internodey[$link][$internodes[$link]],
												-10*$width_arrow,-4*$width_arrow)-$factor*imagefontwidth($font-1)*(strlen($todisplay)-strlen("$outrate%"))/2,
									$posy[$nodeb[$link]]+newy(
												$posx[$nodeb[$link]]-$internodex[$link][$internodes[$link]],
												$posy[$nodeb[$link]]-$internodey[$link][$internodes[$link]],
												-10*$width_arrow,-4*$width_arrow)+imagefontheight($font-1)+($width_arrow+1),
									$font-1,$black,$white);

							} else {
								label($im,"$todisplay",
									middle($posx[$nodea[$link]],$internodex[$link][1]),
									middle($posy[$nodea[$link]],$internodey[$link][1])+19,
									$font,$black,$white);
							}
					}

				} else {
					# else we can draw a simple arrow
					switch ($arrow[$link]):
						case "circle":
							draw_arrow_circle3($posx[$nodea[$link]],$posy[$nodea[$link]],
								middle($posx[$nodea[$link]],$posx[$nodeb[$link]]),middle($posy[$nodea[$link]],$posy[$nodeb[$link]]),
								$width_arrow,0,1,select_color($im,$outrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue));
							draw_arrow_circle3($posx[$nodea[$link]],$posy[$nodea[$link]],
								middle($posx[$nodea[$link]],$posx[$nodeb[$link]]),middle($posy[$nodea[$link]],$posy[$nodeb[$link]]),
								$width_arrow,0,0,$black);
							if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) {
								draw_arrow_map(
									$posx[$nodea[$link]],$posy[$nodea[$link]],
									middle($posx[$nodea[$link]],$posx[$nodeb[$link]]),middle($posy[$nodea[$link]],$posy[$nodeb[$link]]),
									$width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
							}

						break;

						case "halfarrow":
							draw_arrow_half(
											$posx[$nodea[$link]],$posy[$nodea[$link]],
											$posx[$nodeb[$link]],$posy[$nodeb[$link]],
							$width_arrow,1,select_color($im,$outrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue));
							if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) {
								draw_arrow_half_map(
									$posx[$nodea[$link]],$posy[$nodea[$link]],
									$posx[$nodeb[$link]],$posy[$nodeb[$link]],
									$width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
							}


						break;


						default:
							draw_arrow($posx[$nodea[$link]],$posy[$nodea[$link]],
							middle($posx[$nodea[$link]],$posx[$nodeb[$link]]),middle($posy[$nodea[$link]],$posy[$nodeb[$link]]),
							$width_arrow,1,select_color($im,$outrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue));
							if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) {
								draw_arrow_map($posx[$nodea[$link]],$posy[$nodea[$link]],
								middle($posx[$nodea[$link]],$posx[$nodeb[$link]]),middle($posy[$nodea[$link]],$posy[$nodeb[$link]]),
								$width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
							}
							
						break;
					endswitch;
					# Display bandwidth % links from node A to node B
					if ($arrow[$link]=="halfarrow") {
								label($im,"$outrate%",
								$posx[$nodeb[$link]]+newx(
													$posx[$nodeb[$link]]-$posx[$nodea[$link]],
													$posy[$nodeb[$link]]-$posy[$nodea[$link]],
													-10*$width_arrow,-3*$width_arrow),
								$posy[$nodeb[$link]]+newy(
													$posx[$nodeb[$link]]-$posx[$nodea[$link]],
													$posy[$nodeb[$link]]-$posy[$nodea[$link]],
													-10*$width_arrow,-3*$width_arrow),
								$font-1,$black,$white);


					} else {
						label($im,"$outrate%",
								middle($posx[$nodea[$link]],middle($posx[$nodea[$link]],$posx[$nodeb[$link]])),
								middle($posy[$nodea[$link]],middle($posy[$nodea[$link]],$posy[$nodeb[$link]])),
								$font,$black,$white);
					}

					if ($DEBUG) echo "displayvalue[$link]=".$displayvalue[$link]."<br>";

					if ($displayvalue[$link]) {
							if ($output[$link] >=125000) {
								$coefdisplay=8/(1000*1000);
								$unitdisplay="Mbits";
							} else {
								$coefdisplay=8/1000;
								$unitdisplay="Kbits";
							}
							$todisplay=round($output[$link]*$coefdisplay,1). "$unitdisplay";

							if ($arrow[$link]=="halfarrow") {
								if ($posy[$nodeb[$link]] < $posy[$nodea[$link]]) {
									$factor=-1; 
								} else {
									$factor=1;
								}

								label($im,"$todisplay",
								$posx[$nodeb[$link]]+newx(
													$posx[$nodeb[$link]]-$posx[$nodea[$link]],
													$posy[$nodeb[$link]]-$posy[$nodea[$link]],
													-10*$width_arrow,-3*$width_arrow)-$factor*imagefontwidth($font-1)*(strlen($todisplay)-strlen("$outrate%"))/2,
								$posy[$nodeb[$link]]+newy(
													$posx[$nodeb[$link]]-$posx[$nodea[$link]],
													$posy[$nodeb[$link]]-$posy[$nodea[$link]],
													-10*$width_arrow,-3*$width_arrow)-imagefontheight($font-1)-5,
								$font-1,$black,$white);

							} else {
								label($im,"$todisplay",middle($posx[$nodea[$link]],middle($posx[$nodea[$link]],$posx[$nodeb[$link]])),
									middle($posy[$nodea[$link]],middle($posy[$nodea[$link]],$posy[$nodeb[$link]]+70)),
									$font,$black,$white);
							}
					} else {
							$todisplay="";
					}

				}

		
		# Display second arrow from node B to node A
		if ($internodes[$link]) {
			switch ($arrow[$link]):
				case "circle":	
					if ($internodes[$link]==1) {
						draw_arrow_circle3($posx[$nodeb[$link]],$posy[$nodeb[$link]],
							$internodex[$link][$internodes[$link]],$internodey[$link][$internodes[$link]],
							$width_arrow,0,1,select_color($im,$inrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue));
						draw_arrow_circle3(
							$posx[$nodeb[$link]],$posy[$nodeb[$link]],
							$internodex[$link][$internodes[$link]],$internodey[$link][$internodes[$link]],
							$width_arrow,0,0,$black);
						if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) {
							draw_arrow_map(
								$posx[$nodeb[$link]],$posy[$nodeb[$link]],
								$internodex[$link][$internodes[$link]],$internodey[$link][$internodes[$link]],
								$width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
						}

					} else {
						draw_circle3($posx[$nodeb[$link]],$posy[$nodeb[$link]],
							$internodex[$link][$internodes[$link]],$internodey[$link][$internodes[$link]],
							$width_arrow,0,1,select_color($im,$inrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue));
						draw_circle3($posx[$nodeb[$link]],$posy[$nodeb[$link]],
							$internodex[$link][$internodes[$link]],$internodey[$link][$internodes[$link]],
							$width_arrow,0,0,$black);
						if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) {
							draw_arrow_map(
								$posx[$nodeb[$link]],$posy[$nodeb[$link]],
								$internodex[$link][$internodes[$link]],$internodey[$link][$internodes[$link]],
								$width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
						}


						for ($i=$internodes[$link]; $i>ceil($internodes[$link]/2)+1;$i--) { 
							draw_circle3(
								$internodex[$link][$i],$internodey[$link][$i],
								$internodex[$link][$i-1],$internodey[$link][$i-1],
								$width_arrow,0,1,select_color($im,$inrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue));
							draw_circle3(
								$internodex[$link][$i],$internodey[$link][$i],
								$internodex[$link][$i-1],$internodey[$link][$i-1],
								$width_arrow,0,0,$black);
							if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) {
								draw_arrow_map(
									$internodex[$link][$i],$internodey[$link][$i],
									$internodex[$link][$i-1],$internodey[$link][$i-1],
									$width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
							}

						}
						if ($internodes[$link]%2) {
								# Draw arrow to middle internode
								draw_arrow_circle3(
									$internodex[$link][$i],$internodey[$link][$i],
									$internodex[$link][ceil($internodes[$link]/2)],$internodey[$link][ceil($internodes[$link]/2)],
									$width_arrow,0,1,select_color($im,$inrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue));
								draw_arrow_circle3(
									$internodex[$link][$i],$internodey[$link][$i],
									$internodex[$link][ceil($internodes[$link]/2)],$internodey[$link][ceil($internodes[$link]/2)],
									$width_arrow,0,0,$black);
								if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) {
									draw_arrow_map(
										$internodex[$link][$i],$internodey[$link][$i],
										$internodex[$link][ceil($internodes[$link]/2)],$internodey[$link][ceil($internodes[$link]/2)],
										$width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
								}

						} else {
								# Draw arrow to middle of central internodes
								draw_arrow_circle3(
									$internodex[$link][$i],$internodey[$link][$i],
									middle($internodex[$link][$i],$internodex[$link][$i-1]),
									middle($internodey[$link][$i],$internodey[$link][$i-1]),
									$width_arrow,0,1,select_color($im,$inrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue));
								draw_arrow_circle3(
									$internodex[$link][$i],$internodey[$link][$i],
									middle($internodex[$link][$i],$internodex[$link][$i-1]),
									middle($internodey[$link][$i],$internodey[$link][$i-1]),
									$width_arrow,0,0,$black);
								if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) {
									draw_arrow_map(
										$internodex[$link][$i],$internodey[$link][$i],
										middle($internodex[$link][$i],$internodex[$link][$i-1]),
										middle($internodey[$link][$i],$internodey[$link][$i-1]),
										$width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
								}

						}
					}	
				break;
				case "halfarrow":	
						draw_rectangle_half($posx[$nodeb[$link]],$posy[$nodeb[$link]],
							$internodex[$link][$internodes[$link]],$internodey[$link][$internodes[$link]],
							$width_arrow,1,select_color($im,$inrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue));
						if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) {
							draw_rectangle_half_map($posx[$nodeb[$link]],$posy[$nodeb[$link]],
							$internodex[$link][$internodes[$link]],$internodey[$link][$internodes[$link]],
							$width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
						}
				
						for ($i=$internodes[$link]; $i>1;$i--) { 
							draw_rectangle_half(
								$internodex[$link][$i],$internodey[$link][$i],
								$internodex[$link][$i-1],$internodey[$link][$i-1],
								$width_arrow,1,select_color($im,$inrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue));
							if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) {
								draw_rectangle_half_map(
									$internodex[$link][$i],$internodey[$link][$i],
									$internodex[$link][$i-1],$internodey[$link][$i-1],
									$width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
							}
						}

						draw_arrow_half(
							$internodex[$link][$i],$internodey[$link][$i],$posx[$nodea[$link]],$posy[$nodea[$link]],
							$width_arrow,1,select_color($im,$inrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue));
						if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) {
							draw_arrow_half_map(
							$internodex[$link][$i],$internodey[$link][$i],$posx[$nodea[$link]],$posy[$nodea[$link]],
								$width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
						}

				break;

				default:
					if ($internodes[$link]==1) {
						draw_arrow($posx[$nodeb[$link]],$posy[$nodeb[$link]],
							$internodex[$link][$internodes[$link]],$internodey[$link][$internodes[$link]],
							$width_arrow,1,select_color($im,$inrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue));
						draw_arrow($posx[$nodeb[$link]],$posy[$nodeb[$link]],
							$internodex[$link][$internodes[$link]],$internodey[$link][$internodes[$link]],
							$width_arrow,0,$black);
						if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) {
							draw_arrow_map($posx[$nodeb[$link]],$posy[$nodeb[$link]],
							$internodex[$link][$internodes[$link]],$internodey[$link][$internodes[$link]],
							$width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
						}
	
					} else {

						# Default mode is "normal" arrow type
						draw_rectangle($posx[$nodeb[$link]],$posy[$nodeb[$link]],
							$internodex[$link][$internodes[$link]],$internodey[$link][$internodes[$link]],
							$width_arrow,1,select_color($im,$inrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue));
						draw_rectangle($posx[$nodeb[$link]],$posy[$nodeb[$link]],
							$internodex[$link][$internodes[$link]],$internodey[$link][$internodes[$link]],
							$width_arrow,0,$black);
						if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) { 
							draw_rectangle_map($posx[$nodeb[$link]],$posy[$nodeb[$link]],
							$internodex[$link][$internodes[$link]],$internodey[$link][$internodes[$link]],
							$width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
						}


						for ($i=$internodes[$link]; $i>ceil($internodes[$link]/2)+1;$i--) { 
							draw_rectangle(
								$internodex[$link][$i],$internodey[$link][$i],
								$internodex[$link][$i-1],$internodey[$link][$i-1],
								$width_arrow,1,select_color($im,$inrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue));
							draw_rectangle(
								$internodex[$link][$i],$internodey[$link][$i],
								$internodex[$link][$i-1],$internodey[$link][$i-1],
								$width_arrow,0,$black);
							if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) { 
								draw_rectangle_map(
								$internodex[$link][$i],$internodey[$link][$i],
								$internodex[$link][$i-1],$internodey[$link][$i-1],
								$width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
							}

						}
						if ($internodes[$link]%2) {
								# Draw arrow to middle internode
								draw_arrow(
									$internodex[$link][$i],$internodey[$link][$i],
									$internodex[$link][ceil($internodes[$link]/2)],$internodey[$link][ceil($internodes[$link]/2)],
									$width_arrow,1,select_color($im,$inrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue));
								draw_arrow(
									$internodex[$link][$i],$internodey[$link][$i],
									$internodex[$link][ceil($internodes[$link]/2)],$internodey[$link][ceil($internodes[$link]/2)],
									$width_arrow,0,$black);
								if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) {
									draw_arrow_map(
									$internodex[$link][$i],$internodey[$link][$i],
									$internodex[$link][ceil($internodes[$link]/2)],$internodey[$link][ceil($internodes[$link]/2)],
									$width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
								}
						} else {
								# Draw arrow to middle of central internodes
								draw_arrow(
									$internodex[$link][$i],$internodey[$link][$i],
									middle($internodex[$link][$i],$internodex[$link][$i-1]),
									middle($internodey[$link][$i],$internodey[$link][$i-1]),
									$width_arrow,1,select_color($im,$inrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue));
								draw_arrow(
									$internodex[$link][$i],$internodey[$link][$i],
									middle($internodex[$link][$i],$internodex[$link][$i-1]),
									middle($internodey[$link][$i],$internodey[$link][$i-1]),
									$width_arrow,0,$black);
								if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) {
									draw_arrow_map(
									$internodex[$link][$i],$internodey[$link][$i],
									middle($internodex[$link][$i],$internodex[$link][$i-1]),
									middle($internodey[$link][$i],$internodey[$link][$i-1]),
									$width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
								}
						}
					}	
				break;
			endswitch;
			# Display bandwidth % links from node B to node A
			if ($arrow[$link]=="halfarrow") {
								label($im,"$inrate%",
								$posx[$nodea[$link]]+newx(
													$posx[$nodea[$link]]-$internodex[$link][1],
													$posy[$nodea[$link]]-$internodey[$link][1],
													-15*$width_arrow,-5*$width_arrow),
								$posy[$nodea[$link]]+newy(
													$posx[$nodea[$link]]-$internodex[$link][1],
													$posy[$nodea[$link]]-$internodey[$link][1],
													-15*$width_arrow,-5*$width_arrow),
								$font-1,$black,$white);

			} else {
				label($im,"$inrate%",
						middle($posx[$nodeb[$link]],$internodex[$link][$internodes[$link]]),
						middle($posy[$nodeb[$link]],$internodey[$link][$internodes[$link]]),
						$font,$black,$white);
			}
			if ($displayvalue[$link]) {
					if ($input[$link] >=125999) {
						$coefdisplay=8/(1000*1000);
						$unitdisplay="Mbits";
					} else {
						$coefdisplay=8/1000;
						$unitdisplay="Kbits";
					}
					$todisplay=round($input[$link]*$coefdisplay,1). "$unitdisplay";

					if ($arrow[$link]=="halfarrow") {
								if ($posy[$nodea[$link]] < $internodey[$link][1]) {
									$factor=-1; 
								} else {
									$factor=1;
								}

								label($im,"$todisplay",
								$posx[$nodea[$link]]+newx(
													$posx[$nodea[$link]]-$internodex[$link][1],
													$posy[$nodea[$link]]-$internodey[$link][1],
													-15*$width_arrow,-5*$width_arrow)+$factor*imagefontwidth($font-1)*(strlen($todisplay)-strlen("$inrate%"))/2,
								$posy[$nodea[$link]]+newy(
													$posx[$nodea[$link]]-$internodex[$link][1],
													$posy[$nodea[$link]]-$internodey[$link][1],
													-15*$width_arrow,-5*$width_arrow)+imagefontheight($font-1)+5,
								$font-1,$black,$white);
					} else {
						label($im,"$todisplay",
							middle($posx[$nodeb[$link]],$internodex[$link][$internodes[$link]]),
							middle($posy[$nodeb[$link]],$internodey[$link][$internodes[$link]])+19,
							$font,$black,$white);
					}
			}

		
		} else {
			# else we can draw a simple arrow
			switch ($arrow[$link]):
				case "circle":
					draw_arrow_circle3($posx[$nodeb[$link]],$posy[$nodeb[$link]],
						middle($posx[$nodea[$link]],$posx[$nodeb[$link]]),middle($posy[$nodea[$link]],$posy[$nodeb[$link]]),
						$width_arrow,0,1,select_color($im,$inrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue));
					draw_arrow_circle3($posx[$nodeb[$link]],$posy[$nodeb[$link]],
						middle($posx[$nodea[$link]],$posx[$nodeb[$link]]),middle($posy[$nodea[$link]],$posy[$nodeb[$link]]),
						$width_arrow,0,0,$black);
					if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) {
						draw_arrow_map(
							$posx[$nodeb[$link]],$posy[$nodeb[$link]],
							middle($posx[$nodea[$link]],$posx[$nodeb[$link]]),middle($posy[$nodea[$link]],$posy[$nodeb[$link]]),
							$width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
					}

					break;
				case "halfarrow":
					draw_arrow_half(
									$posx[$nodeb[$link]],$posy[$nodeb[$link]],
								    $posx[$nodea[$link]],$posy[$nodea[$link]],
						$width_arrow,1,select_color($im,$inrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue),
						"normal");
					if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) {
							draw_arrow_half_map(
							$posx[$nodeb[$link]],$posy[$nodeb[$link]],
							$posx[$nodea[$link]],$posy[$nodea[$link]],
							$width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
					}
				break;
				
				default:
					# Default mode is "normal" arrow
					draw_arrow($posx[$nodeb[$link]],$posy[$nodeb[$link]],
						middle($posx[$nodea[$link]],$posx[$nodeb[$link]]),middle($posy[$nodea[$link]],$posy[$nodeb[$link]]),
						$width_arrow,1,select_color($im,$inrate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue),
						"normal");
					if ( ($linkoverlibgraph[$link]) || ($linkinfourl[$link]) ) {
						draw_arrow_map($posx[$nodeb[$link]],$posy[$nodeb[$link]],
						middle($posx[$nodea[$link]],$posx[$nodeb[$link]]),middle($posy[$nodea[$link]],$posy[$nodeb[$link]]),
						$width_arrow,$link,$linkoverlibgraph[$link],$linkinfourl[$link]);
					}
					break;
			endswitch;
			# Display bandwidth % links from node B to node A
			if ($arrow[$link]=="halfarrow") {
								label($im,"$inrate%",
								$posx[$nodea[$link]]+newx(
													$posx[$nodea[$link]]-$posx[$nodeb[$link]],
													$posy[$nodea[$link]]-$posy[$nodeb[$link]],
													-10*$width_arrow,-3*$width_arrow),
								$posy[$nodea[$link]]+newy(
													$posx[$nodea[$link]]-$posx[$nodeb[$link]],
													$posy[$nodea[$link]]-$posy[$nodeb[$link]],
													-10*$width_arrow,-3*$width_arrow),
								$font-1,$black,$white);

			} else {
				label($im,"$inrate%",
							middle($posx[$nodeb[$link]],middle($posx[$nodea[$link]],$posx[$nodeb[$link]])),
							middle($posy[$nodeb[$link]],middle($posy[$nodea[$link]],$posy[$nodeb[$link]])),
							$font,$black,$white);
			}
			if ($displayvalue[$link]) {
					if ($input[$link] >=125999) {
						$coefdisplay=8/(1000*1000);
						$unitdisplay="Mbits";
					} else {
						$coefdisplay=8/1000;
						$unitdisplay="Kbits";
					}
					$todisplay=round($input[$link]*$coefdisplay,1). "$unitdisplay";
					if ($arrow[$link]=="halfarrow") {
								if ($posy[$nodea[$link]] < $posy[$nodeb[$link]]) {
									$factor=-1; 
								} else {
									$factor=1;
								}

								label($im,"$todisplay",
								$posx[$nodea[$link]]+newx(
													$posx[$nodea[$link]]-$posx[$nodeb[$link]],
													$posy[$nodea[$link]]-$posy[$nodeb[$link]],
													-10*$width_arrow,-3*$width_arrow)+$factor*imagefontwidth($font-1)*(strlen($todisplay)-strlen("$outrate%"))/2,
								$posy[$nodea[$link]]+newy(
													$posx[$nodea[$link]]-$posx[$nodeb[$link]],
													$posy[$nodea[$link]]-$posy[$nodeb[$link]],
													-10*$width_arrow,-3*$width_arrow)+imagefontheight($font-1)+($width_arrow+1),
								$font-1,$black,$white);
					} else {
						label($im,"$todisplay",
							middle($posx[$nodeb[$link]],middle($posx[$nodea[$link]],$posx[$nodeb[$link]])),
							middle($posy[$nodeb[$link]],middle($posy[$nodea[$link]],$posy[$nodeb[$link]]+70)),
							$font,$black,$white);
					}
			} else {
					$todisplay="";
			}
		}

		# Display internodes
		if ($internodedisplay[$link]) {
			if (!$internodenumdisp[$link]) {
				$internodenumdisp[$link]=0;
			}
			for ($i=1; $i<=($internodes[$link]); $i++) {
				if  ( !((! $internodedisplaymid[$link]) && ($i==($internodes[$link]+1)/2) && ($internodes[$link] %2 <> 0) )) {
					$gdinternode=draw_internode($i,$font,$black,$white,$internodenumdisp[$link]);
					imagecopymerge($im,$gdinternode,$internodex[$link][$i]-imagesx($gdinternode)/2,$internodey[$link][$i]-imagesy($gdinternode)/2,0,0,imagesx($gdinternode),imagesy($gdinternode),$internodedisplay{$link});
				}
			}
		}
	}
	}

	# Display all node labels
	foreach ($posx as $node => $i) {
		if ($check[$node] && $ipcheck) {
			switch ($check[$node]) {
				case "ping":
					if (ping($ip[$node])) {
						switch ($labeltype[$node]) :
							case "round" :
								label_round($im,$label[$node],$posx[$node],$posy[$node],$font,$black,$green);
							break;
							default :
								if ($label[$node]) { 
										$labeltmp=labelv2($im,$label[$node],$font,$black,$green);
										imagecopymerge($im,$labeltmp,
														$posx[$node]-imagesx($labeltmp)/2,
														$posy[$node]-imagesy($labeltmp)/2,
														0,0,
														imagesx($labeltmp),imagesy($labeltmp),($labeltpt[$node]>0) ? $labeltpt[$node]:100);
								}
							break;
						endswitch;
					} else {
						switch ($labeltype[$node]) :
							case "round" :
								label_round($im,$label[$node],$posx[$node],$posy[$node],$font,$black,$red);
							break;
							default :
								if ($label[$node]) { 
										$labeltmp=labelv2($im,$label[$node],$font,$black,$red);
										imagecopymerge($im,$labeltmp,
														$posx[$node]-imagesx($labeltmp)/2,
														$posy[$node]-imagesy($labeltmp)/2,
														0,0,
														imagesx($labeltmp),imagesy($labeltmp),($labeltpt[$node]>0) ? $labeltpt[$node]:100);
								}

							break;
						endswitch;

					}
				break;
				case "tcp":
					if (checkservice($ip[$node],$checkport[$node])) {
						switch ($labeltype[$node]) :
							case "round" :
								label_round($im,$label[$node],$posx[$node],$posy[$node],$font,$black,$green);
							break;
							default :
								if ($label[$node]) { 
										$labeltmp=labelv2($im,$label[$node],$font,$black,$green);
										imagecopymerge($im,$labeltmp,
														$posx[$node]-imagesx($labeltmp)/2,
														$posy[$node]-imagesy($labeltmp)/2,
														0,0,
														imagesx($labeltmp),imagesy($labeltmp),($labeltpt[$node]>0) ? $labeltpt[$node]:100);
								}

							break;
						endswitch;

					} else {
						switch ($labeltype[$node]) :
							case "round" :
								label_round($im,$label[$node],$posx[$node],$posy[$node],$font,$black,$red);
							break;
							default :
								if ($label[$node]) { 
										$labeltmp=labelv2($im,$label[$node],$font,$black,$red);
										imagecopymerge($im,$labeltmp,
														$posx[$node]-imagesx($labeltmp)/2,
														$posy[$node]-imagesy($labeltmp)/2,
														0,0,
														imagesx($labeltmp),imagesy($labeltmp),($labeltpt[$node]>0) ? $labeltpt[$node]:100);
														
								}
							break;
						endswitch;

					}

				    break;
			}
		} else {
			switch ($labeltype[$node]) :
				case "round" :
					label_round($im,$label[$node],$posx[$node],$posy[$node],$font,$black,$white);
				break;
				default :
					label($im,$label[$node],$posx[$node],$posy[$node],$font,$black,$white);
				break;
				endswitch;
		}
	}
	

	# Display title of map
	$gdtitle=draw_title($titlegraph,$font,$titleforeground_red,$titleforeground_green,$titleforeground_blue,$titlebackground_red,$titlebackground_green,$titlebackground_blue,$unixtime);
	imagecopy($im,$gdtitle,$titlexpos,$titleypos-1,0,0,imagesx($gdtitle),imagesy($gdtitle));

	# Display legend of map
	switch ($legendstyle) {
		case "line":
				$gdlegend=draw_legend2("Link load",$font,$scale_low,$scale_high,$scale_red, $scale_green, $scale_blue);
				break;
		default:
				$gdlegend=draw_legend("Traffic load",$font,$scale_low,$scale_high,$scale_red, $scale_green, $scale_blue);
		break;
	}
	imagecopy($im,$gdlegend,$keyxpos,$keyypos,0,0,imagesx($gdlegend),imagesy($gdlegend));

	# write png file to disk if OUTPUTFILE directive is used
	if ($OUTPUTFILE) {
		imagepng($im,$OUTPUTFILE);
	}


	if ( strlen($linkoverlibgraph) || strlen($nodeoverlibgraph) ) {
		if (! $HTMLFILE) {
			error_display("HTMLFILE directive is not correctly defined in configuration.");
			exit;
		}
		
			if (!$handle = fopen($HTMLFILE, 'w')) {
			         echo "Unable to open/create file ($HTMLFILE)";
					 exit;
			}
			$content.='<MAP NAME="weathermap_imap">';
			$content.=$html_map."";
			$content.='</MAP>';

			if (fwrite($handle, $content) == FALSE) {
			       echo "Unable to write in file ($HTMLFILE)";
				   exit;
			}
			fclose($handle);
	}

	# Generate PNG file for browser
	header('Content-type: image/png');
	// HTTP/1.1
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	// HTTP/1.0
	header("Pragma: no-cache");
	imagepng($im);
	imagedestroy($im);
?>
