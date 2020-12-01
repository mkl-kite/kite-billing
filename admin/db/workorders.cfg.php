<?php
include_once("classes.php");
include_once("rayon_packet.cfg.php");
include_once("claimperform.cfg.php");
include_once("workpeople.cfg.php");
include_once("geodata.php");
$errors = array();

$tables['workorders']=array(
	'title'=>'Служащие',
	'target'=>'form',
	'module'=>"stdform",
	'key'=>'woid',
	'limit'=>'yes',
	'style'=>'width:770px;position:relative',
	'table_style'=>'width:100%',
	'delete'=>'yes',
	'form_query'=>"
		SELECT 
			woid,
			status,
			1 as employers,
			createtime,
			prescribe,
			type,
			worktype,
			manager,
			operator,
			1 as jobs
		FROM
			workorders
		",
	'table_query'=>"
		SELECT 
			o.woid,
			o.woid as worder,
			o.status,
			o.prescribe,
			group_concat(distinct employer) as temployers,
			group_concat(distinct concat(cast(c.type AS CHAR CHARACTER SET utf8),':',c.address,':',c.content) separator '&') as `claims`,
			o.type,
			o.operator
		FROM
			workorders o
			LEFT OUTER JOIN workpeople p ON o.woid = p.worder
			LEFT OUTER JOIN claimperform cp ON o.woid=cp.woid
			LEFT OUTER JOIN claims c ON cp.cid=c.unique_id
		WHERE 1 :FILTER:
		GROUP BY o.woid
		ORDER BY :SORT:
		",
		'class'=>'normal',
	'field_alias'=>array('status'=>'o','prescribe'=>'o','operator'=>'o','type'=>'o'),
	'filters'=>array(
		'start'=>array(
			'type'=>'date',
			'origin'=>'prescribe',
			'label'=>'назначен с',
			'style'=>'width:80px',
			'title'=>'назначен на >',
			'value'=>cyrdate(strtotime('-7 day'))
		),
		'operator'=>array(
			'type'=>'select',
			'label'=>'оператор',
			'title'=>'оператор',
			'style'=>'width:80px',
			'list'=>all2array(list_operators()),
			'value'=>'_'
		),
		'status'=>array(
			'type'=>'select',
			'label'=>'статус',
			'style'=>'width:80px',
			'title'=>'статус наряда',
			'keep'=>true,
			'list'=>all2array($worder_status),
			'value'=>'_'
		),
		'type'=>array(
			'type'=>'select',
			'label'=>'тип н-да',
			'title'=>'тип наряда',
			'style'=>'width:80px',
			'list'=>all2array($config['wo']['type']),
			'value'=>'_'
		),
	),
	'defaults'=>array(
		'sort'=>'prescribe',
		'filter'=>'build_filter_for_workorders'
	),
	'layout'=>array(
		'eids'=>array(
			'type'=>'fieldset',
			'legend'=>'исполнители',
			'style'=>'width:230px;height:80px;float:left',
			'fields'=>array('employers')
		),
		'mydate'=>array(
			'type'=>'fieldset',
			'legend'=>'дата',
			'style'=>'width:65px;height:80px;float:left',
			'fields'=>array('prescribe')
		),
		'alljobs'=>array(
			'type'=>'fieldset',
			'legend'=>'задания <img class="add-button" src="pic/add.png" title="добавить задание">',
			'style'=>'width:350px;height:314px;float:left',
			'fields'=>array('jobs')
		),
		'bodyform'=>array(
			'type'=>'fieldset',
			'legend'=>'данные наряда',
			'style'=>'width:355px;height:212px;top:164px;position:absolute',
			'fields'=>array('createtime','status','type','worktype','manager','operator')
		),
	),
	'table_menu'=>array(
		'edit'=>array('label'=>"<img src=\"pic/gtk-edit16.png\"> изменить",'to'=>'form'),
		'close'=>array('label'=>"<img src=\"pic/lock.png\"> закрыть",'to'=>'edit','query'=>"go=stdform&do=save&status=3&table=workorders"),
		'print'=>array('label'=>"<img src=\"pic/gtk-print.png\"> печатать",'to'=>'window','target'=>"docpdf.php",'query'=>"type=workorder"),
		'delete'=>array('label'=>"<img src=\"pic/del.png\"> удалить",'to'=>'form','query'=>"go=stdform&do=delete&table=workorders"),
	),
	'table_footer'=>array(
		'worder'=>'Всего:',
		'operator'=>'fcount',
	),
	'checks'=>array(
		'save'=>'check_workorder_for_save'
	),
// 	преобразование данных формы к заданному формату base->server->client
	'form_triggers'=>array(
		'employers'=>'get_wemployers',
		'operator'=>'get_wo_operator',
		'createtime'=>'get_createtime',
		'prescribe'=>'cell_date',
	),
	'table_triggers'=>array(
 		'type'=>'get_twotypes',
 		'worktype'=>'get_tworktypes',
 		'prescribe'=>'get_prescribe',
 		'status'=>'get_workstatus',
 		'temployers'=>'get_temployers',
		'jobs'=>'get_employertime',
		'manager'=>'get_employer',
		'claims'=>'get_tclaims',
		'operator'=>'get_wo_operator',
	),
	'before_new'=>'before_new_workorder',
	'before_edit'=>'before_edit_workorder',
	'before_save'=>'before_save_workorder',
	'form_onsave'=>'onsave_workorder',
	'allow_delete'=>'allow_delete_workorder',
	'before_delete'=>'before_delete_workorder',
	'group'=>'',

	// поля
	'fields'=>array(
		'woid'=>array(
			'label'=>'id',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'worder'=>array(
			'label'=>'N',
			'type'=>'text',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>3)
		),
		'status'=>array(
			'label'=>'статус',
			'type'=>'select',
			'list'=>$worder_status,
//			'style'=>'width:150px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'employers'=>array(
			'label'=>'исполнители',
			'type'=>'photolist',
			'list'=>'get_list_of_employers',
			'query'=>'go=workpeople&do=get',
			'ratio'=>'3:4',
			'native'=>false,
			'style'=>'width:230px;height:70px;overflow:hidden;background:#F5EFE9',
			'access'=>array('r'=>2,'w'=>3)
		),
		'temployers'=>array(
			'label'=>'исполнители',
			'type'=>'text',
			'native'=>false,
			'style'=>'width:230px',
			'access'=>array('r'=>3,'w'=>3)
		),
		'createtime'=>array(
			'label'=>'создан',
			'type'=>'nofield',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>3),
		),
		'prescribe'=>array(
			'label'=>'назначен на',
			'type'=>'date',
			'class'=>'date',
			'style'=>'width:80px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'performed'=>array(
			'label'=>'выполнен',
			'type'=>'date',
			'style'=>'width:80px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'type'=>array(
			'label'=>'тип наряда',
			'type'=>'select',
			'list'=>'get_wotypes',
			'style'=>'width:170px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'worktype'=>array(
			'label'=>'тип работ',
			'type'=>'select',
			'list'=>'get_worktypes',
			'style'=>'width:170px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'claims'=>array(
			'label'=>'адреса',
			'type'=>'text',
			'class'=>'claims',
			'style'=>'max-width:270px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'manager'=>array(
			'label'=>'р-тель работ',
			'type'=>'select',
			'list'=>'get_managers',
			'style'=>'width:170px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'operator'=>array(
			'label'=>'оператор',
			'type'=>'nofield',
			'class'=>'nowr',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'jobs'=>array(
			'label'=>'задания',
			'type'=>'subform',
			'tname'=>'claimperform',
			'sub'=>'get_subform_of_claimperform',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>3),
		),
		'note'=>array(
			'label'=>'примечание',
			'type'=>'textarea',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3),
		),
	)
);

