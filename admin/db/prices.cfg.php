<?php
include_once("classes.php");
include_once("table.php");
include_once("users.cfg.php");
include_once("rayon_packet.cfg.php");

$tables['prices']=array(
	'name'=>'prices',
	'title'=>'прайс',
	'target'=>"form",
	'module'=>"stdform",
	'key'=>'id',
	'action'=>'references.php',
	'class'=>'normal',
	'delete'=>'yes',
	'limit'=>'yes',
	'form_query'=>"
		SELECT 
			id,
			pid,
			service,
			begintime,
			endtime,
			cost
		FROM prices
	",
	'table_query'=>"
		SELECT 
			id,
			pid,
			service,
			begintime,
			endtime,
			cost
		FROM 
			prices
		WHERE 1 :FILTER:
		ORDER BY :SORT:
	",
	'filters'=>array(
		'pid'=>array(
			'label'=>'тариф',
			'type'=>'select',
			'style'=>'width:90px',
			'list'=>all2array(list_of_packets()),
			'value'=>'_'.numeric(@$_REQUEST['pid']),
		),
	),
	'defaults'=>array(
		'sort'=>'pid, service, begintime',
		'filter'=>'build_filter_for_prices',
		'begintime'=>'00:00:00',
		'endtime'=>'23:59:59',
	),
	'before_new'=>'before_new_price',
	'before_edit'=>'before_edit_price',
	'before_save'=>'before_save_price',
	'form_onsave'=>'onsave_price',
	'allow_delete'=>'allow_delete_price',
	'before_delete'=>'before_delete_price',

	'fields'=>array(
		'id'=>array(
			'label'=>'id',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'pid'=>array(
			'label'=>'тариф',
			'type'=>'select',
			'style'=>'min-width:150px',
			'list'=>'list_of_packets',
			'table_style'=>'width:290px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'service'=>array(
			'label'=>'тип',
			'type'=>'select',
			'style'=>'min-width:120px',
			'table_style'=>'width:120px',
			'list'=>'list_of_services',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'begintime'=>array(
			'label'=>'начало',
			'type'=>'text',
			'class'=>'time',
			'style'=>'width:70px;text-align:center',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>5)
		),
		'endtime'=>array(
			'label'=>'конец',
			'type'=>'text',
			'class'=>'time',
			'style'=>'width:70px;text-align:center',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>5)
		),
		'cost'=>array(
			'label'=>'стоимость',
			'type'=>'text',
			'class'=>'summ',
			'style'=>'width:70px;text-align:right',
			'table_style'=>'width:70px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>5)
		),
	),
	'form_triggers'=>array(
		'pid'=>'get_packet_name',
		'service'=>'get_service_name',
		'cost'=>'price_cost'
	),
	'table_triggers'=>array(
		'pid'=>'get_packet_name',
		'service'=>'get_service_name',
		'cost'=>'price_cost'
	),
	'group'=>'',
);

function before_new_price($f) {
	global $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	$srv = array('','time','traffic','');
	$f['id'] = 'new';
	$f['header'] = 'Новая тарифная позиция';
	$pid = isset($_REQUEST['pid'])? numeric($_REQUEST['pid']) : false;
	if($pid){
		$pk = $q->get('packets',$pid);
		if($pk){
			if($pk['tos']==0){
				stop(array('result'=>"ERROR",'desc'=>"Тип пакета не позволяет<br> добавлять тарифные позиции!"));
			}
			if($pk['tos']>0){
				$f['defaults']['pid'] = $pk['pid'];
				$f['fields']['pid']['disabled'] = true;
			}
			if($pk['tos']>0 && $pk['tos']<3){
				$f['defaults']['service'] = $srv[$pk['tos']];
				$f['fields']['service']['disabled'] = true;
				$last = time2int($q->select("SELECT max(endtime) FROM prices WHERE pid='{$pk['pid']}' AND service='{$srv[$pk['tos']]}'",4))+1;
				$f['defaults']['begintime'] = sectime('h:m:s',$last);
				$f['defaults']['endtime'] = '23:59:59';
			}
		}
	}
	return $f;
}

function before_edit_price($f) {
	global $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	if(!$f['id']) stop(array('result'=>"ERROR",'desc'=>" Не указан id записи!"));
	if(!($p = $q->select("SELECT * FROM prices WHERE {$f['key']}='{$f['id']}'",1)))
		stop(array('result'=>"ERROR",'desc'=>"Запись не найдена в базе!"));
	return $f;
}

function before_save_price($cmp,$old,$my) {
	return $cmp;
}

function before_delete_price($old,$my) {
	return true;
}
function get_service_name($v,$r=null,$fn=null) {
	$a = list_of_services();
	return isset($a[$v])? $a[$v] : $v;
}

function list_of_services() {
	$s = array('time'=>'время','traffic'=>'трафик');
	return $s;
}

function price_cost($v,$r,$fn=null) {
	return sprintf("%.2f",$v);
}

function allow_delete_price($id) {
	global $config, $q, $opdata, $DEBUG;
	if($DEBUG>0) log_txt(__function__.": id='$id'");
	if(!$q) $q = new sql_query($config['db']);
	if($opdata['level']<5){
		log_txt(__function__.": Удаление тарифной позиции не разрешено!");
		return "Удаление тарифной позиции не разрешено!";
	}
	return 'yes';
}

function onsave_price($id,$res,$my) {
	global $config, $q;
	return true;
}

function build_filter_for_prices($t) {
	global $tables, $_REQUEST;
	$r = array(); $s = '';
	$a = $tables[$t]['filters'];
	if(is_array($a)){
		foreach($a as $k=>$v){
			if($v['type']=='checklist') {
				if(isset($_REQUEST[$k]) && $v!='') $r[] = "AND `$k` in (".preg_replace($in,$out,$_REQUEST[$k]).")";
			}elseif($v['type']=='text'){
				if(isset($_REQUEST[$k]) && $v!='') $r[] = "AND `$k` like '%".preg_replace('/^[^0-9a-z]*_/','',$_REQUEST[$k])."%'";
			}elseif($v['type']=='select'){
				if(isset($_REQUEST[$k]) && $v!='') $r[] = "AND `$k`='".preg_replace('/^[^0-9a-z]*_/','',$_REQUEST[$k])."'";
			}else{
				if(isset($_REQUEST[$k]) && $v!='') $r[] = "AND `$k`='".strict($_REQUEST[$k])."'";
			}
		}
		$s = implode(' ',$r);
	}
  	log_txt(__function__.": return: $s");
	return $s;
}
?>
