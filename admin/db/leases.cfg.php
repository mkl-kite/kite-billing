<?php
include_once("classes.php");
include_once("rayon.cfg.php");
if(!$q) $q = sql_query($config['db']);

$tables['leases']=array(
	'name'=>'leases',
	'title'=>'Объект',
	'target'=>"form",
	'limit'=>'yes',
	'module'=>"stdform",
	'key'=>'id',
	'class'=>'normal',
	'delete'=>'yes',
	'table_query'=>"
		SELECT 
			id,
			object,
			contract,
			owner,
			address,
			rayon,
			amount,
			camount,
			note
		FROM
			leases
		WHERE 1 :FILTER:
		ORDER BY :SORT:
		",
	'form_query'=>"
		SELECT 
			id,
			object,
			contract,
			owner,
			address,
			rayon,
			amount,
			camount,
			note
		FROM
			leases
		",
	'filters'=>array(
		'rayon'=>array(
			'type'=>'checklist',
			'label'=>'районы',
			'title'=>'выбор районов',
			'list'=>$q->fetch_all("SELECT concat('_',rid) as id, r_name as name FROM rayon ORDER BY r_name"),
			'value'=>'_'
		),
		'owner'=>array(
			'type'=>'select',
			'label'=>'владелец',
			'title'=>'название владельца',
			'style'=>'width:80px',
			'list'=>all2array($q->fetch_all("SELECT distinct owner as id, owner as name FROM leases ORDER BY name")),
			'value'=>'_'
		),
		'object'=>array(
			'type'=>'select',
			'label'=>'объект',
			'title'=>'арендуемый объект',
			'list'=>all2array($q->fetch_all("SELECT distinct object as id, object as name FROM leases ORDER BY name")),
			'value'=>'_'
		),
	),
	'header'=>"",
	'table_footer'=>array(
		'object'=>'Итого:',
		'amount'=>'fsum',
		'camount'=>'fsum'
	),
	'defaults'=>array(
		'sort'=>'address',
		'filter'=>'build_filter_for_leases',
	),
	'table_triggers'=>array(
		'rayon'=>'get_rayon'
	),
	'form_triggers'=>array(
	),
	'form_autocomplete'=>array(
		'object'=>'leases_auto_object',
		'address'=>'leases_auto_address',
		'owner'=>'leases_auto_owner'
	),
	'before_new'=>'before_new_lease',
	'before_save'=>'before_save_lease',
	'form_onsave'=>'onsave_lease',
	'checks'=>'checks_lease',
	'group'=>'',

	// поля
	'fields'=>array(
		'id'=>array(
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'object'=>array(
			'label'=>'объект',
			'type'=>'autocomplete',
			'class'=>'nowr',
			'style'=>'width:195px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'contract'=>array(
			'label'=>'N договора',
			'type'=>'autocomplete',
			'class'=>'nowr',
			'style'=>'width:100px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'owner'=>array(
			'label'=>'владелец',
			'type'=>'autocomplete',
			'class'=>'nowr',
			'style'=>'width:195px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'rayon'=>array(
			'label'=>'район',
			'type'=>'select',
			'list'=>'leases_get_rayons',
			'class'=>'nowr',
			'style'=>'width:200px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'address'=>array(
			'label'=>'адрес',
			'type'=>'autocomplete',
			'class'=>'nowr',
			'style'=>'width:195px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'amount'=>array(
			'label'=>'кол-во',
			'type'=>'text',
			'style'=>'width:50px;text-align:right',
			'class'=>'summ',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3),
		),
		'camount'=>array(
			'label'=>'к-во по договору',
			'type'=>'text',
			'style'=>'width:50px;text-align:right',
			'class'=>'summ',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3),
		),
		'note'=>array(
			'label'=>'примечания',
			'type'=>'textarea',
			'style'=>'width:230px;height:70px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
	),
);

function before_new_lease($f) {
	return $f;
}

function checks_lease($r) {
	global $DEBUG, $config, $q;
	if($DEBUG>0) log_txt(__function__.": start");
}

function onsave_lease($id,$s) {
	global $DEBUG, $config, $opdata, $tables;
	if(!is_numeric($id)) $id = $s['id'];	
	if($DEBUG>0) log_txt(__function__.": s=".arrstr($s));
	return true;
}

function leases_auto_object() {
	global $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	$req = (isset($_REQUEST['req']))? str($_REQUEST['req']) : '';
	$out['result'] = 'OK';
	$out['complete'] = $q->select("
		SELECT distinct object as label FROM leases
		WHERE owner like '%$req%'
		HAVING label!=''
		ORDER BY owner
	");
	return $out;
}

function leases_auto_owner() {
	global $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	$req = (isset($_REQUEST['req']))? str($_REQUEST['req']) : '';
	$out['result'] = 'OK';
	$out['complete'] = $q->select("
		SELECT distinct owner as label FROM leases
		WHERE owner like '%$req%'
		HAVING label!=''
		ORDER BY owner
	");
	return $out;
}

function leases_auto_address() {
	global $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	$req = (isset($_REQUEST['req']))? str($_REQUEST['req']) : '';
	$out['result'] = 'OK';
	$out['complete'] = $q->select("
		SELECT distinct address as label
		FROM ((SELECT distinct address FROM leases) UNION
			(SELECT distinct left(trim(address),char_length(trim(address))-locate(' ',reverse(trim(address)))) as address FROM users)) as a
		WHERE address like '%$req%'
		HAVING label!=''
		ORDER BY address
	");
	return $out;
}

function leases_get_rayons() {
	global $opdata, $config, $q;
	if(!$q) $q=new sql_query($config['db']);
	$sql="SELECT rid as id, r_name as name FROM rayon ORDER BY name";
	return $q->fetch_all($sql);
}

function build_filter_for_leases($t) {
	return filter2db('leases');
}
?>