function before_new_workorder($f) {
	global $config, $tables, $opdata, $_REQUEST, $DEBUG, $q;
	if(!$q) $q = sql_query($config['db']);
	$nonclosed = $q->select("SELECT count(*) FROM workorders WHERE status<2 AND prescribe<date(now())",4);
	if($nonclosed>0) stop(array('result'=>"ERROR",'desc'=>"У Вас $nonclosed незакрытых нарядов!"));
	$cl = isset($_REQUEST['claims'])? preg_replace('/[^0-9,]/','',$_REQUEST['claims']) : false;
	if($cl){
		$wo = array('operator'=>$opdata['login'],'status'=>0,'createtime'=>date2db(),'prescribe'=>date2db(false,false));
		$wo['id'] = $q->insert('workorders',$wo);
			
		$claims = $q->select("SELECT * FROM claims WHERE unique_id in ($cl)");
		if($claims) foreach($claims as $c){
			if($c['status']>1 || $c['woid']>0) stop(array('result'=>"ERROR",'desc'=>"Указанные заявления уже отработаны!"));
			$all[] = array('cid'=>$c['unique_id'],'woid'=>$wo['id'],'status'=>1);
		}
		if(isset($all)){
			$t = $tables['claimperform']; $t['id'] = 'new';
			$form = new Form($config);
			foreach($all as $cp) $form->save($t,$cp);
		}
		if($wo['id']) $f['header'] = "Наряд &#8470; {$wo['id']}";
		$f['id'] = $wo['id'];
		// при закрытии предыдущего наряда с незавершёнными заданиями
		$oldwo = isset($_REQUEST['oldwo'])? numeric($_REQUEST['oldwo']) : false;
		if($oldwo){
			// $q->query("DELETE FROM claimperform WHERE woid='$oldwo' AND status=2");
			if($wo['id']) $e = $q->select("SELECT '{$wo['id']}' as worder, employer  FROM workpeople WHERE worder='{$oldwo}'");
			if(@$e) $q->insert('workpeople',$e);
		}
	}else{
		$f['header'] = "Новый наряд";
		$f['defaults']['createtime'] = date2db();
		$f['defaults']['operator'] = $opdata['login'];
		$f['defaults']['employers'] = '';
	}
	return $f;
}

