#!/usr/bin/php-cgi
<?php
function middle($x1,$x2) {
	return ($x1+($x2-$x1)/2);
}

function dist($x1,$x2) {
	return (sqrt($x1*$x1+$x2*$x2));
}

function dist_bw_points($x1,$y1,$x2,$y2) {
	#echo "sqrt(($x2-$x1)*($x2-$x1)+($y2-$y1)*($y2-$y1))=".sqrt(($x2-$x1)*($x2-$x1)+($y2-$y1)*($y2-$y1))."<br>";
	$x1=round($x1);
	$x2=round($x2);
	$y1=round($y1);
	$y2=round($y2);

	return (sqrt(($x2-$x1)*($x2-$x1)+($y2-$y1)*($y2-$y1)));
}

function newx ($a,$b,$x,$y) {
	return (cos(atan2($y,$x)+atan2($b,$a))*sqrt($x*$x+$y*$y));
}

function newy ($a,$b,$x,$y) {
	return (sin( atan2($y,$x) + atan2($b,$a) ) * sqrt( $x*$x + $y*$y ));
}

function getposy_line($x,$x1,$y1,$x2,$y2) {
	$a=($y2-$y1)/($x2-$x1);
	$b=$y1-$a*$x1;
	return ($a*$x+$b);
}
function getposx_line($y,$x1,$y1,$x2,$y2) {
	$c=($x2-$x1)/($y2-$y1);
	$d=$x1-$c*$y1;
	return ($c*$y+$d);
}

function draw_rectangle($x1,$y1,$x2,$y2,$w,$solid,$color) {
	global $im;
	global $black,$red,$white;

	$point[0]=$x1 + newx($x2-$x1, $y2-$y1, 0, $w);
	$point[1]=$y1 + newy($x2-$x1, $y2-$y1, 0, $w);
	
	$point[2]=$x2 + newx($x2-$x1, $y2-$y1, 0, $w);
	$point[3]=$y2 + newy($x2-$x1, $y2-$y1, 0, $w);

	$point[4]=$x2 + newx($x2-$x1, $y2-$y1, 0, -$w);
	$point[5]=$y2 + newy($x2-$x1, $y2-$y1, 0, -$w);

	$point[6]=$x1 + newx($x2-$x1, $y2-$y1, 0, -$w);
	$point[7]=$y1 + newy($x2-$x1, $y2-$y1, 0, -$w);
	
	if ($solid) {
			imagefilledPolygon($im,$point,4,$color);
			imagepolygon($im,$point,4,$black);
	}else{
		imagepolygon($im,$point,4,$black);
	}
}


function draw_rectangle_map($x1,$y1,$x2,$y2,$w,$linkname,$linkoverlibgraph,$linkinfourl) {

	$point[0]=round($x1 + newx($x2-$x1, $y2-$y1, 0, $w));
	$point[1]=round($y1 + newy($x2-$x1, $y2-$y1, 0, $w));
	
	$point[2]=round($x2 + newx($x2-$x1, $y2-$y1, 0, $w));
	$point[3]=round($y2 + newy($x2-$x1, $y2-$y1, 0, $w));

	$point[4]=round($x2 + newx($x2-$x1, $y2-$y1, 0, -$w));
	$point[5]=round($y2 + newy($x2-$x1, $y2-$y1, 0, -$w));

	$point[6]=round($x1 + newx($x2-$x1, $y2-$y1, 0, -$w));
	$point[7]=round($y1 + newy($x2-$x1, $y2-$y1, 0, -$w));


	$html_map_string="<AREA SHAPE=\"POLY\" COORDS=\"";
	$html_map_string.=implode(",",$point)."\"";
	$html_map_string.=get_jslib_html_string($linkname,$linkoverlibgraph,$linkinfourl);
	$html_map_string.=">\n";


	add_to_html_map($html_map_string);

}

function draw_rectangle_half($x1,$y1,$x2,$y2,$w,$solid,$color) {
	global $im;
	global $black,$red,$white;

	$point[0]=$x1;
	$point[1]=$y1;
	
	$point[2]=$x2;
	$point[3]=$y2;

	$point[4]=$x2 + newx($x2-$x1, $y2-$y1, 0, -$w);
	$point[5]=$y2 + newy($x2-$x1, $y2-$y1, 0, -$w);

	$point[6]=$x1 + newx($x2-$x1, $y2-$y1, 0, -$w);
	$point[7]=$y1 + newy($x2-$x1, $y2-$y1, 0, -$w);
	
	if ($solid) {
			imagefilledPolygon($im,$point,4,$color);
			imagepolygon($im,$point,4,$black);
	}else{
		imagepolygon($im,$point,4,$black);
	}
}

