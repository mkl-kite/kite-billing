<?php
include_once("classes.php");
if(!$q) $q = sql_query($config['db']);

$tables['radgroupcheck']=array(
	'name'=>'radgroupcheck',
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
			attribute,
			op,
			value
		FROM
			radgroupcheck
		WHERE 1 :FILTER:
		ORDER BY :SORT:
		",
	'form_query'=>"
		SELECT
			id,
			groupname,
			attribute,
			op,
			value
		FROM
			radgroupcheck
		",
	'filters'=>array(
		'groupname'=>array(
			'label'=>'',
			'type'=>'hidden',
			'style'=>'display:none',
			'value'=> isset($_REQUEST['groupname'])? strict($_REQUEST['groupname']): ""
		),
	),
	'defaults'=>array(
		'sort'=>'groupname',
		'filter'=>'build_filter_for_chacks',
	),
// 	'footer'=>array(),
	'table_triggers'=>array(
		'rayon'=>'get_rayon'
	),
	'form_triggers'=>array(
	),
	'form_autocomplete'=>array(
		'attribute'=>'check_auto_attribute',
		'op'=>'check_auto_op',
	),
	'before_new'=>'before_new_attribute',
	'before_edit'=>'before_edit_attribute',
	'before_save'=>'before_save_attribute',
	'checks'=>'checks_attribute',
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
			'table_style'=>'width:150px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'op'=>array(
			'label'=>'Операция',
			'type'=>'autocomplete',
			'class'=>'nowr ctxt',
			'style'=>'width:60px',
			'table_style'=>'width:50px',
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

function before_new_attribute($f) {
	global $config, $q, $DEBUG;
	if(isset($_REQUEST['groupname']))
		$f['defaults']['groupname'] = str($_REQUEST['groupname']);
	$f['style']='width:700px';
	return $f;
}

function before_edit_attribute($f) {
	global $config, $q, $DEBUG;
	$f['style']='width:700px';
	return $f;
}

function checks_attribute($r,$my) {
	global $DEBUG, $config, $q;
	if($DEBUG>0) log_txt(__function__.": start");
}

function check_auto_attribute() {
	global $config, $q;
	$req = (isset($_REQUEST['req']))? str($_REQUEST['req']) : '';
	$out['result'] = 'OK';
	$out['complete'] = $q->select("
		SELECT distinct attribute as label FROM radgroupcheck
		WHERE attribute like '%$req%'
		HAVING label!=''
		ORDER BY attribute
	");
	return $out;
}

function check_auto_op() {
	global $config, $q;
	$req = (isset($_REQUEST['req']))? str($_REQUEST['req']) : '';
	$out['result'] = 'OK';
	$out['complete'] = $q->select("
		SELECT distinct op as label FROM radgroupcheck
		WHERE op like '%$req%'
		HAVING label!=''
		ORDER BY op
	");
	return $out;
}

function build_filter_for_chacks($t) {
	global $tables, $_REQUEST;
	$r = array(); $s = '';
	$a = $tables[$t]['filters'];
	if(is_array($a)){
		foreach($a as $k=>$v){
			if(isset($_REQUEST[$k]) && $v!='') $r[] = "AND `$k`='".str($_REQUEST[$k])."'";
		}
		$s .= implode(' ',$r);
	}
//  	log_txt(__function__.": return: $s");
	return $s;
}
?>