function before_edit_workorder($f) {
	global $config, $tables, $opdata, $DEBUG, $q;
	if(isset($f['record'])) $id = $f['record']['woid'];
	elseif(isset($f['id'])) $id = $f['id'];
	elseif(isset($_REQUEST['woid'])) $id = numeric($_REQUEST['woid']);
	elseif(isset($_REQUEST['id'])) $id = numeric($_REQUEST['id']);
	if(!isset($f['header']) && isset($id)) $f['header'] = "Наряд &#8470; $id";
	if(!isset($id)) stop(array('result'=>"ERROR",'desc'=>"Не указан номеер наряда!"));

	if(!$q) $q = sql_query($config['db']);
	$r = $q->select("SELECT * FROM workorders WHERE woid='$id'",1);
	if($r && $r['status']>1){
		foreach(array('type','worktype','manager') as $n) $f['fields'][$n]['disabled'] = 1;
	}
	$cp = $q->select("SELECT count(*) FROM claimperform WHERE woid='$id' AND status>1",4);
	if($r['status']>1 || $cp>0){
		$f['fields']['prescribe']['type'] = 'nofield';
		$f['fields']['prescribe']['style'] = 'width:89px;font:11pt sans-serif';
	}
	$cl = (isset($_REQUEST['claims']))? preg_replace('/[^0-9,]/','',$_REQUEST['claims']) : false;
	if($cl &&  $r['status']>1) stop(array('result'=>"ERROR",'desc'=>"Наряд уже {$worder_status[$r['status']]}!"));
	if($cl){
		$claims = $q->select("SELECT * FROM claims WHERE unique_id in ($cl)");
		if($claims) foreach($claims as $c) if($c['status']>1) 
			stop(array('result'=>"ERROR",'desc'=>"Указанные заявления уже отработаны!"));;
		if($claims) {
			$t = $tables['claimperform']; $t['id'] = 'new';
			$form = new Form($config);
			foreach($claims as $c){
				$a = array('cid'=>$c['unique_id'],'woid'=>$r['woid'],'status'=>1);
				$form->save($t,$a);
			}
		}
	}
	if(!isset($f['header'])) $f['header'] = "Наряд &#8470; $id";
	$f['header'] .= "<span class=\"linkform\" add=\"docpdf.php?type=workorder&id=$id\" style=\"float:right\"><img src=\"pic/prn1.png\"></span>";
	return $f;
}

