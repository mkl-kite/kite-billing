<?php
include_once("defines.php");
include_once("classes.php");
if(!isset($q)) $q = new sql_query($config['db']);
header("Content-Type: image/jpeg");
if(!($photo = $q->get_blob('photo',$_GET['id']))) {
	$file='pic/unknown.png';
	if(!($fd = fopen($file,"r"))){
		log_txt("photo.php: Файл $file не найден!");
		exit(0);
	}
	while(!feof($fd)) echo fread($fd, 8192);
	fclose($fd);
}else{
	echo $photo;
}
?>
