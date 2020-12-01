<?php
include_once("classes.php");
if(!$q) $q = sql_query($config['db']);

$tables['devprofiles']=array(
	'name'=>'devprofiles',
	'title'=>'Объект',
	'target'=>"form",
	'limit'=>'no',
	'module'=>"stdform",
	'key'=>'id',
	'class'=>'normal',
	'delete'=>'yes',
	'header'=>"",
	'table_query'=>"
		SELECT
			id,
			name,
			port,
			rucolor,
			color,
			option,
			htmlcolor
		FROM
			devprofiles
		WHERE 1 :FILTER:
		ORDER BY :SORT:
		",
	'form_query'=>"
		SELECT
			id,
			name,
			port,
			rucolor,
			color,
			option,
			htmlcolor
		FROM
			devprofiles
		",
	'filters'=>array(
		'name'=>array(
			'label'=>'',
			'type'=>'hidden',
			'style'=>'display:none',
			'access'=>array('r'=>3,'w'=>3)
		),
	),
	'defaults'=>array(
		'sort'=>'port',
		'filter'=>'build_filter_for_dprof',
	),
// 	'footer'=>array(),
	'table_triggers'=>array(
		'color'=>'get_color'
	),
	'form_triggers'=>array(
	),
	'form_autocomplete'=>array(
		'attribute'=>'reply_auto_attribute',
		'op'=>'reply_auto_op',
	),
	'before_new'=>'before_new_rattribute',
	'before_edit'=>'before_edit_rattribute',
	'before_save'=>'before_save_rattribute',
	'checks'=>'checks_rattribute',
	'group'=>'',

	// поля
	'fields'=>array(
		'id'=>array(
			'label'=>'id',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'groupname'=>array(
			'label'=>'профиль',
			'type'=>'nofield',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'attribute'=>array(
			'label'=>'Атрибут',
			'type'=>'autocomplete',
			'class'=>'nowr',
			'style'=>'width:250px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'op'=>array(
			'label'=>'Операция',
			'type'=>'autocomplete',
			'class'=>'nowr ctxt',
			'style'=>'width:60px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'value'=>array(
			'label'=>'Значение',
			'type'=>'textarea',
			'class'=>'note',
			'style'=>'width:550px;height:80px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
	),
);

function before_new_rattribute($f) {
	global $config, $q, $DEBUG;
	if(isset($_REQUEST['groupname']))
		$f['defaults']['groupname'] = str($_REQUEST['groupname']);
	$f['style']='width:700px';
	return $f;
}

function before_edit_rattribute($f) {
	global $config, $q, $DEBUG;
	$f['style']='width:700px';
	return $f;
}

function checks_rattribute($r,$my) {
	global $DEBUG, $config, $q;
	if($DEBUG>0) log_txt(__function__.": start");
}

function reply_auto_attribute() {
	global $config, $q;
	$req = (isset($_REQUEST['req']))? str($_REQUEST['req']) : '';
	$out['result'] = 'OK';
	$out['complete'] = $q->select("
		SELECT distinct attribute as label FROM devprofiles
		WHERE attribute like '%$req%'
		HAVING label!=''
		ORDER BY attribute
	");
	return $out;
}

function reply_auto_op() {
	global $config, $q;
	$req = (isset($_REQUEST['req']))? str($_REQUEST['req']) : '';
	$out['result'] = 'OK';
	$out['complete'] = $q->select("
		SELECT distinct op as label FROM devprofiles
		WHERE op like '%$req%'
		HAVING label!=''
		ORDER BY op
	");
	return $out;
}

function build_filter_for_reply($t) {
	global $tables, $_REQUEST;
	$r = array(); $s = '';
	$a = $tables[$t]['filters'];
	if(is_array($a)){
		foreach($a as $k=>$v){
			if(isset($_REQUEST[$k]) && $v!='') $r[] = "AND `$k`='".str($_REQUEST[$k])."'";
		}
		$s .= implode(' ',$r);
	}
 	log_txt(__function__.": return: $s");
	return $s;
}
?>