function before_save_workorder($cmp,$old) {
	global $config, $opdata, $DEBUG, $q, $worder_status;
	if(!isset($q)) $q = new sql_query($config['db']);
	$r = array_merge(($old)? $old : array(), ($cmp)? $cmp : array());
	if(!isset($r['woid'])){
		log_txt(__function__.": ERROR not found woid! r: ".arrstr($r));
		return false;
	}
	if(isset($cmp['status']) && $cmp['status']<2 && isset($old['status']) && $old['status']>1){
		if($DEBUG>0) log_txt(__function__.": status<2 and old status>1");
		if(strtotime($r['prescribe']) < strtotime(date('Y-m-d')) && $opdata['level']<4)
			stop(array('result'=>"ERROR",'desc'=>"Вы не можете заново открыть наряд!"));
		if(strtotime($r['prescribe']) < strtotime('-3 day') && $opdata['level']==4)
			stop(array('result'=>"ERROR",'desc'=>"Вы не можете заново открыть наряд!"));
		$cmp['performed'] = null;
	}
	if(isset($old['status']) && $old['status']>1){
		if($DEBUG>0) log_txt(__function__.": old status>1");
		if(isset($cmp['status']) && $cmp['status']>1 && count($cmp)>1 || !isset($cmp['status']) && count($cmp)>0)
			stop(array('result'=>"ERROR",'desc'=>"Вы не можете редактировать наряд, который {$worder_status[$old['status']]}!"));
	}
	if((!isset($old['status']) || $old['status']<2) && (!isset($r['manager']) || $r['manager']==0 || $r['manager']==''))
		stop(array('result'=>"ERROR",'desc'=>"Не указан руководитель работ!"));
	if($r['type'] == 'permit' && (!isset($r['worktype']) || $r['worktype']==''))
		stop(array('result'=>"ERROR",'desc'=>"Не указан тип выполняемых работ!"));
	if(isset($cmp['status']) && $cmp['status']>1){
		if($DEBUG>0) log_txt(__function__.": status>1");
		if(!isset($r['prescribe']) || strtotime($r['prescribe']) > strtotime(date('Y-m-d')))
			stop(array('result'=>"ERROR",'desc'=>"Нельзя закрывать будущие наряды!"));
		$not_served = $q->fetch_all("SELECT c.address FROM claimperform p, claims c WHERE c.unique_id=p.cid AND p.woid = {$r['woid']} AND p.status=3 AND c.type=1 AND c.uid=0");
		if($not_served) stop(array('result'=>"ERROR",'desc'=>"В наряде есть задания на установку!<BR>Клиенты:<br>".implode('<br>&emsp; ',$not_served)."<BR> не найдены!"));
		$cmp['performed'] = date2db();
	}
	if(isset($cmp['status']) && $cmp['status']<2){
		if($DEBUG>0) log_txt(__function__.": status<2");
		$cmp['performed'] = null;
	}
	if(isset($cmp['employers'])){
		if($DEBUG>0) log_txt(__function__.": change employers: ".arrstr($cmp['employers']));
		$o = ($old['employers'] != '')? preg_split('/,/',$old['employers']) : array();;
		if($DEBUG>0) log_txt(__function__.": WP: ".arrstr($o));
		$e = ($cmp['employers'] != '')? preg_split('/,/',$cmp['employers']) : array();
		$cmp1 = array_diff($o,$e);
 		if(count($cmp1)>0) $q->query("DELETE FROM workpeople WHERE worder='{$r['woid']}' AND employer in (".implode(',',$cmp1).")");
		if($DEBUG>0) log_txt(__function__.": workpeople remove: ".arrstr($cmp1));
		$cmp2 = array_diff($e,$o);
		$ins = array();
		if(count($cmp2)>0) foreach($cmp2 as $k=>$v) $ins[] = array('worder'=>$r['woid'],'employer'=>$v);
		if(count($ins)>0) $q->insert('workpeople',$ins);
		if($DEBUG>0) log_txt(__function__.": workpeople add: ".arrstr($ins));
//		unset($cmp['employers']);
	}
	if($DEBUG>0) log_txt(__function__.": return cmp=".arrstr($cmp));
	return $cmp;
}

function check_workorder_for_save($r) {
	global $config, $errors, $DEBUG;
	$result = true;
	return $result;
}

