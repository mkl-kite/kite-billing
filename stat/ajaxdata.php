<?php
if(@$_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest') {
	include_once("log.php");
	include_once("defines.php");
	include_once("utils.php");
	include_once("authorize.php");

	$object=(key_exists('GeoJSON',$_REQUEST))? $_REQUEST['GeoJSON'] : false;

	$go = (array_key_exists('go',$_REQUEST))? strict($_REQUEST['go']) : '';
	$do = (array_key_exists('do',$_REQUEST))? strict($_REQUEST['do']) : '';

	$dir = "ajax/";
	if (is_dir($dir)) {
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if(filetype($dir . $file)=='file' && preg_match('/.php$/',$file)) {
					$calls[]=array(
						'file'=>preg_replace('/[\r\n]/','',$dir.$file),
						'call'=>preg_replace('/.php$/','',$file)
					);
				}
			}
			closedir($dh);
		}else{
			log_txt("невозможно открыть каталог: $dir");
			stop(array('result'=>'ERROR','desc'=>"невозможно открыть каталог: $dir"));
		}
	}
	if(count($calls)>0) {
		$module_found=false;
		foreach($calls as $a) {
			if($go==$a['call']) { 
				$module_found=true;
				include($a['file']); 
				break;
			}
		}
		if(!$module_found) {
			log_txt("Модуль обработки $go не найден!");
			stop(array('result'=>'ERROR','desc'=>"Модуль обработки не найден!<BR>go=$go<BR>"));
		}
	}else{
		log_txt("отсутствуют модули!");
		stop(array('result'=>'ERROR','desc'=>'отсутствуют модули'));
	}
}
?>
