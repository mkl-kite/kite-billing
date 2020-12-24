<?php
include_once("classes.php");
include_once("table.php");
$exist_iptv = key_exists('iptv',$q->table_fields('users'));
$iptv = $exist_iptv? "sum(IF(u.user is not null AND u.iptv>DATE_ADD(now(),interval -1 month),1,0)) as live_iptv," : "";

$tables['packets']=array(
	'name'=>'packets',
	'title'=>'тариф',
	'target'=>"html",
	'module'=>"stdform",
	'key'=>'pid',
	'action'=>'references.php',
	'class'=>'normal',
	'delete'=>'yes',
	'limit'=>'yes',
	'form_query'=>"
		SELECT 
			pid,
			num,
			name,
			groupname,
			tos,
			direction,
			fixed,
			period,
			fixed_cost,
			su,
			hg,
			switched,
			switchedout
		FROM packets
	",
	'table_query'=>"
		SELECT 
			p.pid,
			p.num,
			p.name,
			p.groupname,
			p.fixed_cost,$iptv
			sum(IF(u.user is not null AND u.last_connection>DATE_ADD(now(),interval -1 month),1,0)) as live,
			sum(IF(u.user is not null AND u.last_connection between DATE_ADD(now(),interval -3 month) AND DATE_ADD(now(),interval -1 month),1,0)) as stale,
			sum(IF(u.user is not null AND u.last_connection<DATE_ADD(now(),interval -3 month),1,0)) as lost,
			sum(IF(u.user is not null,1,0)) as allusr
			
		FROM 
			`packets` as p
			LEFT OUTER JOIN `users` as u ON p.pid=u.pid
		WHERE 1 :FILTER:
		GROUP BY p.num, p.pid
		ORDER BY :SORT:
	",
	'field_alias'=>array('pid'=>'u','rid'=>'u','name'=>'p','num'=>'p','fixed_cost'=>'p'),
	'filters'=>array(
		'name'=>array(
			'type'=>'text',
			'label'=>'название',
			'style'=>'width:80px',
			'title'=>'назначен на >',
			'value'=>''
		),
	),
	'table_menu'=>array(
		'edit'=>array('label'=>"<img src=\"pic/gtk-edit16.png\"> изменить",'to'=>'form'),
		'profile'=>array('label'=>"<img src=\"pic/conf.png\"> профиль", 'to'=>'self','target'=>"references.php",'query'=>"table=radusergroup&go=profiles"),
		'price'=>array('label'=>"<img src=\"pic/doc.png\"> прайс", 'to'=>'self','target'=>"references.php",'query'=>"table=prices&go=prices"),
		'delete'=>array('label'=>"<img src=\"pic/del.png\"> удалить",'to'=>'form','query'=>"go=stdform&do=delete&table=packets"),
	),
	'defaults'=>array(
		'sort'=>'num',
		'filter'=>'build_filter_for_packets',
	),
	'form_triggers'=>array(
		'employers'=>'get_wemployers',
		'fixed_cost'=>'packet_fixed_cost',
	),
	'table_triggers'=>array(
 		'fixed_cost'=>'packet_fixed_cost',
	),
	'before_new'=>'before_new_packet',
	'before_edit'=>'before_edit_packet',
	'before_save'=>'before_save_packet',
	'form_onsave'=>'onsave_packet',
	'allow_delete'=>'allow_delete_packet',
	'before_delete'=>'before_delete_packet',
 	'table_footer'=>array(
		'num'=>'Всего:',
		'live'=>'fsum',
		'stale'=>'fsum',
		'lost'=>'fsum',
		'allusr'=>'fsum',
 	),

	'fields'=>array(
		'pid'=>array(
			'label'=>'id',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'num'=>array(
			'label'=>'номер п/п',
			'type'=>'text',
			'class'=>'ctxt',
			'style'=>'width:50px;text-align:right',
			'table_style'=>'width:40px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>5)
		),
		'name'=>array(
			'label'=>'Название',
			'type'=>'text',
			'class'=>'nowr',
			'style'=>'width:240px',
			'table_style'=>'width:210px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>5)
		),
		'groupname'=>array(
			'label'=>'Профиль',
			'type'=>'select',
			'class'=>'nowr',
			'list'=>'get_profiles',
			'style'=>'width:160px',
			'table_style'=>'width:150px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>5)
		),
		'tos'=>array(
			'label'=>'За что снимать дегьги',
			'type'=>'select',
			'list'=>$tosname,
			'style'=>'width:160px',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>5)
		),
		'direction'=>array(
			'label'=>'Какой трафик учитывать',
			'type'=>'select',
			'list'=>$dirtraf,
			'style'=>'width:160px',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>5)
		),
		'fixed'=>array(
			'label'=>'Снимать фиксированную сумму',
			'type'=>'select',
			'list'=>$abonpl,
			'style'=>'width:320px',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>5),
			'onchange'=>"
				var o = this, fixed=$(o).val().replace(/[^0-9]/g,''), f = $(o).parents('form').first(),
					p = f.find('#field-period'), c = f.find('#field-fixed_cost');
				if(fixed == 10) p.show(); else p.hide();
				if(fixed != 0) c.show(); else c.hide();
			",
		),
		'period'=>array(
			'label'=>'Период действия тарифа',
			'type'=>'text',
			'style'=>'width:50px;text-align:right',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>5)
		),
		'fixed_cost'=>array(
			'label'=>'абонплата',
			'type'=>'text',
			'class'=>'summ',
			'style'=>'width:50px;text-align:right',
			'table_style'=>'width:50px',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>5)
		),
		'switched'=>array(
			'label'=>'Разрешать пользователям переход на этот пакет',
			'type'=>'checkbox',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>5)
		),
		'hg'=>array(
			'label'=>'Имя списка разрешенных NAS',
			'type'=>'text',
			'style'=>'width:170px',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>5)
		),
		'su'=>array( // simultaneous use
			'label'=>'Одновременно подключений по одному логину',
			'type'=>'text',
			'style'=>'width:50px',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>5)
		),
		'live_iptv'=>array(
			'label'=>'используют IPTV',
			'type'=>'text',
			'class'=>'summ',
			'style'=>'width:50px',
			'native'=>false,
			'access'=>array('r'=>1,'w'=>5)
		),
		'live'=>array(
			'label'=>'активных',
			'type'=>'text',
			'class'=>'summ',
			'style'=>'width:50px',
			'native'=>false,
			'access'=>array('r'=>1,'w'=>5)
		),
		'stale'=>array(
			'label'=>'думающих',
			'type'=>'text',
			'class'=>'summ',
			'style'=>'width:50px',
			'native'=>false,
			'access'=>array('r'=>1,'w'=>5)
		),
		'lost'=>array(
			'label'=>'Ушедших',
			'type'=>'text',
			'class'=>'summ',
			'style'=>'width:50px',
			'native'=>false,
			'access'=>array('r'=>1,'w'=>5)
		),
		'allusr'=>array(
			'label'=>'Всего',
			'type'=>'text',
			'class'=>'summ',
			'style'=>'width:50px',
			'native'=>false,
			'access'=>array('r'=>1,'w'=>5)
		),
	),
	'group'=>'',
);

