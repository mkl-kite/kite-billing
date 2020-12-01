<?php
include_once("classes.php");
if(!@$q) $q = new sql_query($config['db']);
if(!@$q1) $q1 = new sql_query($config['db_cards']);

$tables['cardseries']=array(
	'name'=>'cardseries',
	'real_name'=>'cards',
	'q'=>$q1,
	'title'=>'Серии карточек',
	'target'=>"html",
	'limit'=>'yes',
	'module'=>"stdform",
	'key'=>'series',
	'delete'=>'yes',
	'style'=>'width:290px;position:relative',
	'table_style'=>'width:100%',
	'table_query'=>"
		SELECT
			series,
			series as serie,
			generated,
			nominal,
			currency,
			count(*) as amount,
			sum(IF(status='u',1,0)) as used,
			sum(IF(status='l',1,0)) as locked,
			sum(IF(status='a',1,0)) as active,
			expired
		FROM
			cards
		WHERE 1 :FILTER:
		GROUP BY series
		ORDER BY :SORT:
		",
	'form_query'=>"
		SELECT
			series,
			series as serie,
			max(generated) as generated,
			count(*) as amount,
			max(nominal) as nominal,
			max(currency) as currency,
			max(expired) as expired
		FROM
			cards
		GROUP BY series
		",
	'filters'=>array(
	),
	'header'=>"",
	'defaults'=>array(
		'amount'=> 100.,
		'generated'=>cyrdate(false,'%d-%m-%Y %H:%M:%S'),
		'nominal'=>10,
		'currency'=>get_currency(),
		'expired'=>cyrdate(strtotime('1 year')),
		'sort'=>'generated',
		'filter'=>'build_filter_for_cards',
	),
	'class'=>'normal',
// 	'footer'=>array(),
	'table_triggers'=>array(
		'currency'=>'get_tcurrency',
		'generated'=>'cell_atime',
		'expired'=>'cell_date',
		'serie'=>'get_tserie',
	),
	'form_triggers'=>array(
	),
	'form_autocomplete'=>array(
	),
	'before_new'=>'before_new_serie',
	'before_edit'=>'before_edit_serie',
	'before_save'=>'before_save_serie',
	'before_delete'=>'before_delete_serie',
	'form_save'=>'save_serie',
	'form_delete'=>'delete_serie',
	'checks'=>'checks_serie',
	'group'=>'',

	// поля
	'fields'=>array(
		'series'=>array(
			'label'=>'серия',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'generated'=>array(
			'label'=>'создано',
			'type'=>'date',
			'class'=>'date',
			'style'=>'width:170px',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>5),
		),
		'serie'=>array(
			'label'=>'серия',
			'type'=>'text',
			'style'=>'width:50px;text-align:right',
				'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'amount'=>array(
			'label'=>'кол-во',
			'type'=>'text',
			'class'=>'summ',
			'style'=>'width:50px;text-align:right',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5),
		),
		'nominal'=>array(
			'label'=>'номинал',
			'type'=>'text',
			'style'=>'width:50px;text-align:right',
			'class'=>'summ',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'currency'=>array(
			'label'=>'валюта',
			'type'=>'select',
			'list'=>'get_valute_list',
			'style'=>'width:60px',
			'class'=>'nowr',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'used'=>array(
			'label'=>'использовано',
			'type'=>'text',
			'class'=>'summ',
			'style'=>'width:60px;text-align:right',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>5),
		),
		'locked'=>array(
			'label'=>'блокировано',
			'type'=>'text',
			'class'=>'summ',
			'style'=>'width:60px;text-align:right',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>5),
		),
		'active'=>array(
			'label'=>'доступно',
			'type'=>'text',
			'class'=>'summ',
			'style'=>'width:60px;text-align:right',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>5),
		),
		'expired'=>array(
			'label'=>'срок годности',
			'type'=>'date',
			'class'=>'date',
			'style'=>'width:80px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
	),
);

function get_tserie($v,$r,$fn=null) {
	return (time() > strtotime($r[9]))? "<b style=\"color:#c00\">$v</b>" : "<b style=\"color:#080\">$v</b>";
}