function draw_rectangle_half_map($x1,$y1,$x2,$y2,$w,$linkname,$linkoverlibgraph,$linkinfourl) {

	$point[0]=$x1;
	$point[1]=$y1;
	
	$point[2]=$x2;
	$point[3]=$y2;

	$point[4]=round($x2 + newx($x2-$x1, $y2-$y1, 0, -$w));
	$point[5]=round($y2 + newy($x2-$x1, $y2-$y1, 0, -$w));

	$point[6]=round($x1 + newx($x2-$x1, $y2-$y1, 0, -$w));
	$point[7]=round($y1 + newy($x2-$x1, $y2-$y1, 0, -$w));


	$html_map_string="<AREA SHAPE=\"POLY\" COORDS=\"";
	$html_map_string.=implode(",",$point)."\"";
	$html_map_string.=get_jslib_html_string($linkname,$linkoverlibgraph,$linkinfourl);
	$html_map_string.=">\n";


	add_to_html_map($html_map_string);

}



function add_to_html_map($string) {
	global $html_map;
	$html_map.=$string."\n";
}

function get_jslib_html_string($linkname,$overlib,$infourl) {
	global $ROLLOVERLIB;
	global $configfile;
	
	if ($ROLLOVERLIB == "overlib") {
		if ($overlib == 1) {
			$html_code=" onmouseover=\"return overlib('<img src=graph.php?link=".urlencode($linkname)."&config=".urlencode($configfile).">',DELAY,250,CAPTION,'$caption');\" ";
			$html_code.=" onmouseout=\"return nd();\" ";
		} else {

	# Section ok
	/*
			#$html_code=" onmouseover=\"return overlib('<img src=$overlib>',DELAY,250,CAPTION,'$caption');\" ";
			$fp=fopen ("$overlib", "r");
			$buffer.= fread($fp, 1024);
			fclose($fp);
			$buffer=str_replace("\"","",$buffer);
			$buffer=str_replace("\n","<br>",$buffer);
			$html_code=" onmouseover=\"return overlib('$buffer',DELAY,250,CAPTION,'');\" ";
			$html_code.=" onmouseout=\"return nd();\" ";
	*/
#$weathermap4php= $_SERVER["HTTP_HOST"].dirname($_SERVER['REQUEST_URI'])."/overlib";

			$fp=fopen ("http://".$_SERVER["HTTP_HOST"].dirname($_SERVER['REQUEST_URI'])."/".$overlib."?link=".urlencode($linkname)."&config=".urlencode($configfile), "r");
#			$fp=fopen ("$overlib", "r");
			$buffer.= fread($fp, 1024);
			fclose($fp);
			
			$buffer=str_replace("\"","",$buffer);
			$buffer=str_replace("\n","<br>",$buffer);
			$html_code=" onmouseover=\"return overlib('$buffer',DELAY,250,CAPTION,'');\" ";
			$html_code.=" onmouseout=\"return nd();\" ";
			
			// For Crinago
			/*
			$html_code=" onmouseover=\"return overlib('<img src=$overlib>',DELAY,250,CAPTION,'$caption');\" ";
			$html_code.=" onmouseout=\"return nd();\" ";
			*/

		}
	}

	if ($infourl == "globalgraph") {
		$html_code.= " HREF=\"linkgraph.php?link=".urlencode($linkname)."&config=".urlencode($configfile)."\" ";
	} else {
		$html_code.= " HREF=\"$infourl\" ";
	}

	return ($html_code);

}

