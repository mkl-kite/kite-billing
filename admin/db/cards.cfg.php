<?php
include_once("classes.php");
include_once("users.cfg.php");
if(!@$q) $q = new sql_query($config['db']);
if(!@$q1) $q1 = new sql_query($config['db_cards']);
$card_status = array(
	'a'=>'активна',
	'l'=>'заблокирована',
	'u'=>'использована',
	'd'=>'удалена',
);

$tables['cards']=array(
	'name'=>'cards',
	'q'=>$q1,
	'title'=>'Список карточек',
	'target'=>"form",
	'limit'=>'yes',
	'module'=>"stdform",
	'key'=>'cards_id',
	'delete'=>'no',
	'style'=>'width:320px;position:relative',
	'table_style'=>'width:100%',
	'table_query'=>"
		SELECT
			cards_id,
			concat(series,sn) as sn,
			generated,
			nominal,
			currency,
			expired,
			status,
			user
		FROM
			cards
		WHERE 1 :FILTER:
		ORDER BY :SORT:
		",
	'form_query'=>"
		SELECT
			cards_id,
			series,
			sn,
			generated,
			nominal,
			currency,
			expired,
			status,
			uid,
			user
		FROM
			cards
		",
	'filters'=>array(
		'series'=>array(
			'label'=>'',
			'type'=>'hidden',
			'style'=>'display:none',
			'access'=>array('r'=>3,'w'=>5),
			'value'=>isset($_REQUEST['series'])? numeric($_REQUEST['series']): "",
		),
		'status'=>array(
			'type'=>'select',
			'label'=>'статус',
			'title'=>'состаяние заявленя',
			'list'=>all2array($card_status),
			'value'=>'_'
		),
		'user'=>array(
			'type'=>'text',
			'label'=>'клиент',
			'title'=>'логин клиента',
			'style'=>'width:80px',
			'value'=>''
		),
	),
	'header'=>"",
	'defaults'=>array(
		'sort'=>'generated',
		'filter'=>'build_filter_for_cards',
	),
	'class'=>'normal',
	'table_triggers'=>array(
		'currency'=>'get_card_tcurrency',
		'status'=>'get_card_tstatus',
		'sn'=>'get_card_sn',
	),
	'form_triggers'=>array(
		'sn'=>'get_form_card_sn',
		'currency'=>'get_card_tcurrency',
	),
	'form_autocomplete'=>array(
		'user'=>'cards_user_autocomplete',
	),
	'before_new'=>'before_new_card',
	'before_edit'=>'before_edit_card',
	'before_save'=>'before_save_card',
	'before_delete'=>'before_delete_card',
	'checks'=>'checks_card',
	'group'=>'',

	// поля
	'fields'=>array(
		'cards_id'=>array(
			'label'=>'серия',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'generated'=>array(
			'label'=>'создана',
			'type'=>'date',
			'class'=>'nowr',
			'style'=>'width:170px',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>5),
		),
		'series'=>array(
			'label'=>'серия',
			'type'=>'nofield',
			'style'=>'width:50px;text-align:right',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'sn'=>array(
			'label'=>'номер',
			'type'=>'nofield',
			'class'=>'nowr',
			'native'=>true,
			'access'=>array('r'=>5,'w'=>5)
		),
		'nominal'=>array(
			'label'=>'номинал',
			'type'=>'nofield',
			'style'=>'width:60px;text-align:right',
			'class'=>'summ',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'currency'=>array(
			'label'=>'валюта',
			'type'=>'nofield',
			'style'=>'width:60px',
			'class'=>'nowr',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'expired'=>array(
			'label'=>'срок годности',
			'type'=>'date',
			'class'=>'date',
			'style'=>'width:90px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'status'=>array(
			'label'=>'статус',
			'type'=>'select',
			'list'=>$card_status,
			'style'=>'max-width:200px',
			'class'=>'nowr',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'uid'=>array(
			'label'=>'клиент',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'user'=>array(
			'label'=>'клиент',
			'type'=>'autocomplete',
			'style'=>'width:90px',
			'class'=>'nowr',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
	),
);

function cards_user_autocomplete($req) {
	global $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	$req = (isset($_REQUEST['req']))? str($_REQUEST['req']) : '';
	$out['result'] = 'OK';
	$out['complete'] = $q->select("SELECT distinct user as label, uid FROM users WHERE user like '%$req%' HAVING label!='' ORDER BY user");
	return $out;
}

function get_valute_list() {
	global $cache, $q;
	return $q->fetch_all("SELECT id, short FROM currency");
}

function get_currency($v) {
	if($a = get_valute($v)) return $a['id'];
	return $v;
}

function get_card_sn($v,$r=array(),$fn=null) {
	$out = preg_replace('/(\d\d\d\d)(\d\d\d\d)(\d\d\d\d)(\d\d\d\d)(\d\d)/','$1-$2-$3-$4-$5',$v);
	if(@$r[6] == 'a') $color='#0a0';
	if(@$r[6] == 'u') $color='#058';
	if(@$r[6] == 'l') $color='#aaa';
	if(@$r[6] == 'd') $color='#a20';
	if(isset($color)) $out = "<b style=\"color:$color\">$out</b>";
	return $out;
}

function get_form_card_sn($v,$r,$my) {
	return preg_replace('/(\d\d\d\d)(\d\d\d\d)(\d\d\d\d)(\d\d)/','$1-$2-$3-$4',$r['sn']);
}

function get_card_tcurrency($v,$r,$fn=null) {
	if($a = get_valute($v)) return $a['short'];
	return $v;
}

function get_card_tstatus($v,$r=null,$fn=null) {
	global $card_status;
	return isset($card_status[$v])? $card_status[$v] : $v;
}

function before_new_card($f) {
	global $config, $DEBUG, $cache, $q, $q1;
	stop(array('result'=>'ERROR','desc'=>"Для генерации используется создание новой серии!"));
	return $f;
}

function before_edit_card($f) {
	$f['header'] = "Карточка {$f['id']}";
	$f['fields']['generated']['type'] = 'nofield';
	unset($f['fields']['currency']['label']);
	return $f;
}

function checks_card($r) {
	global $DEBUG, $config, $q;
	if($DEBUG>0) log_txt(__function__.": start");
}

function before_save_card($c,$o,$my) {
	global $DEBUG, $config, $opdata, $tables, $q, $q1;
	if($DEBUG>0) log_txt(__function__.": s=".arrstr($c));
	$r = array_merge($my->row,$o,$c);
	$card = get_card_sn($r['series'].$r['sn']);
	if(isset($my->row['status']) && $my->row['status']=='d')
		stop(array('result'=>'ERROR', 'desc'=>"Карточка удалена!"));
	if(isset($c['status'])){
		if(isset($my->row['status']) && $my->row['status'] == 'u'){
			$p = new payment($config);
			$p->remove_pay(array('user'=>$r['user'],'card'=>$card));
			$c['user'] = '';
		}
		if($c['status'] == 'u' && (@$r['user']=='' || @$r['uid']==''))
			stop(array('result'=>'ERROR', 'desc'=>"Если статус 'использована'-<br>нужно указать клиента!"));
		if($c['status'] == 'u'){
			if(!isset($p)) $p = new payment($config);
			if(!($pay = $p->pay(array('user'=>$c['user'],'card'=>$card))))
				stop(array('result'=>'ERROR','desc'=>"Ошибка зачисления оплаты!<br>".implode('<br>',$p->errors)));
		}
	}
	if(isset($c['user'])){
		if($c['user']!='' && $r['status'] != 'u')
			stop(array('result'=>'ERROR', 'desc'=>"Если указан клиент -<br>то статус карточки должен быть 'использована'!"));
		if($c['user']!='' && !($user = $q->select("SELECT * FROM users WHERE user='{$c['user']}'",1)))
			stop(array('result'=>'ERROR', 'desc'=>"Пользователь '{$c['user']}' не найден в базе!"));
		if($c['user']!='' && $r['uid']!=$user['uid']) $c['uid'] = $user['uid'];
		if($c['user']!='' && isset($my->row['user']) && $my->row['user'] != ''){
			if(!isset($p)) $p = new payment($config);
			$p->remove_pay(array('user'=>$my->row['user'],'card'=>$card));
		}
		if($c['user']!='' && $my->row['user'] != ''){
			if(!isset($p)) $p = new payment($config);
			if(!isset($pay) && !($pay = $p->pay(array('user'=>$r['user'],'card'=>$card)))) 
				stop(array('result'=>'ERROR','desc'=>"Ошибка зачисления оплаты!<br>".implode('<br>',$p->errors)));
		}
	}
	return $c;
}

function save_card($s,$my) {
	global $DEBUG, $config, $opdata, $tables, $q, $q1;
	if($my->id != 'new') stop(array('result'=>'ERROR','desc'=>"Ошибка создания серии! (id={$my->id})"));
	if($DEBUG>0) log_txt(__function__.": s=".arrstr($s));

	if(isset($s['card'])){
		if(isset($s['id'])) unset($s['id']);
		$s['cards'] = sprintf("%04d", $s['card']); unset($s['card']);
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

function before_delete_card($id,$s) {
	global $DEBUG, $config, $opdata, $tables, $q;
	if($DEBUG>0) log_txt(__function__.": s=".arrstr($s));
	return true;
}

function build_filter_for_cards($t) {
	global $tables, $_REQUEST;
	$r = array(); $s = '';
	$a = $tables[$t]['filters'];
	if(is_array($a)){
		foreach($a as $k=>$v){
			if($k == 'begin'){
				$val = (isset($_REQUEST[$k]))? date2db($_REQUEST[$k],false):date('Y-m-d',strtotime('-1 month'));
				$d1 = "AND `claimtime`>'$val'";
			}elseif ($k == 'end') {
				$val = (isset($_REQUEST[$k]))? date2db($_REQUEST[$k],false):date('Y-m-d 23:59:59');
				$d2 = "AND `claimtime`<'$val'";
			}elseif ($k == 'user') {
				if(isset($_REQUEST[$k]) && $v!='') $r[] = "AND `$k` like '%".str($_REQUEST[$k])."%'";
			}else{
				if(isset($_REQUEST[$k]) && $v!='') $r[] = "AND `$k`='".strict($_REQUEST[$k])."'";
			}
		}
		if(isset($d1) && isset($d2)) $r[] = "$d1 $d2";
		$s .= implode(' ',$r);
	}
	return $s;
}

?>
