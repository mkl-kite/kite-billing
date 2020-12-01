<?php
include_once("classes.php");
include_once("rayon.cfg.php");
if(!$q) $q = sql_query($config['db']);
$sms_status = array('открыто','ошибка','останов');
$sendtypes = array(
	'phone'=>'По номеру телефона',
	'address'=>'По адресу',
	'combine'=>'По сочетанию параметров',
);

$tables['sms']=array(
	'name'=>'sms',
	'title'=>'Объект',
	'target'=>"no",
	'limit'=>'yes',
	'add'=>'no',
	'module'=>"stdform",
	'key'=>'unique_id',
	'delete'=>'no',
	'table_query'=>"
		SELECT 
			s.unique_id,
			s.created,
			s.op,
			u.user,
			s.phone,
			s.message,
			s.smsresult
		FROM sms s
			LEFT OUTER JOIN users u ON s.uid = u.uid
		WHERE 1 :FILTER: :PERIOD:
		ORDER BY :SORT:
		",
	'form_query'=>"
		SELECT 
			unique_id,
			created,
			updated,
			op,
			'_1' as sendtype,
			'all' as ustate,
			'' as address,
			'_1' as rid,
			'_0' as pid,
			'' as source,
			'' as mobile_operator,
			uid,
			phone,
			message,
			status,
			smsresult
		FROM
			sms
		",
	'filters'=>array(
		'op'=>array(
			'type'=>'select',
			'label'=>'оператор',
			'title'=>'оператор',
			'list'=>all2array(list_operators()),
			'style'=>'width:80px',
			'value'=>'_'
		),
		'user'=>array(
			'type'=>'autocomplete',
			'label'=>'клиент',
			'title'=>'логин клиента',
			'style'=>'width:80px',
			'value'=>''
		),
		'phone'=>array(
			'type'=>'text',
			'label'=>'телефон',
			'title'=>'телефон',
			'style'=>'width:100px',
			'value'=>''
		),
		'status'=>array(
			'type'=>'select',
			'label'=>'статус',
			'title'=>'статус сообщения',
			'list'=>all2array($sms_status),
			'style'=>'width:80px',
			'value'=>''
		),
		'end'=>array(
			'type'=>'date',
			'label'=>'конец',
			'style'=>'width:80px',
			'title'=>'дата конца',
			'value'=>cyrdate(strtotime('now'))
		),
		'begin'=>array(
			'type'=>'date',
			'label'=>'начало',
			'style'=>'width:80px',
			'title'=>'дата начала',
			'value'=>cyrdate(strtotime('now'))
		),
	),
	'header'=>"",
	'table_footer'=>array(
		'created'=>'Всего:',
		'smsresult'=>'fcount'
	),
	'defaults'=>array(
		'sort'=>'created',
		'filter'=>'build_filter_for_sms',
		'period'=>'build_period_for_sms',
	),
	'field_alias'=>array('phone'=>'s','user'=>'u'),
	'class'=>'normal',
// 	'footer'=>array(),
	'table_triggers'=>array(
		'created'=>'cell_atime',
		'user'=>'get_sms_user',
		'rayon'=>'get_rayon',
		'admin'=>'get_sms_opepator',
	),
	'form_triggers'=>array(
	),
	'form_autocomplete'=>array(
		'user'=>'sms_auto_user',
		'address'=>'sms_auto_address',
	),
	'group'=>'',

	// поля
	'fields'=>array(
		'unique_id'=>array(
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'created'=>array(
			'label'=>'время',
			'type'=>'date',
			'style'=>'width:80px',
			'class'=>'date nowr',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'status'=>array(
			'label'=>'статус',
			'type'=>'select',
			'list'=>$sms_status,
			'class'=>'nowr ctxt',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'updated'=>array(
			'label'=>'обновлено',
			'type'=>'date',
			'style'=>'width:80px',
			'class'=>'date nowr',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'op'=>array(
			'label'=>'оператор',
			'type'=>'select',
			'list'=>'list_operators',
			'class'=>'nowr ctxt',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'sendtype'=>array(
			'label'=>'тип отсылки',
			'type'=>'select',
			'list'=>$sendtypes,
			'style'=>'width:200px',
			'class'=>'nowr ctxt',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3),
			'onchange'=>"
				var s=$(this).val(),
					f=$(this).parents('form'),
 					fld={rid:0,pid:1,source:2,mobile_operator:3,ustate:4}, n;
				if(s=='phone'){
					for(n in fld) f.find('#field-'+n).hide();
					f.find('#field-address').hide();
					f.find('#field-phone').show();
				}else if(s=='address'){
					for(n in fld) f.find('#field-'+n).hide();
					f.find('#field-phone').hide();
					f.find('#field-address').show();
				}else if(s=='combine'){
					for(n in fld) f.find('#field-'+n).show();
					f.find('#field-address').show();
					f.find('#field-phone').hide();
				}
			"
		),
		'ustate'=>array(
			'label'=>'абоненты',
			'type'=>'select',
			'list'=>array('all'=>'все','live'=>"активные (1мес)",'live3'=>"активные (3мес)",'down'=>"ушедшие"),
			'style'=>'width:100px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3),
		),
		'address'=>array(
			'label'=>'адрес',
			'type'=>'autocomplete',
			'style'=>'width:230px',
			'class'=>'nowr ctxt',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3),
		),
		'rid'=>array(
			'label'=>'район',
			'type'=>'select',
			'list'=>all2array(list_of_rayons()),
			'style'=>'width:230px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3),
		),
		'pid'=>array(
			'label'=>'пакет',
			'type'=>'select',
			'list'=>all2array(list_of_packets()),
			'style'=>'width:230px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3),
		),
		'source'=>array(
			'label'=>'принадлежит',
			'type'=>isset($config['owns'])? 'select':'hidden',
			'list'=>all2array(isset($config['owns'])? $config['owns']: array()),
			'style'=>'width:230px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3),
		),
		'mobile_operator'=>array(
			'label'=>'Мобильный оп-р',
			'type'=>'select',
			'list'=>all2array($config['sms']['mobile_operators']),
			'style'=>'width:230px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3),
		),
		'uid'=>array(
			'label'=>'логин',
			'type'=>'hidden',
			'style'=>'width:150px',
			'class'=>'nowr ctxt',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'user'=>array(
			'label'=>'логин',
			'type'=>'autocomplete',
			'style'=>'width:150px',
			'class'=>'nowr ctxt',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>3)
		),
		'phone'=>array(
			'label'=>'телефон',
			'type'=>'text',
			'style'=>'width:150px',
			'class'=>'nowr ctxt',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'message'=>array(
			'label'=>'сообщение',
			'type'=>'textarea',
//			'class'=>'nowr',
			'style'=>'width:250px;height:80px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'smsresult'=>array(
			'label'=>'результат',
			'type'=>'text',
			'class'=>'nowr',
			'style'=>'width:250px;height:80px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
	),
	'before_new'=>'before_new_sms',
	'before_edit'=>'before_edit_sms',
	'before_save'=>'before_save_sms',
	'form_save'=>'send_sms',
	'form_onsave'=>'onsave_sms',
	'checks'=>'checks_sms',
);

function get_sms_user($v,$r=null,$fn=null) {
	return ($v!='' && $v!='0')? "<a href=\"users.php?go=usrstat&user=".strict($v)."\">$v</a>" : "";
}

function get_sms_date($v) {
	return cyrdate(strtotime($v),'%d-%m-%y %H:%M');
}

function get_sms_opepator($v,$r=null,$fn=null) {
	$r = list_operators();
	return isset($r[$v])? $r[$v] : $v;
}

function before_new_sms($f) {
	global $config, $q;
	if(USE_SMS<1) stop('Для отсылки SMS требуется изменение настроек!');
	$unfld = array('created','status','updated','op','user','smsresult');
	foreach($unfld as $n) unset($f['fields'][$n]);
	$f['fields']['uid']['type'] = 'hidden';
	if(!isset($config['sms']['mobile_operators'])) unset($f['fields']['mobile_operator']);
	$f['header'] = "новая SMS";
	return $f;
}

function before_edit_sms($f) {
	stop(array('result'=>'ERROR','desc'=>'Ручное добавление логов не предусмотрено!'));
	return $f;
}

function checks_sms($r) {
	global $DEBUG, $config, $q;
	if($DEBUG>0) log_txt(__function__.": start");
}

function onsave_sms($id,$s) {
	global $DEBUG, $config, $opdata, $tables;
	if(!is_numeric($id)) $id = $s['id'];	
	if($DEBUG>0) log_txt(__function__.": s=".arrstr($s));
	return true;
}

function before_save_sms($cmp,$old,$my) {
	global $DEBUG, $config, $opdata, $tables, $q;
	if(!isset($q)) $q = new sql_query($config['db']);
	if($DEBUG>0) log_txt(__function__.": cmp=".arrstr($cmp));
	if($my->id != 'new') stop('Редактирование отсылаемых SMS не предусмотрено!');
	if(!isset($cmp['message'])) return 1;
	elseif(!$cmp['message']) stop('Сообщение пустое!');
	elseif(mb_strlen($cmp['message'])>240) stop('Сообщение слишком длинное!');
	if($cmp['sendtype']=='address'){
		$a = parse_address($cmp['address'],true);
		if(!$a['street'] && !$a['rayon'])
			stop('Этот вид рассылки предполагает,<BR>по крайней мере, улицу или район!');
		if($_REQUEST['do'] != 'realsave'){
			$adr = trim($a['addr']);
			if($a['home'] && !$a['apartment']) $filter = "address = '{$adr}' OR address like '{$adr}/%'";
			elseif(!$a['home']) $filter = "address like '{$adr} %'";
			else $filter = "address = '{$a['full']}'";
			$c = $q->select("SELECT count(*) FROM users WHERE $filter",4);
		}
	}
	if($cmp['sendtype']=='combine'){
		if($_REQUEST['do'] != 'realsave'){
			$filter = array();
			if(isset($cmp['address']) && $cmp['address']){
				if($a = parse_address($cmp['address'],true)){
					$adr = trim($a['addr']);
					if($a['home'] && !$a['apartment']) $filter[] = "(address = '{$adr}' OR address like '{$adr}/%')";
					elseif(!$a['home']) $filter[] = "address like '{$adr} %'";
					else $filter[] = "address like '{$a['full']}%'";
				}else $filter[] = "address like '%{$cmp['address']}%'";
			}
			if(isset($cmp['ustate']) && $cmp['ustate']=='live') $filter[] = "last_connection>date_add(now(),interval -1 month)";
			if(isset($cmp['ustate']) && $cmp['ustate']=='live3') $filter[] = "last_connection>date_add(now(),interval -3 month)";
			if(isset($cmp['ustate']) && $cmp['ustate']=='down') $filter[] = "last_connection<date_add(now(),interval -3 month)";
			if(isset($cmp['rid']) && $cmp['rid']) $filter[] = "rid = '{$cmp['rid']}'";
			if(isset($cmp['pid']) && $cmp['pid']) $filter[] = "pid = '{$cmp['pid']}'";
			if(isset($cmp['source']) && $cmp['source']) $filter[] = "source = '{$cmp['source']}'";
			if(isset($cmp['mobile_operator']) && $cmp['mobile_operator']) $filter[] = "phone rlike '^({$cmp['mobile_operator']})'";
			$filter = (count($filter)>0)? implode(' AND ',$filter) : 1;
			$c = $q->select("SELECT count(*) FROM users WHERE $filter",4);
		}
	}
	if(isset($c) && $c>1){
		if($DEBUG>0) log_txt(__function__.": Подготовлено $c SMS");
		$form = new form($config);
		$out = $form->confirmForm('new', 'realsave', $m="Подготовлено $c сообщений! Отправлять?", 'sms');
		foreach($cmp as $k=>$v) $out['form']['fields'][$k] = array('type'=>'hidden','value'=>$v);
		$out['nosubmit'] = true;
		stop($out);
	}elseif(isset($c) && $c==0) stop('Клиентов не найдено!');
	return $cmp;
}

function send_sms($s,$my) {
	global $DEBUG, $config, $opdata, $tables, $q;
	if(!isset($q)) $q = new sql_query($config['db']);
	if($DEBUG>0) log_txt(__function__.": s=".arrstr($s));
	$s['op'] = $opdata['login'];
	switch ($s['sendtype']) {
		case 'phone':
			if($s['phone'] = normalize_phone($s['phone'])){
				$q->insert('sms',$s);
			}
			break;
		case 'address':
			$a = parse_address($s['address'],true);
			if($a['apartment'] && ($u = $q->select("SELECT uid, phone FROM users WHERE address='{$a['full']}'",1))) {
				$s = array_merge($s,$u);
				$q->insert('sms',$s);
			}else{
				$adr = trim($a['addr']);
				if($a['home'] && !$a['apartment']) $filter = "(address = '{$adr}' OR address like '{$adr}/%')";
				elseif(!$a['home']) $filter = "address like '{$adr} %'";
				else $filter = "address = '{$a['full']}'";
				$r = $q->query("SELECT uid, phone FROM users WHERE $filter");
				$i=0; $m = array();
				if($r){
					$ins = array_intersect_key($s,array('op'=>0,'message'=>1));
					while ( $v = $r->fetch_assoc() ){
						$i++;
						if($i>100){ $q->insert('sms',$m); $i=1; $m=array(); }
						$m[]=array_merge($ins,$v);
					}
					if($i>0) $q->insert('sms',$m);
				}
			}
			break;
		case 'combine':
			$filter = array();
			if(isset($s['ustate']) && $s['ustate']=='live') $filter[] = "last_connection>date_add(now(),interval -1 month)";
			if(isset($s['ustate']) && $s['ustate']=='live3') $filter[] = "last_connection>date_add(now(),interval -3 month)";
			if(isset($s['ustate']) && $s['ustate']=='down') $filter[] = "last_connection<date_add(now(),interval -3 month)";
			if(isset($s['address']) && $s['address']) $filter[] = "address like '%{$s['address']}%'";
			if(isset($s['rid']) && $s['rid']) $filter[] = "rid = '{$s['rid']}'";
			if(isset($s['pid']) && $s['pid']) $filter[] = "pid = '{$s['pid']}'";
			if(isset($s['source']) && $s['source']) $filter[] = "source = '{$s['source']}'";
			if(isset($s['mobile_operator']) && $s['mobile_operator']) $filter[] = "phone rlike '^({$s['mobile_operator']})'";
			$filter = (count($filter)>0)? implode(' AND ',$filter) : 1;
			$r = $q->query("SELECT uid, phone FROM users WHERE $filter");
			log_txt(__function__.": SQL: ".$q->sql);
			$i=0; $m = array();
			if($r){
				$q1 = new sql_query($config['db']);
				$ins = array_intersect_key($s,array('op'=>0,'message'=>1));
				while ( $v = $r->fetch_assoc() ){
					$i++;
					if($i>100){ $q1->insert('sms',$m); $i=1; $m=array(); }
					$m[]=array_merge($ins,$v);
				}
				if($i>0) $q1->insert('sms',$m);
			}
			break;
	}
	return 1;
}

function sms_auto_user() {
	global $config, $q;
	if(!isset($q)) $q = new sql_query($config['db']);
	$req = (isset($_REQUEST['req']))? str($_REQUEST['req']) : '';
	$out['result'] = 'OK';
	$out['complete'] = $q->select("
		SELECT distinct user as label, uid FROM users
		WHERE user like '%$req%'
		HAVING label!=''
		ORDER BY user
	");
	return $out;
}

function sms_auto_address() {
	global $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	$req = (isset($_REQUEST['req']))? str($_REQUEST['req']) : '';
	$out['result'] = 'OK';
	$out['complete'] = $q->select("
		SELECT distinct address as label, uid FROM users
		WHERE address like '%$req%'
		HAVING label!=''
		ORDER BY user
		LIMIT 50
	");
	log_txt(__function__.": req:{$req}");
	return $out;
}

function build_period_for_sms() {
	return period2db('sms','created');
}

function build_filter_for_sms() {
	return filter2db('sms');
}
?>