function draw_arrow($x1,$y1,$x2,$y2,$w,$solid,$color) {
	global $im;
	global $black,$red,$white;

	$point[0]=$x1 + newx($x2-$x1, $y2-$y1, 0, $w);
	$point[1]=$y1 + newy($x2-$x1, $y2-$y1, 0, $w);
	$point[2]=$x2 + newx($x2-$x1, $y2-$y1, -4*$w, $w);
	$point[3]=$y2 + newy($x2-$x1, $y2-$y1, -4*$w, $w);
	$point[4]=$x2 + newx($x2-$x1, $y2-$y1, -4*$w, 2*$w);
	$point[5]=$y2 + newy($x2-$x1, $y2-$y1, -4*$w, 2*$w);
	$point[6]=$x2;
	$point[7]=$y2;
	$point[8]=$x2 + newx($x2-$x1, $y2-$y1, -4*$w, -2*$w);
	$point[9]=$y2 + newy($x2-$x1, $y2-$y1, -4*$w, -2*$w);
	$point[10]=$x2 + newx($x2-$x1, $y2-$y1, -4*$w, -$w);
	$point[11]=$y2 + newy($x2-$x1, $y2-$y1, -4*$w, -$w);
	$point[12]=$x1 + newx($x2-$x1, $y2-$y1, 0, -$w);
	$point[13]=$y1 + newy($x2-$x1, $y2-$y1, 0, -$w);
	
	if ($solid) {
			imagefilledPolygon($im,$point,7,$color);
			imagepolygon($im,$point,7,$black);
	}else{
		imagepolygon($im,$point,7,$black);
	}
}

function draw_arrow_half($x1,$y1,$x2,$y2,$w,$solid,$color) {
	global $im;
	global $black,$red,$white;

	$point[0]=$x1;
	$point[1]=$y1; 
	$point[2]=$x2;
	$point[3]=$y2;
	$point[4]=$x2 + newx($x2-$x1, $y2-$y1, -6*$w, -3*$w);
	$point[5]=$y2 + newy($x2-$x1, $y2-$y1, -6*$w, -3*$w);
	$point[6]=$x2 + newx($x2-$x1, $y2-$y1, -6*$w, -$w);
	$point[7]=$y2 + newy($x2-$x1, $y2-$y1, -6*$w, -$w);
	$point[8]=$x1 + newx($x2-$x1, $y2-$y1, 0, -$w);
	$point[9]=$y1 + newy($x2-$x1, $y2-$y1, 0, -$w);

	
	if ($solid) {
			imagefilledPolygon($im,$point,5,$color);
			imagepolygon($im,$point,5,$black);
	}else{
		imagepolygon($im,$point,5,$black);
	}
}


function draw_arrow_map($x1,$y1,$x2,$y2,$w,$linkname,$linkoverlibgraph,$linkinfourl) {
	$point[0]=round($x1 + newx($x2-$x1, $y2-$y1, 0, $w));
	$point[1]=round($y1 + newy($x2-$x1, $y2-$y1, 0, $w));
	$point[2]=round($x2 + newx($x2-$x1, $y2-$y1, -4*$w, $w));
	$point[3]=round($y2 + newy($x2-$x1, $y2-$y1, -4*$w, $w));
	$point[4]=round($x2 + newx($x2-$x1, $y2-$y1, -4*$w, 2*$w));
	$point[5]=round($y2 + newy($x2-$x1, $y2-$y1, -4*$w, 2*$w));
	$point[6]=round($x2);
	$point[7]=round($y2);
	$point[8]=round($x2 + newx($x2-$x1, $y2-$y1, -4*$w, -2*$w));
	$point[9]=round($y2 + newy($x2-$x1, $y2-$y1, -4*$w, -2*$w));
	$point[10]=round($x2 + newx($x2-$x1, $y2-$y1, -4*$w, -$w));
	$point[11]=round($y2 + newy($x2-$x1, $y2-$y1, -4*$w, -$w));
	$point[12]=round($x1 + newx($x2-$x1, $y2-$y1, 0, -$w));
	$point[13]=round($y1 + newy($x2-$x1, $y2-$y1, 0, -$w));
	
	$html_map_string="<AREA SHAPE=\"POLY\" COORDS=\"";
	$html_map_string.=implode(",",$point)."\"";
	$html_map_string.=get_jslib_html_string($linkname,$linkoverlibgraph,$linkinfourl);
	$html_map_string.=">\n";

	add_to_html_map($html_map_string);

}

