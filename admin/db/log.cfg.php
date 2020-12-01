<?php
include_once("classes.php");
include_once("rayon.cfg.php");
if(!$q) $q = sql_query($config['db']);

$tables['log']=array(
	'name'=>'log',
	'title'=>'Объект',
	'target'=>"no",
	'limit'=>'yes',
	'add'=>'no',
	'module'=>"none",
	'key'=>'unique_id',
	'delete'=>'no',
	'table_query'=>"
		SELECT 
			unique_id,
			`date`,
			admin,
			user,
			action,
			content
		FROM
			log
		WHERE 1 :FILTER: :PERIOD:
		ORDER BY :SORT:
		",
	'form_query'=>"
		SELECT 
			unique_id,
			`date`,
			admin,
			user,
			uid,
			action,
			content
		FROM
			log
		",
	'filters'=>array(
		'uid'=>array(
			'type'=>'hidden',
			'label'=>'uid',
			'title'=>'id клиента',
			'value'=>isset($_REQUEST['uid'])? numeric($_REQUEST['uid']) : $uid,
		),
		'admin'=>array(
			'type'=>'select',
			'label'=>'оператор',
			'title'=>'оператор',
			'list'=>all2array(list_operators()),
			'style'=>'width:80px',
			'value'=>'_'
		),
		'content'=>array(
			'type'=>'text',
			'label'=>'контент',
			'title'=>'поиск по содержимому',
			'style'=>'width:80px',
			'value'=>''
		),
		'user'=>array(
			'type'=>'autocomplete',
			'label'=>'клиент',
			'title'=>'логин клиента',
			'style'=>'width:80px',
			'value'=>''
		),
		'end'=>array(
			'type'=>'date',
			'label'=>'конец',
			'style'=>'width:80px',
			'title'=>'дата конца',
			'value'=>date('d-m-Y')
		),
		'begin'=>array(
			'type'=>'date',
			'label'=>'начало',
			'style'=>'width:80px',
			'title'=>'дата начала',
			'value'=>cyrdate(strtotime(((isset($_REQUEST['uid']) && $_REQUEST['uid']>0)? '-3 year':'today')))
		),
	),
	'header'=>"",
	'defaults'=>array(
		'sort'=>'`date`',
		'filter'=>'build_filter_for_log',
		'period'=>'build_period_for_log',
	),
	'class'=>'normal',
// 	'footer'=>array(),
	'table_triggers'=>array(
		'date'=>'cell_atime',
		'user'=>'get_log_user',
		'rayon'=>'get_rayon',
		'admin'=>'get_log_opepator',
		'action'=>'get_log_action',
	),
 	'table_footer'=>array(
		'date'=>'Всего:',
		'content'=>'fcount',
 	),
	'form_triggers'=>array(
	),
	'form_autocomplete'=>array(
		'user'=>'log_auto_user',
	),
	'before_new'=>'before_new_log',
	'before_save'=>'before_save_log',
	'form_onsave'=>'onsave_log',
	'checks'=>'checks_log',
	'group'=>'',

	// поля
	'fields'=>array(
		'unique_id'=>array(
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'date'=>array(
			'label'=>'время',
			'type'=>'date',
			'style'=>'width:80px',
			'class'=>'date nowr',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'admin'=>array(
			'label'=>'оператор',
			'type'=>'select',
			'list'=>'list_operators',
			'class'=>'nowr ctxt',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'user'=>array(
			'label'=>'логин',
			'type'=>'autocomplete',
			'style'=>'width:150px',
			'class'=>'nowr ctxt',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'uid'=>array(
			'label'=>'uid',
			'type'=>'hidden',
			'class'=>'ctxt',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'action'=>array(
			'label'=>'действие',
			'type'=>'text',
			'class'=>'fio',
			'style'=>'width:250px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'content'=>array(
			'label'=>'содержимое',
			'type'=>'textarea',
			'class'=>'note',
			'style'=>'width:250px;height:80px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
	),
);

function get_log_action($v,$r=null,$fn=null) {
	global $config;
	$t = array_flip($config['log']['tables']);
	if(preg_match('/(\w+)\[(\d+)\]/u',$v,$m)){
		if(isset($t[$m[1]])) {
			$v = preg_replace('/\[(\d+)\]/',"[<span class=\"linkform\" add=\"go=stdform&do=edit&table={$t[$m[1]]}&id=$1\">$1</span>]",$v);
		}
	}
	return $v;
}

function get_log_user($v,$r=null,$fn=null) {
	return ($v!='' && $v!='0')? "<a href=\"users.php?go=usrstat&user=".strict($v)."\">$v</a>" : "";
}

function get_log_opepator($v,$r=null,$fn=null) {
	$r = list_operators();
	return isset($r[$v])? $r[$v] : $v;
}

function before_new_log($f) {
	stop(array('result'=>'ERROR','desc'=>'Ручное добавление логов не предусмотрено!'));
	return $f;
}

function checks_log($r) {
	global $DEBUG, $config, $q;
	if($DEBUG>0) log_txt(__function__.": start");
}

function onsave_log($id,$s) {
	global $DEBUG, $config, $opdata, $tables;
	if(!is_numeric($id)) $id = $s['id'];	
	if($DEBUG>0) log_txt(__function__.": s=".arrstr($s));
	return true;
}

function log_auto_user() {
	global $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	$req = (isset($_REQUEST['req']))? str($_REQUEST['req']) : '';
	$out['result'] = 'OK';
	$out['complete'] = $q->select("
		SELECT distinct user as label FROM users
		WHERE user like '%$req%'
		HAVING label!=''
		ORDER BY user
	");
	return $out;
}

function build_period_for_log() {
	return period2db('log','`date`');
}

function build_filter_for_log() {
	global $tables, $_REQUEST, $q;
	$r = array(); $s = '';
	$a = $tables['log']['filters'];
	if(isset($tables['log']['field_alias'])) $fld = $tables['log']['field_alias'];
	if(is_array($a)){
		foreach($a as $k=>$v){
			if(isset($fld[$k])) $fn = "{$fld[$k]}.`$k`"; else $fn = "`$k`";
			if($k == 'user' && isset($_REQUEST[$k])) {
				if(preg_match('/[^\d]/',$_REQUEST[$k]) && ($val = strict($_REQUEST[$k]))!=''){
					$uid = $q->select("SELECT uid FROM users WHERE user='$val'",4);
					$r[] = ($uid)? "`$k` = '$val'" : "`$k` like '%$val%'";
				}elseif(preg_match('/^'.CITYCODE.'/',($val = numeric($_REQUEST[$k])))){
					$uid = $q->select("SELECT uid FROM users WHERE contract='$val'",4);
					$r[] = "`uid` ='$uid'";
				}else $r[] = "`uid` ='$val'";
			}elseif($v['type']=='checklist') {
				if(isset($_REQUEST[$k]) && ($val = preg_replace($in,$out,$_REQUEST[$k]))!='') $r[] = "$fn in ($val)";
			}elseif($v['type']=='text'){
				if(isset($_REQUEST[$k]) && ($val = preg_replace('/^[^0-9a-z]*_/','',$_REQUEST[$k])) !='') $r[] = "$fn like '%$val%'";
			}elseif($v['type']=='select'){
				if(isset($_REQUEST[$k]) && ($val = preg_replace('/^[^0-9a-z]*_/','',$_REQUEST[$k]))!='') $r[] = "$fn='$val'";
			}elseif($v['type']!='date'){
				if(isset($_REQUEST[$k]) && ($val = strict($_REQUEST[$k]))!='') $r[] = "$fn='$val'";
			}
		}
		$s = implode(' AND ',$r);
		if($s) $s = 'AND '.$s;
	}
//  	log_txt(__function__.": return: $s");
	return $s;
}
?>
