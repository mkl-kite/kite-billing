<?php
include_once("classes.php");
include_once("map.cfg.php");

$tables['nagios']=array(
	'title'=>'Объект NAGIOS',
	'name'=>'nagios',
	'key'=>'id',
	'query'=>"
		SELECT 
			'new' as id,
			'' as type,
			'' as host_name,
			'' as alias,
			'' as address,
			'' as community,
			'' as parents,
			'' as services,
			'' as realsave,
			'' as restart
		FROM map
		",
	'class'=>'normal',
// 	'footer'=>array(),
	'before_save'=>'before_save_nagios',
	'form_onsave'=>'onsave_nagios',
	// поля
	'fields'=>array(
		'id'=>array(
			'type'=>'hidden',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>5)
		),
		'type'=>array(
			'label'=>'тип',
			'type'=>'select',
			'style'=>'width:100px',
			'list'=>array('host'=>'хост','service'=>'сервис'),
			'enable'=>false,
			'native'=>false,
			'access'=>array('r'=>3,'w'=>5)
		),
		'host_name'=>array(
			'label'=>'название',
			'type'=>'text',
			'style'=>'width:200px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'alias'=>array(
			'label'=>'описание',
			'type'=>'text',
			'style'=>'width:200px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'address'=>array(
			'label'=>'ip адрес',
			'type'=>'text',
			'style'=>'width:120px',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>5)
		),
		'community'=>array(
			'label'=>'community',
			'type'=>'text',
			'style'=>'width:120px',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>5)
		),
		'parents'=>array(
			'label'=>'родитель',
			'type'=>'select',
			'list'=>'get_host_names',
			'style'=>'width:210px',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>5)
		),
		'realsave'=>array(
			'label'=>'запись',
			'type'=>'select',
			'style'=>'width:70px',
			'list'=>array('yes'=>'да','no'=>'нет'),
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'restart'=>array(
			'label'=>'пезагрузить',
			'type'=>'select',
			'style'=>'width:70px',
			'list'=>array('no'=>'нет','yes'=>'да'),
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		)
	),
);

?>