function draw_arrow_half_map($x1,$y1,$x2,$y2,$w,$linkname,$linkoverlibgraph,$linkinfourl) {
	$point[0]=$x1;
	$point[1]=$y1; 
	$point[2]=$x2;
	$point[3]=$y2;
	$point[4]=$x2 + newx($x2-$x1, $y2-$y1, -6*$w, -3*$w);
	$point[5]=$y2 + newy($x2-$x1, $y2-$y1, -6*$w, -3*$w);
	$point[6]=$x2 + newx($x2-$x1, $y2-$y1, -6*$w, -$w);
	$point[7]=$y2 + newy($x2-$x1, $y2-$y1, -6*$w, -$w);
	$point[8]=$x1 + newx($x2-$x1, $y2-$y1, 0, -$w);
	$point[9]=$y1 + newy($x2-$x1, $y2-$y1, 0, -$w);

	$html_map_string="<AREA SHAPE=\"POLY\" COORDS=\"";
	$html_map_string.=implode(",",$point)."\"";
	$html_map_string.=get_jslib_html_string($linkname,$linkoverlibgraph,$linkinfourl);
	$html_map_string.=">\n";

	add_to_html_map($html_map_string);

}


function draw_circle3($x1,$y1,$x2,$y2,$w,$step,$solid,$color) {
	global $im;
	global $black,$red,$white,$orange;
	$dif_horizontal=abs($x1-$x2);
	$dif_vertical=abs($y1-$y2);
	if ($dif_horizontal>$dif_vertical) {
		if ( ($x1 < $x2) ) {
				for ($xi = $x1+$w; dist_bw_points($xi,$yi,$x2,$y2)>2*$w; $xi=$xi+2*$w+$step) {
					$yi=getposy_line($xi,$x1,$y1,$x2,$y2);
					if ($solid) {
						imagefilledarc($im,$xi,$yi,2*$w, 2*$w, 0, 360, $color,"IMG_ARC_PIE");
					} else {
						imagearc($im,$xi,$yi,2*$w, 2*$w, 0, 360, $color);
					}
				}
		} else {
				for ($xi = $x1-2*$w; dist_bw_points($xi,$yi,$x2,$y2)>2*$w; $xi=$xi-2*$w-$step) {
					$yi=getposy_line($xi,$x1,$y1,$x2,$y2);
					if ($solid) {
						imagefilledarc($im,$xi,$yi,2*$w, 2*$w, 0, 360, $color,"IMG_ARC_PIE");
					} else {
						imagearc($im,$xi,$yi,2*$w, 2*$w, 0, 360, $color);
					}
				}
		}
	} else {
		if ( ($y1<$y2) ) {
			for ($yi = $y1; dist_bw_points($xi,$yi,$x2,$y2)>2*$w; $yi=$yi+2*$w+$step) {
				$xi=getposx_line($yi,$x1,$y1,$x2,$y2);
				if ($solid) {
					imagefilledarc($im,$xi,$yi,2*$w, 2*$w, 0, 360, $color,"IMG_ARC_PIE");
				} else {
					imagearc($im,$xi,$yi,2*$w, 2*$w, 0, 360, $color);
				}
			}
		} else {
			for ($yi = $y1-2*$w; dist_bw_points($xi,$yi,$x2,$y2)>2*$w; $yi=$yi-2*$w-$step) {
				$xi=getposx_line($yi,$x1,$y1,$x2,$y2);
				if ($solid) {
					imagefilledarc($im,$xi,$yi,2*$w, 2*$w, 0, 360, $color,"IMG_ARC_PIE");
				} else {
					imagearc($im,$xi,$yi,2*$w, 2*$w, 0, 360, $color);
				}
			}
		}
	}
}



