<?php

function checkservice($ip, $port)
{
	$fp = @fsockopen("$ip", $port, &$errno, &$errstr, 10);
	if(!$fp) {
		return (0);
	} else {
		return (1);
	}
}

function ping($ip){
	exec("ping -c 1 $ip", $list); 
	if (strpos($list[1],"Destination Host Unreachable")) {
		return 0;
	} else {
		preg_match("/time=([0-9|.]*) ms/",$list[1],$matches);
		return $matches[1];
	}
}
?>
