#!/usr/bin/php-cgi
<?php

function read_config($filename) {

	# Global variables
	global $DEBUG;
	global $scalecolor;
	global $configfile;

	# Global directives
	global $background, $backgroundcolor_red,$backgroundcolor_green,$backgroundcolor_blue, $font;
	global $titlexpos, $titleypos, $titlegraph, $titleforeground, $titlebackground;
	global $keyxpos, $keyypos, $legendxpos, $legendypos, $legendstyle;
	global $scale_low, $scale_high, $scale_red, $scale_green, $scale_blue;
	global $titleforeground_red,$titleforeground_green,$titleforeground_blue;
	global $titlebackground_red,$titlebackground_green,$titlebackground_blue;
	global $OUTPUTFILE, $HTMLFILE;
	global $ipcheck,$refresh_display;

	# NODE directives
	global $node,$posx,$posy,$height,$width,$label,$labeltype,$labeltpt,$iconpng,$iconx,$icony,$iconresize,$icon_transparent;
	global $nodeoverlibgraph,$nodeinfourl;
	global $ip,$check,$checkport;

	# LINK directives
	global $link,$nodea,$nodeb,$displayvalue;
	global $bandwidth,$maxbytesin,$maxbytesout;
	global $target,$targetin,$targetout,$forcemrtg,$inpos,$outpos,$unit,$coef;
	global $arrow,$group_name;
	global $internodes,$internodex,$internodey,$internodedisplay,$internodenumdisp,$internodedisplaymid;
	global $linkoverlibgraph,$linkinfourl;

	$HTMLFILE = "";

	if (! file_exists($filename)) {
		error_display("file $filename not found. You should check that $filename file exists."); 
		exit;
	} else {
		$lines=file($filename);
		$autoscale=0;
		foreach ($lines as $line_num => $line) {
			if ($DEBUG) { echo 'Ligne No <strong>' . $line_num . '</strong> : ' . $line . '<br />'."\n"; }
		
			# Read background if one is specified
			if (preg_match("/^\s*\bBACKGROUND\b\s+(\S+)/i",$line,$out)) {
				$background=$out[1];
				if ($DEBUG) echo "<font color=red>configuration : BACKGROUND directive detected : " .$background . "</font><br>";
			} 

			# Define backgroundcolor
			if (preg_match("/^\s*\bBACKGROUNDCOLOR\b\s+(\d+)\s+(\d+)\s+(\d+)/i",$line,$out)) {
				$backgroundcolor_red=$out[1];
				$backgroundcolor_green=$out[2];
				$backgroundcolor_blue=$out[3];
				if ($DEBUG) echo "<font color=red>configuration : BACKGROUNDCOLOR directive detected : ($backgroundcolor_red,$backgroundcolor_green,$backgroundcolor_blue)</font><br>";
			}
			
			if (preg_match("/^\s*\bREFRESH\b\s+(\d+)/i",$line,$out)) {
				$refresh_display=$out[1];
				if ($DEBUG) echo "<font color=red>configuration : REFRESH directive detected : " .$refresh_display. "</font><br>";
			}


			# Definition of height and width 
			if (preg_match("/^\s*\bWIDTH\b\s+(\d+)/i",$line,$out)) {
				$width=$out[1];
				if ($DEBUG) echo "<font color=red>configuration : WIDTH directive detected : " .$width. "</font><br>";
			}
			if (preg_match("/^\s*\bHEIGHT\b\s+(\d+)/i",$line,$out)) {
				$height=$out[1];
				if ($DEBUG) echo "<font color=red>configuration : HEIGHT directive detected : " .$height. "</font><br>";
			}
			
			if (preg_match("/^\s*\bFONT\b\s+(\d+)/i",$line,$out)) {
				$font=$out[1];
				if ($DEBUG) echo "<font color=red>configuration : FONT directive detected : " .$font . "</font><br>";
			}
			if (preg_match("/^\s*\bCNT_WIDTH_ARROW_BASE\b\s+(\d+)/i",$line,$out)) {
				$CNT_WIDTH_ARROW_BASE=$out[1];
				if ($DEBUG) echo "<font color=red>configuration : CNT_WIDTH_ARROW_BASE directive detected : " .$CNT_WIDTH_ARROW_BASE . "</font><br>";
			}
			if (preg_match("/^\s*\bTITLE\b\s+\"(.+)\"/i",$line,$out)) {
				$titlegraph=$out[1];
				if ($DEBUG) echo "<font color=red>configuration : TITLE directive detected : " .$titlegraph. "</font><br>";
			}
			if (preg_match("/^\s*\bTITLEPOS\b\s+(\d+)\s+(\d+)/i",$line,$out)) {
				$titlexpos=$out[1];
				$titleypos=$out[2];
				if ($DEBUG) echo "<font color=red>configuration : TITLEPOS directive detected : (" .$titlexpos.",".$titleypos.")</font><br>";
			}

			if (preg_match("/^\s*\bTITLEBACKGROUND\b\s+(\d+)\s+(\d+)\s+(\d+)/i",$line,$out)) {
				$titlebackground_red=$out[1];
				$titlebackground_green=$out[2];
				$titlebackground_blue=$out[3];
				if ($DEBUG) echo "<font color=red>configuration : TITLEBACKGROUND directive detected : $titlebackground_red $titlebackground_green $titlebackground_blue</font><br>";
			}
			if (preg_match("/^\s*\bTITLEFOREGROUND\b\s+(\d+)\s+(\d+)\s+(\d+)/i",$line,$out)) {
				$titleforeground_red=$out[1];
				$titleforeground_green=$out[2];
				$titleforeground_blue=$out[3];
				if ($DEBUG) echo "<font color=red>configuration : TITLEFOREGROUND directive detected : $titleforeground_red $titleforeground_green $titleforeground_blue</font><br>";
			}
							
			if (preg_match("/^\s*\bKEYPOS\b\s+(\d+)\s+(\d+)/i",$line,$out)) {
				$keyxpos=$out[1];
				$keyypos=$out[2];
				if ($DEBUG) echo "<font color=red>configuration : KEYPOS directive detected : (" .$keyxpos.",".$keyypos.")</font><br>";
			}
			
			if (preg_match("/^\s*\bLEGENDPOS\b\s+(\d+)\s+(\d+)/i",$line,$out)) {
				$legendxpos=$out[1];
				$legendypos=$out[2];
				$keyxpos=$legendxpos;
				$keyypos=$legendypos;
				if ($DEBUG) echo "<font color=red>configuration : LEGENDPOS directive detected : (" .$legendxpos.",".$legendypos.")</font><br>";
			}


			
			if (preg_match("/^\s*\bLEGENDSTYLE\b\s+(\S+)/i",$line,$out)) {
				$legendstyle=$out[1];
				if ($DEBUG) echo "<font color=red>configuration : LEGENDSTYLE directive detected : (" .$legendstyle.")</font><br>";
			}

			if (preg_match("/^\s*\bOUTPUTFILE\b\s+(\S+)/i",$line,$out)) {
				$OUTPUTFILE=$out[1];
				if ($DEBUG) echo "<font color=red>configuration : OUTPUTFILE directive detected : " .$OUTPUTFILE."</font><br>";
			}
			
			if (preg_match("/^\s*\bHTMLFILE\b\s+(\S+)/i",$line,$out)) {
				$HTMLFILE=$out[1];
				if ($DEBUG) echo "<font color=red>configuration : HTMLFILE directive detected : " .$HTMLFILE."</font><br>";
			}


			#
			# Read colors scale (Directives AUTOSCALE or SCALE)
			#
			
			if (preg_match("/^\s*\bAUTOSCALE\b\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/i",$line,$out)) {
					$autoscale=1;
					autoscale($out[1],$out[2],$out[3],$out[4],$out[5],$out[6],$out[7]);
					$scales=$out[1];
					if ($DEBUG) echo "<font color=red>configuration : AUTOSCALE directive detected : " .$autoscale."</font><br>";
			}

			if (preg_match("/^\s*\bAUTOSCALE\b\s+(\d+)/i",$line,$out) && !$autoscale) {
					$autoscale=1;
					autoscale($out[1],24,232,2,233,28,1);
					$scales=$out[1];
					if ($DEBUG) echo "<font color=red>configuration : AUTOSCALE directive detected : " .$autoscale."</font><br>";
			}

			# Definition of colors scale
			if (preg_match("/^\s*\bSCALE\b\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/i",$line,$out) && !$autoscale) {
					$scale_low["$out[1]:$out[2]"]=$out[1];
					$scale_high["$out[1]:$out[2]"]=$out[2];
					$scale_red["$out[1]:$out[2]"]=$out[3];
					$scale_green["$out[1]:$out[2]"]=$out[4];
					$scale_blue["$out[1]:$out[2]"]=$out[5];
					if ($DEBUG) echo "<font color=red>configuration : SCALE directive detected : (";
					if ($DEBUG) echo $scale_low["$out[1]:$out[2]"].",";
					if ($DEBUG) echo $scale_high["$out[1]:$out[2]"].",";
					if ($DEBUG) echo $scale_red["$out[1]:$out[2]"].",";
					if ($DEBUG) echo $scale_green["$out[1]:$out[2]"].",";
					if ($DEBUG) echo $scale_blue["$out[1]:$out[2]"];
					if ($DEBUG) echo ")</font><br>";
			}

			# 
			# Read nodes directives
			#
			if (preg_match("/^\s*\bNODE\b\s+(\S+)/i",$line,$out)) {
				$node=$out[1];
				if ($DEBUG) echo "<font color=blue>configuration : NODE directive detected : ".$node."</font><br>";;
			}

			# Definition of position of node
			if (preg_match("/^\s*\bPOSITION\b\s+(\d+)\s+(\d+)/i",$line,$out)) {
				$posx[$node]=$out[1];
				$posy[$node]=$out[2];
				if ($DEBUG) echo "<font color=blue>configuration : POSITION directive detected : (".$posx[$node].",".$posy[$node].")</font><br>";;
			}

			# Definition of label for node
			if (preg_match("/^\s*\bLABEL\b\s+(.+)/i",$line,$out)) {
				$label[$node]=$out[1];
				if ($DEBUG) echo "<font color=blue>configuration : LABEL directive detected : ".$label[$node]."</font><br>";;
			}

			if (preg_match("/^\s*\bLABELTYPE\b\s+(\S+)/i",$line,$out)) {
				$labeltype[$node]=$out[1];
				if ($DEBUG) echo "<font color=blue>configuration : LABELTYPE directive detected : ".$labeltype[$node]."</font><br>";;
			}
			
			if (preg_match("/^\s*\bLABELTPT\b\s+(\S+)/i",$line,$out)) {
				$labeltpt[$node]=$out[1];
				if ($DEBUG) echo "<font color=blue>configuration : LABELTPT directive detected : ".$labeltpt[$node]."</font><br>";;
			}

			

			# Definition of icons for node
			if (preg_match("/^\s*\bICON\b\s+(\S+)/i",$line,$out)) {
				$iconpng[$node]=$out[1];
				if ($DEBUG) echo "<font color=blue>configuration : ICON directive detected : ".$iconpng[$node]."</font><br>";;
			}

			if (preg_match("/^\s*\bICONRESIZE\b\s+(\S+)/i",$line,$out)) {
				$iconresize[$node]=$out[1];
				if ($DEBUG) echo "<font color=blue>configuration : ICONRESIZE directive detected : ".$iconresize[$node]."</font><br>";;
			}

			if (preg_match("/^\s*\bICONPOS\b\s+(\d+)\s+(\d+)/i",$line,$out)) {
				$iconx[$node]=$out[1];
				$icony[$node]=$out[2];
				if ($DEBUG) echo "<font color=blue>configuration : ICONPOS directive detected : (".$iconx[$node].",".$icony[$node].")</font><br>";;
			}

			if (preg_match("/^\s*\bICONTPT\b\s+(\d+)/i",$line,$out)) {
				$icon_transparent[$node]=$out[1];
				if ($DEBUG) echo "<font color=blue>configuration : ICONTPT directive detected : ".$icon_transparent[$node]."</font><br>";;
			}

			if (preg_match("/^\s*\bIP\b\s+(\d+.\d+.\d+.\d+)/i",$line,$out)) {
				$ip[$node]=$out[1];
				if ($DEBUG) echo "<font color=blue>configuration : IP directive detected : ".$ip[$node]."</font><br>";;
			}
			
			if (preg_match("/^\s*\bCHECK\b\s+(\S+)/i",$line,$out)) {
				$check[$node]=$out[1];
				if ($DEBUG) echo "<font color=blue>configuration : CHECK directive detected : ".$check[$node]."</font><br>";;
			}
			
			if (preg_match("/^\s*\bCHECK\b\s+(\S+)\s+(\d+)/i",$line,$out)) {
				$check[$node]=$out[1];
				$checkport[$node]=$out[2];
				if ($DEBUG) echo "<font color=blue>configuration : CHECK directive detected : ".$check[$node]." ".$checkport[$node]."</font><br>";;
			}

			if (preg_match("/^\s*\bIPCHECK\b\s+(\S+)/i",$line,$out)) {
				$ipcheck=$out[1];
				if ($DEBUG) echo "<font color=red>configuration : IPCHECK directive detected : ".$ipcheck."</font><br>";;
			}

			#
			# Read link directives
			#
			
			if (preg_match("/^\s*\bLINK\b\s+(\S+)/i",$line,$out)) {
				$link=$out[1];
				if ($DEBUG) echo "<font color=green>configuration : LINK directive detected : ".$link."</font><br>";;
			}

			if (preg_match("/^\s*\bTARGETIN\b\s+(\S+)/i",$line,$out)) {
				$targetin[$link]=$out[1];
				$target[$link]=$out[1];
				if ($DEBUG) echo "<font color=green>configuration : TARGETIN directive detected : ".$targetin[$link]."</font><br>";;
			}

			if (preg_match("/^\s*\bTARGETOUT\b\s+(\S+)/i",$line,$out)) {
				$targetout[$link]=$out[1];
				if ($DEBUG) echo "<font color=green>configuration : TARGETOUT directive detected : ".$targetout[$link]."</font><br>";;
			}

			
			if (preg_match("/^\s*\bTARGET\b\s+(\S+)/i",$line,$out)) {
				$target[$link]=$out[1];
				$targetin[$link]=$target[$link];
				$targetout[$link]=$target[$link];
				if ($DEBUG) echo "<font color=green>configuration : TARGET directive detected : ".$target[$link]."</font><br>";;
			}

			if (preg_match("/^\s*\bFORCEMRTG\b\s+(\d+)/i",$line,$out)) {
				$forcemrtg[$link]=$out[1];
				if ($DEBUG) echo "<font color=green>configuration : FORCEMRTG directive detected : ".$forcemrtg[$link]."</font><br>";;
			}

			# If only one value is set for bandwidth. IN=OUT bandwidth.
			if (preg_match("/^\s*\bBANDWIDTH\b\s+(\d+)/i",$line,$out)) {
				$bandwidth[$link]=$out[1]; # Read in Kbits
				# Convert in bytes
				$maxbytesin[$link]=$bandwidth[$link]*1000/8;
				$maxbytesout[$link]=$maxbytesin[$link];
				if ($DEBUG) echo "<font color=green>configuration : BANDWIDTH directive detected : ".$bandwidth[$link]."</font><br>";;
			}
			
			if (preg_match("/^\s*\bBANDWIDTH\b\s+(\d+)\s+(\d+)/i",$line,$out)) {
				$bandwidthin[$link]=$out[1]; # Read in Kbits
				$bandwidthout[$link]=$out[2]; # Read in Kbits
				# Convert in bytes
				$maxbytesin[$link]=$bandwidthin[$link]*1000/8;
				$maxbytesout[$link]=$bandwidthout[$link]*1000/8;
				if ($DEBUG) echo "<font color=green>configuration : BANDWIDTH directive detected : IN=".$bandwidthin[$link]." OUT=".$bandwidthout[$link]."</font><br>";;
			}



			if (preg_match("/^\s*\bDISPLAYVALUE\b\s+(\d+)/i",$line,$out)) {
				$displayvalue[$link]=$out[1];
				if ($DEBUG) echo "<font color=green>configuration : DISPLAYVALUE directive for $link detected : " .$displayvalue[$link]. "</font><br>";
			}

			if (preg_match("/^\s*\bUNIT\b\s+(\S+)/i",$line,$out)) {
				$unit[$link]=$out[1];
				if ( $unit[$link] == "Mbits" ) {
					$coef[$link]=1000*1000/8;
				}
				if ( $unit[$link] == "Kbits" ) {
					$coef[$link]=1000/8;
				}
				if ( $unit[$link] == "bits" ) {
					$coef[$link]=1/8;
				}
				if ( $unit[$link] == "Mbytes" ) {
					$coef[$link]=1024*1024;
				}
				if ( $unit[$link] == "Kbytes" ) {
					$coef[$link]=1024;
				}
				if ( $unit[$link] == "bytes" ) {
					$coef[$link]=1;
				}
				if ($DEBUG) echo "<font color=green>configuration : UNIT directive detected : " .$unit[$link]. "</font><br>";
			}


			if (preg_match("/^\s*\bINPOS\b\s+(\d+)/i",$line,$out)) {
				$inpos[$link]=$out[1]; 
				if ($DEBUG) echo "<font color=green>configuration : INPOS directive detected : " .$inpos[$link]. "</font><br>";
			}

			if (preg_match("/^\s*\bOUTPOS\b\s+(\d+)/i",$line,$out)) {
				$outpos[$link]=$out[1]; 
				if ($DEBUG) echo "<font color=green>configuration : OUTPOS directive detected : " .$outpos[$link]. "</font><br>";
			}

			# Definition of link nodes
			if (preg_match("/^\s*\bNODES\b\s+(\S+)\s+(\S+)/i",$line,$out)) {
				$nodea[$link]=$out[1];
				$nodeb[$link]=$out[2];
				$coef[$link]=1; // By default set coef to bytes value
				if ($DEBUG) echo "<font color=green>configuration : NODES directive detected : (" .$nodea[$link]. ",".$nodeb[$link].")</font><br>";
			}

			# Definition of arrow type to display for link
			if (preg_match("/^\s*\bARROW\b\s+(\S+)/i",$line,$out)) {
				$arrow[$link]=$out[1];
				if ($DEBUG) echo "<font color=green>configuration : ARROW directive detected : " .$arrow[$link]. "</font><br>";
			}

			# Definition of group
			if (preg_match("/^\s*\bGROUP\b\s+(\S+)/i",$line,$out)) {
				$group_name[$link]=$out[1];
				if ($DEBUG) echo "<font color=green>configuration : GROUP directive detected : " .$group_name[$link]. "</font><br>";
			}

			# Definition of internodes
			if (preg_match("/^\s*\bINTERNODE\b\s+(\d+)\s+(\d+)/i",$line,$out)) {
				$internodes[$link]++;
				$internodex[$link][$internodes[$link]]=$out[1];
				$internodey[$link][$internodes[$link]]=$out[2];
				if ($DEBUG) echo "<font color=green>configuration : INTERNODE directive detected : (" .$internodex[$link][$internodes[$link]].",".$internodey[$link][$internodes[$link]]. ")</font><br>";
			}
			# Define if internodes will be displayed
			if (preg_match("/^\s*\bINTERNODEDISPLAY\b\s+(\d+)/i",$line,$out)) {
				$internodedisplay[$link]=$out[1];
				if ($DEBUG) echo "<font color=green>configuration : INTERNODEDISPLAY directive detected : $internodedisplay[$link]</font><br>";
			}
			
			if (preg_match("/^\s*\bINTERNODEDISPLAYNUM\b\s+(\S+)/i",$line,$out)) {
				if (strtolower($out[1])=="yes" || strtolower($out[1])=="1") {
					$internodenumdisp[$link]=1;
				} else {
					$internodenumdisp[$link]=0;
				}
				if ($DEBUG) echo "<font color=green>configuration : INTERNODEDISPLAYNUM directive detected : $internodenumdisp[$link] </font><br>";
			}

			if (preg_match("/^\s*\bINTERNODEDISPLAYMID\b\s+(\S+)/i",$line,$out)) {
				if (strtolower($out[1])=="yes" || strtolower($out[1])=="1") {
					$internodedisplaymid[$link]=1;
				} else {
					$internodedisplaymid[$link]=0;
				}
				if ($DEBUG) echo "<font color=green>configuration : INTERNODEDISPLAYMID directive detected : $internodedisplaymid[$link] </font><br>";
			}




			# Define popup content for mouse rollover above link
			if (preg_match("/^\s*\bLINKOVERLIBGRAPH\b\s+/i",$line,$out)) {
				$linkoverlibgraph[$link]=1;
				if ($DEBUG) echo "<font color=green>configuration : LINKOVERLIBGRAPH directive detected : $linkoverlibgraph[$link]</font><br>";
			}

			# Define popup content for mouse rollover above link
			if (preg_match("/^\s*\bLINKOVERLIBGRAPH\b\s+(\S+)/i",$line,$out)) {
				$linkoverlibgraph[$link]=$out[1];
				if ($DEBUG) echo "<font color=green>configuration : LINKOVERLIBGRAPH directive detected : $linkoverlibgraph[$link]</font><br>";
			}

			
			# Define popup content for mouse rollover above node
			if (preg_match("/^\s*\bNODEOVERLIBGRAPH\b\s+(\S+)/i",$line,$out)) {
				$nodeoverlibgraph[$node]=$out[1];
				if ($DEBUG) echo "<font color=green>configuration : NODEOVERLIBGRAPH directive detected : $nodeoverlibgraph[$node]</font><br>";
			}

			# Define target link for mouse click on link
			if (preg_match("/^\s*\bLINKINFOURL\b\s+(\S+)/i",$line,$out)) {
				$linkinfourl[$link]=$out[1];
				if ($DEBUG) echo "<font color=green>configuration : LINKINFOURL directive detected : $linkinfourl[$link]</font><br>";
			}
			
			# Define target link for mouse click on node
			if (preg_match("/^\s*\bNODEINFOURL\b\s+(\S+)/i",$line,$out)) {
				$nodeinfourl[$node]=$out[1];
				if ($DEBUG) echo "<font color=green>configuration : NODEINFOURL directive detected : $nodeinfourl[$node]</font><br>";
			}



		}

	}
}

