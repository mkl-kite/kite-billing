<?php
include_once("classes.php");
include_once("geodata.php");
include_once("rayon_packet.cfg.php");
include_once("rayon.cfg.php");

if(!isset($q)) $q = new sql_query($config['db']);

$tables['users']=array(
	'name'=>'users',
	'title'=>'Клиент',
	'target'=>"form",
	'module'=>"stdform",
	'limit'=>'yes',
	'key'=>'uid',
	'style'=>'white-space:nowrap',
	'class'=>'normal',
	'style'=>'max-width:685px',
	'table_style'=>'100%',
	'delete'=>'no',
	'form_query'=>"
		SELECT 
			uid,
			'' as cid,
			contract,
			source,
			pid,
			blocked,
			disabled,
			user,
			password,
			fio,
			psp,
			deposit,
			credit,
			expired,
			phone,
			address,
			'' as ip,
			opt82,
			csid,
			rid,
			add_date,
			last_connection,
			late_payment,
			next_pid,
			note,
			email
		FROM
			users
		",
	'table_query'=>"
		SELECT 
			uid,
			concat(contract,':',blocked) as contract,
			last_connection,
			user,
			rid,
			address,
			pid,
			deposit,
			credit,
			expired,
			phone,
			note
		FROM
			users
		WHERE 1 :FILTER:
		ORDER BY :SORT:
		",
	'table_menu'=>array(
		'edit'=>array('label'=>"<img src=\"pic/gtk-edit16.png\"> изменить",'to'=>'form','query'=>"go=stdform&do=edit&table=users"),
		'claim'=>array('label'=>"<img src=\"pic/newdoc.png\"> заявка",'to'=>'form','query'=>"go=claims&do=new&table=claims&key=uid"),
		'pay'=>array('label'=>"<img src=\"pic/add.png\"> платеж",'to'=>'form','query'=>"go=stdform&do=new&table=pay&key=uid"),
		'usrcard'=>array('label'=>"<img src=\"pic/usr.png\"> уч. запись",'to'=>'users.php','query'=>"go=usrstat&table=users"),
		'showmap'=>array('label'=>"<img src=\"pic/usr.png\"> показать на карте",'to'=>'map','query'=>"go=clients&do=clientobject&table=users"),
		'blocked'=>array('label'=>"<img src=\"pic/lock.png\"> блокировать",'to'=>'edit','query'=>"go=clients&do=block&table=users"),
		'disconnect'=>array('label'=>"<img src=\"pic/ported.png\"> сбросить",'to'=>'edit','query'=>"go=clients&do=userkill&table=users"),
		'delete'=>array('label'=>"<img src=\"pic/del.png\"> удалить",'to'=>'form','query'=>"go=stdform&do=delete&table=users"),
	),
	'filters'=>array(
		'rid'=>array(
			'type'=>'checklist',
			'label'=>'районы',
			'title'=>'выбор районов',
			'list'=>$q->fetch_all("SELECT concat('_',rid) as id, r_name as name FROM rayon ORDER BY r_name"),
			'value'=>'_'.(isset($_REQUEST['rid'])? numeric($_REQUEST['rid']): ""),
		),
		'pid'=>array(
			'type'=>'checklist',
			'label'=>'пакеты',
			'title'=>'выбор пакетов',
			'list'=>$q->fetch_all("SELECT concat('_',pid) as id, name FROM packets ORDER BY num"),
			'value'=>'_'.(isset($_REQUEST['pid'])? numeric($_REQUEST['pid']): ""),
		),
		'address'=>array(
			'type'=>'text',
			'label'=>'адрес',
			'style'=>"width:110px",
			'title'=>'выбор по адресу',
			'value'=>''
		),
		'last_connection'=>array(
			'type'=>'select',
			'typeofvalue'=>'active',
			'label'=>'Активность',
			'style'=>"width:110px",
			'list'=>array(
				'_'=>'все',
				">'".date2db(strtotime('-2 month'),false)."'"=>'Живые',
				"<'".date2db(strtotime('-2 month'),false)."'"=>'Ушедшие',
				"blocked = 1"=>'Заблокированы',
				"disabled = 1"=>'Выключены'
			),
			'title'=>'выбор по активности',
			'keep'=>true,
			'value'=>''
		),
		'source'=>array(
			'type'=>'select',
			'label'=>'Чей',
			'style'=>"width:110px",
			'list'=>all2array(isset($config['owns'])? $config['owns']: array()),
			'title'=>'выбор по активности',
			'keep'=>true,
			'value'=>''
		),
	),
	'layout'=>array(
		'common'=>array(
			'type'=>'fieldset',
			'legend'=>'личные данные',
			'style'=>'float:left;max-width:305px',
			'fields'=>array('contract','source','fio','address','rid','psp','phone','email')
		),
		'tech'=>array(
			'type'=>'fieldset',
			'legend'=>'технические параметры',
			'style'=>'float:left;max-width:315px',
			'fields'=>array('user','password','pid','next_pid','expired','opt82','ip','csid')
		),
		'other'=>array(
			'type'=>'fieldset',
			'legend'=>'разное',
			'style'=>'height:85px;width:140px',
			'fields'=>array('blocked','disabled')
		),
		'money'=>array(
			'type'=>'fieldset',
			'legend'=>'деньги',
			'style'=>'height:85px;width:135px',
			'fields'=>array('deposit','credit')
		),
		'note'=>array(
			'type'=>'fieldset',
			'legend'=>'Примечание',
			'style'=>'height:85px;width:305px',
			'fields'=>array('note')
		),
		'adding'=>array(
			'type'=>'fieldset',
			'legend'=>'даты',
			'style'=>'float:left;width:652px',
			'fields'=>array('last_connection','late_payment','add_date')
		),
	),
	'focus'=>'note',
	'defaults'=>array(
		'filter'=>'build_filter_for_users',
		'sort'=>'fio',
		'source'=>'terraline'
	),
 	'table_footer'=>array(
		'contract'=>'Всего:',
		'note'=>'fcount',
		'ip'=>'user_ip_address',
 	),
	'group'=>'',

	'fields'=>array(
		'uid'=>array(
			'label'=>'uid',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>3)
		),
		'cid'=>array(
			'label'=>'Номер заявления',
			'type'=>'hidden',
			'native'=>false,
			'access'=>array('r'=>1,'w'=>3)
		),
		'contract'=>array(
			'label'=>'контракт',
			'type'=>'text',
			'class'=>'ctxt',
			'style'=>'width:68px;text-align:center',
			'table_style'=>'width:50px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
		),
		'source'=>array(
			'label'=>'чей',
			'type'=>'hidden',
			'style'=>'width:110px',
			'list'=>isset($config['owns'])? $config['owns'] : array(),
			'native'=>true,
			'access'=>array('r'=>2,'w'=>5)
		),
		'fio'=>array(
			'label'=>'Ф.И.О.',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>4)
		),
		'address'=>array(
			'label'=>'адрес',
			'type'=>'autocomplete',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'rid'=>array(
			'label'=>'район',
			'type'=>'select',
			'onselect'=>'reload_pid',
			'style'=>'max-width:220px',
			'list'=>'list_of_rayons',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'psp'=>array(
			'label'=>'паспорт',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'phone'=>array(
			'label'=>'телефон',
			'type'=>'text',
			'class'=>'nowr',
			'table_style'=>'width:100px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>2)
		),
		'email'=>array(
			'label'=>'email',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>2)
		),
		'user'=>array(
			'label'=>'логин',
			'type'=>'text',
			'style'=>'width:150px',
			'table_style'=>'',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
		),
		'subtype'=>array(
			'label'=>'тип подкл-я',
			'type'=>'select',
			'list'=>$config['map']['clienttypes'],
			'table_style'=>'width:50px',
			'class'=>'ctxt',
			'native'=>false,
			'access'=>array('r'=>2,'w'=>4)
		),
		'password'=>array(
			'label'=>'пароль',
			'type'=>'password',
			'style'=>'width:150px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'pid'=>array(
			'label'=>'Тарифный пакет',
			'type'=>'select',
			'list'=>'',
			'style'=>'max-width:195px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
		),
		'next_pid'=>array(
			'label'=>'следующий пакет',
			'type'=>'select',
			'list'=>'',
			'style'=>'max-width:195px',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>2)
		),
		'expired'=>array(
			'label'=>'пакет заканчивается',
			'type'=>'date',
			'class'=>'date',
			'style'=>'width:80px',
			'table_style'=>'width:80px',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>3)
		),
		'ip'=>array(
			'label'=>'статический IP',
			'type'=>'text',
			'class'=>'csid',
			'native'=>false,
			'access'=>array('r'=>2,'w'=>4)
		),
		'opt82'=>array(
			'label'=>'Привязка',
			'type'=>'checkbox',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'csid'=>array(
			'label'=>'mac',
			'type'=>'text',
			'class'=>'csid',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'note'=>array(
			'label'=>'Примечание',
			'type'=>'textarea',
			'class'=>'note',
			'style'=>'width:300px;height:58px',
			'table_style'=>'width:300px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'blocked'=>array(
			'label'=>'Блокирован',
			'type'=>'checkbox',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'disabled'=>array(
			'label'=>'пользователь выключен',
			'type'=>'checkbox',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'groupname'=>array(
			'label'=>'groupname',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>4,'w'=>4)
		),
		'deposit'=>array(
			'label'=>'на счету',
			'type'=>'nofield',
			'class'=>'summ',
			'style'=>'width:80px;text-align:right',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'credit'=>array(
			'label'=>'кредит',
			'type'=>'nofield',
			'class'=>'summ',
			'style'=>'width:80px;text-align:right',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
		),
		'add_date'=>array(
			'label'=>'дата создания',
			'type'=>'nofield',
			'style'=>'width:105px;text-align:center',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
		),
		'last_connection'=>array(
			'label'=>'последнее подключение',
			'type'=>'nofield',
			'class'=>'date',
			'style'=>'width:105px;text-align:center',
			'table_style'=>'width:80px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
		),
		'late_payment'=>array(
			'label'=>'последняя оплата',
			'type'=>'nofield',
			'style'=>'width:105px;text-align:center',
			'table_style'=>'width:80px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
		),
		'prev_pid'=>array(
			'label'=>'prev_pid',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
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
	'checks'=>array(
		'save'=>'check_user_for_save'
	),
	'before_table_load'=>'before_load_users',
	'before_check'=>'before_check_users',
	'before_new'=>'before_new_user',
	'before_edit'=>'before_edit_user',
	'before_save'=>'before_save_user',
	'form_onsave'=>'user_onsave',
	'allow_delete'=>'allow_delete_user',
	'before_delete'=>'before_delete_user',
	'form_triggers'=>array(
		'deposit'=>'cell_summ',
		'credit'=>'cell_summ',
		'add_date'=>'cell_date',
		'last_connection'=>'cell_date',
		'late_payment'=>'cell_date',
		'ip'=>'get_user_ip',
		'source'=>'get_source',
	),
	'table_triggers'=>array(
		'contract'=>'user_contract',
		'user'=>'user_traffic',
		'subtype'=>'user_subtype',
		'add_date'=>'cell_date',
		'fio'=>'shortfio',
		'pid'=>'user_packet',
		'next_pid'=>'user_packet',
		'rid'=>'user_rayon',
		'deposit'=>'cell_summ',
		'credit'=>'cell_summ',
		'paidsum'=>'cell_summ',
		'expired'=>'user_expired',
		'source'=>'get_source',
		'last_connection'=>'get_last_connection',
	),
	'before_save_triggers'=>array(
		'deposit'=>''
	),
	'form_autocomplete'=>array(
		'address'=>'auto_address',
	),
	'form_reloadselect'=>array(
		'pid'=>'reload_packets',
	),
);

function before_load_users($t) {
	global $config, $q, $DEBUG;
	if(!$q) $q = new sql_query($config['db']);
 	if(!isset($config['owns'])) unset($t['filters']['source']);
//	log_txt(__function__.": filters:".arrstr(array_keys($t['filters'])));
	if(@$_REQUEST['key']=='rid' && isset($_REQUEST['id'])){
		$t['filters']['rid']['value'] = "_".numeric($_REQUEST['id']);
	}
	return $t;
}

function before_new_user($f) {
	global $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	$cid = isset($_REQUEST['cid'])? numeric($_REQUEST['cid']) : '';
	if($cid){
		$c = $q->select("SELECT * FROM claims WHERE unique_id='{$cid}'",1);
		if($c){
			$f['defaults'] = array_merge($f['defaults'],array_intersect_key($c,$f['fields']));
			$f['defaults']['cid'] = $cid;
		}
	}
	$f['fields']['source']['access']['w']=3;
	foreach(array_keys($f['fields']) as $n)
		if(isset($_REQUEST[$n])) $f['defaults'][$n] = str(preg_replace('/^_/','',$_REQUEST[$n]));
	$f['fields']['expired']['type'] = 'nofield';
	$f['fields']['next_pid']['list'] = $f['fields']['pid']['list'] = all2array(list_of_packets($f['defaults']['rid']),'',0);
//	$f['fields']['contract']['type'] = 'nofield';
	if(isset($config['owns'])) $f['fields']['source']['type'] = 'select';
	if(!isset($config['owns'])) unset($f['fields']['source']);
	$f['header'] = "Новая учётная запись";
	unset($f['fields']['note']['label']);
	return $f;
}

function before_edit_user($f) {
	global $config, $q, $opdata, $devtype;
	if(!$q) $q = new sql_query($config['db']);
	if(!isset($config['owns'])) unset($f['fields']['source']);
	else $f['fields']['source']['type'] = 'select';
	if((!isset($f['id']) || !$f['id']) && isset($_REQUEST['uid']))
		$f['id'] = numeric($_REQUEST['uid']);
	if((!isset($f['id']) || !$f['id']) && isset($_REQUEST['user']))
		$f['id'] = $q->select("SELECT uid FROM users WHERE user='".strict($_REQUEST['user'])."'",4);
	if(isset($f['id']) && is_numeric($f['id'])){
		$u = $q->get("users",$f['id']);
	}elseif(isset($f['id']) && is_string($f['id'])){
		$u = $q->get("users",$f['id'],'user');
		$f['id'] = $u['uid'];
	}
	if(!$u) stop(array('result'=>'ERROR','desc'=>"Пользователь не найден!"));
	if($u['opt82']){
		$ui = parse_opt82($u['opt82']);
		$dev = $q->select("SELECT name, type, ip, node1 FROM devices WHERE macaddress='{$ui['device']}'",1);
		$addr = ($dev)? $q->select("SELECT address FROM map WHERE id='{$dev['node1']}'",4) : "";
		if(!$dev) $info = "Устройство {$ui['device']} отсутствует в базе";
		else $info = "Устройство: {$devtype[$dev['type']]}\rназвание: {$dev['name']}\rнаходится: {$addr}\r".(($dev['type']=='onu')? "mac: ".$ui['device'] : "ip: ".$dev['ip']).($ui['vlan']?"\rvlan: {$ui['vlan']}":"")."\rпорт: {$ui['port']}".($ui['subport']?":{$ui['subport']}":"");
		$f['fields']['opt82']['title'] = $info;
	}
	$f['header'] = "Учётная запись {$f['id']}";
// ограничение списка пакетов по району
	$pk = list_of_packets();
	$rn = $q->fetch_all("SELECT gid as id, unique_id FROM rayon_packet WHERE rid='{$u['rid']}'");
	if(!isset($rn[$u['pid']])) $rn[$u['pid']] = 1;
	if($rn) $pk = array_intersect_key($pk,$rn);
	$f['fields']['next_pid']['list'] = $f['fields']['pid']['list'] = all2array($pk,'',0);
	$last = $q->select("SELECT * FROM radacct WHERE username='{$u['user']}' AND acctstoptime is NULL ORDER BY acctstarttime DESC LIMIT 1",1);

	foreach(array('source','contract',) as $n){
		if($opdata['level']<5 && $u['add_date']!=date('Y-m-d')){
			if(isset($f['fields'][$n]) && $opdata['level'] < $f['fields'][$n]['access']['w'] && $u['add_date']!=date('Y-m-d'))
				$f['fields'][$n]['type'] = 'nofield';
		}
	}
	if($opdata['level']>4){
		$f['fields']['deposit']['type'] = 'text';
		$f['fields']['deposit']['style'] = 'width:70px;text-align:right';
	}
	if($opdata['level']<4 && $u['add_date']!=date('Y-m-d')){
		$fl = array('pid','ip',);
		foreach($fl as $n) if(isset($f['fields'][$n])) $f['fields'][$n]['disabled'] = true;
	}
	$stl = $f['fields']['last_connection']['style'];
	if($last) $f['fields']['last_connection']['style'] = css($stl,'background-color:rgba(0,255,0,0.3)');
	else{
		if(timecmp($u['last_connection'],'-1 month') > 0){
			$f['fields']['last_connection']['style'] = css($stl,'background-color:rgba(255,0,0,0.2)');
		}else{
			if(timecmp($u['last_connection'],'-3 month') > 0)
				$f['fields']['last_connection']['style'] = css($stl,'background-color:rgba(0,0,0,0.08)');
			else
				$f['fields']['last_connection']['style'] = css($stl,'background-color:rgba(0,0,0,0.23)');
		}
	}
	unset($f['fields']['note']['label']);
	return $f;
}

function get_source($v,$r,$fn=null){
	global $config;
	$a = isset($config['owns'][$v])? $config['owns'][$v] : $v;
	return $a;
}

function get_user_ip($v,$r) {
	global $cache, $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	$ip = '';
	if(isset($r['user'])){
		$ip = $q->select("SELECT value FROM radreply WHERE attribute='Framed-IP-Address' and username='{$r['user']}'",4);
		if(!$ip) $ip = '';
	}
	return $ip;
}

function get_last_connection($v,$r=null,$fn=null) {
	if($v=='0000-00-00') return "";
	else return cyrdate($v);
}

function user_subtype($i,$r=null,$fn=null) {
	global $config;
	$r = (isset($config['map']['clienttypes'][$i]))? $config['map']['clienttypes'][$i] : $i;
	return $r;
}

function user_packet($i,$r=null,$fn=null) {
	global $DEBUG;
	$p = list_of_packets();
	$r = (isset($p[$i]))? $p[$i] : $i;
	$r = preg_replace('/^([^(]*) \(([^\)]*)\).*/','<span title="$2">$1</span>',$r);
	return $r;
}

function user_expired($v,$r=null,$fn=null) {
	global $DEBUG;
	if(preg_match('/^0000-00-00/',$v)) return "";
	return cyrdate($v);
}

function user_traffic($v,$r) {
	global $DEBUG;
	$v = "<span class=\"usrview\" style=\"color:#00d\" uid=\"".@$r[0]."\">$v</span>";
	return $v;
}

function user_contract($v,$r,$fn=null) {
	global $DEBUG;
	$color = '#008';
	$c = preg_split('/:/',$v);
	if(isset($r[7])) {
		$color = '#999';
		$dp = @$r[7]; $cr = $r[8];
		$fix = (@$r[9])? true : false;
		$on = $fix? strtotime(@$r[9]) > strtotime('now') : false;
		if($dp - $cr < -0.009 && $fix && $on) $color = '#b00';
		if($dp - $cr < -0.009 && (!$fix || !$on)) $color = '#f00';
		elseif($dp - $cr >= 0 && ($fix && $on || !$fix)) $color = '#090';
		elseif(strtotime(@$r[2]) > strtotime('-3 month')) $color = '#222';
		if($c[0]==560010) log_txt(__function__.": fix=".arrstr($fix)." on=".arrstr($on)." dp=$dp cr=$cr live=".arrstr(strtotime(@$r[2]) > strtotime('-3 month'))." color=$color");
	}
	if($c[1] == 1) $color = '#808';
	$v = "<span class=\"usrview\" style=\"font-weight:bold;color:$color\" uid=\"".@$r[0]."\">".@$c[0]."</span>";
	return $v;
}

function check_user_for_save($r) {
	global $config, $errors, $DEBUG;
	$result = true;
	if(!$result) stop(array('result'=>'ERROR','desc'=>implode(",<br>",$errors)));
	return $result;
}

function before_check_users($f,$my) {
	global $opdata, $q;
	if(!isset($q)) $q = new sql_query($config['db']);
	$new = $_REQUEST['id']=='new';
	if(isset($f['id']) || $new){
		$client = $new? array('add_date'=>date('Y-m-d')) : $q->get('users',$f['id']);
		if($client['add_date']==date('Y-m-d')){ // понижение уровня доступа в первый день создания клиента
			$f['fields']['source']['access']['w'] = 2;
			$f['fields']['pid']['access']['w'] = 2;
		}
	}
	if($_REQUEST['do'] == 'realsave' && !$new){
		$id = numeric($_REQUEST['id']);
		$n = array_intersect_key($_REQUEST,$q->table_fields('users'));
		$usr = $q->get('users',$id);
		foreach($n as $k=>$v) if(isset($_REQUEST['old_'.$k]) && $_REQUEST['old_'.$k]!=$usr[$k]) $_REQUEST['old_'.$k] = $usr[$k];
	}
	return $f;
}

function before_save_user($c,$o,$my) {
	global $opdata, $cache, $config, $q, $errors;
	if(!isset($q)) $q = new sql_query($config['db']);
	$rad = new radius();
	$r = array_merge($o,$c);
	$err = array(); $warn = array();

	if(isset($c['fio']) && !$c['fio']) $err[] = "Не введено ФИО клиента!";
	elseif(isset($c['fio']) && !($r['fio'] = normalize_fio($c['fio']))) $err[] = "ФИО клиента введено неправильно!";

	if(isset($c['address']) && !$c['address']) $err[] = "Не введен адрес клиента!";
	elseif(isset($c['address']) && !($c['address'] = normalize_address($c['address']))) $err[] = "Адрес клиента введён неправильно!";

	if(isset($c['rid']) && !$r['rid']) $err[] = "Не введен район!";
	elseif(isset($c['rid']) && !$q->get('rayon',$r['rid'])) $err[] = "Район не существует!";

	if($my->id != 'new' && isset($c['phone']) && $c['phone']!='' && !($c['phone'] = normalize_phone($c['phone']))) $err[] = "Неправильный номер телефона!";
	if($my->id != 'new' && isset($c['email']) && $c['email']!='' && !($c['email'] = normalize_email($c['email']))) $err[] = "Неправильный электронный адрес!";

	if(isset($c['user'])){
		if(!$c['user']) $err[] = "Не указан логин клиента!";
		elseif(mb_strlen($c['user'])<3) $err[] = "Слишком короткий логин!";
		elseif(preg_match('/[^0-9A-Za-z\-_.]/u',$c['user'])) $err[] = "В логине присутствуют неразрешённые символы!";
	}

	if(isset($c['password']) && !$c['password']) $err[] = "Не указан пароль!";
	elseif(isset($c['password']) && mb_strlen($c['password'])<6) $err[] = "пароль слишком простой!";

	if(isset($c['pid'])){
		if(!$c['pid']) $err[] = "Не указан пакет!";
		elseif(!($packet = $q->get('packets',$c['pid']))) $err[] = "Пакет не существует!";
		else{
			if($packet['fixed']!=10) $c['expired'] = '';
			if($r['credit']>0 && $r['credit']<1) $c['credit'] = 0;
			$c['deposit'] = round($r['deposit'],2);
		}
	}

	if($my->id == 'new'){
		if(!$c['phone'] && !$c['email']) $err[] = "Не введены данные для связи с клиентом!";
		if($c['phone'] && !($c['phone'] = normalize_phone($c['phone']))) $err[] = "Неправильный номер телефона!";
		if($c['email'] && !($c['email'] = normalize_email($c['email']))) $err[] = "Неправильный электронный адрес!";
		if($q->get('users',$c['address'],'address')) $warn[] = "Клиент с таким адресом уже есть!";
		$r['add_date'] = $c['add_date'] = date2db();
	}
	if(isset($c['user']) && $q->get('users',$c['user'],'user')) $err[] = "Такой логин уже есть!";

	if(key_exists('csid',$c) && $c['csid']){
		$other = $q->select("SELECT * FROM users WHERE csid='{$c['csid']}' AND uid!='{$r['uid']}'",1);
		if($other) $err[] = "Введённый мак адрес принадлежит пользователю {$other['user']} ({$other['address']})!";
	}
	
	if(isset($c['opt82'])) { if($c['opt82']) unset($c['opt82']); else $c['opt82'] = ""; }

	if(isset($errors) && count($errors)>0) $err = array_merge($err,$errors);
	if(count($err)>0) stop(array('result'=>"ERROR",'desc'=>implode('<br>',$err)));
	if(count($warn)>0 && $_REQUEST['do'] != 'realsave'){
		if($cl = $q->select("SELECT * FROM claims WHERE address='{$r['address']}' AND claimtime>date(now())")){
			$form = new form($config);
			$out = $form->confirmForm('new','realsave',implode('<br>',$warn)."<br>Создать учётную запись?",'users');
			foreach($c as $k=>$v) $out['form']['fields'][$k] = array('type'=>'hidden','value'=>$v);
			$out['nosubmit'] = true;
			stop($out);
		}
	}
	if($my->id == 'new'){
		$uid = $q->select("
			SELECT u1.uid+1 as id, u2.uid as chk 
			FROM users u1 LEFT OUTER JOIN users u2 ON u2.uid = u1.uid + 1
			WHERE u1.uid<10000 HAVING chk is NULL LIMIT 1
		",4);
		if(!$uid){
			if($q->select("SELECT count(*) FROM users",4)>0){
				stop(array('result'=>'ERROR','desc'=>'Закончился диапазон ID'));
			}else{
				$uid=1;
			}
		}
		$c['uid'] = $r['uid'] = $uid;
		if (@$c['cid'] && ($claim = $q->get("claims",$c['cid']))) { // если есть указано заявление пользователя
			$q->update_record('claims',array('unique_id'=>$claim['unique_id'],'uid'=>$uid,'user'=>$c['user']));
			$q->query("UPDATE documents SET uid='$uid' WHERE val='{$c['cid']}'");
		}
	}
	if(isset($c['ip'])){
		$c['ip'] = trim($c['ip']);
		if(!$r['user']) stop(array('result'=>"ERROR",'desc'=>"Не найден логин пользователя!"));
		if(($un = $q->select("SELECT username FROM radreply WHERE attribute='Framed-IP-Address' and value='{$c['ip']}'",4)) && $un != $r['user'])
			stop(array('result'=>"ERROR",'desc'=>"ip адрес {$c['ip']} уже имеет владельца!"));
		$rr = $q->select("SELECT * FROM radreply WHERE username='{$r['user']}' AND attribute='Framed-IP-Address'",1);
		if(!isset($packet)) $packet = $q->get('packets',$r['pid']);
		if(($packet['fixed']>0 || $packet['tos']>0) && $c['ip'] != '' && !$rad->gray_ip($c['ip'])) {
			if($r['deposit'] + $r['credit'] < IP_COST * (($packet['fixed']!=10)? 1 : $packet['period'])){
				stop(array('result'=>"ERROR",'desc'=>"Не достаточно денег для аренды IP адреса!"));
			}
			$usr = new user($r['uid']);
		}
		$newip = normalize_ip($c['ip']);
		if(!$rr && $newip){
			if(!$q->insert('radreply',array('username'=>$r['user'],'attribute'=>'Framed-IP-Address','op'=>':=','value'=>$newip)))
				stop(array('result'=>"ERROR",'desc'=>"Ошибка: запись ip не выполнена!"));
			log_db($r['user'],$r['uid'],"установлен постоянный IP","$newip");
		}elseif($rr && $newip && $newip != $rr['value']){
			$oldip = $rr['value'];
			$rr['value'] = $newip;
			if(!$q->update_record('radreply',$rr)) stop(array('result'=>"ERROR",'desc'=>"Ошибка: замена ip не выполнена!"));
			log_db($r['user'],$r['uid'],"изменен постоянный IP","$oldip -> $newip");
		}elseif($rr && !$newip){
			if(!$q->del('radreply',$rr['id'])) stop(array('result'=>"ERROR",'desc'=>"Ошибка: удаление ip не выполнено!"));
			log_db($r['user'],$r['uid'],"уладён постоянный IP","{$rr['value']}");
		}
		if(isset($usr)) $usr->activate();
	}
	if(!$r['contract']){
		$contract = CITYCODE * 10000 + $r['uid'];
		if($q->select("SELECT * FROM users WHERE contract=$contract",4)){
			$con = $q->select("
				SELECT u1.contract+1 as id, u2.contract as chk 
				FROM users u1 LEFT OUTER JOIN users u2 ON u2.contract = u1.contract + 1
				WHERE u1.contract<".((CITYCODE + 1) * 10000)." HAVING chk is NULL LIMIT 1
			",4);
			if(!$con){
				if($q->select("SELECT count(*) FROM users",4)>0){
					stop(array('result'=>'ERROR','desc'=>'Закончился диапазон для контрактов'));
				}else{
					stop(array('result'=>'ERROR','desc'=>'Ошибка поиска свободного номера контракта'));
				}
			}
			$contract = $con;
		}
		$c['contract'] = $contract;
	}
	if(isset($c['contract']) && ($chk = $q->select("SELECT * FROM users WHERE contract={$c['contract']}"))){
		if($chk['uid'] != $r['uid']) stop(array('result'=>'ERROR','desc'=>'Номер контракта уже используется'));
	}
	return $c;
}

function user_onsave($id,$s,$my) {
	global $opdata, $cache, $config, $q, $errors;
	if(!isset($q)) $q = new sql_query($config['db']);
	$r = array_merge($my->row,$s);
	$fld = array('address'=>0,'phone'=>0,'user'=>'name','rid'=>0);
	if($my->id != 'new'){
		$user = isset($s['user'])? $s['user'] : $my->row['user'];
		if(($upcl = array_intersect_key($s,$fld)) && count($upcl)>0){ // обновляем данные по полям из $fld в заявках
			foreach($upcl as $k=>$v) $cl[] = "`$k`='$v'";
			$q->query("UPDATE claims SET ".implode(', ',$cl)." WHERE status<3 AND uid='{$r['uid']}'");
		}
		if(isset($s['user'])){
			$q->query("UPDATE radreply SET username='{$r['user']}' WHERE username='{$my->row['user']}'");
			$q->query("UPDATE map SET name='{$s['user']}' WHERE type='client' AND name='{$my->row['user']}'");
		}
		if(isset($s['rid'])){
			$q->query("UPDATE map SET rayon='{$s['rid']}' WHERE type='client' AND name='{$user}'");
			if($q->modified()==1) $client = $q->select("SELECT id FROM map WHERE type='client' AND name='$user'",4);
		}
		if(isset($s['address'])){
			$q->query("UPDATE map SET address='{$s['address']}' WHERE type='client' AND name='{$user}'");
			if($q->modified()==1) $client = $q->select("SELECT id FROM map WHERE type='client' AND name='$user'",4);
		}
		if(key_exists('blocked',$s) || @$s['disabled'] || @$s['pid'] || @$s['expired']) {
			$u = $q->select("SELECT * FROM radacct WHERE username='{$r['user']}' AND acctstoptime is NULL");
			if($u) foreach($u as $k=>$conn){
				if($conn['framedprotocol']=='PPP'){
					$q->insert('raddropuser',array_intersect_key($conn,$q->table_fields('raddropuser')));
				}elseif($conn['framedprotocol']=='IPoE'){
					if($g = send_coa($r,$v)) log_db($r['user'],$r['uid'],"динамическое обновление","включение профиля '$g'");
				}
			}
		}
		if(isset($client) && $client>0){
			$f = getFeatureCollection($client);
			return array('result'=>'OK','feature'=>$f['features'][0]);
		}
	}
}

function build_filter_for_users() {
	return filter2db('users');
}

function build_period_for_users() { // используется ajax/stat.php
	return period2db('users','add_date');
}

function build_period_for_paid() { // используется ajax/stat.php
	return period2db('users','acttime');
}

function allow_delete_user($id) {
	global $opdata, $config, $q;
	if(!isset($q)) $q = new sql_query($config['db']);
	if(!$id || !($client = $q->get('users',$id))){
		log_txt(__function__.": клиент не найден! id=".arrstr($id));
		return "Клиент не найден!";
	}
	if($opdata['level'] < 5){
		if($q->select("SELECT count(*) FROM pay WHERE uid='{$client['uid']}' AND acttime>date_add(now(),interval -1 year)",4)>0)
			return "Имеются платежи по клиенту!";
		if(strtotime($q->select("SELECT last_connection FROM users WHERE uid='{$client['uid']}'",4))>strtotime('-3 month'))
			return "Имеются нестарые подключения по клиенту!";
	}
	if($opdata['level'] < 3) return "Доступ запрещён!";
	return 'yes';
}

function before_delete_user($o,$my) {
	global $opdata, $config, $q;
	if(!isset($q)) $q = new sql_query($config['db']);
	$q->query("DELETE FROM radreply WHERE username = '{$o['user']}'");
	if($claim = $q->select("SELECT * FROM claims WHERE type=1 AND uid = '{$o['uid']}'",1)){
		if($claim['status']==4 && strtotime(date('Y-m-01')) < strtotime($o['add_date'])){
			$q->query("UPDATE claimperform SET status=2 WHERE cid='{$claim['unique_id']}'");
			$q->query("UPDATE claims SET status=0, uid=0 WHERE unique_id='{$claim['unique_id']}'");
		}elseif($claim['status']==2){
			$q->query("UPDATE claims SET uid=0 WHERE unique_id='{$claim['unique_id']}'");
			$q->query("UPDATE claimperform SET status=1 WHERE cid='{$claim['unique_id']}'");
		}
	}
	if($c = $q->select("SELECT * FROM map WHERE type='client' AND name = '{$o['user']}'",1)){
		deleteFeatures($c['id']);
	}
	$q->query("DELETE FROM pay WHERE uid = '{$o['uid']}'");
	return $o;
}

function reload_packets() {
	global $opdata, $config, $q;
	if(!isset($q)) $q = new sql_query($config['db']);
	$rid=(key_exists('value',$_REQUEST))? numeric($_REQUEST['value']):'';
	$uid=(key_exists('id',$_REQUEST))? numeric($_REQUEST['id']):'';
	$pk = $q->select("SELECT p.pid, concat(p.name,' (',round(p.fixed_cost),'р.)') as name FROM packets p, users u WHERE u.pid=p.pid AND uid='$uid'",1);
	foreach(list_of_packets($rid) as $k=>$v) $list['_'.$k] = $v;
	if($pk) $list['_'.$pk['pid']] = $pk['name'];
	$out = array('result'=>'OK','select'=>array('list'=>$list),'target'=>'pid');
	return $out;
}

function check_map4client($user,$new,$id) { // для проверки целостности введённых данных (карты)
	global $config, $errors, $q;
	log_txt(__function__.": user[$id]: {$user['user']} address: {$user['address']} source: {$user['source']}");
	$err = array();
	$pk = $q->fetch_all("SELECT pid, name FROM packets WHERE fixed_cost>0 AND name not like '%Wi-Fi%'",'pid');
	if(isset($pk[$user['pid']]) && strtotime($user['add_date']) >= strtotime('2018-07-01') && $user['rid']!=22){
		include_once 'geodata.php';
		$h = preg_replace('/\/.*/','',$user['address']);
		$d = $q->select("SELECT * FROM devices WHERE type='client' AND name='{$user['user']}'",1);
		if(!$d && preg_match('/\//',$user['address'])){
			if(!($d = $q->select("SELECT * FROM map WHERE type='home' AND address='$h'",1))) $err[]="дом $h не найден на карте!";
		}else{
			if(!$d) $err[]="клиент $h не найден на карте!";
			elseif(!($cb = $q->select("SELECT * FROM devices WHERE type='cable' AND (node1='{$d['node1']}' OR  node2='{$d['node1']}')",1)))$err[]="клиент $h без кабеля!";
			elseif(!($p = $q->select("SELECT * FROM devports WHERE device='{$cb['id']}' AND node!='{$d['node1']}' AND link is not NULL",1)))$err[]="кабель клиента $h не имеет подключения!";
			else{
				$tr = new Trace();
				$cd = $tr->capdevices($p['id']);
				if($cd['begin']['type']=='switch'||$cd['end']['type']=='switch'){
					$err[]="не найден свич подключения клиента $h в картах!";
					log_txt(__function__.": capdevice for port {$p['id']} = begin: {$cd['begin']['type']}   end: {$cd['end']['type']}");
				}
			}
		}
		if(count($err)>0 && $_REQUEST['do'] != 'realsave' && $id!='new'){
			$form = new form($config);
			$out = $form->confirmForm($id,'realsave',implode('<br>',$err)."<br>Пользователь останется заблокирован<br>до исправления ошибок. Продолжить?",'users');
			foreach($new as $k=>$v) $out['form']['fields'][$k] = array('type'=>'hidden','value'=>$v);
			$out['nosubmit'] = true;
			stop($out);
		}
	}
	return (count($err)>0)? false : true;
}

?>
