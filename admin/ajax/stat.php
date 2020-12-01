<?php
include_once("map.cfg.php");
include_once("rayon.cfg.php");
include_once("classes.php");
include_once("form.php");
include_once("users.cfg.php");

$in['go'] = (array_key_exists('go',$_REQUEST))? strict($_REQUEST['go']) : 'rayons';
$in['do'] = (array_key_exists('do',$_REQUEST))? strict($_REQUEST['do']) : '';
$in['id'] = (array_key_exists('id',$_REQUEST))? numeric($_REQUEST['id']) : 0;

$create_usr_filters = array(
	'end'=>array(
		'type'=>'date',
		'label'=>'до',
		'style'=>"width:80px;text-align:center",
		'title'=>'данные с',
		'value'=>cyrdate(date('d-m-Y')),
	),
	'begin'=>array(
		'type'=>'date',
		'label'=>'от',
		'style'=>"width:80px;text-align:center",
		'title'=>'данные до',
		'value'=>cyrdate(date('01-m-Y')),
	),
);
$flt = array(
	'subtype'=>array(
		'type'=>'select',
		'label'=>'тип подкл.',
		'title'=>'выбор по типу подключения',
		'list'=>all2array($config['map']['clienttypes']),
		'value'=>'_'.(isset($_REQUEST['subtype'])? strict($_REQUEST['subtype']): ""),
	),
);

$q = new sql_query($config['db']);
$tables['users']['name']='usrmac';
$tables['users']['delete'] = 'no';
$tables['users']['module'] = 'stat';
$tables['users']['table_name'] = 'users';
$tables['users']['target'] = 'html';

