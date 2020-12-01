<?php
include_once("classes.php");
include_once("map.cfg.php");
$errors = array();

$tables['entrances']=array(
	'title'=>'Подъезд',
	'target'=>'form',
	'module'=>"entrances",
	'key'=>'id',
	'limit'=>'no',
// 	'style'=>'width:754px;position:relative',
	'form_query'=>"
		SELECT 
			id,
			home,
			entrance,
			apartinit,
			apartfinal,
			onroof,
			roofkeyplace,
			keytype,
			boxtype,
			note
		FROM
			entrances
		",
	'table_query'=>"
		SELECT 
			id,
			home,
			entrance,
			concat(apartinit,'-',apartfinal) as apartments
		FROM
			entrances
		WHERE 1 :FILTER:
		ORDER BY :SORT:
	",
	'class'=>'normal',
	'delete'=>'no',
	'filters'=>array(
	),
	'defaults'=>array(
		'sort'=>'entrance',
	),
	'checks'=>array(
		'save'=>'check_entrance_for_save'
	),
// 	преобразование данных формы к заданному формату base->server->client
	'form_triggers'=>array(
	),
	'table_triggers'=>array(
		'entrance'=>'show_entrance'
	),
	'before_save'=>'before_save_entrance',
// 	'form_onsave'=>'onsave_entrance',
	'allow_delete'=>'allow_delete_entrance',
	'group'=>'',

	// поля
	'fields'=>array(
		'id'=>array(
			'label'=>'id',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'home'=>array(
			'label'=>'строение',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'entrance'=>array(
			'label'=>'подъезд',
			'type'=>'nofield',
			'native'=>true,
			'style'=>'width:40px',
			'access'=>array('r'=>1,'w'=>3)
		),
		'floors'=>array(
			'label'=>'этажей',
			'type'=>'text',
			'style'=>'width:40px',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>3),
		),
		'apartinit'=>array(
			'label'=>'начальная кв.',
			'type'=>'text',
			'style'=>'width:40px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'apartfinal'=>array(
			'label'=>'конечная кв.',
			'type'=>'text',
			'style'=>'width:40px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'onroof'=>array(
			'label'=>'выход на крышу',
			'type'=>'checkbox',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'roofkeyplace'=>array(
			'label'=>'ключ от крыши',
			'type'=>'text',
			'style'=>'width:165px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'keytype'=>array(
			'label'=>'тип ключа ящика',
			'type'=>'select',
			'list'=>'list_of_keytypes',
			'style'=>'width:170px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'boxtype'=>array(
			'label'=>'габариты ящика',
			'type'=>'text',
			'style'=>'width:140px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'note'=>array(
			'label'=>'примечание',
			'type'=>'textarea',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>4),
		),
		'apartments'=>array(
			'label'=>'квартиры',
			'type'=>'text',
			'style'=>'width:40px',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>3),
		),
	)
);

function show_entrance($v,$r=null,$fn=null) {
	return $v.' подъезд';
}


function before_save_entrance($cmp,$old) {
	global $config, $opdata;
	$r = array_merge($old,$cmp);
// 	log_txt(__function__.": cmp: ".arrstr($cmp));
	return $cmp;
}

function check_entrance_for_save($r) {
	global $config, $errors, $DEBUG;
	$result = true;
	return $result;
}

function onsave_entrance($id,$res) {
	global $config, $entrance_types, $opdata;
	if(!is_numeric($id)) $id = $res['id'];	
	if(@$res['id']=='new') {}
	return true;
}

function allow_delete_entrance($r) {
	global $config;
	return 'yes';
}

function list_of_keytypes($r) {
	global $config;
	$e = array(
		'ключ'=>'ключ',
		'винт'=>'винт',
		'супервинт'=>'супервинт',
	);
	return $e;
}

?>