function onsave_workorder($id,$s,$my) {
	global $config, $q, $claim_types, $DEBUG;
	$out = true;
	if(!$q) $q = new sql_query($config['db']);
	$r = $q->get('workorders',$id);
	if(isset($s['status'])){
		if($r['status']==0){
			$q->query("UPDATE claims c, claimperform p SET c.status=2, p.status=1 WHERE c.unique_id=p.cid AND p.woid='{$id}' AND p.status!=3");
		}elseif($r['status']==1){
			$q->query("UPDATE claims c, claimperform p SET c.status=2, p.status=1 WHERE c.unique_id=p.cid AND p.woid='{$id}' AND p.status!=3");
			if($DEBUG>1) log_txt(__function__.": SQL: ".$q->sql);
			if($my->row['status']<2){
				$jobs = $q->select("SELECT c.fio, c.uid, c.type, u.email, p.begintime FROM claims c, claimperform p, users u WHERE c.unique_id=p.cid AND c.uid=u.uid AND p.woid='$id'");
				if($jobs) foreach($jobs as $i=>$j){
					$type = preg_replace('/а$/','у',$claim_types[$j['type']]);
					send_notify('new_job',$j);
				}
			}
		}elseif($r['status']==2){
			$q->query("UPDATE claims c, claimperform p SET c.status=4, p.status=3 WHERE c.unique_id=p.cid AND p.woid='{$id}' AND p.status!=2");
			if($DEBUG>1) log_txt(__function__.": SQL: ".$q->sql);
		}elseif($r['status']==3){
			$q->query("UPDATE claims c, claimperform p SET c.status=0, c.woid=null, p.status=2 WHERE c.unique_id=p.cid AND p.woid='{$id}' AND p.status!=3");
			if($DEBUG>1) log_txt(__function__.": SQL: ".$q->sql);
			if($claims = $q->fetch_all("SELECT cid FROM claimperform WHERE status=2 AND woid='{$id}'")){
				$form = new form($config);
				$out = $form->confirmForm($id,'new',"	Хотите переместить не выполненные задания<br>в новый наряд?",'workorders');
				$out['form']['fields']['claims'] = array('type'=>'hidden','value'=>implode(',',$claims));
				$out['form']['fields']['oldwo'] = array('type'=>'hidden','value'=>$id);
			}
		}
	}
	if(isset($s['prescribe'])){
		$d = date2db($s['prescribe'],false);
		$q->query("UPDATE claimperform SET begintime=DATE_FORMAT(begintime,'{$d} %H:%i'), endtime=DATE_FORMAT(endtime,'{$d} %H:%i') WHERE woid='{$r['woid']}'");
		if(SMS_SEND_OF_PLANECLAIM>0 || USE_EMAIL>0){
			$jobs = $q->select("SELECT c.fio, c.uid, c.type, c.claimtime, u.email, u.phone, p.* FROM claims c, claimperform p, users u WHERE c.unique_id=p.cid AND c.uid=u.uid AND p.woid='{$r['woid']}'");
			foreach($jobs as $k=>$j){
				$j['type'] = preg_replace('/а$/','у',@$claim_types[$j['type']]);
				$j['claimtime'] = cyrdate($j['claimtime']);
				send_notify('move_job',$j);
			}
		}
		if($DEBUG>0) log_txt(__function__.": SQL: ".$q->sql);
	}
	return $out;
}

function allow_delete_workorder($id,$my) {
	global $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	if(is_array($id)) { $r = $id; $id = $r['woid']; } 
	if(!is_numeric($id) || $id == 0) {
		log_txt(__function__.": ERROR not found 'woid'");
		stop(array('result'=>'ERROR', 'desc'=>"Не найден номер наряда!"));
	}
	$cp = $q->select("SELECT * FROM claimperform WHERE woid='{$id}' LIMIT 1",1);
	if($cp) return "Удаление невозможно!<br> Присутствуют задания.";
	return 'yes';
}

function before_delete_workorder($old,$my) {
	global $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	if(!isset($old['woid'])) {
		log_txt(__function__.": ERROR not found 'woid'");
		stop(array('result'=>'ERROR', 'desc'=>"Не найден номер наряда!"));
	}
	$q->query("DELETE FROM workpeople WHERE worder='{$old['woid']}'");
	$q->query("UPDATE claims c, claimperform p SET c.status=0 WHERE c.unique_id=p.cid AND p.woid='{$old['woid']}'");
	$q->query("DELETE FROM claimperform WHERE woid='{$old['woid']}'");
	return true;
}

function get_createtime($v) {
	return cyrdate($v,'%d-%m-%Y %T');
}

function get_tclaims($v,$r=null,$fn=null) {
	global $claim_colors;
	if($v == '') return $v;
	$r = preg_split('/&/',$v);
	foreach($r as $k=>$c) {
		$a = preg_split('/:/',$c,3);
		if(!$c) $c = $claim_colors[0];
		if(count($a)>1) {
			$c = $claim_colors[$a[0]]; $street = $a[1];
		}else{
			$street = $a[0];
		}
		$r[$k] = "<span title=\"".preg_replace('/([,.]) /',"$1\r",$a[2])."\" style=\"color:{$c}\">".trim($street)."</span> ";
	}
	return implode(' ',$r);
}

