<?php
include_once("classes.php");
$woid = (key_exists('woid',$_REQUEST))? $_REQUEST['woid']:0;
$errors = array();

$tables['povod']=array(
	'name'=>'povod',
	'title'=>'Валюта',
	'target'=>'form',
	'module'=>"stdform",
	'key'=>'povod_id',
	'delete'=>'no',
	'limit'=>'no',
	'sort'=>'',
	'group'=>'',
	'form_query'=>"
		SELECT 
			povod_id,
			povod,
			/* name,
			period, */
			calculate,
			kassa,
			diagram,
			/* private, */
			typeofpay
		FROM
			povod
		",
	'table_query'=>"
		SELECT 
			povod_id,
			povod_id as num,
			povod,
			calculate,
			kassa,
			diagram,
			typeofpay
		FROM
			povod
		WHERE 1 :FILTER:
		ORDER BY :SORT:
		",
	'table_menu'=>array(
		'edit'=>array('label'=>"<img src=\"pic/gtk-edit16.png\"> изменить",'to'=>'form','query'=>"go=stdform&do=edit&table=povod"),
		'delete'=>array('label'=>"<img src=\"pic/del.png\"> удалить",'to'=>'form','query'=>"go=stdform&do=delete&table=povod"),
	),
	'class'=>'normal',
	'delete'=>'yes',
	'defaults'=>array(
        'sort'=>'num',
        'calculate'=>0,
        'kassa'=>0,
        'diagram'=>0,
        'typeofpay'=>0
	),
//	'footer'=>array(),
// 	если проверка не пройдена функция должна прервать обработку данных
	'checks'=>array(
		'save'=>'check_povod_for_save'
	),
// 	преобразование данных формы к заданному формату base->server->client
	'form_triggers'=>array(
	),
	'table_triggers'=>array(
		'calculate'=>'get_checkbox',
		'kassa'=>'get_checkbox',
		'diagram'=>'get_checkbox',
		'typeofpay'=>'get_typeofpay',
	),
// 	преобразование данных к заданному формату client->server->base
	'before_edit'=>'before_edit_povod',
	'before_save'=>'before_save_povod',
 	'form_onsave'=>'onsave_povod',
	'allow_delete'=>'allow_delete_povod',
	'before_delete'=>'before_delete_povod',

// поля
	'fields'=>array(
		'povod_id'=>array(
			'label'=>'id',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'num'=>array(
			'label'=>'код',
			'type'=>'text',
			'table_class'=>'ctxt',
			'table_style'=>'width:40px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'povod'=>array(
			'label'=>'Название',
			'type'=>'text',
			'table_style'=>'width:340px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'typeofpay'=>array(
			'label'=>'Тип',
			'type'=>'select',
			'list'=>$typeofpay,
			'table_style'=>'width:140px',
			'table_class'=>'ctxt',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>4),
			'onchange'=>"
				var s=$(this).val().replace(/[^0-9]/g,''),
					f=$(this).parents('form'),
 					fld=['name','period','private'],
 					uid=f.find('[name=uid]').val(), n;
				if(s!=5 && !(uid>0)) for(n in fld) f.find('#field-'+fld[n]).hide();
				else for(n in fld) f.find('#field-'+fld[n]).show();
			"
		),
		'name'=>array(
			'label'=>'код услуги',
			'type'=>'text',
			'style'=>'width:125px',
			'table_style'=>'width:100px',
			'table_class'=>'ltxt',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>4),
		),
		'period'=>array(
			'label'=>'Период',
			'type'=>'select',
			'list'=>all2array($service_period,'',0),
			'style'=>'width:200px',
			'table_style'=>'width:140px',
			'table_class'=>'ltxt',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>4),
		),
		'calculate'=>array(
			'label'=>'Зачислять на счёт клиенту',
			'type'=>'checkbox',
			'table_class'=>'ctxt',
			'table_style'=>'width:120px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'kassa'=>array(
			'label'=>'Проводить по кассе',
			'type'=>'checkbox',
			'table_class'=>'ctxt',
			'table_style'=>'width:120px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'diagram'=>array(
			'label'=>'Учитывать в диаграммах',
			'type'=>'checkbox',
			'table_class'=>'ctxt',
			'table_style'=>'width:120px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>4)
		),
		'private'=>array(
			'label'=>'персонально',
			'type'=>'checkbox',
			'table_class'=>'ctxt',
			'table_style'=>'width:120px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
	)
);

function get_checkbox($v,$r,$fn) {
	$img = ($v)? "pic/stop.png" : "pic/off.png";
	return "<span class=\"linkform\" add=\"go=stdform&do=switch&field=$fn&id={$r[0]}&table=povod\"><img src=\"{$img}\"></span>";
}

function get_typeofpay($v,$r) {
	global $typeofpay;
	return isset($typeofpay[$v])? $typeofpay[$v] : $v;
}

function before_edit_povod($f) {
	return $f;
}

function before_save_povod($cmp, $old, $my) {
	return $cmp;
}

function check_povod_for_save($r) {
	return true;
}

function onsave_povod($id,$s,$my) {
	global $config, $opdata, $claim_types, $DEBUG, $q;
	if($DEBUG>0) log_txt(__function__.": ".arrstr($s));
	if(!$q) $q=new sql_query($config['db']);
	return true;
}

function before_delete_povod($old) {
	global $config, $q;
	log_txt(__function__.": old=".arrstr($old));
	if(!$q) $q=new sql_query($config['db']);
	if($q->select("SELECT * FROM pay WHERE povod_id='{$old['id']}' LIMIT 1",1)){
		stop(array('result'=>'ERROR', 'desc'=>"Имеются платежи по этой статье!"));
	}
	return true;
}

function allow_delete_povod($id,$my) {
	global $config, $q;
	log_txt(__function__.": povod_id=".arrstr($id));
	if(!$q) $q=new sql_query($config['db']);
	if($q->select("SELECT * FROM pay WHERE povod_id='{$id}' LIMIT 1",1)){
		return "Имеются платежи по этой статье!";
	}
	return 'yes';
}
?>
