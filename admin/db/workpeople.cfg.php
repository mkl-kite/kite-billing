<?php

$tables['workpeople']=array(
	'table_name'=>'workpeople',
	'form_name'=>'workpeople',
	'form_key'=>'id',
	'title'=>'Исполнитель',
	'target'=>"form",
	'module'=>"workpeople",
	'limit'=>'yes',
	'key'=>'id',
	'style'=>"white-space:nowrap",
	'form_query'=>"
		SELECT 
			id,
			worder,
			fio,
			photo
		FROM
			workpeople wp,
			employers e
		WHERE wp.employer = e.eid
	",
	'table_query'=>"
		SELECT 
			id,
			worder,
			fio,
			photo
		FROM
			workpeople wp,
			employers e
		WHERE wp.employer = e.eid
			:FILTER:
		ORDER BY :SORT:
	",
	'defaults'=>array(
		'sort'=>'fio',
	),
	'form_triggers'=>array(
	),
	'class'=>'normal',
	'delete'=>'no',
// 	'footer'=>array(),
	'table_triggers'=>array(
		'fio'=>'shortfio'
	),
	'before_save_triggers'=>array(
		'deposit'=>''
	),
	'group'=>'',

	// поля
	'fields'=>array(
		'id'=>array(
			'label'=>'id',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>5)
		),
		'worder'=>array(
			'label'=>'номер наряда',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
		),
		'fio'=>array(
			'label'=>'ФИО',
			'type'=>'text',
			'native'=>false,
			'access'=>array('r'=>2,'w'=>3)
		),
		'photo'=>array(
			'label'=>'фото',
			'type'=>'photo',
			'native'=>false,
			'access'=>array('r'=>2,'w'=>3)
		),
		'employer'=>array(
			'label'=>'служащий',
			'type'=>'select',
			'list'=>'get_employers',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
	)
);

function get_employers() {
	global $config;
	$q = new sql_query($config['db']);
	$r = $q->fetch_all("SELECT eid, fio FROM employers WHERE blocked=0 ORDER BY fio",'eid');
	foreach($r as $k=>$v) $r[$k] = shortfio($v);
}

function user_packet($i,$r=null,$fn=null) {
	global $cache, $DEBUG;
	$p = list_of_packets();
	$r = (isset($p[$i]))? $p[$i] : $i;
	if($DEBUG>6) log_txt("user_packet: p[$i]=$r");
	return $r;
}
?>