switch($in['do']){

	case 'debtors':
		$tables['users']['table_query'] = "
			SELECT uid, contract, address, pid, rid, phone, last_connection, deposit, credit, note
			FROM `users`
			WHERE deposit+credit<-1 :FILTER:
			ORDER BY :SORT:
		";
		$tables['users']['table_name'] = 'dolg';
		$tables['users']['defaults']['sort'] = 'address';
		$tables['users']['table_footer']['note'] = 'fcount';
		$tables['users']['add'] = 'no';
		$t = $tables['users'];
		$tab = new Table($t);
		$out = array('result'=>'OK','table'=>$tab->get($t));
		stop($out);
		break;

	case 'credits':
		$tables['users']['table_query'] = "
			SELECT u.uid, user, u.address, u.pid, u.rid, u.phone, u.last_connection, u.deposit, u.credit
			FROM `users` u, `packets` p
			WHERE u.pid=p.pid AND u.credit > '5.00' AND (p.tos>0 or p.fixed>0) :FILTER:
			ORDER BY :SORT:
		";
		unset($tables['users']['filters']['begin']);
		$tables['users']['field_alias'] = array('pid'=>'u','rid'=>'u');
		$tables['users']['table_name'] = 'credit';
		$tables['users']['table_footer']['user'] = 'Итого:';
		$tables['users']['table_footer']['deposit'] = 'fsum';
		$tables['users']['table_footer']['credit'] = 'fsum';
		$tables['users']['defaults']['sort'] = 'address';
		$tables['users']['add'] = 'no';
		$t = $tables['users'];
		$tab = new Table($t);
		$out = array('result'=>'OK','table'=>$tab->get($t));
		stop($out);
		break;

	case 'budget':
		stop($out);
		break;

	case 'usrmac':
		$tables['users']['table_query'] = "
			SELECT uid, fio, phone, address, rid, last_connection, csid
			FROM `users`
			WHERE csid != '' :FILTER:
			ORDER BY :SORT:
		";
		$tables['users']['add'] = 'no';
		$tables['users']['table_name'] = 'usrmac';
		$tables['users']['table_footer']['fio'] = 'Всего:';
		$tables['users']['table_footer']['csid'] = 'fcount';
		$t = $tables['users'];
		$tab = new Table($t);
		$out = array('result'=>'OK','table'=>$tab->get($t));
		stop($out);
		break;

	case 'create':
		$tables['users']['table_query'] = "
			SELECT uid, add_date, user, subtype, rid, u.address, pid, phone
			FROM `users` u LEFT OUTER JOIN map m ON m.type='client' AND m.name=u.user
			WHERE user != '' :FILTER::PERIOD:
			ORDER BY :SORT:
		";
		$create_usr_filters['begin']['value'] = cyrdate(date('Y-m-d'));
		$create_usr_filters['end']['value'] = cyrdate(date('Y-m-d 23:59:59'));
		foreach(array('address','last_connection') as $n) unset($tables['users']['filters'][$n]);
		$tables['users']['filters'] = array_merge($tables['users']['filters'], $create_usr_filters,$flt);
		$tables['users']['add'] = 'no';
		$tables['users']['table_name'] = 'create';
		$tables['users']['defaults']['period'] = 'build_period_for_users';
		$tables['users']['table_footer']['add_date'] = 'Всего:';
		$tables['users']['table_footer']['phone'] = 'fcount';
		$tables['users']['table_footer']['user'] = '';
		$tables['users']['defaults']['sort'] = 'add_date';
		$t = $tables['users'];
		$tab = new Table($t);
		$out = array('result'=>'OK','table'=>$tab->get($t));
		stop($out);
		break;

	case 'paid':
		$tables['users']['table_query'] = "
		SELECT 
			u.pid as id,
			u.pid, 
			count(distinct u.user) as countusr, 
			count(distinct p.user) as countpaid, 
			sum(p.summ) as paidsum
		FROM
			`users` as u 
			LEFT OUTER JOIN `pay` as p ON u.user=p.user :PERIOD:
			LEFT OUTER JOIN `packets` as pk ON pk.pid = u.pid
		WHERE 1 :FILTER:
		GROUP BY u.pid
		ORDER BY pk.num
		";
		foreach(array('pid','address','last_connection') as $n) unset($tables['users']['filters'][$n]);
		$tables['users']['filters'] = array_merge($tables['users']['filters'], $create_usr_filters);
		$tables['users']['field_alias'] = array('pid'=>'u','rid'=>'u');
		$tables['users']['table_name'] = 'paid';
		$tables['users']['add'] = 'no';
		$tables['users']['limit'] = 'no';
		$tables['users']['defaults']['filter'] = 'build_filter_for_users';
		$tables['users']['defaults']['period'] = 'build_period_for_paid';
		$tables['users']['defaults']['sort'] = 'add_date';
		$tables['users']['table_footer']['pid'] = 'Всего:';
		$tables['users']['table_footer']['countusr'] = 'fsum';
		$tables['users']['table_footer']['countpaid'] = 'fsum';
		$tables['users']['table_footer']['paidsum'] = 'fsum';
		$t = $tables['users'];
		$tab = new Table($t);
		$out = array('result'=>'OK','table'=>$tab->get($t));
		stop($out);
		break;

	case 'lostusers':
		$tables['users']['table_query'] = "
			SELECT u.uid as id, u.user, m.subtype, u.address, u.pid, u.phone, u.last_connection, u.deposit
			FROM `users` as u left outer join map as m ON m.type='client' AND m.name=u.user, `packets` as p
			WHERE u.pid=p.pid AND (p.tos>0 or (p.fixed>0 and p.fixed<10) or (p.fixed=10 and (u.expired<now() or u.blocked>0))) AND
				last_connection>'0000-00-00' AND
				last_connection<DATE_ADD(now(),INTERVAL -2 MONTH)
				:FILTER:
			ORDER BY :SORT:
		";
		foreach(array('last_connection') as $n) unset($tables['users']['filters'][$n]);
		$tables['users']['filters'] = array_merge($tables['users']['filters'], $flt);
		$tables['users']['field_alias'] = array('pid'=>'u','rid'=>'u');
		$tables['users']['add'] = 'no';
		$tables['users']['table_name'] = 'lost';
		$tables['users']['defaults']['sort'] = 'last_connection desc';
		$tables['users']['table_footer']['deposit'] = 'fcount';
		$t = $tables['users'];
		$tab = new Table($t);
		$out = array('result'=>'OK','table'=>$tab->get($t));
		stop($out);
		break;

	case 'tearing':
		include_once("radacct.cfg.php");
		$tables['radacct']['table_query'] = "
		SELECT max(radacctid) as id,
			username, address, r_name,
			sum(if(acctterminatecause='User-Request', 1, 0)) as ur,
			sum(if(acctterminatecause='Lost-Carrier', 1, 0)) as lc,
			sum(if(acctterminatecause='NAS-Request', 1, 0)) as nr,
			sum(if(acctterminatecause='Session-Timeout', 1, 0)) as st,
			sum(if(acctterminatecause='Alive-Timeout', 1, 0)) as at,
			sum(if(acctterminatecause='Port-Error', 1, 0)) as pe,
			sum(if(acctterminatecause='NAS-Error', 1, 0)) as ne,
			count(*) c
		FROM radacct a
			JOIN users u ON a.username=u.user
			JOIN packets p ON p.pid=u.pid
			JOIN rayon r on u.rid=r.rid
		WHERE acctsessiontime < 900 :FILTER:
		GROUP BY username
		HAVING c>2
		ORDER BY r_name, p.name, address;
		";
		$tables['radacct']['limit']='no';
		unset($tables['radacct']['filter']['pid']);
		$tables['radacct']['filters']['acctstarttime'] = array(
			'type'=>'select',
			'typeofvalue'=>'time',
			'label'=>'за последние',
			'title'=>'фильтр по времени',
			'style'=>"width:110px",
			'list'=>array(
				'-1 hour'=>'1 час',
				'-2 hour'=>'2 часа',
				'-3 hour'=>'3 часа',
				'-4 hour'=>'4 часа',
				'-6 hour'=>'6 часов',
				'-12 hour'=>'12 часов',
				'-24 hour'=>'1 сутки',
				'-48 hour'=>'2 суток'
			),
			'value'=>isset($_REQUEST['acctstarttime'])? str($_REQUEST['acctstarttime']) : "-2 hour",
		);
		if(!isset($_REQUEST['acctstarttime'])) $_REQUEST['acctstarttime'] = "-2 hour";
		$tab = new Table($tables['radacct']);
// 		log_txt("stat::tearing SQL: ".sqltrim($tab->q->sql));
		$out = array('result'=>'OK','table'=>$tab->get($t));
		stop($out);
		break;

	case 'homes':
		include_once("homes.cfg.php");
		if(isset($_REQUEST['groupby'])=='entrances'){
		$tables['homes']['table_query'] = "
		SELECT
			h.id,
			concat(h.address,' подъезд ',e.entrance,' (',apartinit,'-',apartfinal,')') as address,
			h.floors,
			entrances,
			if(apartfinal>apartinit,apartfinal - apartinit + 1,h.entrances) as apartments,
			sum(if(last_connection>date_add(now(),interval -1 month),1,0)) as active,
			sum(if(last_connection>date_add(now(),interval -3 month) && last_connection<date_add(now(),interval -1 month),1,0)) as passive,
			sum(if(last_connection<date_add(now(),interval -3 month),1,0)) as lost,
			count(*) as cnt
		FROM homes h JOIN map m ON h.object=m.id JOIN entrances e ON h.id=e.home LEFT OUTER JOIN (
			SELECT
				rid,
				left(address,locate('/',address)-1) as home,
				right(address,locate('/',reverse(address))-1) as kv,
				last_connection
			FROM users
			WHERE address like '%/%'
		) u ON h.address = u.home AND m.rayon=u.rid AND u.kv>=e.apartinit AND u.kv<=e.apartfinal
		WHERE e.apartinit<e.apartfinal :FILTER:
		GROUP BY h.id, e.entrance
		ORDER BY :SORT:
		";
		}else $tables['homes']['table_query'] = "
		SELECT 
			h.id,
			h.address,
			h.floors,
			h.entrances,
			h.apartments,
			u.active,
			u.passive,
			u.lost,
			cnt
		FROM homes h JOIN map m ON h.object=m.id LEFT OUTER JOIN (
			SELECT 
				rid,
				left(address,locate('/',address)-1) as home,
				sum(if(last_connection>date_add(now(),interval -1 month),1,0)) as active,
				sum(if(last_connection>date_add(now(),interval -3 month) && last_connection<date_add(now(),interval -1 month),1,0)) as passive,
				sum(if(last_connection<date_add(now(),interval -3 month),1,0)) as lost,
				count(*) as cnt
			FROM users
			WHERE address like '%/%'
			GROUP BY home
			) u ON h.address = u.home AND m.rayon = u.rid
		WHERE 1 :FILTER:
		ORDER BY :SORT:
		";
		$tables['homes']['filters'] = array_merge($tables['homes']['filters'],array(
			'groupby'=>array(
				'type'=>'select',
				'typeofvalue'=>'active',
				'label'=>'Группировка',
				'style'=>"width:110px",
				'list'=>array(
					'_'=>'по домам',
					'entrances'=>'по подъездам',
				),
				'title'=>'выбор по активности',
				'keep'=>true,
				'value'=>''
			),
		));
		$tables['homes']['add'] = 'no';
		$tables['homes']['delete'] = 'no';
		$tables['homes']['field_alias'] = array('address'=>'h');
		$tables['homes']['table_footer']['address'] = 'Всего:';
		$tables['homes']['table_footer']['cnt'] = 'fcount';
		$tables['homes']['target'] = 'html';
		$t = $tables['homes'];
		$tab = new Table($t);
		$out = array('result'=>'OK','table'=>$tab->get($t));
		stop($out);
		break;

	case '':
		stop($out);
		break;

	default:
		stop(array(
		'result'=>'ERROR',
		'desc'=>"</pre><center>неверные данные<br>
			go={$in['go']}<br>
			do={$in['do']}<br>
			id={$in['id']}<br>
			</center><pre>"
		));

}
?>
