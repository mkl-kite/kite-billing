<?php
include_once("classes.php");
include_once("map.cfg.php");
include_once("entrances.cfg.php");

$tables['homes']=array(
	'name'=>'homes',
	'title'=>'Объект',
	'target'=>"form",
	'limit'=>'yes',
	'module'=>"stdform",
	'key'=>'id',
	'delete'=>'yes',
	'table_query'=>"
		SELECT 
			id,
			address,
			floors,
			entrances,
			apartments,
			boxplace,
			note
		FROM
			homes
		WHERE 1 :FILTER:
		ORDER BY :SORT:
		",
	'form_query'=>"
		SELECT 
			id,
			object,
			address,
			floors,
			entrances,
			apartments,
			boxplace,
			note,
			'' as allentranc
		FROM
			homes
		",
	'table_menu'=>array(
		'edit'=>array('label'=>"<img src=\"pic/gtk-edit16.png\"> изменить",'to'=>'form','query'=>"go=stdform&do=edit&table=homes"),
		'showmap'=>array('label'=>"<img src=\"pic/usr.png\"> показать на карте",'to'=>'map','query'=>"go=homes&do=homeobject&table=homes"),
		'delete'=>array('label'=>"<img src=\"pic/del.png\"> удалить",'to'=>'form','query'=>"go=stdform&do=delete&table=homes"),
	),
	'header'=>"",
	'layout'=>array(
		'home'=>array(
			'type'=>'fieldset',
			'legend'=>"Данные по дому",
			'style'=>'width:340px;height:300px;float:left;',
			'fields'=>array('address','floors','entrances','apartments','boxplace','note')
		),
		'entrances'=>array(
			'type'=>'fieldset',
			'legend'=>'Данные по подъездам',
			'style'=>'width:300px;height:300px;float:left;',
			'fields'=>array('allentranc')
		),
	),
	'filters'=>array(
		'address'=>array(
			'type'=>'text',
			'label'=>'адрес',
			'style'=>"width:110px",
			'title'=>'выбор по адресу',
			'value'=>''
		),
	),

	// поля
	'fields'=>array(
		'id'=>array(
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'object'=>array(
			'label'=>'объект на карте',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'address'=>array(
			'label'=>'адрес',
			'type'=>'autocomplete',
			'style'=>'width:180px',
			'class'=>'nowr',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'floors'=>array(
			'label'=>'этажей',
			'type'=>'text',
			'style'=>'width:40px',
			'class'=>'summ',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'entrances'=>array(
			'label'=>'подъездов',
			'type'=>'text',
			'style'=>'width:40px',
			'class'=>'summ',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3),
			'onkeyup'=>jsFunc('entrances')
		),
		'apartments'=>array(
			'label'=>'квартир',
			'type'=>'text',
			'style'=>'width:40px',
			'class'=>'summ',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3),
			'onkeyup'=>jsFunc('apartments',300)
		),
		'boxplace'=>array(
			'label'=>'подъезд<br>с ц.ящиком',
			'type'=>'text',
			'style'=>'width:40px',
			'class'=>'summ',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'note'=>array(
			'label'=>'примечания',
			'type'=>'textarea',
			'style'=>'width:230px;height:70px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'allentranc'=>array(
			'label'=>'подъезды',
			'type'=>'subform',
			'tname'=>'entrances',
			'sub'=>'get_subform_home_entrances',
			'native'=>true,
		),
		'active'=>array(
			'label'=>'активные',
			'type'=>'text',
			'style'=>'width:40px',
			'class'=>'summ',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>3)
		),
		'passive'=>array(
			'label'=>'думающие',
			'type'=>'text',
			'style'=>'width:40px',
			'class'=>'summ',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>3)
		),
		'lost'=>array(
			'label'=>'ушедшие',
			'type'=>'text',
			'style'=>'width:40px',
			'class'=>'summ',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>3)
		),
		'cnt'=>array(
			'label'=>'всего',
			'type'=>'text',
			'style'=>'width:40px',
			'class'=>'summ',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>3)
		),
	),
	'defaults'=>array(
		'filter'=>'build_filter_for_homes',
		'sort'=>'address'
	),
	'class'=>'normal',
	'table_footer' => array(
		'address'=>'Всего:',
		'cnt'=>'fcount',
		'note'=>'fcount',
	),
	'table_triggers'=>array(
	),
	'form_triggers'=>array(
	),
	'form_autocomplete'=>array(
		'address'=>'home_auto_address'
	),
	'before_new'=>'before_new_home',
	'before_save'=>'before_save_home',
	'form_onsave'=>'onsave_home',
	'form_check_exist'=>array('object'),
	'checks'=>'checks_home',
	'group'=>'',
);

function before_new_home($f) {
	unset($f['fields']['entrances']['onkeyup']);
	unset($f['fields']['apartments']['onkeyup']);
	unset($f['fields']['allentranc']);
	unset($f['layout']['entrances']);
	return $f;
}

function checks_home($r) {
	global $DEBUG, $config, $q;
	if($DEBUG>0) log_txt(__function__.": start");
	if(!isset($r['object']) || $r['object'] == '' || $r['object'] == 0){
		stop(stop(array('result'=>'ERROR','desc'=>"Не указан объект!")));
	}
	if(!$q) $q = new sql_query($config['db']);
	if(!$q->get("map",$r['object'])){
		stop(stop(array('result'=>'ERROR','desc'=>"Не найден GeoJSON объект!")));
	}
	if($r['apartments']>$config['map']['max_apartments']){
		stop(stop(array('result'=>'ERROR','desc'=>"Кол-во квартир не должно превышать {$config['map']['max_apartments']}")));
	}
	if($r['entrances']>$config['map']['max_entrances']){
		stop(stop(array('result'=>'ERROR','desc'=>"Кол-во подъездов не должно превышать {$config['map']['max_entrances']}")));
	}
}

function onsave_home($id,$s) {
	global $DEBUG, $config, $opdata, $tables;
	if(!is_numeric($id)) $id = $s['id'];	
	if($DEBUG>0) log_txt(__function__.": s=".arrstr($s));
	if(isset($s['entrances'])) setentrances($s['id'],$s['entrances']);
	if(isset($s['apartments'])) setapartments($s['id'],$s['apartments']);
	return true;
}

function home_auto_address() {
	global $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	$req = (isset($_REQUEST['req']))? str($_REQUEST['req']) : '';
	$out['result'] = 'OK';
	$out['complete'] = $q->select("
		SELECT m.address as label, m.id as object, h.id as nop
		FROM map m LEFT OUTER JOIN homes h ON h.object = m.id
		WHERE m.address like '%$req%' AND m.type='home'
		HAVING label!='' AND nop is NULL
		ORDER BY m.address
	");
	return $out;
}

function get_subform_home_entrances($id) {
	global $DEBUG, $config, $opdata, $tables;
	if($DEBUG>0) log_txt(__function__.": id=$id");
	$q = new sql_query($config['db']);
	// делаем выборку подъездов
	$tname = 'entrances';
	$t=array_merge($tables[$tname],array(
		'type'=>'table',
		'limit'=>'no',
		'module'=>'entrances',
		'filter'=>"AND home='{$id}'",
		'style'=>'width:100%',
		'name'=>$tname
	));
	$c = new Table($t);
	return array(
		'class'=>'subform',
		'style'=>'width:100%;height:100%;overflow:auto;background:#F5EFE9',
		'table'=>$c->get()
	);
}

function build_filter_for_homes() {
	return filter2db('homes');
}

?>