#
# Function used for AUTOSCALE 
#
function autoscale($autoscale_div,$start_red,$start_green,$start_blue,$end_red,$end_green,$end_blue) {
	global $scale_low,$scale_high,$scale_red,$scale_green,$scale_blue;
	
	if (!$autoscale_div) { $autoscale_div=7; }
	
	$dif_red=-($start_red-$end_red);
	$dif_green=-($start_green-$end_green);
	$dif_blue=-($start_blue-$end_blue);

	$step_red=$dif_red/$autoscale_div;
	$step_green=$dif_green/$autoscale_div;
	$step_blue=$dif_blue/$autoscale_div;

	$bounder_inf=0;
	$bounder_sup=round(100/$autoscale_div);

    for ($i=0; $i<$autoscale_div; $i++) {
		$scale_low["$bounder_inf:$bounder_sup"]=$bounder_inf;
		$scale_high["$bounder_inf:$bounder_sup"]=$bounder_sup;
		$scale_red["$bounder_inf:$bounder_sup"]=$start_red+$i*$step_red;
		$scale_green["$bounder_inf:$bounder_sup"]=$start_green+$i*$step_green;
		$scale_blue["$bounder_inf:$bounder_sup"]=$start_blue+$i*$step_blue;
		$bounder_inf=$bounder_sup;
		if ( $i == ($autoscale_div-2)) {
			$bounder_sup=100;
		} else {
		    $bounder_sup=($i+2)*round(100/$autoscale_div);
		}
	}
}
function error_display($text) {
	global $VERSION;
	echo "<b>";
	echo "WeatherMap4RRD $VERSION : An error occured : <font color=red>$text</font><br>";
	echo "</b>";
}

?>
