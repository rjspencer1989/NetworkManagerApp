<?php
	$json = $_POST['json'];	
	if(isset($json)){
		$dataArray = json_decode($json, TRUE);
		$router = $dataArray['router'];
		$fh = fopen("weathermap/".$router."/weathermap.conf", "w");
		fwrite("#Weathermap configuration file\n");
		fwrite("HEIGHT 600\n");
		fwrite("WIDTH 740\n");
		fwrite("FONT 4\n");
		fwrite("LEGENDPOS 300 460\n");
		fwrite("LEGENDSTYLE line\n");
		fwrite("TITLE \"Home Network Map\"\n");
		fwrite("TITLEPOS 4 470\n");
		fwrite("TITLEBACKGROUND 255 255 128\n");
		fwrite("TITLEFOREGROUND 0 0 0\n");
		fwrite("REFRESH 60\n");
		fwrite("OUTPUTFILE weathermap/".$id."/weathermap.png\n");
		fwrite("HTMLFILE weathermap/".$id."/weathermap.html\n");
		fwrite("SCALE 1 10 140 0 255\n");
		fwrite("SCALE 10 25 32 32 255\n");
		fwrite("SCALE 25 40 0 192 255\n");
		fwrite("SCALE 40 55 0 240 0\n");
		fwrite("SCALE 55 70 240 240 0\n");
		fwrite("SCALE 70 85 255 192 0\n");
		fwrite("SCALE 85 90 255 100 0\n");
		fwrite("SCALE 90 100 255 0 0\n");
		fwrite("NODE router\n");
		fwrite("POSITION 60 40\n");
		fwrite("LABEL Router\n");
		fwrite("ICON weathermap/icons/wireless_router.png\n");
		foreach($dataArray['device'] as $device){
			$name = $device['macAddress'] . ".rrd";
			$bytesIn = $device['in'];
			$bytesOut = $device['out'];
			$labelText = $device['label'];
			$x = rand(60, 660);
			$y = rand(60, 510);
			if(!file_exists("weathermap/".$router."/".$name)){
				exec("rrdtool create weathermap/".$router."/".$name." --start N --step=60 DS:out:GAUGE:60:U:U DS:in:GAUGE:60:U:U RRA:AVERAGE:0.5:1:24");
			}
			exec("rrdtool update weathermap/".$router."/".$name." N:$bytesout:$bytesin");
			fwrite("NODE ".$device['macAddress']."\n");
			fwrite("POSITION " . $x ." " .$y ."\n");
			fwrite("LABEL " . $labelText ."\n");
			fwrite("LIfwriteNK " . $device['macAddress']."-router\n");
			fwrite("TARGET weathermap/".$router."/".$name."\n");
			fwrite("INPOS 1\n");
			fwrite("OUTPOS 2\n");
			fwrite("UNIT bytes\n");
			fwrite("BANDWIDTH 50000\n");
			fwrite("DISPLAYVALUE 1\n");
			fwrite("ARROW normal\n");
		}
		fclose($fh);
	}
?>