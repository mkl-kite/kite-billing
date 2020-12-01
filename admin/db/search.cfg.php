<?php

$tables['search']=array(
	'name'=>"search",
	'title'=>"Найдено пользователей: ",
	'key'=>'uid',
	'target'=>'tab',
	'form_log'=>'no',
	'form_query'=>"
		SELECT 
			0 as uid,
			'' as user,
			'' as fio,
			'' as phone,
			'' as address,
			'' as rid,
			'' as source,
			'' as pid,
			'' as note,
			'' as ip,
			'' as csid,
			'' as contract
		FROM
			users
	",
	'query'=>"
		SELECT
			u.uid,
			concat('<b>',u.user,'</b>') as login,
			fio,
			u.rid,
			u.address,
			p.name as packetname,
			u.deposit,
			u.credit,
			u.expired,
			u.phone,
			u.email,
			u.last_connection,
			IF(u.last_connection>DATE_ADD(now(),INTERVAL -1 MONTH),1,0) as live,
			u.note,
			u.contract,
			p.fixed,
			p.fixed_cost,
			trim(left(u.address,char_length(u.address)-locate(' ',reverse(u.address)))) as street,
			trim(SUBSTRING(u.address,char_length(u.address)-locate(' ',reverse(u.address))+1,locate(' ',reverse(u.address))-locate('/',reverse(u.address)))) as nd,
			cast(trim(right(u.address,locate('/',reverse(u.address))-1)) as unsigned) as nk,
			m.subtype as type
		FROM users u LEFT OUTER JOIN map m ON m.type='client' AND m.name=u.user, packets p
		WHERE u.pid=p.pid AND (:FILTER:)
		ORDER BY street, nd, nk
		",
	'query1bis'=>"
		SELECT
			concat('<b>',username,'</b>') as login,
			nasipaddress,
			nasportid,
			DATE_FORMAT(acctstarttime,'%H:%i %d-%m') as acctstarttime,
			acctsessiontime,
			framedipaddress,
			callingstationid
		FROM radacct
		WHERE acctstoptime is NULL AND username in 
			(SELECT user FROM users WHERE :FILTER:)
		",
	'query1'=>"
		SELECT
			concat('<b>',a.username,'</b>') as login,
			a.groupname,
			a.nasipaddress,
			a.nasportid,
			a.acctstarttime,
			a.acctstoptime,
			a.acctsessiontime,
			a.framedipaddress,
			a.callingstationid
		FROM radacct a, (
			SELECT username, max(acctstarttime) as maxstart 
			FROM radacct, (SELECT user FROM users as u WHERE :FILTER:) as u
			WHERE username = user
			GROUP BY username) as x
		WHERE a.username = x.username AND a.acctstarttime = x.maxstart
		",
	'query2'=>"
		SELECT 
			concat('<b>',username,'</b>') as login, 
			count(username) as connects 
		FROM radacct 
		WHERE 
			acctstarttime>DATE_ADD(now(),INTERVAL -3 MONTH) AND
			:FILTER:
		GROUP BY username
		",
	'query3'=>"
		SELECT 
			concat('<b>',username,'</b>') as login, 
			count(username) as connects 
		FROM radacct 
		WHERE 
			acctstarttime>DATE_ADD(now(),INTERVAL -1 MONTH) AND 
			:FILTER:
		GROUP BY username
		ORDER BY INET_ATON(framedipaddress)
		",
	'query4'=>"
		SELECT 
			concat('<b>',m.name,'</b>') as login
		FROM map m, devices d
		WHERE 
			m.type = 'client' AND
			m.id = d.node1 AND
			d.type = 'onu' AND
			:FILTER:
		GROUP BY login
		",

	'class'=>'',
	'footer'=>array(
		'inbytes'=>'sum',
		'outbytes'=>'sum',
		'billing_minus'=>'sum',
		'callingstationid'=>'fcount'
	),
	'triggers'=>array(
		'inbytes'=>'cell_traf',
		'outbytes'=>'cell_traf',
		'username'=>'cell_login',
		'acctsessiontime'=>'cell_stime',
		'billing_minus'=>'cell_summ'
	),
	'sort'=>'address',
	'group'=>'',
	'field_alias'=>array('address'=>'u'),

	'fields'=>array(
		'uid'=>array(
			'title'=>'Код пользователя',
			'type'=>'hidden',
			'class'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'blocked'=>array(
			'title'=>'Блокирован',
			'type'=>'checkbox',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'disabled'=>array(
			'title'=>'Соглашение получено',
			'type'=>'checkbox',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'login'=>array(
			'title'=>'Логин',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'user'=>array(
			'label'=>'логин',
			'title'=>'Логин',
			'type'=>'text',
			'style'=>'width:200px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'fio'=>array(
			'label'=>'Ф.И.О.',
			'title'=>'Ф.И.О.',
			'type'=>'text',
			'style'=>'width:200px',
			'prepen'=>'<img src="pic/login.png">',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'psp'=>array(
			'label'=>'Паспорт',
			'title'=>'Паспортные данные',
			'type'=>'text',
		),
		'password'=>array(
			'title'=>'Пароль',
			'type'=>'password',
			'class'=>'password',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'deposit'=>array(
			'title'=>'Счет',
			'type'=>'text',
			'class'=>'money',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'credit'=>array(
			'title'=>'Кредит',
			'type'=>'text',
			'class'=>'money',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'expired'=>array(
			'title'=>'Пакет заканчивается',
			'type'=>'text',
			'class'=>'date',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'phone'=>array(
			'label'=>'Телефон',
			'title'=>'Телефон',
			'type'=>'text',
			'style'=>'width:200px',
			'prepend'=>'<img src="pic/phone.png">',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'address'=>array(
			'label'=>'Адрес',
			'title'=>'Адрес',
			'type'=>'autocomplete',
			'class'=>'address',
			'style'=>'width:200px',
			'prepend'=>'<img src="pic/bighome.png">',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'csid'=>array(
			'label'=>'MAC',
			'title'=>'MAC устройства клиента',
			'type'=>'text',
			'class'=>'csid',
			'style'=>'width:200px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'rid'=>array(
			'label'=>'Район',
			'title'=>'Код района',
			'type'=>'select',
			'style'=>'width:210px',
			'list'=>all2array(list_of_rayons()),
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'add_date'=>array(
			'title'=>'Дата установки',
			'type'=>'text',
			'class'=>'date readonly',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'last_connection'=>array(
			'title'=>'Последнее подключение',
			'type'=>'datetime,readonly',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'prev_pid'=>array(
			'title'=>'Код предыдущего пакета',
			'type'=>'select',
			'list'=>'list_of_packets',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'next_pid'=>array(
			'title'=>'Код следующего пакета',
			'type'=>'select',
			'list'=>'list_of_packets',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'note'=>array(
			'label'=>'Примечание',
			'title'=>'Примечание',
			'type'=>'text',
			'style'=>'width:200px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'contract'=>array(
			'label'=>'Лиц.сч.',
			'title'=>'Лицевой счет',
			'type'=>'text',
			'style'=>'width:200px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'radacctid'=>array(
			'label'=>'id',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'acctsessionid'=>array(
			'label'=>'session id',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'acctuniqueid'=>array(
			'label'=>'unique id',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'username'=>array(
			'label'=>'Логин',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'groupname'=>array(
			'label'=>'Профиль',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'pid'=>array(
			'label'=>'Тариф',
			'type'=>'select',
			'extra'=>'packet',
			'style'=>'width:210px',
			'list'=>all2array(list_of_packets()),
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'credit'=>array(
			'label'=>'Кредит при подключении',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'before_billing'=>array(
			'label'=>'Счет при подключении',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'billing_minus'=>array(
			'label'=>'Начислено',
			'type'=>'text',
			'class'=>'money',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'realm'=>array(
			'label'=>'область',
			'type'=>'text',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>2)
		),
		'nasipaddress'=>array(
			'label'=>'IP сервера доступа',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'nasportid'=>array(
			'label'=>'Порт сервера доступа',
			'type'=>'text',
			'class'=>'number',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'nasporttype'=>array(
			'label'=>'Тип порта',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'acctstarttime'=>array(
			'label'=>'Время подключения',
			'type'=>'text',
			'class'=>'start',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'acctstoptime'=>array(
			'label'=>'Время отключения',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'acctsessiontime'=>array(
			'label'=>'Время сессии',
			'type'=>'text',
			'class'=>'number',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'acctauthentic'=>array(
			'label'=>'Тип аутентификации',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'connectinfo_start'=>array(
			'label'=>'Инф. о подключении',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'connectinfo_stop'=>array(
			'label'=>'Инф. об отключении',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'inputgigawords'=>array(
			'label'=>'Получено Гбайт',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'acctinputoctets'=>array(
			'label'=>'Получено байт',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'outputgigawords'=>array(
			'label'=>'Послано Гбайт',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'acctoutputoctets'=>array(
			'label'=>'Послано байт',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'calledstationid'=>array(
			'label'=>'ID вызываемого',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'callingstationid'=>array(
			'label'=>'ID вызывающего',
			'type'=>'text',
			'class'=>'csid',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'acctterminatecause'=>array(
			'label'=>'Причина рассоединения',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'servicetype'=>array(
			'label'=>'Тип сервиса',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'framedprotocol'=>array(
			'label'=>'Протокол',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'framedipaddress'=>array(
			'label'=>'IP клиента',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'acctstartdelay'=>array(
			'label'=>'Задержка начала сессии',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'acctstopdelay'=>array(
			'label'=>'Задержка конца сессии',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'xascendsessionsvrkey'=>array(
			'label'=>'Что-то с чем-то',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'dropped'=>array(
			'label'=>'Требование завершения сессии',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'inbytes'=>array(
			'label'=>'In Mb',
			'type'=>'text',
			'class'=>'traf',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'outbytes'=>array(
			'label'=>'Out Mb',
			'type'=>'text',
			'class'=>'traf',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
	),
	'before_new'=>'before_new_search',
	'before_edit'=>'before_edit_search',
	'form_save'=>'save_search',
	'form_record'=>'search_record',
	'form_onsave'=>'delivere_search',
	'before_delete'=>'before_delete_search',
	'form_triggers'=>array(
		'ip'=>'get_search_ip',
	),
	'form_autocomplete'=>array(
		'address'=>'auto_address'
	)
);

function before_new_search($f) {
	global $config, $q;
	$fld = array('uid','address','user','fio','phone','rid','source','pid','note','ip','csid','contract');
	$fk = array('label'=>0,'type'=>1,'native'=>2,'list'=>3,'access'=>4,'class'=>5,'style'=>6,'form_style'=>7);
	$nf = array();
	foreach($fld as $n) if(isset($f['fields'][$n])) $nf[$n] = array_intersect_key($f['fields'][$n],$fk);
	$f['fields'] = $nf;
	$f['class'] = 'normal';
	$f['header'] = "Поиск клиентов";
 	$f['footer'] = array('cancelbutton'=>array('txt'=>'Отменить'),'submitbutton'=>array('txt'=>'Найти'));
	return $f;
}

function before_edit_search($f) {
	global $config, $q;
	$fld = array('uid'=>0,'address'=>'','user'=>'','fio'=>'','phone'=>'','rid'=>'','source'=>'','pid'=>'','note'=>'','ip'=>'','csid'=>'','contract'=>'');
	$fk = array('label'=>0,'type'=>1,'native'=>2,'list'=>3,'access'=>4,'class'=>5,'style'=>6,'form_style'=>7);
	$nf = array();
	foreach($fld as $n=>$v) if(isset($f['fields'][$n])) $nf[$n] = array_intersect_key($f['fields'][$n],$fk);
	$f['record'] = $fld;
	$f['fields'] = $nf;
	$f['class'] = 'normal';
	$f['header'] = "Поиск клиентов";
 	$f['footer'] = array('cancelbutton'=>array('txt'=>'Отменить'),'submitbutton'=>array('txt'=>'Найти'));
	return $f;
}

function save_search($r) {
	global $DEBUG;
	if($DEBUG>0) log_txt(__FUNCTION__.": r=".arrstr($r));
	return 1;
}

function search_record($new,$old,$my) {
	global $DEBUG;
	if($DEBUG>0) log_txt(__FUNCTION__.": data: ".arrstr($new));
	return array('uid'=>0,'address'=>'','user'=>'','fio'=>'','phone'=>'','rid'=>'','source'=>'','pid'=>'','note'=>'','ip'=>'','csid'=>'','contract'=>'');
}

function delivere_search($id,$s,$my) {
	global $config, $tables, $q, $DEBUG;
	if($DEBUG>0) log_txt(__FUNCTION__.": data: ".arrstr($s));
	$fld = array('rid'=>'u','pid'=>'u');
	$fld1 = array('rid'=>'u','pid'=>'u','address'=>'u');
	unset($s['uid']);
	$opt = array(); $html = ''; $query = array();
	foreach($s as $n=>$v){
		if($v) {
			if($n=='csid'){
				$v = preg_replace(array('/^\s+|\s+$/','/[^0-9a-f\-\:\.]/i','/[\-]/'),array('','',':'),$v);
				$csid = "`callingstationid` like '%$v%'";
			}else{
				if($n=='phone') $v = normalize_phone($v);
				$n1 = isset($fld1[$n])? "`{$fld1[$n]}`.`{$n}`" : "`$n`";
				$opt1[] = isset($fld[$n])? "$n1 = '$v'":"$n1 like '%$v%'";
				$opt[] = isset($fld[$n])? "`$n` = '$v'":"`$n` like '%$v%'";
			}
			$query[] = isset($fld[$n])? "  ":$v;
		}
	}
	if($DEBUG>0) log_txt(__FUNCTION__.": query: ".arrstr($query));
	$filter = implode(' AND ',$opt);
	$filter1 = implode(' AND ',$opt1);
	if(isset($csid)){
		$srch1=getresult($csid,$tables['search']['query3']);
		if(count($srch1)>0) {
			$logins=array(); foreach(array_keys($srch1) as $l) $logins[]=preg_replace('/<[^>]*>/','',$l);
			if($DEBUG>0) log_txt(__FUNCTION__.": logins = ".sprint_r($logins));
			$filter2="user in ('".implode("','",$logins)."')";
			$srch2=getresult($filter2,$tables['search']['query']);
			$srch3=getresult($filter2,$tables['search']['query1']);
			$srch=array_merge_recursive($srch1,$srch2,$srch3);
		}
	}else{
		$srch1=getresult($filter1,$tables['search']['query']);
		$srch2=getresult($filter,$tables['search']['query1']);
		$srch=array_merge_recursive($srch1,$srch2);
	}
	$total = count($srch);

	$count_srch=count($srch);
	if($count_srch==0) {
		$html.="<h3>Поиск не дал результатов!</h3>";
	}else{
		$html.="</table>";
		$ld=$count_srch-floor($count_srch/10.0)*10.0;
		$ld1=$count_srch-floor($count_srch/100.0)*100.0;
		if($ld==1) $e='е'; elseif($ld>4||$ld==0) $e='й'; else $e='я';
		if($ld1>10 && $ld1<14) $e='й';
		$html.="<h3>Найдено $count_srch соответстви$e.</h3>";
		$html.="<table target=\"{$tables['search']['target']}\" module=\"users\">";
		foreach($srch as $key=>$r) $html.=get_client($r,$key);
	}
	$html = "<div class=\"searchdata\">".$html."</div>";
	$out['result'] = "OK";
	$out['query'] = implode('|',$query);
	$out['modify'] = array();
	$out['tab']['link'] = 'users';
	$out['tab']['name'] = 'user';
	$out['tab']['title'] = 'Поиск';
	$out['tab']['content'] = $html;
	log_txt(__FUNCTION__.": всего:$total    filter: $filter1");
	return $out;
}

function before_delete_search($r) {
	stop("Эта форма только для поиска");
}

function getresult($f,$query) {
	global $q, $DEBUG;
	$sqlstr=preg_replace('/:FILTER:/u',$f,$query);
	if($DEBUG>0) log_query($sqlstr);
	$s = $q->select($sqlstr,SELECT_ARRWITHOUTKEY,'login');
	if($DEBUG>0) log_txt(__FUNCTION__.": count(srch1)='".$q->result->num_rows."'");
	return ($s)? $s : array();
}

function searchUsers($query) {
	global $tabname, $t, $q, $explain_packet, $DEBUG;
	if($DEBUG>0) log_txt(__FUNCTION__.": data: ".arrstr($query));
	if($query&&$query!='') {
		$html="";
		$ip=false;$csideq=false;$csid=false;
		// попробуем угадать тип запроса
		if(preg_match('/\//',$query)||preg_match('/[А-Яа-я]\.[А-Яа-я]/u',$query)||(!preg_match('/[A-Za-z_]/u',$query)&&preg_match('/ [0-9][0-9]*[а-я]?$/u',$query))) {
			if($DEBUG>0) log_txt(__FUNCTION__.": запрос содержит слэш или точку - скорее всего адрес");
			if(preg_match('/\s+/u',$query)) {
				$chanks=preg_split('/\s+/u',$query);
				$endchank=count($chanks)-1;
				$conditions=array();
				foreach($chanks as $k=>$v) {
					if($k==$endchank) $conditions[]="(u.address like '% $v' OR u.address like '% $v/%')";
					else $conditions[]="u.address like '%$v%'";
				}
				$filter=implode(' AND ',$conditions);
			}else{
				$filter="u.address like '%$query%'";
			}
		}elseif(preg_match('/[\:\-][0-9A-F][0-9A-F][\:\-]/iu',$query) && !preg_match('/\d\d\d/u',$query)) {
			if(preg_match('/\-/u',$query)) $query=preg_replace('/\-/',':',$query);
			if(mb_strlen($query)==17) {
				$err[] = "запрос содержит полный мак адрес";
				$csideq="callingstationid = '$query'";
				$onu="macaddress = '$query'";
			}else{
				$err[] = "запрос содержит не полный мак адрес";
				$csid="callingstationid like '%$query%'";
				$onu="macaddress like '%$query%'";
			}
		}elseif(preg_match('/[^0-9:\/\- \.]/u',$query)) {
			$err[] = "запрос содержит буквы";
			if(preg_match('/[A-Za-z]/u',$query)) {
				$err[] = "запрос содержит английские буквы";
				if(preg_match('/@/',$query)) {
					$err[] = "запрос содержит email";
					$filter="email like '%$query%'";
				}elseif(!preg_match('/[А-Яа-я]/u',$query)) {
					$err[] = "запрос не содержит русских букв - логин или email";
					$filter="user like '%$query%' OR email like '%$query%' OR u.note like '%$query%'";
				}else{
					$err[] = "есть и русские и английские буквы";
					$filter="u.address like '%$query%' OR fio like '%$query%' OR u.note like '%$query%'";
				}
			}else{
				$err[] = "запрос содержит русские буквы - адрес или фио";
				$filter="u.address like '%$query%' OR fio like '%$query%' OR u.note like '%$query%'";
			}
		}elseif(preg_match('/\-/',$query)&&preg_match('/[0-9]/',$query)) {
			$err[] = "запрос содержит тире и цифры - скорее всего телефон";
			$filter="phone like '%$query%'";
		}elseif(preg_match('/\d{1,3}\.\d{1,3}\.(\d{1,3})?(\.\d{1,3})?/',$query,$m)) {
			$err[] = "запрос содержит ip";
			$ip = $m[0];
			$filter="framedipaddress like '{$ip}%' AND acctstoptime is NULL";
		}else{
			$err[] = "запрос содержит только цифры - номер контракта или телефон или uid";
			if(preg_match('/^'.CITYCODE.'/',$query)){
				$err[] = "запрос содержит contract";
				$filter="contract like '$query%'";
			}elseif(mb_strlen($query)>5){
				$err[] = "запрос содержит телефон";
				$filter="phone like '%".normalize_phone($query)."%'";
			}else{
				$err[] = "запрос содержит логин или uid";
				$filter="uid = '$query' OR user = '$query'";
			}
		}
		if($DEBUG>2) log_txt(__FUNCTION__.": filter = ".mb_substr($filter,0,70));

		$srch=array();
		if(!$csid && !$csideq && !$ip) {
			$srch1=getresult($filter,$t['query']);
			$srch2=getresult($filter,$t['query1']);
			$srch=array_merge_recursive($srch1,$srch2);
		}elseif($csideq) {
			$srch1=getresult($csideq,$t['query2']);
			if(count($srch1)>0) {
				$logins=array(); foreach(array_keys($srch1) as $l) $logins[]=preg_replace('/<[^>]*>/','',$l);
				if($DEBUG>0) log_txt(__FUNCTION__.": logins = ".sprint_r($logins));
				$filter="user in ('".implode("','",$logins)."')";
				$srch2=getresult($filter,$t['query']);
				$srch3=getresult($filter,$t['query1']);
				$srch=array_merge_recursive($srch1,$srch2,$srch3);
			}else{
				$srch1=getresult($onu,$t['query4']);
				if(count($srch1)>0) {
					$logins=array(); foreach(array_keys($srch1) as $l) $logins[]=preg_replace('/<[^>]*>/','',$l);
					if($DEBUG>0) log_txt(__FUNCTION__.": logins = ".sprint_r($logins));
					$filter="user in ('".implode("','",$logins)."')";
					$srch2=getresult($filter,$t['query']);
					$srch3=getresult($filter,$t['query1']);
					$srch=array_merge_recursive($srch1,$srch2,$srch3);
				}
			}
		}elseif($ip) {
			if($DEBUG>0) log_txt(__FUNCTION__." ip = ".sprint_r($ip));
			$srch1=getresult($filter,$t['query3']);
			if(count($srch1)>0) {
				$logins=array(); foreach(array_keys($srch1) as $l) $logins[]=preg_replace('/<[^>]*>/','',$l);
				if($DEBUG>0) log_txt(__FUNCTION__.": logins = ".sprint_r($logins));
				$filter="user in ('".implode("','",$logins)."')";
				$srch2=getresult($filter,$t['query']);
				$srch3=getresult($filter,$t['query1']);
				$srch=array_merge_recursive($srch1,$srch2,$srch3);
			}
		}else{
			$srch1=getresult($csid,$t['query3']);
			if(count($srch1)>0) {
				$logins=array(); foreach(array_keys($srch1) as $l) $logins[]=preg_replace('/<[^>]*>/','',$l);
				if($DEBUG>2) log_txt(__FUNCTION__.": logins = ".sprint_r($logins));
				$filter="user in ('".implode("','",$logins)."')";
				$srch2=getresult($filter,$t['query']);
				$srch3=getresult($filter,$t['query1']);
				$srch=array_merge_recursive($srch1,$srch2,$srch3);
			}else{
				$srch1=getresult($onu,$t['query4']);
				if(count($srch1)>0) {
					$logins=array(); foreach(array_keys($srch1) as $l) $logins[]=preg_replace('/<[^>]*>/','',$l);
					if($DEBUG>1) log_txt(__FUNCTION__.": logins = ".sprint_r($logins));
					$filter="user in ('".implode("','",$logins)."')";
					$srch2=getresult($filter,$t['query']);
					$srch3=getresult($filter,$t['query1']);
					$srch=array_merge_recursive($srch1,$srch2,$srch3);
				}
			}
		}
		if($DEBUG>2) log_txt(__FUNCTION__.": count(srch)='".count($srch)."'");
		if($DEBUG>7) log_txt(__FUNCTION__.": srch = ".@sprint_r(reset($srch)));


		$html.="<table target=\"$t[target]\" module=\"users\">";
		$f=$t['fields'];
		foreach($srch as $key=>$r) {
			$html.=get_client($r,$key);
		}
		$html.="</table>";
		$count_srch=count($srch);
		if($count_srch==0) {
			$html.="<h3>Поиск не дал результатов!</h3>";
			log_txt("не найдено: ".implode(', ',$err));
		}else{
			$ld=$count_srch-floor($count_srch/10.0)*10.0;
			$ld1=$count_srch-floor($count_srch/100.0)*100.0;
			if($ld==1) $e='е'; elseif($ld>4||$ld==0) $e='й'; else $e='я';
			if($ld1>10 && $ld1<14) $e='й';
			$html="<h3>Найдено ".count($srch)." соответстви$e.</h3>".$html;
		}
		return "<div class=\"searchdata\">".$html."</div>";
	}else{
		return false;
	}
}
?>