function get_worktypes($r) {
	global $config;
	$e = array('_'=>'');
	foreach($config['wo']['worktype'] as $k=>$v){
		$e[$k] = $v['name'];
	}
	return $e;
}

function get_wotypes($r) {
	global $config;
	foreach($config['wo']['type'] as $k=>$v){
		$e[$k] = $v;
	}
	return $e;
}

function get_employerphoto($r,$a=null,$fn=null) {
	return photo_link($r['photo']);
}

function get_managers($r) {
	global $config;
	$q = new sql_query($config['db']);
	$e = $q->fetch_all("SELECT eid as id, fio FROM employers WHERE blocked=0 AND seat like 'менеджер%' ORDER BY fio");
	if(!$e) $e = $q->fetch_all("SELECT eid as id, fio FROM employers WHERE blocked=0");
	$o[0] = '';
	foreach($e as $k=>$v) $o[$k] = shortfio($v);
	return $o;
}

function get_list_of_employers($r) {
	global $config;
	$q = new sql_query($config['db']);
	$e = $q->select("SELECT e.eid as id, e.fio, e.photo FROM employers e, workpeople p WHERE eid=p.employer AND p.worder='{$r['woid']}'");
	foreach($e as $k=>$v){
		$e[$k]['photo'] = photo_link($v['photo']);
		$e[$k]['fio'] = shortfio($v['fio']);
	}
	if(!$e) $e = array();
	return $e;
}

function get_subform_of_claimperform($id) {
	global $DEBUG, $config, $opdata, $tables;
	if($DEBUG>0) log_txt(__function__.": id=$id");
	// делаем выборку пакетов
	$tname = 'claimperform';
	$t=array_merge($tables[$tname],array(
		'type'=>'table',
		'limit'=>'no',
		'module'=>'claimperform',
		'filter'=>"AND cp.woid='$id'",
		'style'=>'width:100%',
		'name'=>$tname
	));
	$c = new Table($t);
	return array(
		'class'=>'subform',
		'style'=>'width:100%;height:295px;overflow:auto;background:#F5EFE9',
		'table'=>$c->get()
	);
}

function get_wemployers($v,$r) {
	global $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	$r = $q->fetch_all("SELECT employer FROM workpeople WHERE worder={$r['woid']}");
	$out = (count($r)>0)? implode(',',$r) : '';
	return $out;
}

function get_workstatus($v,$r,$fn=null) {
	global $worder_status;
	if(isset($worder_status[$v]))
		return "<b style=\"color:".(($v<=1)?'#070':'#900')."\">".$worder_status[$v]."</b>";
	return $v;
}

function get_tworktypes($v,$r=null,$fn=null) {
	global $config;
	if(isset($config['wo']['worktype'][$v])) return $config['wo']['worktype'][$v]['name'];
	return $v;
}

function get_twotypes($v,$r=null,$fn=null) {
	global $config;
	if(isset($config['wo']['type'][$v])){
		return $config['wo']['type'][$v];
	}
	return $v;
}

function get_temployers($v,$r=null,$fn=null) {
	$r = array();
	$a = preg_split('/,/',$v);
	foreach($a as $k=>$e) $r[] = "<span class=\"\">".get_employer($e)."</span>";
	return implode(' ',$r);
}

function get_employer($v,$r=null,$fn=null) {
	global $config, $cache, $q;
	if(!$q) $q = new sql_query($config['db']);
	if(!isset($cache['employers'])) {
		$cache['employers'] = $q->fetch_all("SELECT eid as id, fio FROM employers ORDER BY fio");
		foreach($cache['employers'] as $k=>$n) $cache['employers'][$k] = shortfio($n);
	}
	if(isset($cache['employers'][$v])) return $cache['employers'][$v];
	return ($v==0)? '' : $v;
}

function get_wo_operator($v,$r=null,$fn=null) {
	global $config, $cache, $q;
	if(!isset($cache['operators'])) {
		if(!$q) $q = new sql_query($config['db']);
		$cache['operators'] = $q->fetch_all("SELECT login as id, fio FROM operators ORDER BY fio");
		foreach($cache['operators'] as $k=>$n) $cache['operators'][$k] = shortfio($n);
	}
	if(isset($cache['operators'][$v])) return $cache['operators'][$v];
	return ($v==0)? '' : $v;
}

function get_prescribe($v,$r=null,$fn=null) {
	return cyrdate($v,'%a. %d-%m-%y');
}

function build_filter_for_workorders($t) {
	return filter2db('workorders');
}
?>
