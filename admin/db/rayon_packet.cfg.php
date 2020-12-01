<?php
include_once("classes.php");
include_once("table.php");
include_once("rayon.cfg.php");

$tables['rayon_packet']=array(
	'name'=>'rayon_packet',
	'title'=>'район',
	'target'=>"form",
	'module'=>"stdform",
	'key'=>'unique_id',
	'action'=>'references.php',
	'class'=>'normal',
	'delete'=>'yes',
	'table_query'=>"
		SELECT rp.unique_id, p.name
		FROM rayon_packet rp, rayon r, packets p
		WHERE r.rid=rp.rid AND rp.gid=p.pid :FILTER:
		ORDER BY p.num
	",
	'form_query'=>"SELECT unique_id, rid, gid FROM rayon_packet",
	'form_triggers'=>array(
	),
	'table_triggers'=>array(
		'rid'=>'get_rayon_name',
		'gid'=>'get_packet_name'
	),
	'defaults'=>array(
		'filter'=>'',
	),
 	'before_new'=>'rp_before_new',
 	'before_edit'=>'rp_before_edit',
	'group'=>'',

	// поля
	'fields'=>array(
		'unique_id'=>array(
			'label'=>'id',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'rid'=>array(
			'label'=>'Район',
			'type'=>'select',
			'style'=>'width:150px',
			'list'=>'rp_list_of_rayons',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>5)
		),
		'gid'=>array(
			'label'=>'Тариф',
			'type'=>'select',
			'list'=>'rp_list_of_packets',
			'style'=>'width:250px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>5)
		),
		'name'=>array(
			'label'=>'Тариф',
			'type'=>'text',
			'style'=>'width:250px',
			'native'=>false,
			'access'=>array('r'=>1,'w'=>5)
		),
	)
);

function rp_before_new($f) {
	global $tables, $config, $q, $DEBUG;
 	if(!$q) $q = new sql_query($config['db']);
	$rid = isset($_REQUEST['id'])? str($_REQUEST['id']) : 0;
	if($rid!='new') $rid = numeric($rid);
	$f['id']='new';
	$f['defaults']['rid'] = $rid;
	$f['fields']['rid']['disabled']='true';
	if($rid=='new'){
		$fld = array('r_name','latitude','longitude','zoom');
		$rn = array_intersect_key($tables['rayon']['defaults'],array_flip($fld));
		$rn['id'] = $q->insert('rayon',$rn);
		if($rn['id']){
			log_txt("Создал район {$rn['id']}");
			$f['defaults']['rid'] = $rn['id'];
			$f['setNewVal'] = $rn;
		}
	}
	return $f;	
}

function rp_list_of_rayons() {
	global $config, $q, $DEBUG;
	$q = new sql_query($config['db']);
	return $q->fetch_all("SELECT rid, r_name FROM rayon","rid");
}

function rp_list_of_packets($r) {
	global $config, $in;
	$q = new sql_query($config['db']);
	return $q->fetch_all("
		SELECT pid, name FROM packets WHERE pid not in (
			SELECT gid FROM rayon_packet WHERE rid='{$in['id']}'
		) ORDER BY num
	","pid");
}

function rp_onsave($id,$s) {
	global $config;
	$q=new sql_query($config['db']);
	if($d = $q->select("SELECT * FROM rayon_packet WHERE unique_id='$id'",1)){
		return array('result'=>'OK','reload'=>1);
	}else{
		return false;
	}
}

function get_rayon_name($v,$r,$fn=null) {
	global $cache, $config;
	if(!key_exists('rayons',$cache)) {
		$q = new sql_query($config['db']);
		$cache['rayons'] = $q->fetch_all("SELECT rid, r_name FROM rayon",'rid');
	}
	return $cache['rayons'][$v];
}

function get_packet_name($id,$r=null,$fn=null) {
	global $cache, $config;
	if(!key_exists('packets',$cache)) {
		$q = new sql_query($config['db']);
		$cache['packets'] = $q->fetch_all("SELECT pid, name FROM packets",'pid');
	}
	return $cache['packets'][$id];
}
?>
