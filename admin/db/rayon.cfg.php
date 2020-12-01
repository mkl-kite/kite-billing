<?php
include_once("classes.php");
include_once("table.php");
include_once("rayon_packet.cfg.php");

$owns = ''; $fields = false;
if(isset($config['owns'])) {
	$owns = array(); $fields = array();
	foreach($config['owns'] as $k=>$v) {
		$owns[] = "sum(if(u.last_connection>date_add(now(),interval -3 month) AND u.source='{$k}',1,0)) as `{$k}`,";
		$fields[$k] = array(
			'label'=>$v,
			'type'=>'text',
			'class'=>'summ',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>5)
		);
	}
}
if($owns) $owns = implode("\n",$owns);

$tables['rayon']=array(
	'name'=>'rayon',
	'title'=>'район',
	'target'=>"html",
	'module'=>"stdform",
	'key'=>'rid',
	'action'=>'references.php',
	'class'=>'normal',
	'delete'=>'yes',
// 	'footer'=>array(),
	'table_query'=>"
		SELECT 
			r.rid, 
			r_name,
			{$owns}
			sum(if(u.last_connection>date_add(now(),interval -3 month),1,0)) as uon,
			sum(if(u.last_connection<date_add(now(),interval -3 month),1,0)) as uoff,
			sum(if(u.user is not null,1,0)) as uall
		FROM rayon r 
			LEFT OUTER JOIN users u ON u.rid=r.rid 
		GROUP BY 
			r.r_name
		ORDER BY :SORT:
		",
	'form_query'=>"
		SELECT 
			rid as id, 
			r_name, 
			latitude, 
			longitude, 
			zoom,
			1 as packets
		FROM rayon 
		ORDER BY r_name
		",
	'defaults'=>array(
		'sort'=>'r_name',
		'r_name'=>'Новый район',
		'latitude'=>'',
		'longitude'=>'',
		'zoom'=>15,
	),
	'table_menu'=>array(
		'edit'=>array('label'=>"<img src=\"pic/gtk-edit16.png\"> изменить",'to'=>'form','query'=>"go=stdform&do=edit&table=rayon"),
		'usrlist'=>array('label'=>"<img src=\"pic/doc.png\"> пользователи",'to'=>'references.php','query'=>"go=stdtable&do=users&tname=users&key=rid"),
	),
	'table_triggers'=>array(
	),
	'form_triggers'=>array(
		'packets'=>'get_packets'
	),
	'layout'=>array(
		'rdata'=>array(
			'type'=>'fieldset',
			'legend'=>'Данные района',
			'style'=>'width:370px;height:160px;',
			'fields'=>array('r_name','latitude','longitude','zoom')
		),
		'packets'=>array(
			'type'=>'fieldset',
			'legend'=>'Список разрешённых пакетов <img class="add-button" src="pic/add.png">',
			'style'=>'width:370px;height:140px;',
			'fields'=>array('packets')
		),
	),
	'form_check_exist'=>array('r_name'),
	'allow_delete'=>'rayon_allow_delete',
	'before_new'=>'before_new_rayon',
	'before_save'=>'before_save_rayon',
	'allow_delete'=>'allow_delete_rayon',
	'before_delete'=>'before_delete_rayon',
	'form_onsave'=>'rayon_onsave',
	'group'=>'',
 	'table_footer'=>array(
		'r_name'=>'Всего:',
		'uon'=>'fsum',
		'uoff'=>'fsum',
		'uall'=>'fsum',
		'ip'=>'user_ip_address',
 	),

	// поля
	'fields'=>array(
		'id'=>array(
			'label'=>'id',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>5)
		),
		'r_name'=>array(
			'label'=>'Название',
			'type'=>'text',
			'style'=>'width:250px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>5)
		),
		'latitude'=>array(
			'label'=>'Широта',
			'type'=>'text',
			'style'=>'width:150px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>5)
		),
		'longitude'=>array(
			'label'=>'Долгоота',
			'type'=>'text',
			'style'=>'width:150px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>5)
		),
		'zoom'=>array(
			'label'=>'Масштаб',
			'type'=>'text',
			'style'=>'width:40px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>5)
		),
		'uon'=>array(
			'label'=>'Живых',
			'type'=>'text',
			'class'=>'summ',
			'native'=>false,
			'access'=>array('r'=>2,'w'=>5)
		),
		'uoff'=>array(
			'label'=>'Ушедших',
			'type'=>'text',
			'class'=>'summ',
			'native'=>false,
			'access'=>array('r'=>2,'w'=>5)
		),
		'uall'=>array(
			'label'=>'Всего',
			'type'=>'text',
			'class'=>'summ',
			'native'=>false,
			'access'=>array('r'=>2,'w'=>5)
		),
		'packets'=>array(
			'label'=>'Список заявителей',
			'type'=>'subform',
			'tname'=>'rayon_packet',
			'sub'=>'get_subform_of_packets',
			'native'=>false,
			'access'=>array('r'=>2,'w'=>5)
		),
	)
);

