<?php
	chdir("..");
	$id = sha1(uniqid("", TRUE));
	if(!is_dir("weathermap/".$id)){
		mkdir("weathermap/".$id, "0777");
	}
	echo $id;
?>