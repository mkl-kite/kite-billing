<?php
include_once("classes.php");
include_once("table.php");

$tables['kassa']=array(
	'title'=>'Список серверов доступа',
	'target'=>"form",
	'name'=>"kassa",
	'module'=>"stdform",
	'key'=>'kid',
	'class'=>'normal',
	'delete'=>'yes',
	'table_query'=>"
		SELECT
			k.kid,
			k.name,
			k.longname,
			sum(IF(p.summ is null, 0, p.summ)) as balance
		FROM kassa k
			LEFT OUTER JOIN pay as p ON p.kid = k.kid :PERIOD:
			LEFT JOIN povod as v ON p.povod_id = v.povod_id AND v.typeofpay>0
		WHERE 1 :FILTER:
		GROUP BY k.kid
		ORDER BY :SORT:
	",
	'form_query'=>"SELECT kid, name, longname, computers FROM kassa",
	'table_triggers'=>array(
		'createtime' => 'cell_atime'
	),
	'form_triggers'=>array(
	),
	'group'=>'',
	'defaults'=>array(
		'filter'=>'build_filter_for_kassa',
		'period'=>'build_period_for_kassa',
		'sort'=>'kid',
		'createtime'=>'now'
	),
	'filters'=>array(
		'end'=>array(
			'type'=>'date',
			'label'=>'до',
			'style'=>'width:80px',
			'title'=>'назначен на >',
			'value'=>date('d-m-Y')
		),
		'begin'=>array(
			'type'=>'date',
			'label'=>'от',
			'style'=>'width:80px',
			'title'=>'назначен на >',
			'value'=>cyrdate(strtotime('today'))
		),
	),
	'fields'=>array(
		'kid'=>array(
			'label'=>'kid',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>5)
		),
		'name'=>array(
			'label'=>'название',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'longname'=>array(
			'label'=>'адрес',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'computers'=>array(
			'label'=>'ip адреса',
			'type'=>'text',
			'title'=>'ip адреса через запятую',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'createtime'=>array(
			'label'=>'создано',
			'type'=>'nofield',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'balance'=>array(
			'label'=>'принято денег',
			'type'=>'text',
			'class'=>'summ',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>5)
		),
	),
	'table_triggers'=>array(
		'balance'=>'cell_summ',
	),
	'form_autocomplete'=>array(
	),
	'before_new'=>'before_new_kassa',
	'before_edit'=>'before_edit_kassa',
	'before_save'=>'before_save_kassa',
	'form_onsave'=>'onsave_kassa',
	'allow_delete'=>'allow_delete_kassa',
	'before_delete'=>'before_delete_kassa',
	'before_table_load'=>'before_table_kassa_load',
);

function build_filter_for_kassa() {
	return filter2db('kassa');
}

function build_period_for_kassa() {
	return period2db('kassa','p.acttime');
}

?>