function draw_arrow_circle3($x1,$y1,$x2,$y2,$w,$step,$solid,$color) {
	global $im;
	global $black,$red,$white,$orange;
	$dif_horizontal=abs($x1-$x2);
	$dif_vertical=abs($y1-$y2);
	if ($dif_horizontal>$dif_vertical) {
		if ( ($x1 < $x2) ) {
				for ($xi = $x1; dist_bw_points($xi,$yi,$x2,$y2)>4*$w; $xi=$xi+2*$w+$step) {
					$yi=getposy_line($xi,$x1,$y1,$x2,$y2);
					if ($solid) {
						imagefilledarc($im,$xi,$yi,2*$w, 2*$w, 0, 360, $color,"IMG_ARC_PIE");
					} else {
						imagearc($im,$xi,$yi,2*$w, 2*$w, 0, 360, $color);
					}
				}
		} else {
				for ($xi = $x1; dist_bw_points($xi,$yi,$x2,$y2)>4*$w; $xi=$xi-2*$w-$step) {
					$yi=getposy_line($xi,$x1,$y1,$x2,$y2);
					if ($solid) {
						imagefilledarc($im,$xi,$yi,2*$w, 2*$w, 0, 360, $color,"IMG_ARC_PIE");
					} else {
						imagearc($im,$xi,$yi,2*$w, 2*$w, 0, 360, $color);
					}
				}
		}
	} else {
		if ( ($y1<$y2) ) {
			for ($yi = $y1+$w; dist_bw_points($xi,$yi,$x2,$y2)>4*$w; $yi=$yi+2*$w+$step) {
				$xi=getposx_line($yi,$x1,$y1,$x2,$y2);
				if ($solid) {
					imagefilledarc($im,$xi,$yi,2*$w, 2*$w, 0, 360, $color,"IMG_ARC_PIE");
				} else {
					imagearc($im,$xi,$yi,2*$w, 2*$w, 0, 360, $color);
				}
			}
		} else {
			for ($yi = $y1-$w; dist_bw_points($xi,$yi,$x2,$y2)>4*$w; $yi=$yi-2*$w-$step) {
				$xi=getposx_line($yi,$x1,$y1,$x2,$y2);
				if ($solid) {
					imagefilledarc($im,$xi,$yi,2*$w, 2*$w, 0, 360, $color,"IMG_ARC_PIE");
				} else {
					imagearc($im,$xi,$yi,2*$w, 2*$w, 0, 360, $color);
				}
			}
		}
	}

	$point[0]=$x2 + newx($x2-$x1, $y2-$y1, -4*$w, $w);
	$point[1]=$y2 + newy($x2-$x1, $y2-$y1, -4*$w, $w);
	$point[2]=$x2 + newx($x2-$x1, $y2-$y1, -4*$w, 2*$w);
	$point[3]=$y2 + newy($x2-$x1, $y2-$y1, -4*$w, 2*$w);
	$point[4]=$x2;
	$point[5]=$y2;
	$point[6]=$x2 + newx($x2-$x1, $y2-$y1, -4*$w, -2*$w);
	$point[7]=$y2 + newy($x2-$x1, $y2-$y1, -4*$w, -2*$w);
	$point[8]=$x2 + newx($x2-$x1, $y2-$y1, -4*$w, -$w);
	$point[9]=$y2 + newy($x2-$x1, $y2-$y1, -4*$w, -$w);

	if ($solid) {
		imagefilledpolygon($im,$point,5,$color);
	} else {
		imagepolygon($im,$point,5,$color);
	}
}

function draw_arrow_dot($x1,$y1,$x2,$y2,$w,$solid,$color) {
	global $im;
	global $black,$red,$white;

	$point[0]=$x1 + newx($x2-$x1, $y2-$y1, 0, $w);
	$point[1]=$y1 + newy($x2-$x1, $y2-$y1, 0, $w);
	$point[2]=$x2 + newx($x2-$x1, $y2-$y1, -4*$w, $w);
	$point[3]=$y2 + newy($x2-$x1, $y2-$y1, -4*$w, $w);
	$point[4]=$x2 + newx($x2-$x1, $y2-$y1, -4*$w, 2*$w);
	$point[5]=$y2 + newy($x2-$x1, $y2-$y1, -4*$w, 2*$w);
	$point[6]=$x2;
	$point[7]=$y2;
	$point[8]=$x2 + newx($x2-$x1, $y2-$y1, -4*$w, -2*$w);
	$point[9]=$y2 + newy($x2-$x1, $y2-$y1, -4*$w, -2*$w);
	$point[10]=$x2 + newx($x2-$x1, $y2-$y1, -4*$w, -$w);
	$point[11]=$y2 + newy($x2-$x1, $y2-$y1, -4*$w, -$w);
	$point[12]=$x1 + newx($x2-$x1, $y2-$y1, 0, -$w);
	$point[13]=$y1 + newy($x2-$x1, $y2-$y1, 0, -$w);
	
		#echo "point[$i]=".$point[$i]." point[".$i+1."]=".$point[$i+1]."<br>";
		#echo "test $point[$i]<br>";
	if ($solid) {
		imagefilledPolygon($im,$point,7,$color);
		$style=array($white, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT ); 
		imagesetstyle ($im, $style);
		imagesetthickness($im,10);
		imageline ($im, middle($point[0],$point[12]),middle($point[1],$point[13]),$point[6],$point[7], IMG_COLOR_STYLED);
		imagesetthickness($im,1);
		imagepolygon($im,$point,7,$black);
	}else{
		imagepolygon($im,$point,7,$black);
	}
}

