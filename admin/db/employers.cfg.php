<?php
include_once("classes.php");
include_once("rayon_packet.cfg.php");
include_once("geodata.php");
$errors = array();

$tables['employers']=array(
	'title'=>'Служащие',
	'target'=>'form',
	'module'=>"employers",
	'class'=>'normal',
	'delete'=>'no',
	'key'=>'eid',
	'limit'=>'yes',
	'query'=>"
		SELECT 
			eid,
			photo,
			fio,
			address,
			seat,
			category,
			homephone,
			workphone,
			workphone1,
			blocked
		FROM
			employers
		",
	'table_query'=>"
		SELECT 
			eid,
			fio,
			address,
			seat,
			homephone,
			workphone,
			workphone1
		FROM
			employers
		WHERE 1 :FILTER:
		ORDER BY :SORT:
		",
	'filters'=>array(
		'blocked'=>array(
			'type'=>'select',
			'label'=>'уволен',
			'title'=>'блокированы',
			'list'=>array('нет','да'),
			'value'=>0
		)
	),
	'defaults'=>array(
		'sort'=>'fio',
		'filter'=>'build_filter_for_employers'
	),
	'layout'=>array(
		'rphoto'=>array(
			'type'=>'fieldset',
			'legend'=>'',
			'style'=>'width:160px;height:205px;float:left;border:none',
			'fields'=>array('photo')
		),
		'employer'=>array(
			'type'=>'fieldset',
			'legend'=>'',
			'style'=>'width:350px;height:245px;float:left;border:none',
			'fields'=>array('fio','address','seat','category','homephone','workphone','workphone1','blocked')
		),
	),

// 	'footer'=>array(),
	'checks'=>array(
		'save'=>'check_employer_for_save'
	),
// 	преобразование данных формы к заданному формату base->server->client
	'form_triggers'=>array(
		'photo'=>'employer_photo'
	),
	'table_triggers'=>array(
		'type'=>'get_employertype',
		'status'=>'get_employerstatus',
		'employertime'=>'get_employertime',
	),
	'before_save'=>'before_save_employer',
// 	'form_onsave'=>'onsave_employer',
	'allow_delete'=>'allow_delete_employer',
	'group'=>'',

	// поля
	'fields'=>array(
		'eid'=>array(
			'label'=>'id',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'photo'=>array(
			'type'=>'photo',
			'style'=>'width:150px;height:200px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>4)
		),
		'fio'=>array(
			'label'=>'Ф.И.О.',
			'type'=>'text',
			'class'=>'fio',
			'style'=>'width:240px',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>4),
		),
		'address'=>array(
			'label'=>'адрес',
			'type'=>'autocomplete',
			'class'=>'fio',
			'style'=>'width:200px',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>4),
		),
		'seat'=>array(
			'label'=>'должность',
			'type'=>'text',
			'class'=>'fio',
			'style'=>'width:200px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'category'=>array(
			'label'=>'категория',
			'type'=>'text',
			'class'=>'',
			'style'=>'width:50px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'homephone'=>array(
			'label'=>'дом.телефон',
			'type'=>'text',
			'style'=>'width:120px',
			'class'=>'phone',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'workphone'=>array(
			'label'=>'раб.телефон',
			'type'=>'text',
			'style'=>'width:120px',
			'class'=>'phone',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'workphone1'=>array(
			'label'=>'раб.телефон1',
			'type'=>'text',
			'style'=>'width:120px',
			'class'=>'phone',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'blocked'=>array(
			'label'=>'уволен',
			'type'=>'select',
			'list'=>array('нет','да'),
			'native'=>true,
			'access'=>array('r'=>3,'w'=>4),
		),
	),
 	'table_footer'=>array(
		'fio'=>'Всего:',
		'workphone1'=>'fcount',
 	)
);

function before_save_employer($cmp,$old) {
	global $config, $opdata, $q;
	$r = array_merge($old,$cmp);
	if(isset($cmp['photo'])) {
		log_txt(__function__.": cmp[photo]: '{$cmp['photo']}'");
		$newphoto = preg_replace('/.*[\\\\\/]/','',$cmp['photo']);
		log_txt(__function__.": tmp_photo: '$newphoto'");
		$file = PHOTO_FOLDER.$newphoto;
		if(is_file($file)){
			if(PHOTO_TARGET==1){
				$cmp['photo'] = $newphoto;
				if(is_file(PHOTO_FOLDER.$old['photo'])) fdelete(PHOTO_FOLDER.$old['photo']);
			}else{
				if(!isset($q)) $q = new sql_query($config['db']);
				if($id = $q->file2blob('photo',$file,'image')) $cmp['photo'] = $id;
				else $cmp['photo'] = '';
				fdelete($file);
				if(isset($old['photo']) && $old['photo']>0) $q->del('photo',$old['photo']);
			}
		}else{
			fdelete($cmp['photo']);
			$cmp['photo'] = '';
		}
		log_txt(__function__.": new photo: '{$cmp['photo']}'");
	}
	if(isset($cmp['fio'])) $cmp['fio'] = trim($cmp['fio']);
	if(isset($cmp['homephone'])) $cmp['homephone'] = normalize_phone($cmp['homephone']);
	if(isset($cmp['workphone'])) $cmp['workphone'] = normalize_phone($cmp['workphone']);
	if(isset($cmp['workphone1'])) $cmp['workphone1'] = normalize_phone($cmp['workphone1']);
// 	log_txt(__function__.": cmp: ".arrstr($cmp));
	return $cmp;
}

function check_employer_for_save($r) {
	global $config, $errors, $DEBUG;
	$result = true;
	return $result;
}

function onsave_employer($id,$res) {
	global $config, $employer_types, $opdata;
	if(!is_numeric($id)) $id = $res['id'];	
	if(@$res['id']=='new')
	return true;
}

function allow_delete_employer($r) {
	global $config;
	$q = new sql_query($config);
	$cp = $q->select("SELECT * FROM workpeople WHERE employer='{$r['eid']}' LIMIT 1",1);
	if($cp) return "Удаление невозможно! Присутствуют связанные данные.";
	return 'yes';
}

function build_filter_for_employers($t) {
	global $tables, $_REQUEST;
	$r = array(); $s = '';
	$a = $tables[$t]['filters'];
	if(is_array($a)){
		foreach($a as $k=>$v){
			if(isset($_REQUEST[$k]) && $v!='') $r[] = "AND `$k`='".strict($_REQUEST[$k])."'";
		}
		$s = implode('',$r);
	}
	if($s == '') $s = "AND blocked=0";
	log_txt(__function__.": return: $s");
	return $s;
}

function employer_photo($v,$r) {
	return photo_link($v);
}

?>