function get_valute_list() {
	global $cache, $q;
	return $q->fetch_all("SELECT id, short FROM currency");
}

function get_currency($v=0) {
	if($a = get_valute($v)) return $a['id'];
	return $v;
}

function get_tcurrency($v,$r=null,$fn=null) {
	if($a = get_valute($v)) return $a['short'];
	return $v;
}

function before_new_serie($f) {
	global $config, $DEBUG, $cache, $q, $q1;
	$s = $q1->select("SELECT max(series) FROM cards",4);
	if(!$s) $s = '0001';
	elseif($s>0) $s++;
	$f['id'] = 'new';
	$s = sprintf("%04d", $s);
	$f['header'] = "Новая серия";
	$f['defaults']['serie'] = $s;
	$f['fields']['generated']['type'] = 'nofield';
	$f['record'] = array('series'=>'new','serie'=>'','generated'=>'','amount'=>'','nominal'=>'','currency'=>'','expired'=>'');
	unset($f['fields']['currency']['label']);
	return $f;
}

function before_edit_serie($f) {
	stop(array('result'=>'ERROR','desc'=>"Для серий карточек изменение не предусмотрены!"));
	return false;
}

function checks_serie($r) {
	global $DEBUG, $config, $q;
	if($DEBUG>0) log_txt(__function__.": start");
	if(!isset($s['series']) || !($s['series']>0)) stop(array('result'=>'ERROR','desc'=>"Неверная серия!"));
	if($q1->select("SELECT * FROM cards WHERE series='".sprintf("%04d", $s['series'])."' LIMIT 1"))
		stop(array('result'=>'ERROR','desc'=>"Серия уже существует!"));
	if(!isset($s['amount']) || !($s['amount']>0)) stop(array('result'=>'ERROR','desc'=>"Не определено кол-во!"));
	if(!isset($s['nominal']) || !($s['nominal']>0)) stop(array('result'=>'ERROR','desc'=>"Не указан номинал!"));
}

function before_save_serie($c,$r,$my) {
	global $DEBUG, $config, $opdata, $tables, $q, $q1;
	if($DEBUG>0) log_txt(__function__.": s=".arrstr($c));
	return $c;
}

function save_serie($s,$my) {
	global $DEBUG, $config, $opdata, $tables, $q, $q1;
	if($my->id != 'new') stop(array('result'=>'ERROR','desc'=>"Ошибка создания серии! (id={$my->id})"));
	if($DEBUG>0) log_txt(__function__.": s=".arrstr($s));

	if(isset($s['serie'])){
		if(isset($s['id'])) unset($s['id']);
		if(!isset($s['generated'])) $s['generated'] = date2db();
		$s['series'] = sprintf("%04d", $s['serie']); unset($s['serie']);
		$amount = $s['amount']; unset($s['amount']);
		$flag = false;
		while (!$flag) {
			for ($i = 1; $i <= $amount; $i++ ) {
				$card[$i] = mt_rand(1000000,9999999);
				$card[$i] .= mt_rand(1000000,9999999);
			}
			$flag = true;
			for ($i = 1; $i <= $amount; $i++) {
				for ($j = 1; $j <= $amount; $j++) {
					if ($card[$i] == $card[$j] && $i != $j) $flag = false;
				}
			}
		}
		for ($i = 1; $i <= $amount; $i++ ) {
			$q1->insert('cards',array_merge($s,array('sn'=>$card[$i],'status'=>'a')));
		}
	}
	return 1;
}

function before_delete_serie($id,$s) {
	global $DEBUG, $config, $opdata, $tables, $q;
	if($DEBUG>0) log_txt(__function__.": s=".arrstr($s));
	return true;
}

function delete_serie($s,$my) {
	global $DEBUG, $config, $opdata, $tables, $q, $q1;
	if($DEBUG>0) log_txt(__function__.": s=".arrstr($s));
	$res = $q1->del("cards",array('series'=>$s['series']));
	return ($res)? 1 : false;
}

function build_filter_for_cards() {
	return "";
}

?>
