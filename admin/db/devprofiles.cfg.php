<?php
include_once("classes.php");
if(!$q) $q = sql_query($config['db']);

$colors = array(
	"aquamarine"	=>"аквамарин",
	"white"			=>"белый",
	"turquoise"		=>"бирюзовый",
	"deepskyblue"	=>"голубой",
	"yellow"		=>"желтый",
	"green"			=>"зеленый",
	"brown"			=>"коричневый",
	"red"			=>"красный",
	"neutral"		=>"нейтральный",
	"unpainted"		=>"неокрашенный",
	"orange"		=>"оранжевый",
	"deeppink"		=>"розовый",
	"gray"			=>"серый",
	"blue"			=>"синий",
	"purple"		=>"фиолетовый",
	"black"			=>"черный",
);

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
			color,
			option,
			rucolor,
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
	'before_new'=>'before_new_devprofiles',
	'before_edit'=>'before_edit_devprofiles',
	'before_save'=>'before_save_devprofiles',
	'checks'=>'checks_devprofiles',
	'group'=>'',

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
			'style'=>'width:150px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'port'=>array(
			'label'=>'Номер п/п',
			'type'=>'text',
			'class'=>'nowr',
			'style'=>'width:50px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'option'=>array(
			'label'=>'Метки',
			'type'=>'select',
			'list'=>array("solid"=>"без меток","dashed"=>"с метками"),
			'class'=>'nowr ctxt',
			'style'=>'width:110px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'color'=>array(
			'label'=>'Цвет',
			'type'=>'select',
			'list'=>$colors,
			'style'=>'width:150px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'htmlcolor'=>array(
			'label'=>'HTML цвет',
			'type'=>'text',
			'class'=>'nowr',
			'style'=>'width:110px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
	),
);

function before_new_devprofiles($f) {
	global $config, $q, $DEBUG;
	return $f;
}

function before_edit_devprofiles($f) {
	global $config, $q, $DEBUG;
	return $f;
}

function checks_devprofiles($r,$my) {
	global $DEBUG, $config, $q;
	if($DEBUG>0) log_txt(__function__.": start");
}
?>