function labelv2($main_im,$string,$font,$textcolor,$bgcolor){
	$pix_width=imagefontwidth($font)*strlen($string);
	$pix_height=imagefontheight($font);
	$pad=2;

	$im = @imagecreate ($pix_width+$pad*2+4,$pix_height+$pad*2) or die ("Cannot Initialize new GD image stream");
	$color_tran_bg = imagecolorsforindex($main_im, $bgcolor);
	$bgcolor=imagecolorallocate($im,$color_tran_bg[red],$color_tran_bg[green],$color_tran_bg[blue]);
	$color_tran_fg = imagecolorsforindex($main_im, $textcolor);
	$textcolor=imagecolorallocate($im,$color_tran_fg[red],$color_tran_fg[green],$color_tran_fg[blue]);

	imagefilledrectangle($im, 0, 0, $pix_width+$pad*2+4, $pix_height+$pad*2-1, $textcolor);
	imagefilledrectangle($im, 1, 1, $pix_width+$pad*2+2, $pix_height+$pad*2-2, $bgcolor);
	imagestring($im, $font, 0+$pad+2, 0+$pad/2, $string, $textcolor);
	return ($im);
}

function labelv2_map($string,$font,$textcolor,$bgcolor){
}

function draw_internode($string,$font,$textcolor,$bgcolor,$internodenumdisp){
	$radius=7;
	$pix_width=2*$radius;
	$pix_height=2*$radius;

	$im = @imagecreate ($pix_width+2,$pix_height+2) or die ("Cannot Initialize new GD image stream");
	$whiteinter=imagecolorallocate($im, 255, 255, 255);
	imagecolortransparent($im,$whiteinter);
	$black=imagecolorallocate($im, 0,0,0);
	$grey=imagecolorallocate($im, 192,192,192);
	$red=imagecolorallocate($im, 255, 0, 0);
	$blue=imagecolorallocate($im, 0, 0, 255);
	$yellow=imagecolorallocate($im, 255, 255, 0);

	imagefilledellipse($im,$radius+1, $radius+1,2*$radius,2*$radius,$grey);
	imageellipse($im,$radius+1, $radius+1,2*$radius,2*$radius,$black);
	if ($internodenumdisp) { imagestring($im, gdTinyFont, $radius, $radius-imagefontwidth(gdTinyFont)*strlen($string)/2, $string, $yellow); }

	return ($im);
}



function label($im,$string,$xpos,$ypos,$font,$textcolor,$bgcolor){
	$pix_width=imagefontwidth($font)*strlen($string);
	$pix_height=imagefontheight($font);
	$pad=2;
	imagefilledrectangle($im, $xpos-$pix_width/2-$pad-2, $ypos-$pix_height/2-$pad+1, $xpos+$pix_width/2+$pad+1, $ypos+$pix_height/2+$pad, $textcolor);
	imagefilledrectangle($im, $xpos-$pix_width/2-$pad-1, $ypos-$pix_height/2-$pad+2, $xpos+$pix_width/2+$pad, $ypos+$pix_height/2+$pad-1, $bgcolor);
	imagestring($im, $font, $xpos-$pix_width/2, $ypos-$pix_height/2, $string, $textcolor);
}

function label_round($im,$string,$xpos,$ypos,$font,$textcolor,$bgcolor){
	$radius=10;
	$pix_width=imagefontwidth($font)*strlen($string);
	$pix_height=imagefontheight($font);
	$black=imagecolorallocate($im, 0,0,0);

	imagefilledellipse($im,$xpos, $ypos,2*$radius,2*$radius,$bgcolor);
	imageellipse($im,$xpos, $ypos,2*$radius,2*$radius,$black);
	imagestring($im, $font-1, $xpos-$pix_width/2, $ypos+$radius, $string, $textcolor);
}


function select_color($im,$rate,$scale_low, $scale_high, $scale_red, $scale_green, $scale_blue) {
	global $scalecolor;

	if($rate=="0") return($darkgray);
	foreach ($scale_low as $i => $value) {
		if ($rate >=$scale_low[$i] && $rate <=$scale_high[$i]) {
			return $scalecolor[$i];
		}
	}
	return ($darkgray);
}

