<?php
include_once("classes.php");
include_once("rayon_packet.cfg.php");
include_once("rayon.cfg.php");

if(!isset($q)) $q = new sql_query($config['db']);

$tables['radacct']=array(
	'name'=>'radacct',
	'title'=>'Клиент',
	'target'=>"users.php",
	'module'=>"stdform",
	'limit'=>'yes',
	'key'=>'radacctid',
	'class'=>'normal',
	'style'=>'max-width:900px',
	'table_style'=>'100%',
	'delete'=>'no',
	'add'=>'no',
	'form_query'=>"
		SELECT 
			radacctid,
			acctsessionid,
			acctuniqueid,
			username,
			groupname,
			uid,
			pid,
			credit,
			before_billing,
			billing_minus,
			realm,
			nasipaddress,
			nasportid,
			nasporttype,
			acctstarttime,
			acctstoptime,
			acctsessiontime,
			acctauthentic,
			connectinfo_start,
			connectinfo_stop,
			inputgigawords,
			acctinputoctets,
			outputgigawords,
			acctoutputoctets,
			calledstationid,
			callingstationid,
			acctterminatecause,
			servicetype,
			framedprotocol,
			framedipaddress,
			acctstartdelay,
			acctstopdelay,
			xascendsessionsvrkey,
			dropped
		FROM
			radacct
		",
	'table_query'=>"
		SELECT
			a.radacctid as id,
			a.username,
			u.address,
			concat(a.nasipaddress,' : <em>',a.nasportid,'</em>') as nas,
			a.acctstarttime,
			a.acctsessiontime,
			a.inputgigawords << 32 | a.acctinputoctets as inbytes,
			a.outputgigawords << 32 | a.acctoutputoctets as outbytes,
			a.billing_minus,
			concat(a.framedipaddress,':',a.groupname) as framedipaddress,
			a.callingstationid
		FROM 
			radacct as a LEFT OUTER JOIN users as u ON u.user=a.username
		WHERE a.acctstoptime is NULL :FILTER:
		ORDER BY :SORT:
	",
	'field_alias'=>array('pid'=>'u','rid'=>'u'),
	'filters'=>array(
		'rid'=>array(
			'type'=>'checklist',
			'label'=>'районы',
			'title'=>'фильтр по районам',
			'keep'=>true,
			'list'=>$q->fetch_all("SELECT concat('_',rid) as id, r_name as name FROM rayon ORDER BY r_name"),
			'value'=>'_'
		),
		'pid'=>array(
			'type'=>'select',
			'label'=>'пакет',
			'title'=>'фильтр по пакету',
			'style'=>"width:110px",
			'list'=>all2array($q->fetch_all("SELECT pid as id, name FROM packets ORDER BY num")),
			'value'=>'_'.(isset($_REQUEST['pid'])? numeric($_REQUEST['pid']): ""),
		),
		'address'=>array(
			'type'=>'text',
			'label'=>'адрес',
			'title'=>'фильтр по адресу',
			'style'=>"width:110px",
			'value'=>''
		),
		'framedipaddress'=>array(
			'type'=>'text',
			'label'=>'ip',
			'title'=>'фильтр по ip',
			'style'=>"width:110px",
			'value'=>''
		),
		'nasipaddress'=>array(
			'type'=>'select',
			'label'=>'NAS',
			'title'=>'фильтр по сервету доступа',
			'style'=>"width:110px",
			'list'=>all2array($q->fetch_all("
				SELECT nasipaddress as id, concat(nasipaddress,' - ',shortname,'') FROM nas ORDER BY INET_ATON(nasipaddress)
			")),
			'value'=>isset($_REQUEST['nasipaddress'])? preg_replace('/[^0-9.]/','',$_REQUEST['nasipaddress']): "",
		),
	),
	'table_menu'=>array(
		'close'=>array('label'=>"<img src=\"pic/remove.png\"> сбросить",'to'=>'edit','query'=>"go=clients&do=userkill&table=radacct"),
		'usrcard'=>array('label'=>"<img src=\"pic/usr.png\"> клиент",'to'=>'users.php','query'=>"go=usrstat&table=radacct"),
		'showmap'=>array('label'=>"<img src=\"pic/usr.png\"> показать на карте",'to'=>'map','query'=>"go=clients&do=clientobject&table=radacct"),
		'blocked'=>array('label'=>"<img src=\"pic/lock.png\"> заблокировать",'to'=>'edit','query'=>"go=radacct&do=block"),
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
			'fields'=>array('user','password','pid','next_pid','expired','ip','csid')
		),
		'other'=>array(
			'type'=>'fieldset',
			'legend'=>'разное',
			'style'=>'height:85px;width:135px',
			'fields'=>array('blocked','disabled')
		),
		'money'=>array(
			'type'=>'fieldset',
			'legend'=>'деньги',
			'style'=>'height:85px;width:140px',
			'fields'=>array('deposit','credit')
		),
		'adding'=>array(
			'type'=>'fieldset',
			'legend'=>'даты',
			'style'=>'float:left;width:652px',
			'fields'=>array('add_date','last_connection','late_payment')
		),
	),
	'defaults'=>array(
		'filter'=>'build_filter_for_radacct',
		'sort'=>'get_racct_sort',
	),
 	'table_footer'=>array(
		'contract'=>'Всего:',
		'note'=>'fcount',
		'ip'=>'user_ip_address',
 	),
	'fields'=>array(
		'radacctid'=>array(
			'label'=>'id',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>3)
		),
		'acctsessionid'=>array(
			'label'=>'session',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>3)
		),
		'acctuniqueid'=>array(
			'label'=>'unique',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>3)
		),
		'username'=>array(
			'label'=>'логин',
			'type'=>'text',
			'table_style'=>'width:180px',
			'table_style'=>'',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
		),
		'groupname'=>array(
			'label'=>'профиль',
			'type'=>'text',
			'style'=>'width:150px',
			'table_style'=>'',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
		),
		'pid'=>array(
			'label'=>'Тарифный пакет',
			'type'=>'select',
			'list'=>'',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
		),
		'before_billing'=>array(
			'label'=>'на счету',
			'type'=>'nofield',
			'class'=>'summ',
			'style'=>'width:80px;text-align:right',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'billing_minus'=>array(
			'label'=>'снято',
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
		'nas'=>array(
			'label'=>'NAS',
			'type'=>'text',
			'class'=>'nowr',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
		),
		'nasipaddress'=>array(
			'label'=>'NAS',
			'type'=>'text',
			'class'=>'ctxt',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
		),
		'nasportid'=>array(
			'label'=>'порт NAS',
			'type'=>'text',
			'class'=>'ctxt',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
		),
		'nasporttype'=>array(
			'label'=>'тип порта NAS',
			'type'=>'text',
			'class'=>'ctxt',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
		),
		'acctstarttime'=>array(
			'label'=>'начало',
			'type'=>'date',
			'class'=>'date',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
		),
		'acctstoptime'=>array(
			'label'=>'конец',
			'type'=>'date',
			'class'=>'date',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
		),
		'acctsessiontime'=>array(
			'label'=>'Время',
			'type'=>'text',
			'class'=>'summ',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
		),
		'acctauthentic'=>array(
			'label'=>'херня какая-то',
			'type'=>'text',
			'class'=>'txt',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'connectinfo_start'=>array(
			'label'=>'инфо при старте',
			'type'=>'text',
			'class'=>'txt',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'connectinfo_stop'=>array(
			'label'=>'инфо при откл.',
			'type'=>'text',
			'class'=>'txt',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'inputgigawords'=>array(
			'label'=>'G in',
			'type'=>'text',
			'class'=>'num',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'acctinputoctets'=>array(
			'label'=>'In bytes',
			'type'=>'text',
			'class'=>'num',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'outputgigawords'=>array(
			'label'=>'G out',
			'type'=>'text',
			'class'=>'num',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'acctoutputoctets'=>array(
			'label'=>'Out bytes',
			'type'=>'text',
			'class'=>'num',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'calledstationid'=>array(
			'label'=>'mac сервера',
			'type'=>'text',
			'class'=>'num',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'callingstationid'=>array(
			'label'=>'mac',
			'type'=>'text',
			'class'=>'csid',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
		),
		'acctterminatecause'=>array(
			'label'=>'причина откл.',
			'type'=>'text',
			'class'=>'txt',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
		),
		'servicetype'=>array(
			'label'=>'сервис',
			'type'=>'text',
			'class'=>'txt',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
		),
		'framedprotocol'=>array(
			'label'=>'протокол',
			'type'=>'text',
			'class'=>'txt',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
		),
		'framedipaddress'=>array(
			'label'=>'IP',
			'type'=>'text',
			'class'=>'nowr',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
		),
		'acctstartdelay'=>array(
			'label'=>'задержка при cтарте',
			'type'=>'text',
			'class'=>'txt',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
		),
		'acctstopdelay'=>array(
			'label'=>'задержка при откл.',
			'type'=>'text',
			'class'=>'txt',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
		),
		'xascendsessionsvrkey'=>array(
			'label'=>'херня какая-то',
			'type'=>'text',
			'class'=>'txt',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
		),
		'dropped'=>array(
			'label'=>'отключение',
			'type'=>'text',
			'class'=>'txt',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
		),
		'address'=>array(
			'label'=>'адрес',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'rid'=>array(
			'label'=>'район',
			'type'=>'select',
			'list'=>'list_of_rayons',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
		),
		'inbytes'=>array(
			'label'=>'In Mb',
			'type'=>'text',
			'class'=>'traf',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
		),
		'outbytes'=>array(
			'label'=>'Out Mb',
			'type'=>'text',
			'class'=>'traf',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>4)
		),
		'r_name'=>array(
			'label'=>'район',
			'type'=>'text',
			'native'=>false,
			'access'=>array('r'=>1,'w'=>1)
		),
		'ur'=>array(
			'label'=>'UR',
			'title'=>'Запрос пользователя',
			'type'=>'text',
			'class'=>'ctxt',
			'native'=>false,
			'access'=>array('r'=>1,'w'=>1)
		),
		'lc'=>array(
			'label'=>'LC',
			'title'=>'Обрыв связи',
			'type'=>'text',
			'class'=>'ctxt',
			'native'=>false,
			'access'=>array('r'=>1,'w'=>1)
		),
		'nr'=>array(
			'label'=>'NR',
			'title'=>'Запрос сервера доступа',
			'type'=>'text',
			'class'=>'ctxt',
			'native'=>false,
			'access'=>array('r'=>1,'w'=>1)
		),
		'st'=>array(
			'label'=>'ST',
			'title'=>'Окончание времени сессии',
			'type'=>'text',
			'class'=>'ctxt',
			'native'=>false,
			'access'=>array('r'=>1,'w'=>1)
		),
		'at'=>array(
			'label'=>'AT',
			'title'=>'Нет подтверждения от сервера доступа',
			'type'=>'text',
			'class'=>'ctxt',
			'native'=>false,
			'access'=>array('r'=>1,'w'=>1)
		),
		'pe'=>array(
			'label'=>'PE',
			'title'=>'Ошибка порта',
			'type'=>'text',
			'class'=>'ctxt',
			'native'=>false,
			'access'=>array('r'=>1,'w'=>1)
		),
		'ne'=>array(
			'label'=>'NE',
			'title'=>'Ошибка сервера доступа',
			'type'=>'text',
			'class'=>'ctxt',
			'native'=>false,
			'access'=>array('r'=>1,'w'=>1)
		),
		'c'=>array(
			'type'=>'hidden',
			'native'=>false,
			'access'=>array('r'=>1,'w'=>1)
		),
	),
	'group'=>'',
	'checks'=>array(
		'save'=>'check_racct_for_save'
	),
	'before_new'=>'before_new_racct',
	'before_edit'=>'before_edit_racct',
	'before_save'=>'before_save_racct',
	'form_onsave'=>'onsave_racct',
	'allow_delete'=>'allow_delete_racct',
	'before_delete'=>'before_delete_racct',
	'form_triggers'=>array(
		'before_billing'=>'cell_summ',
		'billing_minus'=>'cell_summ',
		'credit'=>'cell_summ',
		'acctstarttime'=>'cell_date',
		'acctstoptime'=>'cell_date',
	),
	'table_triggers'=>array(
		'before_billing'=>'cell_summ',
		'billing_minus'=>'cell_summ',
		'credit'=>'cell_summ',
		'username'=>'get_racct_user',
		'inbytes'=>'get_racct_mbytes',
		'outbytes'=>'get_racct_mbytes',
		'acctstarttime'=>'cell_atime',
		'acctsessiontime'=>'get_racct_sesstime',
		'framedipaddress'=>'get_framedipaddress'
	),
 	'table_footer'=>array(
		'username'=>'Всего:',
		'callingstationid'=>'fcount',
 	),
	'before_save_triggers'=>array(
		'deposit'=>''
	),
);

function before_new_racct($f) {
	global $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	return $f;
}

function before_edit_racct($f) {
	global $config, $q, $opdata;
	if(!$q) $q = new sql_query($config['db']);
	return $f;
}

function get_racct_sort() {
	$s = isset($_REQUEST['sort'])? str($_REQUEST['sort']) : 'acctstarttime';
	$s = preg_replace('/\bnas\b/','nasipaddress, cast(nasportid as unsigned)',$s);
	return $s;
}

function get_racct_user($v,$r=null,$fn=null) {
	return "<span class=\"linkform usrview\" add=\"go=stdform&do=edit&table=users&id=$v\">$v</span>";
}

function get_racct_sesstime($v,$r=null,$fn=null) {
	return sectime('h:m:s',$v);
}

function get_racct_mbytes($v,$r=null,$fn=null) {
	$r = floor( $v / MBYTE ); $l = mb_strlen("$r");
	if($l>3) $r = mb_substr("$r",0,$l-3)."'".mb_substr("$r",$l-3,3);
	if($l>6) $r = mb_substr("$r",0,$l-6).'"'.mb_substr("$r",$l-6,7);
	return $r;
}

function get_framedipaddress($v,$r=null,$fn=null) {
	global $explain_packet;
	$v = preg_split('/:/',$v,2);
	if(isset($explain_packet[$v[1]])){
		$title = $explain_packet[$v[1]];
		return "<span style=\"color:#a00\" title=\"$title\">$v[0]</span>";
	}else return $v[0];
}

function check_racct_for_save($r) {
	global $config, $errors, $DEBUG;
	$result = true;
	if(!$result) stop(array('result'=>'ERROR','desc'=>implode(",<br>",$errors)));
	return $result;
}

function before_save_racct($c,$o,$my) {
	global $cache, $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	$r = array_merge($o,$c);
	return $c;
}

function build_filter_for_radacct() {
	return filter2db('radacct');
}
?>