function before_new_packet($f) {
	global $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	$f['id'] = 'new';
	$f['header'] = 'Новый тарифный план';
	$f['defaults']['num'] = $q->select("SELECT max(num)+5 FROM packets",4);
	$f['defaults']['name'] = "Новый тарифный план";
	$f['defaults']['su'] = 1;
	return $f;
}

function before_edit_packet($f) {
	global $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	$id = isset($_REQUEST['id'])? numeric($_REQUEST['id']) : false;
	if(!$id) stop(array('result'=>"ERROR",'desc'=>" Не указан тарифный пакет!"));
	if(!($p = $q->select("SELECT * FROM packets WHERE pid='{$id}'",1)))
		stop(array('result'=>"ERROR",'desc'=>"Пакет не найден в базе!"));
	$f['id'] = $id;
	$f['header'] = "{$p['name']}";
	return $f;
}

function before_save_packet($cmp,$old,$my) {
	return $cmp;
}

function before_delete_packet($old,$my) {
	return true;
}

function allow_delete_packet($id) {
	global $config, $q, $DEBUG;
	if($DEBUG>0) log_txt(__function__.": id='$id'");
	if(!$q) $q = new sql_query($config['db']);
	$usr = $q->select("SELECT count(*) FROM users WHERE pid='$id'",4);
	if($usr > 0){
		log_txt(__function__.": Удаление таривного пакета {$r['name']} невозможно!<br> Присутствуют $usr пользователей");
		return "Удаление таривного пакета {$r['name']} невозможно!<br> Присутствуют $usr пользователей";
	}
	return 'yes';
}

function packet_fixed_cost($v,$r,$fn=null) {
	return ($v==0)? '' : sprintf("%.2f",$v);
}

function onsave_packet($id,$res,$my) {
	global $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	if($my->id != 'new'){
		if(isset($res[''])){
		}
	}
	$sql = preg_replace('/:FILTER:/',"AND p.pid='{$res['pid']}'",$my->cfg['table_query']);
	$sql = preg_replace('/ORDER BY.*/','',$sql);
	return array('result'=>'OK','modify'=>$q->select($sql));
}

function get_profiles() {
	global $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	$r = $q->fetch_all("SELECT groupname as id, groupname FROM radusergroup WHERE username!='ALL' ORDER BY groupname");
	return $r;
}

function get_packet_name($id,$r=null,$fn=null) {
	global $cache, $config;
	if(!key_exists('packets',$cache)) {
		$q = new sql_query($config['db']);
		$cache['packets'] = $q->fetch_all("SELECT pid, name FROM packets ORDER BY num",'pid');
	}
	return $cache['packets'][$id];
}

function build_filter_for_packets($t) {
	global $tables, $_REQUEST;
	$r = array(); $s = '';
	$a = $tables[$t]['filters'];
	if(is_array($a)){
		foreach($a as $k=>$v){
			if($k == 'name'){
				$r[] = "AND `$k` like '%".str($_REQUEST[$k])."%'";
			}elseif(isset($_REQUEST[$k]) && $v!='')
				$r[] = "AND `$k`='".strict($_REQUEST[$k])."'";
		}
		$s = implode(' ',$r);
	}//  	log_txt(__function__.": return: $s");
	return $s;
}
?>