function define_colors($im) {
	global $backgroundcolor_red,$backgroundcolor_green,$backgroundcolor_blue;
	global $titleforeground_red,$titleforeground_green,$titleforeground_blue,$titlebackground_red,$titlebackground_green,$titlebackground_blue;
	global $black,$orange,$white,$darkgray,$gray,$red,$green,$titleforeground,$titlebackground;
	global $scale_low,$scale_red,$scale_blue,$scale_green,$scalecolor; 

	if ( ($backgroundcolor=imagecolorallocate($im,$backgroundcolor_red,$backgroundcolor_green,$backgroundcolor_blue)) =="-1") {
		$backgroundcolor=imagecolorclosest($im,$backgroundcolor_red,$backgroundcolor_green,$backgroundcolor_blue);
	} 
	
	if ( ($white=imagecolorallocate($im, 255, 255, 255)) =="-1") {
		$white=imagecolorclosest($im,255,255,255);
	}

	if ( ($red=imagecolorallocate($im, 255, 0, 0)) =="-1") {
		$red= imagecolorClosest($im, 255, 0, 0);
	}
	if ( ($darkgray=imagecolorallocate($im, 128, 128, 128)) =="-1") {
		$darkgray = imagecolorClosest($im,128,128,128);
	}
	if ( ($gray=imagecolorallocate($im, 248, 248, 248)) =="-1") {
		$gray = imagecolorClosest($im, 248, 248, 248);
	}
	if ( ($orange=imagecolorallocate($im, 220, 210, 60)) =="-1") {
		$orange = imagecolorClosest($im, 220, 210, 60);
	}
	if ( ($black=imagecolorallocate($im, 0, 0, 0)) =="-1") {
		$black = imagecolorClosest($im, 0, 0, 0);
	}
	if ( ($green=imagecolorallocate($im, 0, 255, 0)) =="-1") {
		$green= imagecolorClosest($im, 0, 255, 0);
	}
	
	foreach ($scale_low as $indice => $value) {
		if ( ($scalecolor[$indice]=imagecolorallocate($im, $scale_red[$indice], $scale_green[$indice], $scale_blue[$indice])) =="-1") {
			$scalecolor[$indice]=imagecolorClosest($im, $scale_red[$indice], $scale_green[$indice], $scale_blue[$indice]);
		}
	}
	
}
	
function draw_title($title,$font,$titleforeground_red,$titleforeground_green,$titleforeground_blue,$titlebackground_red,$titlebackground_green,$titlebackground_blue,$unixtime) {

    $t=date("D M d H:i:s Y",$unixtime);
    #$t="$unixtime";

	if (imagefontwidth(2)*strlen("Last update on $t")>imagefontwidth($font)*strlen($title)) {
		$titlewidth=imagefontwidth($font-2)*strlen("Last update on $t");
	} else {
		$titlewidth=imagefontwidth($font)*strlen($title);
	}

	$im = @imagecreate ($titlewidth+5,imagefontheight($font)*2+5) or die ("Cannot Initialize new GD image stream");

    if ( ($titlebackground=imagecolorAllocate($im,$titlebackground_red,$titlebackground_green,$titlebackground_blue)) =="-1") {
		$titlebackground=imagecolorClosest($im,$titlebackground_red,$titlebackground_green,$titlebackground_blue);
	}

	if (($titleforeground=imagecolorAllocate($im,$titleforeground_red,$titleforeground_green,$titleforeground_blue)) =="-1") {
		$titleforeground=imagecolorClosest($im,$titleforeground_red,$titleforeground_green,$titleforeground_blue);
	}

	imagefilledrectangle($im, 0, 0, 
							  $titlewidth+3, imagefontheight($font)*2+3,
							  $titlebackground);
	imagerectangle($im, 0, 0, 
							  $titlewidth+4, imagefontheight($font)*2+4,
							  $titleforeground);
	imagestring($im, $font, 2, 2, $title, $titleforeground);
	imagestring($im, $font-2, 2, imagefontheight($font)+4, "Last update on $t", $titleforeground);
	return ($im);
}

