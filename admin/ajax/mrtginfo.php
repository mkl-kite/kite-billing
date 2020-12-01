<?php
include_once("defines.php");
include_once("classes.php");
include_once("utils.php");
define("MRTG_DIR","/usr/local/etc/mrtg");
define("MRTG_HTTP","mrtg");
if(!isset($q)) $q = new sql_query($config['db']);

function readInfo($file) {
	$info='';
 	log_txt("читаем файл $file");
	if ($pfile=@fopen($file,"r")) {
		while (($buffer = fgets($pfile, 4096)) !== false) {
			$info.=$buffer;
		}
		fclose($pfile);
	}else{
		return '';
	}
	return $info;
}

function saveInfo($file,$what) {
	if($pfile=@fopen ($file,"w")) {
		fputs($pfile,"$what");
		fclose($pfile);
		log_txt("запись в файл $file '$what'");
		return true;
	}else{
		log_txt("Не удалось записать файл $file");
		return false;
	}
}

if($do=='edit') {
	$device=str($_REQUEST['device']);
	$m=preg_split('/_/',$device);
	if(count($m)==2) {
		$info=readInfo(MRTG_HTTP."/".$m[0]."/".$device.".info");
		$f=array(
			'class'=>'normal',
			'fields'=>array(
				'ip'=>array(
					'type'=>'hidden',
					'value'=>$m[0]
				),
				'port'=>array(
					'type'=>'hidden',
					'value'=>$m[1]
				),
				'info'=>array(
					'label'=>'Название порта',
					'type'=>'textarea',
					'value'=>$info
				),
				'go'=>array(
					'type'=>'hidden',
					'value'=>'mrtginfo'
				),
				'do'=>array(
					'type'=>'hidden',
					'value'=>'save'
				)
			),
			'name'=>'portname_object',
			'id'=>'new'
		);
		$out['form']=$f;
	}else{
		log_txt("mrtginfo.php: device='$device'");
		$out['result']='ERROR';
		$out['desc']='Неправильные входные данные!';
	}
	stop($out);

}elseif($do=='save') {
	$dir = MRTG_HTTP.preg_replace(array('/\/$/','/.*\//'),array('',''),$dev[$mydev]['workdir']);
	$info=str($_REQUEST['info']);
	$ip=str($_REQUEST['ip']);
	$port=str($_REQUEST['port']);
	if(saveInfo(MRTG_HTTP."/".$ip."/".$ip."_".$port.".info",$info)) {
		$r = $q->select("SELECT address, mrtg FROM map WHERE mrtg LIKE '%".$ip."_".$port."-%'",1);
		$resp='порт '.$port;
		if($r['address']!='') $resp.=" : ".$r['address'];
		if($info!='') $resp.=" : ".$info;
		$out['result']='OK';
		$out['nameport']=$resp;
	}else{
		$out['result']='ERROR';
		$out['desc']='Не удалось записать файл!';
	}

}else{
	log_txt("Неизвестная команда\n REQUEST = ".sprint_r($_REQUEST));
	$out['result']='ERROR';
	$out['desc']='Неизвестная команда!';
}

stop($out);
?>
