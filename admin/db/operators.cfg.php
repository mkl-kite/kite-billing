<?php
include_once("classes.php");
include_once("rayon_packet.cfg.php");
include_once("geodata.php");
$errors = array();

$tables['operators']=array(
	'title'=>'Служащие',
	'target'=>'form',
	'module'=>"stdform",
	'key'=>'unique_id',
	'limit'=>'yes',
	'query'=>"
		SELECT 
			unique_id,
			photo,
			status,
			groups,
			login,
			fio,
			pass,
			homephone,
			workphone,
			workphone1,
			blocked
		FROM
			operators
		",
	'table_query'=>"
		SELECT 
			unique_id,
			login,
			fio,
			status,
			homephone,
			workphone,
			workphone1
		FROM
			operators
		WHERE 1 :FILTER:
		ORDER BY :SORT:
		",
		'class'=>'normal',
	'delete'=>'no',
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
		'filter'=>'build_filter_for_operators'
	),
	'layout'=>array(
		'rphoto'=>array(
			'type'=>'fieldset',
			'legend'=>'',
			'style'=>'width:160px;height:205px;float:left;border:none',
			'fields'=>array('photo')
		),
		'operator'=>array(
			'type'=>'fieldset',
			'legend'=>'',
			'style'=>'width:350px;height:260px;float:left;border:none',
			'fields'=>array('fio','login','pass','status','groups','address','homephone','workphone','workphone1','blocked')
		),
	),

// 	'footer'=>array(),
	'checks'=>array(
		'save'=>'check_operator_for_save'
	),
// 	преобразование данных формы к заданному формату base->server->client
	'form_triggers'=>array(
		'photo'=>'operator_photo'
	),
	'form_check_exist'=>array('login'),
	'table_triggers'=>array(
		'status'=>'get_operatorstatus',
	),
	'before_edit'=>'before_edit_operator',
	'before_save'=>'before_save_operator',
 	'form_onsave'=>'onsave_operator',
	'allow_delete'=>'allow_delete_operator',
	'group'=>'',

	// поля
	'fields'=>array(
		'unique_id'=>array(
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
		'status'=>array(
			'label'=>'доступ',
			'type'=>'select',
			'list'=>$op_status,
			'class'=>'fio',
			'style'=>'width:140px',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>5),
		),
		'groups'=>array(
			'label'=>'группы',
			'type'=>'text',
			'class'=>'txt',
			'style'=>'width:150px',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>5,'g'=>'admins'),
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
		'login'=>array(
			'label'=>'логин',
			'type'=>'text',
			'class'=>'fio',
			'style'=>'width:100px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'pass'=>array(
			'label'=>'пароль',
			'type'=>'password',
			'style'=>'width:100px',
			'native'=>true,
			'access'=>array('r'=>5,'w'=>5)
		),
		'homephone'=>array(
			'label'=>'дом.телефон',
			'type'=>'text',
			'style'=>'width:150px',
			'class'=>'phone',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'workphone'=>array(
			'label'=>'раб.телефон',
			'type'=>'text',
			'style'=>'width:150px',
			'class'=>'phone',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'workphone1'=>array(
			'label'=>'раб.телефон1',
			'type'=>'text',
			'style'=>'width:150px',
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
		'login'=>'Всего:',
		'workphone1'=>'fcount',
 	)
);

function before_edit_operator($f) {
	global $DEBUG, $config, $q, $home_fields, $map_home_fields;
	$f['style']='width:580px';
	return $f;
}


function before_save_operator($cmp,$old) {
	global $config, $opdata;
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

function check_operator_for_save($r) {
	global $config, $errors, $DEBUG;
	$result = true;
	return $result;
}

function onsave_operator($id,$res) {
	global $config, $operator_types, $opdata;
	return true;
}

function allow_delete_operator($r) {
	global $config, $q;
	if(!$q) $q = new sql_query($config);
	return "Удаление оператора<br>возможно только администратором базы данных!";
}

function build_filter_for_operators($t) {
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
//	log_txt(__function__.": return: $s");
	return $s;
}

function operator_photo($v,$r) {
	return photo_link($v);
}

function get_operatorstatus($v,$r=null,$fn=null) {
	global $op_status;
	return isset($op_status[$v])? $op_status[$v] : $v;
}

?>
