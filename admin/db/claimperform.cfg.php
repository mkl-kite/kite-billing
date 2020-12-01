<?php
include_once("classes.php");
include_once("claims.cfg.php");
include_once("rayon_packet.cfg.php");
include_once("geodata.php");
$woid = (key_exists('woid',$_REQUEST))? $_REQUEST['woid']:0;
$errors = array();

$tables['claimperform']=array(
	'name'=>'claimperform',
	'title'=>'Заявка',
	'target'=>'form',
	'module'=>"claimperform",
	'key'=>'unique_id',
	'focus'=>'begintime',
	'form_query'=>"
		SELECT 
			cp.unique_id,
			cp.cid,
			cp.woid,
			cp.status,
			begintime,
			endtime,
			cp.note,
			c.type,
			c.status as clstatus,
			c.claimtime,
			/* c.user, */
			c.uid,
			c.address,
			c.rid,
			c.fio,
			c.phone,
			c.content
		FROM
			claims c,
			claimperform cp
		WHERE
			c.unique_id = cp.cid
		",
	'table_query'=>"
		SELECT 
			cp.unique_id as id,
			begintime,
			cp.status,
			c.address,
			cp.note
		FROM
			claims c,
			claimperform cp
		WHERE
			c.unique_id = cp.cid :FILTER:
		ORDER BY
			cp.begintime
		",
	'layout'=>array(
		'job'=>array(
			'type'=>'fieldset',
			'legend'=>"данные",
			'style'=>'width:315px;height:300px;float:left;',
			'fields'=>array('woid','cid','status','begintime','endtime','note','content')
		),
		'cldata'=>array(
			'type'=>'fieldset',
			'legend'=>'Данные по заявке',
			'class'=>'closable',
			'style'=>'display:none;width:355px;height:300px;float:left;',
			'fields'=>array('type','claimtime','clstatus','rid',/*'user',*/'fio','address','phone')
		),
	),
	'table_menu'=>array(
		'planned'=>array('label'=>"<img src=\"pic/bookmark_green.png\"> в работе",'to'=>'edit','query'=>"go=claimperform&do=save&status=0"),
		'performed'=>array('label'=>"<img src=\"pic/bookmark.png\"> выполнено",'to'=>'edit','query'=>"go=claimperform&do=save&status=3"),
		'notperformed'=>array('label'=>"<img src=\"pic/bookmark_dark.png\"> не выполнено",'to'=>'edit','query'=>"go=claimperform&do=save&status=2"),
		'usrcard'=>array('label'=>"<img src=\"pic/usr.png\"> клиент",'to'=>'edit','query'=>"go=claimperform&do=user"),
		'edit'=>array('label'=>"<img src=\"pic/gtk-edit16.png\"> изменить",'to'=>'form'),
	),
	'class'=>'normal',
	'delete'=>'yes',
	'header'=>"<div class=\"button\" id=\"ext\" style=\"position:relative;height:30px;width:30px;float:right;background:url(pic/next.png) no-repeat center\"></div>",
	'defaults'=>array(
		'numports'=>1
	),
// 	'footer'=>array(),
// 	если проверка не пройдена функция должна прервать обработку данных
	'checks'=>array(
		'save'=>'check_claimperform_for_save'
	),
// 	преобразование данных формы к заданному формату base->server->client
	'form_triggers'=>array(
		'type'=>'get_claimtype',
		'clstatus'=>'get_clstatus',
		'status'=>'get_claimperformstatus',
		'rid'=>'get_rayon_name',
		'begintime'=>'cp_time',
		'endtime'=>'cp_time',
	),
	'table_triggers'=>array(
		'status'=>'get_tclaimperformstatus',
		'rid'=>'get_rayon_name',
		'note'=>'get_tclaimperformnote',
		'begintime'=>'cp_time',
		'endtime'=>'cp_time',
	),
// 	преобразование данных к заданному формату client->server->base
	'before_edit'=>'before_edit_claimperform',
	'before_save'=>'before_save_claimperform',
 	'form_onsave'=>'onsave_claimperform',
	'allow_delete'=>'allow_delete_claimperform',
	'before_delete'=>'before_delete_claimperform',
	'sort'=>'',
	'group'=>'',

	'fields'=>array(
		'id'=>array(
			'label'=>'id',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'unique_id'=>array(
			'label'=>'id',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'cid'=>array(
			'label'=>'заявка &#8470;',
			'type'=>'nofield',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'woid'=>array(
			'label'=>'наряд',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'type'=>array(
			'label'=>'тип',
			'type'=>'select',
			'list'=>$claim_types,
			'native'=>false,
			'access'=>array('r'=>3,'w'=>3)
		),
		'claimtime'=>array(
			'label'=>'дата заявки',
			'type'=>'nofield',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>3)
		),
		'clstatus'=>array(
			'label'=>'статус заявки',
			'type'=>'select',
			'list'=>$claim_status,
			'native'=>false,
			'access'=>array('r'=>3,'w'=>3)
		),
		'rid'=>array(
			'label'=>'район',
			'type'=>'select',
			'list'=>'list_of_rayons',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>3)
		),
		'address'=>array(
			'label'=>'адрес',
			'type'=>'text',
			'style'=>'width:250px',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>3)
		),
		'user'=>array(
			'label'=>'логин',
			'type'=>'text',
			'style'=>'width:150px',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>3)
		),
		'fio'=>array(
			'label'=>'Ф.И.О.',
			'type'=>'text',
			'style'=>'width:250px',
			'native'=>false,
			'access'=>array('r'=>1,'w'=>3)
		),
		'uid'=>array(
			'label'=>'uid',
			'type'=>'hidden',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>3)
		),
		'phone'=>array(
			'label'=>'телефон',
			'type'=>'text',
			'style'=>'width:250px',
			'native'=>false,
			'access'=>array('r'=>2,'w'=>3)
		),
		'content'=>array(
			'label'=>'содержимое',
			'style'=>'width:200px;height:90px;overflow-y:auto',
			'type'=>'textarea',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'status'=>array(
			'label'=>'статус',
			'type'=>'select',
			'list'=>$clperf_status,
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'begintime'=>array(
			'label'=>'начало',
			'type'=>'text',
			'style'=>'width:70px',
			'table_style'=>'width:50px',
// 			'list'=>'list_of_worktimes',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'endtime'=>array(
			'label'=>'окончание',
			'type'=>'text',
			'style'=>'width:70px',
// 			'list'=>'list_of_worktimes',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'note'=>array(
			'label'=>'примечания',
			'style'=>'width:200px;height:40px;overflow-y:auto;',
			'table_style'=>'width:20px',
			'type'=>'textarea',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		)
	)
);

function cp_time($v,$r,$fn=null) {
	return cyrdate($v,'%H:%M');
}

function list_of_worktimes() {
	$r = array();
	$thisday = strftime('%Y-%m-%d');
	$begintime = strtotime(strftime('%Y-%m-%d 08:00'));
	$endtime = strtotime(strftime('%Y-%m-%d 18:00'));
	for($t=$begintime;$t<=$endtime;$t+=1800) {
		$key = strftime('%H:%M',$t);
		$r[$key] = $key;
	}
	return $r;
}

function get_clstatus($v,$r) {
	global $claim_status;
	if(isset($claim_status[$v])) return $claim_status[$v];
	return $v;
}

function get_claimperformstatus($v,$r,$fn=null) {
	global $clperf_status;
	if(isset($clperf_status[$v])) return $clperf_status[$v];
	return $v;
}

function get_tclaimperformstatus($v,$r=null,$fn=null) {
	global $status_cp;
	if(isset($status_cp[$v])) return $status_cp[$v];
	return $v;
}

function get_tclaimperformnote($v,$r=null,$fn=null) {
	return ($v!='')? "<img src=\"pic/doc.png\" title=\"$v\">" : "";
}

function before_edit_claimperform($f) {
	global $config, $opdata, $DEBUG, $q;
	$fld = array('type','claimtime','clstatus','rid','fio','address','phone');
	$fld1 = array('begintime','endtime','note');
	$fld2 = $fld1; array_push($fld2,'status','content');
	if(!$q) $q = new sql_query($config['db']);

	if(!isset($f['id'])){
		$id = isset($_REQUEST['unique_id'])? numeric($_REQUEST['unique_id']) : null;
		if(!$id) $id = isset($_REQUEST['id'])? numeric($_REQUEST['id']) : null;
		if(!$id) stop(array('result'=>'ERROR','desc'=>'Задание не найдено!'));
		$f['id'] = $id;
	}
	if(!($cp = $q->select("SELECT * FROM `claimperform` WHERE {$f['key']}='{$f['id']}'",1)))
		stop(array('result'=>'ERROR','desc'=>'Это задание отсутствует в базе!'));
	if(!($wo = $q->select("SELECT * FROM `workorders` WHERE woid='{$cp['woid']}'",1)))
		stop(array('result'=>'ERROR','desc'=>'Этот наряд отсутствует в базе!'));
	foreach($fld as $n) $f['fields'][$n]['type'] = 'nofield';
	if($wo['status']>1) foreach($fld1 as $n) $f['fields'][$n]['type'] = 'nofield';
	$f['header'] .= 'Задание по наряду &#8470;'.$wo['woid'];
	return $f;
}

function adjustJobTime($begin,$end,$day='') { // проверка времени задания на соответствие ограничениям
	global $config, $DEBUG;
	if(!$begin || !$end) return false;
	$bt = strtotime($begin);
	$et = strtotime($end);
	$dt = ($day)? $day : date('Y-m-d',$bt);
	$wb = ($config['wo']['work_begin'])? strtotime($dt.' '.$config['wo']['work_begin']) : strtotime($dt.' 08:00');
	$mod = false;
	if($dt != ($d = date('Y-m-d',$bt))) {
		log_txt(__function__.": дата начала не совпадает с указанной $d ($dt)  ".date2db($bt));
		$bt = strtotime($dt.' '.date('H:i:s',$bt)); // дата начала не совпадает с указанной
		$mod = true;
	}
	if($dt != ($d = date('Y-m-d',$et))) {
		log_txt(__function__.": дата конца не совпадает с указанной $d ($dt)  ".date2db($et));
		$et = strtotime($dt.' '.date('H:i:s',$et)); // дата конца не совпадает с указанной
		$mod = true;
	}
	if($bt < $wb) { // начало работ меньше мин. вр. начала
		log_txt(__function__.": начало работ меньше мин. вр. начала   ".date('H:i',$bt)."  ({$config['wo']['work_begin']})");
		$et = $et + ($wb - $bt);
		$bt = $wb;
		$mod = true;
	}
	if($et > $bt + $config['wo']['max_wo_exec']) { // вр. выполнения больше максимального
		$delta = $et - $bt;
		log_txt(__function__.": вр. выполнения больше максимального $delta ({$config['wo']['max_wo_exec']})  ".date('H:i',$bt)."  ".date('H:i',$et));
		$et = $bt + $config['wo']['max_wo_exec'];
		$mod = true;
	}
	if($et < $bt + $config['wo']['min_wo_exec']){ // вр. выполнения меньше минимального
		$delta = $et - $bt;
		log_txt(__function__.": вр. выполнения меньше минимального: $delta ({$config['wo']['min_wo_exec']})  ".date('H:i',$bt)."  ".date('H:i',$et));
		$et = $bt + $config['wo']['min_wo_exec'];
		$mod = true;
	}
	if($mod) {
		$r['begintime'] = date2db($bt);
		$r['endtime'] = date2db($et);
		return $r;
	}else
		return false;
}


function before_save_claimperform($cmp, $old, $my) {
	global $config, $tables, $q, $DEBUG, $worder_status;
	if(!$q) $q = new sql_query($config['db']);
	$match_time = '/\s*\b(\d?\d[:\-.]\d\d)\b[^\d]*$/';
	$id = $my->id; $key = $my->key;
	if(!isset($config['wo']['work_begin'])) $config['wo']['work_begin'] = '08:00';
	$min_wo_exec = ($config['wo']['min_wo_exec'])? $config['wo']['min_wo_exec'] : 900;
	$max_wo_exec = ($config['wo']['max_wo_exec'])? $config['wo']['max_wo_exec'] : 3600;
	$min_pause = ($config['wo']['min_pause'])? $config['wo']['min_pause'] : 0;
	if($DEBUG>0) log_txt(__function__.": start cmp: ".arrstr($cmp).(($DEBUG>1)?" old: ".arrstr($old):""));
	if((isset($cmp['begintime']) && !preg_match('/\b\d?\d[:\-]\d\d\b/',$cmp['begintime']))||
		(isset($cmp['endtime']) && !preg_match('/\b\d?\d[:\-]\d\d\b/',$cmp['endtime'])))
		stop(array('result'=>'ERROR','desc'=>'Неправильное значение времени!'));
	if(isset($cmp['begintime'])) $cmp['begintime'] = preg_replace('/\b(\d?\d)[:\-](\d\d)\b/','$1:$2',$cmp['begintime']);
	if(isset($cmp['endtime'])) $cmp['endtime'] = preg_replace('/\b(\d?\d)[:\-](\d\d)\b/','$1:$2',$cmp['endtime']);
	$r = array_merge(($old)?$old:array(),$cmp);
	if($id != 'new'){
		$wo = $q->get('workorders',$r['woid']);
		$wo_day = ($wo && $wo['prescribe']!='')? $wo['prescribe'] : date('Y-m-d');
		if(isset($cmp['content'])){
			$ot = preg_match($match_time,$old['content'],$m)? $m[1]:"";
			$nt = preg_match($match_time,$cmp['content'],$m)? $m[1]:"";
			if($ot!=$nt) stop(array('result'=>'ERROR','desc'=>'Время надо указывать в поле "начало"!'));
		}
		if(isset($cmp['status'])) {
			if($wo['status']>1) stop(array('result'=>"ERROR",'desc'=>"Наряд уже {$worder_status[$wo['status']]}!"));
			if($old['status']<$cmp['status'] && strtotime($wo_day)>=strtotime('tomorrow'))
				stop(array('result'=>"ERROR",'desc'=>"Это будущий наряд!"));
			if($cmp['status']==3){
				$noserv = $q->fetch_all("SELECT address FROM claims WHERE unique_id='{$r['cid']}' AND type=1 AND uid=0");
				if($noserv) stop(array('result'=>"ERROR",'desc'=>"Установка не произведена<br>Клиент не найден в базе!"));
			}
		}
		if(isset($cmp['begintime']) || isset($cmp['endtime'])) { // уст. параметров времени для изменённого задания
			$r['begintime'] = $wo_day." ".$r['begintime'];
			$r['endtime'] = $wo_day." ".$r['endtime'];
			if(isset($cmp['begintime'])) $cmp['begintime'] = $wo_day." ".$cmp['begintime'];
			if(isset($cmp['endtime'])) $cmp['endtime'] = $wo_day." ".$cmp['endtime'];
			if(isset($cmp['begintime']) && !isset($cmp['endtime'])){
				$cmp['endtime'] = $r['endtime'] = date2db(strtotime($cmp['begintime']) + (strtotime($old['endtime']) - strtotime($old['begintime'])));
			}
			if($tmp = adjustJobTime($r['begintime'],$r['endtime'],$wo_day)) $cmp = array_merge($cmp,$tmp); // коррекция времени по конфигурации
			$jobs = $q->select("SELECT * FROM claimperform WHERE woid = '{$r['woid']}' ORDER BY begintime");
			$jcount = count($jobs);
			$bt = isset($cmp['begintime'])? strtotime($cmp['begintime']) : strtotime($r['begintime']);
			$et = isset($cmp['endtime'])? strtotime($cmp['endtime']) : strtotime($r['endtime']);
			foreach($jobs as $k=>$j) { // ищем пересечение времени заданий
				if($j['unique_id']==$id) continue;
				$jbt = strtotime($j['begintime']); $jet = strtotime($j['endtime']);
				if(($jbt>=$bt && $jbt<=$et)||($jet>$bt && $jet<=$et)||($jbt<=$bt && $jet>=$et)) { $cross = $k; break; }
			}
			if(isset($cross)){
				for($i=$cross; $i<$jcount; $i++){
					if($jobs[$i]['unique_id']==$id) continue;
					$jbt = strtotime($jobs[$i]['begintime']); $jet = strtotime($jobs[$i]['endtime']);
					if($jbt < ($et + $min_pause)){
						$bt = $et + $min_pause;
						$et = $jet + ($et + $min_pause - $jbt);
						$q->update_record('claimperform',array($key=>$jobs[$i][$key],'begintime'=>date2db($bt),'endtime'=>date2db($et)));
					}
				}
			}
		}
	}
	if($id == 'new'){ // уст. параметров времени для нового задания
		$claim = $q->get('claims',$r['cid']);
		$wo = $q->get('workorders',$r['woid']);
		$wo_day = ($wo && $wo['prescribe']!='')? $wo['prescribe'] : date('Y-m-d');
		$jobs = $q->select("SELECT * FROM claimperform WHERE woid = '{$r['woid']}' ORDER BY begintime");
		$jcount = count($jobs);
		if(preg_match($match_time,$claim['content'],$m)){ // в содержимом встречается указание времени
			$cmp['begintime'] = $wo_day.' '.preg_replace('/\b(\d?\d)[:\-.](\d\d)\b/','$1:$2',$m[1]);
			$cmp['endtime'] = date('Y-m-d H:i',strtotime($cmp['begintime'])+$config['wo']['min_wo_exec']);
			$bt = strtotime($cmp['begintime']);
			$et = strtotime($cmp['endtime']);
			foreach($jobs as $k=>$j) { // ищем пересечение времени заданий
				$jbt = strtotime($j['begintime']); $jet = strtotime($j['endtime']);
				if(($jbt>=$bt && $jbt<$et)||($jet>$bt && $jet<=$et)||($jbt<=$bt && $jet>=$et)) { $cross = $k; break; }
			}
			if(isset($cross)) // если есть пересечения - сдвигаем задания пока есть пересечение
				for($i=$cross; $i<$jcount; $i++){
					$jbt = strtotime($jobs[$i]['begintime']); $jet = strtotime($jobs[$i]['endtime']);
					if($jbt < ($et + $min_pause)){
						$bt = $et + $min_pause;
						$et = $jet + ($et + $min_pause - $jbt);
						$q->update_record('claimperform',array($key=>$jobs[$i][$key],'begintime'=>date2db($bt),'endtime'=>date2db($et)));
					}
				}
		}else{
			$bt = strtotime($wo_day." ".$config['wo']['work_begin']);
			$et = strtotime($wo_day." ".$config['wo']['work_begin']) + $min_wo_exec;
			if($jobs){ // ищем свободные интервалы в расписании и вставляем новое задание в подходящий интервал
				for($i=0; $i<$jcount; $i++) {
					if($i>0){
						$oet = strtotime($jobs[$i-1]['endtime']);
						$jbt = strtotime($jobs[$i]['begintime']);
						if($jbt - $oet >= $min_wo_exec + $min_pause * 2){
							$bt = $oet + $min_pause;
							$et = $bt + $min_wo_exec;
							break;
						}
					}
				}
				if($i >= $jcount) { $bt = strtotime($jobs[$i-1]['endtime']) + $min_pause; $et = $bt + $min_wo_exec; }
			}
			$cmp['begintime'] = strftime('%Y-%m-%d %H:%M',$bt);
			$cmp['endtime'] = strftime('%Y-%m-%d %H:%M',$et);
		}
	}
	if(isset($cmp['content'])) {
		$ctable = $tables['claims'];
		$form = new Form($config);
		if(!$r['cid']) log_txt(__function__.": Не определён cid! r=".arrstr($r));
		$new = array('unique_id'=>$r['cid'],'content'=>$cmp['content']);
		$form->save($ctable,$new);
	}
	if(isset($cmp['content'])) unset($cmp['content']);
	if($DEBUG>0) log_txt(__function__.": out: ".arrstr($cmp));
	return $cmp;
}

function check_claimperform_for_save($r) {
	global $config, $errors;
	return true;
}

function onsave_claimperform($id,$save,$my) {
	global $config, $opdata, $claim_types, $errors, $DEBUG, $q;
	if($DEBUG>0) log_txt(__function__.": ".arrstr($save));
	if(!$q) $q = new sql_query($config['db']);
	$id = $my->id; $key = $my->key; $old = $my->row; $r = array_merge($old,$save);
	$out = false;
	if(is_numeric($id) && (isset($save['begintime']) || isset($save['endtime']))) $wo = $q->get('workorders',$r['woid']);
	if(!isset($save['status']) && $my->id == 'new') $save['status'] = 0;
	if(isset($save['status'])){
		$claim = $q->get('claims',$r['cid']);
		$fld = array('cid','woid');
		foreach($fld as $n) if(!isset($r[$n])) $err[] = "'$n'";
		if(isset($err))
			log_txt(__function__.": ERROR: отсустствуют поля ( ".implode(', ',$err)." ) r=".arrstr($r)." s=".arrstr($save));
		else{
			$wo = $q->get('workorders',$r['woid']);
			if($save['status']<2) {
				$q->update_record('claims', $a = array('unique_id'=>$r['cid'],'status'=>2,'woid'=>$r['woid'],'perform_operator'=>''));
			}elseif($save['status']==2) {
				$q->update_record('claims', $a = array('unique_id'=>$r['cid'],'status'=>0,'woid'=>null,'perform_operator'=>''));
			}elseif($save['status']==3) {
				$q->update_record('claims', $a = array('unique_id'=>$r['cid'],'status'=>4,'perform_operator'=>$opdata['login']));
			}
			if(isset($a)) dblog('claims',$claim,$a);
		}
	}
	if($DEBUG>0 && isset($a)) log_txt(__function__.": claim[{$a['unique_id']}] status = {$a['status']}");
	if($my->id == 'new'){
		$s=$claim; $s['type'] = $claim_types[$claim['type']];
		send_notify('new_job',array_merge($s,$r));
	}
	if(isset($wo)){ // поменялись данные наряда -> отослать данные по всем заданиям
		$out['delete'] = $q->fetch_all("SELECT unique_id FROM claimperform WHERE woid='{$r['woid']}'");
		$sql = preg_replace('/:FILTER:/',"AND cp.woid = '{$r['woid']}'",$my->table_query); 
		$out['append'] = $q->select($sql,5); // выборка заданий
		if($out['append']){
			$qf = $q->sql_fields();
			foreach($out['append'] as $k=>$job) {
				foreach($job as $n=>$v){
				if(isset($my->cfg['table_triggers'][$qf[$n]]) && function_exists($my->cfg['table_triggers'][$qf[$n]]))
					$out['append'][$k][$n] = $my->cfg['table_triggers'][$qf[$n]]($v,null);
				}
			}
		}
		$out['result'] = 'OK';
	}
	return $out;
}

function before_delete_claimperform($old) {
	global $config, $_REQUEST, $tables, $claim_types;
	$q=new sql_query($config['db']);
	$claim = $q->get('claims',$old['cid']);
	$wo = $q->get('workorders',$old['woid']);
	$q->update_record('claims', $a = array('unique_id'=>$old['cid'],'status'=>0,'woid'=>null));
	dblog('claims',$claim,$a);
	return $old;
}

function allow_delete_claimperform($id,$my) {
	global $config, $q, $worder_status, $clperf_status;
	if(!$q) $q = new sql_query($config['db']);
	if(!is_numeric($id) || $id == 0) {
		log_txt(__function__.": ERROR not found 'id'");
		stop(array('result'=>'ERROR', 'desc'=>"Не найден номер задания!"));
	}
	$job = $q->select("SELECT * FROM claimperform WHERE unique_id='$id'",1);
	$wo = $q->get('workorders',$job['woid']);
	if($wo['status']>1) return "Удаление невозможно! Наряд уже ".@$worder_status[$wo['status']].".";
	if($job['status']>1) return "Удаление невозможно! Задание уже ".@$clperf_status[$job['status']].".";
	return 'yes';
}
?>