if($fields) $tables['rayon']['fields'] = array_merge($tables['rayon']['fields'],$fields);
if($owns) foreach($config['owns'] as $k=>$v) $tables['rayon']['table_footer'][$k] = 'fsum';

function before_new_rayon($f) {
	global $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	if($q->select("SELECT count(*) FROM packets",4)===0)
		stop(array('result'=>"ERROR",'desc'=>"Сперва необходимо создать тарифный пакет!"));
	$f['defaults']['r_name'] = 'Новый район';
	$f['defaults']['zoom'] = 15;
	return $f;
}


function before_save_rayon($c,$o) {
	if(isset($c['latitude']) && ($c['latitude']==='' || $c['latitude']==0)) $c['latitude']=null;
	if(isset($c['longitude']) && ($c['longitude']==='' || $c['longitude']==0)) $c['longitude']=null;
	return $c;
}

function rayon_onsave($id) {
	if($d = get_rayons()){
		return array('result'=>'OK','rayons'=>$d);
	}else{
		return false;
	}
}

function get_subform_of_packets($id) {
	global $DEBUG, $config, $opdata, $tables;
	if($DEBUG>0) log_txt("get_subform_of_packets: id=$id");
	// делаем выборку пакетов
	$tname = 'rayon_packet';
	$t=array_merge($tables[$tname],array(
		'type'=>'table',
		'limit'=>'no',
		'module'=>'stdform',
		'filter'=>"AND rp.rid='$id'",
		'style'=>'width:100%',
		'name'=>$tname
	));
	$c = new Table($t);
	return array(
		'class'=>'subform',
		'style'=>'width:100%;height:100%;overflow:auto;background:#F5EFE9',
		'table'=>$c->get()
	);
}

function rayon_allow_delete($id) {
	global $config, $q;
	if(!$q) $q=new sql_query($config['db']);
	$usr = $q->select("SELECT count(*) from users WHERE rid=$id",4);
	$upay = $q->select("SELECT count(*) from pay WHERE rid=$id",4);
	if($usr>0 || $upay>0) 
		return "Ещё присутствуют записи связанные с этим районом!<BR> users: $usr<BR> pay: $upay";
	else return 'yes';
}

function before_delete_rayon($r) {
	global $config;
	$q=new sql_query($config['db']);
	if(!is_array($r)) $r = array('rid'=>$r);
	else $r = array('rid'=>$r['id']);
	$rp = $q->del("rayon_packet",$r);
	return $rp;
}

function get_rayon($id,$r=null,$fn=null) {
	global $config, $cache;
	if(!isset($cache['rayons'])) {
		$q = new sql_query($config['db']);
		$cache['rayons'] = $q->fetch_all("SELECT rid, r_name FROM rayon ORDER BY r_name",'rid');
	}
	return $cache['rayons'][$id];
}

function allow_delete_rayon($id,$my) {
	global $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	if(is_array($id)) { $r = $id; $id = $r['woid']; } 
	if(!is_numeric($id) || $id == 0) {
		log_txt(__function__.": ERROR not found 'rid'");
		stop(array('result'=>'ERROR', 'desc'=>"Район не найден!"));
	}
	$cp = $q->select("SELECT * FROM users WHERE rid='{$id}' LIMIT 1",1);
	if($cp) return "Удаление невозможно!<br> В этом районе есть клиенты.";
	return 'yes';
}

function get_rayons() {
	global $opdata, $config;
	$q=new sql_query($config['db']);
	$sql="
		SELECT rid as id, r_name as name, latitude, longitude, zoom 
		FROM rayon 
		ORDER BY r_name
	";
	return $q->select($sql);
}
?>