function draw_legend($label,$font,$scale_low,$scale_high,$scale_red, $scale_green, $scale_blue) {
	global $VERSION;

	$strwidth=imagefontwidth($font);
	$strheight=imagefontheight($font);

	$im = @imagecreate ($strwidth*strlen($label)+11,$strheight*(count($scale_low)+1)+11+imagefontheight(1)*2) or die ("Cannot Initialize new GD image stream");
	$white=imagecolorallocate($im, 255, 255, 255);
	$orange = imagecolorallocate($im, 220, 210, 60);
	$black=imagecolorallocate($im, 0,0,0);
	$gray=imagecolorallocate($im, 248, 248, 248);
	$textcolor = imagecolorallocate($im, 0, 0, 0);
	foreach ($scale_low as $indice => $value) {
		if ( ($scalecolor[$indice]=imagecolorallocate($im, $scale_red[$indice], $scale_green[$indice], $scale_blue[$indice])) =="-1") {
			$scalecolor[$indice]=imagecolorClosest($im, $scale_red[$indice], $scale_green[$indice], $scale_blue[$indice]);
		}
	}


	imagefilledrectangle($im, 0, 0, 
							  $strwidth*strlen($label)+10, $strheight*(count($scale_low)+1)+10+imagefontheight(1)*2, 
							  $black);
	imagefilledrectangle($im, 0, 0, 
							  $strwidth*strlen($label)+10, $strheight*(count($scale_low)+1)+10+imagefontheight(1), 
							  $gray);
	imagerectangle($im, 0, 0, 
							  $strwidth*strlen($label)+10, $strheight*(count($scale_low)+1)+10+imagefontheight(1)*2, 
							  $black);

	imagestring($im, $font, 4, 4, $label, $black);
	foreach ($scale_low as $indice => $value) {
			imagefilledrectangle($im, 6, $strheight*($i+1)+8, 
									  6+16, $strheight*($i+1)+$strheight+6, 
									  $scalecolor["$scale_low[$indice]:$scale_high[$indice]"]);
			imagestring($im, $font, 6+20, $strheight*($i+1)+8,$scale_low[$indice]."-".$scale_high[$indice]."%", $black);
	$i++;
	}

	imagestring($im, 1, (($strwidth*strlen($label)+10)-imagefontwidth(1)*strlen("WeatherMap4RRD $VERSION"))/2, $strheight*($i+1)+18, "WeatherMap4RRD $VERSION", $white);
	return ($im);
}
function draw_legend2($label,$font,$scale_low,$scale_high,$scale_red, $scale_green, $scale_blue) {
	global $VERSION;
	$strwidth=imagefontwidth($font);
	$strheight=imagefontheight($font);

	#$im = @imagecreate ($strwidth*strlen($label)+11,$strheight*(count($scale_low)+1)+11+imagefontheight(1)*2) or die ("Cannot Initialize new GD image stream");
	
	#$im = @imagecreate (280,55) or die ("Cannot Initialize new GD image stream");

	$x=count($scale_low)*36+6+1+$strwidth*4;
	$y=6+$strheight*2+23;
	
	$im = @imagecreate ($x,$y) or die ("Cannot Initialize new GD image stream");
	$white=imagecolorallocate($im, 255, 255, 255);
	$orange = imagecolorallocate($im, 220, 210, 60);
	$black=imagecolorallocate($im, 0,0,0);
	$gray=imagecolorallocate($im, 248, 248, 248);
	$textcolor = imagecolorallocate($im, 0, 0, 0);
	foreach ($scale_low as $indice => $value) {
		if ( ($scalecolor[$indice]=imagecolorallocate($im, $scale_red[$indice], $scale_green[$indice], $scale_blue[$indice])) =="-1") {
			$scalecolor[$indice]=imagecolorClosest($im, $scale_red[$indice], $scale_green[$indice], $scale_blue[$indice]);
		}
	}


#	imagefilledrectangle($im, 0, 0, 
#							  $x,$y,
#							  $orange);

	imagestring($im, $font, 4, 4, $label, $black);
	foreach ($scale_low as $indice => $value) {
			imagefilledrectangle($im, 
								  6+36*$i, 23, 
								  6+($i+1)*36, 23+10, 
								  $scalecolor["$scale_low[$indice]:$scale_high[$indice]"]);
			imagerectangle($im, 
								  6+36*$i, 23, 
								  6+($i+1)*36, 23+10, 
								  $black);
			imagestring($im, $font, 
								  6+($i+1)*36-5, 23+12,
								  $scale_high[$indice], 
								  #$scale_low[$indice]."-".$scale_high[$indice]."%", 
								  $black);
		$i++;
	}
	imagestring($im, $font,6+($i)*36+strlen($scale_high[$indice])*$strwidth, 23+12, "%", $black);
	imagestring($im, 1,6+($i)*36-strlen("WeatherMap4RRD $VERSION")*imagefontwidth(1),23-10, "WeatherMap4RRD $VERSION", $black);
	return ($im);
}

?>
