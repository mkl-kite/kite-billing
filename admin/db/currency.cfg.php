<?php
include_once("classes.php");
$woid = (key_exists('woid',$_REQUEST))? $_REQUEST['woid']:0;
$errors = array();

$tables['currency']=array(
	'name'=>'currency',
	'title'=>'Валюта',
	'target'=>'form',
	'module'=>"stdform",
	'key'=>'id',
	'delete'=>'no',
	'limit'=>'no',
	'sort'=>'',
	'group'=>'',
	'form_query'=>"
		SELECT 
			id,
			name,
			short,
			rate,
			blocked
		FROM
			currency
		",
	'table_query'=>"
		SELECT 
			id,
			name,
			short,
			rate,
			blocked
		FROM
			currency
		WHERE 1 :FILTER:
		ORDER BY :SORT:
		",
	'table_menu'=>array(
		'edit'=>array('label'=>"<img src=\"pic/gtk-edit16.png\"> изменить",'to'=>'form','query'=>"go=stdform&do=edit&table=currency"),
		'(un)block'=>array('label'=>"<img src=\"pic/ok.png\"> блокировать",'to'=>'form','query'=>"go=stdform&do=edit&blocked=1&table=currency"),
		'delete'=>array('label'=>"<img src=\"pic/del.png\"> удалить",'to'=>'form','query'=>"go=stdform&do=delete&table=currency"),
	),
	'class'=>'normal',
	'delete'=>'yes',
	'defaults'=>array(
        'sort'=>'name',
        'blocked'=>0,
        'rate'=>0
	),
//	'footer'=>array(),
// 	если проверка не пройдена функция должна прервать обработку данных
	'checks'=>array(
		'save'=>'check_currency_for_save'
	),
// 	преобразование данных формы к заданному формату base->server->client
	'form_triggers'=>array(
	),
	'table_triggers'=>array(
		'blocked'=>'get_currencystatus',
	),
// 	преобразование данных к заданному формату client->server->base
	'before_edit'=>'before_edit_currency',
	'before_save'=>'before_save_currency',
 	'form_onsave'=>'onsave_currency',
	'allow_delete'=>'allow_delete_currency',
	'before_delete'=>'before_delete_currency',

// поля
	'fields'=>array(
		'id'=>array(
			'label'=>'id',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'name'=>array(
			'label'=>'Название',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'short'=>array(
			'label'=>'Кратко',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'rate'=>array(
			'label'=>'Курс',
			'type'=>'text',
			'class'=>'summ',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'blocked'=>array(
			'label'=>'блок',
			'type'=>'checkbox',
			'class'=>'ctxt',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>4)
		),
	)
);

function get_currencystatus($v,$r,$fn=null) {
	global $clperf_status;
	$img = ($v)? "pic/stop.png" : "pic/off.png";
	return "<span class=\"linkform\" add=\"go=stdform&do=switch&field=blocked&id={$r[0]}&table=currency\"><img src=\"{$img}\"></span>";
}

function before_edit_currency($f) {
	return $f;
}

function before_save_currency($cmp, $old, $my) {
	return $cmp;
}

function check_currency_for_save($r) {
	return true;
}

function onsave_currency($id,$s,$my) {
	global $config, $opdata, $claim_types, $DEBUG, $q;
	if($DEBUG>0) log_txt(__function__.": ".arrstr($s));
	if(!$q) $q=new sql_query($config['db']);
	return true;
}

function before_delete_currency($old) {
	global $config, $q;
	log_txt(__function__.": old=".arrstr($old));
	if(!$q) $q=new sql_query($config['db']);
	if($q->select("SELECT * FROM pay WHERE currency='{$old['id']}' LIMIT 1",1)){
		stop(array('result'=>'ERROR', 'desc'=>"Имеются платежи по этой валюте!"));
	}
	return true;
}

function allow_delete_currency($id,$my) {
	global $config, $q;
	log_txt(__function__.": id=".arrstr($id));
	if(!$q) $q=new sql_query($config['db']);
	if($q->select("SELECT * FROM pay WHERE currency='{$id}' LIMIT 1",1)){
		return "Имеются платежи по этой валюте!";
	}
	return 'yes';
}
?>
