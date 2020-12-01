<?php
include_once("classes.php");
include_once("users.cfg.php");

if(!isset($q)) $q = new sql_query($config['db']);

$tables['pay']=array(
	'name'=>'pay',
	'title'=>'Клиент',
	'target'=>"form",
	'module'=>"stdform",
	'limit'=>'yes',
	'key'=>'unique_id',
	'style'=>'white-space:nowrap',
	'class'=>'normal',
	'style'=>'max-width:420px',
	'table_style'=>'100%',
	'delete'=>'yes',
	'focus'=>'money',
	'form_query'=>"
		SELECT 
			p.unique_id,
			u.contract,
			p.pid,
			p.uid,
			p.acttime,
			p.service,
			p.money,
			p.currency,
			u.credit,
			0 as activate,
			p.note,
			p.paytime,
			p.povod_id,
			p.rid,
			p.oid,
			p.kid,
			u.fio,
			u.user,
			u.address,
			u.phone
		FROM
			pay as p
			LEFT OUTER JOIN users as u ON p.uid=u.uid
			LEFT OUTER JOIN packets as pk ON p.pid=pk.pid
		",
	'table_query'=>"
		SELECT 
			p.unique_id as id,
			p.acttime,
			p.povod_id,
			u.address,
			r.r_name as rid,
			pk.name as pid,
			p.money,
			p.currency,
			p.card,
			p.note,
			p.`from`
		FROM
			pay as p
			LEFT OUTER JOIN users as u ON p.uid=u.uid
			LEFT OUTER JOIN packets as pk ON p.pid=pk.pid
			LEFT OUTER JOIN rayon as r ON r.rid=p.rid
		WHERE 1 :FILTER: :PERIOD:
		ORDER BY :SORT:
		",
	'table_user_query'=>"
		SELECT 
			p.unique_id as id,
			p.acttime,
			p.povod_id,
			pk.name as pid,
			p.money,
			p.currency,
			p.card,
			p.note,
			p.`from`
		FROM
			pay as p
			LEFT OUTER JOIN users as u ON p.uid=u.uid
			LEFT OUTER JOIN packets as pk ON p.pid=pk.pid
		WHERE 1 :FILTER: :PERIOD:
		ORDER BY :SORT:
		",
	'field_alias'=>array('pid'=>'pk','rid'=>'r','uid'=>'p'),
	'table_menu'=>array(
		'edit'=>array('label'=>"<img src=\"pic/gtk-edit16.png\"> изменить",'to'=>'form','query'=>"go=stdform&do=edit&table=pay"),
		'usrcard'=>array('label'=>"<img src=\"pic/usr.png\"> уч. запись",'to'=>'users.php','query'=>"go=usrstat&table=pay"),
		'delete'=>array('label'=>"<img src=\"pic/del.png\"> удалить",'to'=>'form','query'=>"go=stdform&do=delete&table=pay"),
	),
	'filters'=>array(
		'rid'=>array(
			'type'=>'checklist',
			'label'=>'районы',
			'title'=>'выбор районов',
			'list'=>$q->fetch_all("SELECT concat('_',rid) as id, r_name as name FROM rayon ORDER BY r_name"),
			'value'=>'_'
		),
		'pid'=>array(
			'type'=>'select',
			'label'=>'пакет',
			'title'=>'выбор пакета',
			'style'=>"width:110px",
			'list'=>all2array($q->fetch_all("SELECT pid as id, name FROM packets ORDER BY num")),
			'value'=>'_'.(isset($_REQUEST['pid'])? numeric($_REQUEST['pid']): ""),
		),
		'address'=>array(
			'type'=>'text',
			'label'=>'адрес',
			'style'=>"width:110px",
			'title'=>'выбор по адресу',
			'value'=>''
		),
		'end'=>array(
			'type'=>'date',
			'label'=>'до',
			'style'=>'width:80px',
			'title'=>'назначен на >',
			'value'=>date('d-m-Y')
		),
		'begin'=>array(
			'type'=>'date',
			'label'=>'с',
			'style'=>'width:80px',
			'title'=>'назначен на >',
			'value'=>cyrdate(isset($_REQUEST['oid'])? strtotime('2000-01-01') : (isset($_REQUEST['uid'])? strtotime('-1 year') : strtotime('first day of')))
		),
		'source' => array(
			'type'=>'select',
			'label'=>'владелец',
			'title'=>'выбор владельца',
			'style'=>"width:110px",
			'list'=>all2array($config['owns']),
			'value'=>'_'.(isset($_REQUEST['source'])? numeric($_REQUEST['source']): ""),
		),
		'oid'=>array(
			'type'=>'text',
			'label'=>'пл.ведомость',
			'style'=>'width:50px',
			'title'=>'платёжная ведомость',
			'value'=>isset($_REQUEST['oid'])? numeric($_REQUEST['oid']) : ''
		),
		'uid'=>array(
			'type'=>'select',
			'label'=>'',
			'style'=>'display:none',
			'value'=>isset($_REQUEST['uid'])? numeric($_REQUEST['uid']) : ''
		),
	),
	'defaults'=>array(
		'filter'=>'build_filter_for_pay',
		'period'=>'build_period_for_pay',
		'sort'=>'acttime',
	),
 	'table_footer'=>array(
		'acttime'=>'Всего:',
		'from'=>'fcount',
		'ip'=>'user_ip_address',
 	),
	'group'=>'',

	'fields'=>array(
		'unique_id'=>array(
			'label'=>'id',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>3)
		),
		'uid'=>array(
			'label'=>'uid',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>3)
		),
		'acttime'=>array(
			'label'=>'время',
			'type'=>'nofield',
			'class'=>'date',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>3)
		),
		'address'=>array(
			'label'=>'адрес',
			'type'=>'autocomplete',
			'class'=>'nowr',
			'style'=>'min-width:210px;max-width:290px;overflow:hidden',
			'native'=>false,
			'access'=>array('r'=>2,'w'=>3)
		),
		'service'=>array(
			'label'=>'сервис API',
			'type'=>'select',
			'list'=>all2array($config['pay']['service'],''),
			'style'=>'width:130px',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>3)
		),
		'contract'=>array(
			'label'=>'контракт',
			'type'=>'text',
			'class'=>'ctxt',
			'style'=>'width:50px',
			'table_style'=>'width:50px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3),
			'onkeyup'=>"
				if(event.keyCode==8 || event.keyCode==46 || (event.keyCode>47 && event.keyCode<59)||(event.keyCode>95 && event.keyCode<106)) {
					console.log('keyCode = '+event.keyCode);
					var c=$(this).val().replace(/[^0-9]/g,''),
						f=$(this).parents('form'), v={fio:'',phone:'',rid:'_',pid:'_',address:''};
					if(!(c > 100000 && c < 1000000)){
						for(var n in v) f.find('[name='+n+']').each(function(){
							if($(this).hasClass('nofield')) $(this).text('');
							else $(this).val(v[n]);
						})
						return false;
					}
					if(typeof(ldr) !== 'object') ldr = $.loader();
					ldr.get({
						data:'go=stdform&do=auto_contract&table=pay&req='+c,
						onLoaded: function(d){
							if(!d['complete'] || !d['complete'][0]) return false
							for(var n in d['complete'][0]) {
								if(n != 'label'){
									f.find('[name='+n+']').each(function(){
										if($(this).hasClass('nofield')) $(this).text(d['complete'][0][n]);
										else $(this).val(d['complete'][0][n]);
									})
								}
							}
						}
					})
				}
			"
		),
		'rid'=>array(
			'label'=>'район',
			'type'=>'select',
			'class'=>'nowr',
			'list'=>'list_of_rayons',
			'style'=>'min-width:210px;max-width:300px;overflow:hidden',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'pid'=>array(
			'label'=>'Тарифный пакет',
			'type'=>'select',
			'list'=>'get_list_packets',
			'class'=>'nowr',
			'style'=>'min-width:210px;max-width:290px;overflow:hidden',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'oid'=>array(
			'label'=>'Ведомость',
			'type'=>'hidden',
			'style'=>'max-width:70px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'kid'=>array(
			'label'=>'Касса',
			'type'=>'hidden',
			'style'=>'max-width:70px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'povod_id'=>array(
			'label'=>'Cтатья платежа',
			'type'=>'select',
			'class'=>'nowr',
			'list'=>$q->fetch_all("SELECT povod_id as id, povod as name FROM povod"),
			'style'=>'max-width:300px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'money'=>array(
			'label'=>'Сумма',
			'type'=>'text',
			'class'=>'summ',
			'style'=>'width:80px;text-align:right',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'currency'=>array(
			'label'=>'Валюта',
			'type'=>'select',
			'list'=>$q->fetch_all("SELECT id, short FROM currency WHERE blocked=0"),
			'style'=>'width:60px',
			'table_style'=>'width:30px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'summ'=>array(
			'label'=>'кредит',
			'type'=>'nofield',
			'class'=>'summ',
			'style'=>'width:80px;text-align:right',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'credit'=>array(
			'label'=>'кредит',
			'type'=>'text',
			'class'=>'summ',
			'style'=>'width:80px;text-align:right',
			'native'=>false,
			'access'=>array('r'=>2,'w'=>3)
		),
		'activate'=>array(
			'label'=>'Активировать сейчас',
			'type'=>'checkbox',
			'style'=>'',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'note'=>array(
			'label'=>'Примечание',
			'type'=>'textarea',
			'class'=>'note',
			'style'=>'width:290px;height:58px',
			'table_style'=>'width:30px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'user'=>array(
			'label'=>'логин',
			'type'=>'nofield',
			'style'=>'max-width:150px;overflow:hidden',
			'table_style'=>'',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'fio'=>array(
			'label'=>'Ф.И.О.',
			'type'=>'autocomplete',
			'class'=>'nowr',
			'style'=>'width:300px;overflow:hidden',
			'native'=>false,
			'access'=>array('r'=>1,'w'=>3)
		),
		'deposit'=>array(
			'label'=>'на счету',
			'type'=>'nofield',
			'class'=>'summ',
			'style'=>'width:80px;text-align:right',
			'native'=>false,
			'access'=>array('r'=>2,'w'=>3)
		),
		'expired'=>array(
			'label'=>'пакет заканчивается',
			'type'=>'date',
			'class'=>'date',
			'style'=>'width:80px',
			'table_style'=>'width:80px',
			'native'=>false,
			'access'=>array('r'=>1,'w'=>3)
		),
		'phone'=>array(
			'label'=>'телефон',
			'type'=>'text',
			'class'=>'nowr',
			'style'=>'max-width:290px;min-width:110px;overflow:hidden',
			'disabled'=>true,
			'native'=>false,
			'access'=>array('r'=>2,'w'=>2)
		),
		'late_payment'=>array(
			'label'=>'последняя оплата',
			'type'=>'nofield',
			'class'=>'date',
			'style'=>'width:100px',
			'table_style'=>'width:80px',
			'native'=>false,
			'access'=>array('r'=>2,'w'=>false)
		),
		'card'=>array(
			'label'=>'карточка',
			'type'=>'text',
			'class'=>'ctxt nowr',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>2)
		),
		'source'=>array(
			'label'=>'владелец',
			'type'=>'text',
			'class'=>'ctxt nowr',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'paytime'=>array(
			'label'=>'время платежа',
			'type'=>'text',
			'class'=>'date',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>3)
		),
		'from'=>array(
			'label'=>'откуда',
			'type'=>'text',
			'class'=>'ctxt nowr',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>2)
		),
		'countusr'=>array(
			'label'=>'всего',
			'type'=>'text',
			'class'=>'summ',
			'native'=>false,
			'access'=>array('r'=>2,'w'=>2)
		),
		'countpaid'=>array(
			'label'=>'оплат',
			'type'=>'text',
			'class'=>'summ',
			'native'=>false,
			'access'=>array('r'=>2,'w'=>2)
		),
		'usrsum'=>array(
			'label'=>'сумма',
			'type'=>'text',
			'class'=>'summ',
			'native'=>false,
			'access'=>array('r'=>2,'w'=>2)
		),
		'paidsum'=>array(
			'label'=>'сумма',
			'type'=>'text',
			'class'=>'summ',
			'native'=>false,
			'access'=>array('r'=>2,'w'=>2)
		),
	),
	'form_triggers'=>array(
		'deposit'=>'cell_summ',
		'credit'=>'cell_summ',
		'pid'=>'get_user_packet',
		'rid'=>'user_rayon',
		'acttime'=>'cell_ftime',
		'paytime'=>'cell_ftime',
		'last_connection'=>'cell_date',
		'late_payment'=>'cell_date',
		'source'=>'get_source',
		'money'=>'cell_summ',
		'povod_id'=>'get_povod',
		'currency'=>'cell_valute',
	),
	'form_autocomplete'=>array(
		'address'=>'pay_auto_address',
		'fio'=>'pay_auto_fio',
		'contract'=>'pay_auto_contract',
	),
	'table_triggers'=>array(
		'contract'=>'user_contract',
		'acttime'=>'cell_atime',
		'paytime'=>'cell_time',
		'fio'=>'shortfio',
		'currency'=>'cell_valute',
		'money'=>'cell_summ',
		'deposit'=>'cell_summ',
		'paidsum'=>'cell_summ',
		'povod_id'=>'get_povod',
		'pid'=>'get_user_packet',
		'rid'=>'user_rayon',
		'from'=>'get_pay_operator',
	),
	'checks'=>array(
		'save'=>'check_pay_for_save'
	),
	'before_new'=>'before_new_pay',
	'before_edit'=>'before_edit_pay',
	'before_save'=>'before_save_pay',
	'form_save_new'=>'save_new_pay',
	'form_onsave'=>'onsave_pay',
	'form_delete'=>'delete_pay',
	'allow_delete'=>'allow_delete_pay',
	'before_delete'=>'before_delete_pay',
	'before_table_load'=>'before_table_pay_load',
	'before_save_triggers'=>array(
		'deposit'=>''
	),
);

function before_table_pay_load($t) {
	global $config, $q, $opdata;
	if(!$q) $q = new sql_query($config['db']); 
	$uid = isset($_REQUEST['uid'])? numeric($_REQUEST['uid']) : 0;
	if(!$uid && isset($_REQUEST['user']))
		$uid = $q->select("SELECT uid FROM users WHERE user='".strict($_REQUEST['user'])."'",4);
 	if(!isset($config['owns'])) unset($t['filters']['source']);
	if(isset($_REQUEST['oid'])){
		$oid = numeric($_REQUEST['oid']);
		$order = $q->get("orders",$oid);
		if($order && $opdata['level']<5 && $order['close']){
			$t['add'] = 'no';
		}elseif($order && $opdata['level']<5 && $order['operator'] != $opdata['login']){
			$t['add'] = 'no';
		}
	}elseif($uid){
		$t['table_query'] = $t['table_user_query'];
		$t['filters']['uid'] = array(
			'type'=>'text',
			'label'=>'',
			'style'=>"display:none",
			'value'=>$uid,
		);
		foreach(array('oid','rid','pid','address','source') as $n) unset($t['filters'][$n]);
	}
	$t['style'] = 'width:100%';
	return $t;
}

function before_new_pay($f) {
	global $config, $q;
	$dfld = array('paytime','service','summ','card','from','acttime','from','source','oid','kid');
	$nofld = array('address','rid','pid','fio','phone');
	if(!$q) $q = new sql_query($config['db']);
	$uid = isset($_REQUEST['uid'])? numeric($_REQUEST['uid']) : '';
	if(!$uid && isset($_REQUEST['key']) && $_REQUEST['key']=='uid' && isset($_REQUEST['id'])) $uid = numeric($_REQUEST['id']);
	if(!isset($config['owns'])) unset($f['fields']['source']);
	if($uid && ($user = $q->get("users",$uid))) {
		$f['defaults'] = array_merge($f['defaults'],array_intersect_key($user,$f['fields']));
		foreach($nofld as $n) $f['fields'][$n]['type'] = 'nofield';
		$f['fields']['contract']['type'] = 'hidden';
	}else{
		foreach($nofld as $n) if($f['fields'][$n]['type'] != 'autocomplete') $f['fields'][$n]['disabled']=true;
		$f['fields']['rid']['style'] .= ';min-width:210px';
		$f['focus'] = 'contract';
	}
	$f['fields']['povod_id']['list'] = $q->fetch_all("SELECT povod_id as id, povod as name FROM povod WHERE typeofpay in (0,2,3)");
	$f['defaults']['note'] = '';
	$f['defaults']['povod_id'] = 6;
	$f['fields'] = array_diff_key($f['fields'],array_flip($dfld));
	$f['defaults']['acttime'] = cyrdate(false,'%d %B %Y %H:%M');
	$f['defaults']['currency'] = arrfld(get_valute(),'id');
	unset($f['fields']['currency']['label']);
	$f['header'] = "Новый платёж".(($user)?": <em>{$user['contract']}</em>":"");
	$f['id'] = 'new';
	$f['fields']['user']['type'] = 'hidden';
	return $f;
}

function before_edit_pay($f) {
	global $config, $q, $opdata;
	$nofld = array('from','paytime','fio','address','phone');
	$forbidden = array('summ','activate','credit');
	$fld = array('povod_id'=>0,'money'=>1,'currency'=>2);
	if(!$q) $q = new sql_query($config['db']);
	if(!isset($f['id'])) {
		if(isset($_REQUEST['id'])) $f['id'] = numeric($_REQUEST['id']);
		elseif(isset($_REQUEST['unique_id'])) $f['id'] = numeric($_REQUEST['unique_id']);
		else stop(array('result'=>'ERROR','desc'=>"Не указан платёж"));
	}
	if(!($p = $q->get('pay',$f['id']))) stop(array('result'=>'ERROR','desc'=>"Платёж отсутствует!"));
	$f['fields'] = array_diff_key($f['fields'],array_flip($forbidden));
	foreach($nofld as $k=>$n) $f['fields'][$n]['type'] = 'nofield';
	unset($f['fields']['currency']['label']);
	if(!$p['paytime']) unset($f['fields']['paytime']);
	$f['header'] = "Платёж &#8470; <em>{$f['id']}</em>";
	$f['focus'] = 'money';
	$order = $q->get('orders',$p['oid']);
	if($order && $opdata['level']<5 && $order['close']){
		if($order['operator'] == $opdata['login'] && date('m',strtotime($p['acttime'])) == date('m')) unset($fld['povod_id']);
		foreach($fld as $n=>$v) $f['fields'][$n]['type'] = 'nofield';
		$f['focus'] = 'contract';
	}elseif($order && $opdata['level']<5 && $order['operator'] != $opdata['login']){
		foreach($fld as $n=>$v) $f['fields'][$n]['type'] = 'nofield';
		$f['focus'] = 'contract';
	}
	$f['fields']['user']['type'] = 'hidden';

// 	log_txt(__function__.": fields:".arrstr(array_keys($f['fields'])));
	return $f;
}

function get_pay_operators() {
	return list_operators();
}

function get_pay_operator($v,$r=null,$fn=null) {
	$op = list_operators();
	return isset($op[$v])? $op[$v] : $v;
}

function get_list_packets() {
	global $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	$p = $q->fetch_all("SELECT pid, name FROM packets ORDER by num",'pid');
	return $p;
}

function get_user_packet($v,$r=null,$fn=null) {
	global $DEBUG;
	$p = list_of_packets();
	$r = (isset($p[$v]))? $p[$v] : $v;
	return $r;
}

function check_pay_for_save($r) {
	global $config, $errors, $DEBUG;
	$result = true;
	if(!$result) stop(array('result'=>'ERROR','desc'=>implode(",<br>",$errors)));
	return $result;
}

function cell_valute($v,$r=null,$fn=null) {
	global $config, $q, $cache;
	if(!$q) $q = new sql_query($config['db']);
	if(!isset($cache['tables']['currency'])){
		$cache['tables']['currency'] = $q->fetch_all("SELECT * FROM currency");
	}
	$out = isset($cache['tables']['currency'][$v])? $cache['tables']['currency'][$v]['short'] : "";
	return $out;
}

function get_povod($v,$r=null,$fn=null) {
	global $config, $q, $cache;
	if(!$q) $q = new sql_query($config['db']);
	if(!isset($cache['tables']['povod'])) {
		$cache['tables']['povod'] = $q->fetch_all("SELECT povod_id as id, povod FROM povod");
	}
	$out = isset($cache['tables']['povod'][$v])? $cache['tables']['povod'][$v] : "";
	return $out;
}

function before_save_pay($c,$o,$my) {
	global $cache, $config, $q, $opdata;
	if(!$q) $q = new sql_query($config['db']);
	// при изменении данных полей проверять закрытость ведомости
	$fl = array('money'=>0,'currency'=>1,'summ'=>2,'card'=>3,'from'=>4,'oid'=>5);
	$r = array_merge($o,$c);
	if(!(isset($r['uid']) && $r['uid']>0) && !(isset($r['contract']) && $r['contract']>0)){
		stop(array('result'=>"ERROR",'desc'=>"Не указаны данные пользователя!"));
	}
	if(!($povod=$q->get("povod",$r['povod_id']))) stop(array('result'=>"ERROR",'desc'=>"Не найдена статья платежа!"));
	if(!isset($r['money'])) $c['money'] = 0;
	if(!isset($r['currency'])) stop(array('result'=>"ERROR",'desc'=>"Не указана валюта!"));
	if(isset($r['contract'])) $client = $q->get('users',$r['contract'],'contract');
	elseif(isset($r['uid'])) $client = $q->get('users',$r['uid']);
	if(!$client) stop(array('result'=>"ERROR",'desc'=>"Пользователь не найден!"));
	if(isset($c['credit'])){
		$new = array('uid'=>$client['uid'],'credit'=>$c['credit']);
		if($q->update_record('users',$new)) dblog('users',$client,$new);
		unset($c['credit']);
	}
	if(!($order = $q->get('orders',$r['oid']))){
		if(isset($opdata['document']) && !($order = $q->get('orders',$opdata['document'])))
			log_txt(__function__.": WARNING Ведомость {$opdata['document']} не найдена!");
	}
	if($my->id != 'new' && $order && $opdata['level']<5){
		if($order['operator'] != $opdata['login'] && (isset($c['money']) || isset($c['currency'])))
			stop(array('result'=>'ERROR','desc'=>"Нельзя изменять суммы чужих платежей!"));
		if($order['close'] && count(array_intersect_key($c,$fl))>0 && strtotime($order['open']) < strtotime('-3 day'))
			stop(array('result'=>'ERROR','desc'=>"Ведомость закрыта!"));
		if($order['close'] && date('m',strtotime($r['acttime'])) != date('m'))
			stop(array('result'=>'ERROR','desc'=>"Ведомость закрыта!"));
	}

	if($c['card']=='') unset($c['card']);
	if($c['paytime']=='') unset($c['paytime']);
	if($my->id != 'new' && ($r['uid'] != $client['uid'] || isset($c['uid']))) {
		dblog('log',array('uid'=>$o['uid'],'user'=>$o['user'],'action'=>"Изменил платеж[$r[unique_id]]",'content'=>"платёж переведён на пользователя {$client['address']} ({$client['contract']})"));
		$c['uid'] = $client['uid'];
		$c['user'] = $client['user'];
		$c['pid'] = $client['pid'];
		$c['rid'] = $client['rid'];
	}
	if($my->id != 'new' && isset($c['contract'])) unset($c['contract']);
	if(isset($c['activate'])){
		$user = $q->get("users",$r['uid']);
		if($user['blocked']) stop(array('result'=>'ERROR','desc'=>"Для активации следует разблокировать!"));
	}
	return $c;
}

function save_new_pay($s,$my) {
	global $cache, $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	if(isset($s['credit']) && isset($s['uid'])){
		$u = new user($s['uid']);
		if(isset($u->data['contract'])) $u->change($s);
	}
	if(isset($s['money'])){
		$p = new payment($config);
		if(!($pay = $p->pay($s))) stop(array('result'=>'ERROR','desc'=>implode('<br>',$p->errors)));
	}
	$my->log = false;
	return $pay;
}

function onsave_pay($id,$s,$my) {
	global $cache, $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	$o = $my->row;
	$r = array_merge($o,$s);
	$client = $q->get('users',$r['uid']);
	if($id != 'new'){
		$opvd = $q->get('povod',$o['povod_id']);
		if(!isset($opvd['calculate'])) $opvd['calculate'] = 1;
		$npvd = isset($s['povod_id'])? $q->get('povod',$s['povod_id']) : $opvd;
		if(isset($s['activate'])) unset($s['activate']);
		$oldsumm = $opvd['calculate'] * $o['money'] * arrfld(get_valute($o['currency']),'rate');
		$newsumm = $npvd['calculate'] * $r['money'] * arrfld(get_valute($r['currency']),'rate');
		if(isset($s['money']) || isset($s['currency'])){ // если есть изменение в сумме или валюте
			if(!isset($s['uid']) || $o['uid'] == $s['uid']){ // если пользователь тот же самый
				$newdeposit = $client['deposit'] - $oldsumm + $newsumm;
				$usrdep = array('uid'=>$client['uid'],'deposit'=>$newdeposit);
				if($q->update_record("users",$usrdep)) dblog("users",$client,$usrdep);
				else log_txt(__function__.": ERROR ошибка обновления депозита пользователя!");
			}else{ // если пользователь другой
				$oldclient = $q->get('users',$o['uid']);
				$deposit = $oldclient['deposit'] - $oldsumm;
				if($oldclient){
					$usrdep = array('uid'=>$oldclient['uid'],'deposit'=>$deposit);
					if($q->update_record("users",$usrdep)) dblog("users",$oldclient,$usrdep);
					else log_txt(__function__.": ERROR ошибка обновления депозита пользователя!");
				}
				$newdeposit = $client['deposit'] + $newsumm;
				if(!$q->update_record("users",array('uid'=>$client['uid'],'deposit'=>$newdeposit)))
					log_txt(__function__.": ERROR ошибка обновления депозита пользователя!");
			}
		}elseif(isset($s['uid'])) {
			$oldclient = $q->get('users',$o['uid']);
			$deposit = $oldclient['deposit'] - $oldsumm;
			if($oldclient) {
				$usrdep = array('uid'=>$oldclient['uid'],'deposit'=>$deposit);
				if($q->update_record("users",$usrdep)) dblog("users",$oldclient,$usrdep);
				else log_txt(__function__.": ERROR ошибка обновления депозита пользователя!");
			}
			$newdeposit = $client['deposit'] + $newsumm;
			if(!$q->update_record("users",array('uid'=>$client['uid'],'deposit'=>$newdeposit)))
				log_txt(__function__.": ERROR ошибка обновления депозита пользователя!");
		}elseif(isset($s['povod_id'])){
			$newdeposit = $client['deposit'] - $oldsumm + $newsumm;
			$usrdep = array('uid'=>$client['uid'],'deposit'=>$newdeposit);
			if($q->update_record("users",$usrdep)) dblog("users",$client,$usrdep);
			else log_txt(__function__.": ERROR ошибка обновления депозита пользователя!");
		}
		if(isset($usrdep)){
			$ok = $opvd['kassa'] * $o['money'] * arrfld(get_valute($o['currency']),'rate');
			$nk = $npvd['kassa'] * $r['money'] * arrfld(get_valute($r['currency']),'rate');
			if($ok != $nk) {
				$ksum = 0 - $ok + $nk;
				$q->query("UPDATE orders SET summa = summa + ($ksum) WHERE oid='{$o['oid']}'");
			}
		}
	}
}

function allow_delete_pay($id) {
	global $opdata, $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	if(!$id || !($p = $q->get('pay',$id))) return "Платеж не найден!";
	if(!($order = $q->get('orders',$p['oid'])))  return "Ведомость не найдена!";
	if($order['close'])  return "Ведомость закрыта!";
	if($opdata['level'] < 5 && (strtotime($p['acttime']) < strtotime('-3 day')))  return "Время возможного удаления истекло!";
	$pvd = $q->fetch_all("SELECT povod_id as id, povod FROM povod WHERE typeofpay in (0,2,3)");
	if($opdata['level'] < 5 && !isset($pvd[$p['povod_id']])) return "Вы не можете удалять<br> платежи этого типа!";
	if($opdata['level'] < 5 && $opdata['login'] != $order['operator']) return "Вы не можете удалять<br> платежи другого оператора!";
	if($opdata['level'] < 3) return "Доступ запрещён!";
	return 'yes';
}

function delete_pay($s,$my) {
	global $opdata, $config, $q;
	if (isset($s['unique_id'])) {
		$p = new payment($config);
		return $p->remove_pay($s);
	}
	return false;
}

function pay_auto_address(){
	global $config, $q;
	$req = (isset($_REQUEST['req']))? str($_REQUEST['req']) : '';
	$out['result'] = 'OK';
	$r = $q->select("
		SELECT DISTINCT 
			address as label, fio, contract, phone, uid, rid, pid
		FROM users 
		WHERE address like '%$req%'
		HAVING label!='' 
		ORDER BY address
		LIMIT 20
	");
	foreach($r as $k=>$v) {
		$r[$k]['rid'] = '_'.$v['rid'];
		$r[$k]['pid'] = '_'.$v['pid'];
	}
	$out['complete'] = $r;
	return $out;
}

function pay_auto_contract(){
	global $config, $q;
	$req = (isset($_REQUEST['req']))? str($_REQUEST['req']) : '';
	$out['result'] = 'OK';
	$r = $q->select("
		SELECT DISTINCT 
			contract as label, address, fio, contract, phone, uid, rid, pid
		FROM users 
		WHERE contract like '%$req%'
		HAVING label!='' 
		ORDER BY user
		LIMIT 20
	");
	foreach($r as $k=>$v) {
		$r[$k]['rid'] = '_'.$v['rid'];
		$r[$k]['pid'] = '_'.$v['pid'];
	}
	$out['complete'] = $r;
	return $out;
}

function pay_auto_fio(){
	global $config, $q;
	$req = (isset($_REQUEST['req']))? str($_REQUEST['req']) : '';
	$out['result'] = 'OK';
	$r = $q->select("
		SELECT DISTINCT 
			fio as label, address, contract, phone, uid, rid, pid
		FROM users 
		WHERE fio like '%$req%'
		HAVING label!='' 
		ORDER BY fio
		LIMIT 20
	");
	foreach($r as $k=>$v) {
		$r[$k]['rid'] = '_'.$v['rid'];
		$r[$k]['pid'] = '_'.$v['pid'];
	}
	$out['complete'] = $r;
	return $out;
}

function build_filter_for_pay() {
	return filter2db('pay');
}

function build_period_for_pay() {
	return period2db('pay','acttime');
}
?>
