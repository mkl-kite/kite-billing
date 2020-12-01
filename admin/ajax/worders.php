<?php
include_once("classes.php");
include_once("form.php");
include_once("workorders.cfg.php");

$tname = 'workorders';
$q = new sql_query($config['db']);
$t = $tables[$tname];

$in['go'] = (isset($_REQUEST['go']))? strict($_REQUEST['go']) : 'claims';
$in['do'] = (isset($_REQUEST['do']))? strict($_REQUEST['do']) : '';
$in['id'] = (isset($_REQUEST['id']))? numeric($_REQUEST['id']) : 0;
$in['woid'] = (isset($_REQUEST['woid']))? numeric($_REQUEST['woid']) : 0;
$in['begin'] = (isset($_REQUEST['begin']))? cyrdate($_REQUEST['begin']) : strftime('%Y-%m-%d 00:00:00');
$in['end'] = (isset($_REQUEST['end']))? cyrdate($_REQUEST['end']) : strftime('%Y-%m-%d 23:59:59',strtotime('7 day'));
$in['sort'] = (isset($_REQUEST['sort']))? strict($_REQUEST['sort']) : 'claimtime';
$in['limit'] = (isset($_REQUEST['limit']) && $_REQUEST['limit']!='no')? 'yes' : 'no';
$in['pagerows'] = (isset($_REQUEST['pagerows']))? numeric($_REQUEST['pagerows']) : 0;
$in['page'] = (isset($_REQUEST['page']))? numeric($_REQUEST['page']) : 1;
//$out['log'][] = "worders: in[begin]={$in['begin']} in[end]={$in['end']}";

switch($in['do']) {
	case 'get':
		if(!($result = $q->select("SELECT woid, date_format(prescribe,'%W %d-%m-%Y') as prescribe FROM workorders WHERE prescribe between '{$in['begin']}' and '{$in['end']}' ".$sql_add))) {
			stop(array('result'=>'ERROR','desc'=>'нет данных по указанным дням!'));
		}
		$orders=array();
		foreach($result as $k=>$r) {
			$employers = $q->select("SELECT e.eid as id, e.fio, e.photo FROM employers e, workpeople p WHERE eid=p.employer AND p.worder='{$r['woid']}'");
			foreach($employers as $k=>$v){
				$employers[$k]['fio'] = shortfio($v['fio']);
				$employers[$k]['photo'] = photo_link($v['photo']);
			}
			if(!$employers) $employers = array();

			$jobs = $q->fetch_all("
				SELECT 
					cp.unique_id,
					cp.cid,
					cp.status,
					cp.begintime,
					cp.endtime,
					c.type,
					c.status as claimstatus,
					c.address,
					c.content
				FROM 
					claims as c,
					claimperform as cp
				WHERE
					c.unique_id=cp.cid and
					cp.woid={$r['woid']}",'unique_id'
			);

			$r['employers']=$employers;
			$r['cids']=$jobs;
			$orders[$r['prescribe']][$r['woid']]=$r;
		}
		$out['result']='OK';
		$out['orders']=$orders;
		stop($out);
		break;

	case 'list':
		if(!isset($_REQUEST['claims']) || ($claims = preg_replace('/[^0-9,]/','',$_REQUEST['claims']))=='')
			stop(array('result'=>'ERROR','desc'=>'Не указаны заявки!'));
		$claims = preg_split('/,/',$claims);
		if($q->select("SELECT count(*) FROM claims WHERE unique_id in (".implode(',',$claims).") AND status>1",4)>0)
			stop(array('result'=>'ERROR','desc'=>'Указанные заявления уже в отработаны!'));
		$r = $q->fetch_all("
			SELECT o.woid as id, o.woid as `order`, prescribe,
				group_concat(distinct p.employer) as employers,
				group_concat(distinct c.address) as `claims`
			FROM workorders o 
				LEFT OUTER JOIN workpeople p ON o.woid=p.worder
				LEFT OUTER JOIN claimperform cp ON o.woid=cp.woid
				LEFT OUTER JOIN claims c ON cp.cid=c.unique_id
			WHERE o.status<2
			GROUP BY o.woid
			ORDER BY prescribe
		",'id');
		if(!$r) stop(array('result'=>'ERROR','desc'=>'Не найдено открытых нарядов!'));
		foreach($r as $k=>$v){
			$e[] = $v['employers']; $cl=array();
			foreach(preg_split('/,/',$v['claims']) as $d) $cl[] = "<span>$d</span>";
			$r[$k]['claims'] = implode(', ',$cl);
		}
		$emp = array_unique(preg_split('/,/',implode(',',$e)));
		foreach($emp as $k=>$m) if($m == '') unset($emp[$k]);
		$e = $q->fetch_all("SELECT * FROM employers WHERE eid in (".implode(',',$emp).")",'eid');
		foreach($e as $k=>$v){
			$e[$k]['fio'] = shortfio($v['fio']);
			$e[$k]['photo'] = photo_link($v['photo']);
		}

		$a=array();
		foreach($r as $k=>$v){
			$html = '';
			$emp = preg_split('/,/',$v['employers']);
			foreach($emp as $eid) $html .= ($e[$eid])? "<div class=\"photoitem\" style=\"width:55px;height:70px\"><img class=\"photo\" src=\"{$e[$eid]['photo']}\"><div class=\"title\">{$e[$eid]['fio']}</div></div>":"";
			$v['employers'] = "<div type=\"photolist\" style=\"min-width:110px\">$html</div>";
			$dt = preg_split('/,/',cyrdate($v['prescribe'],'%u,%d,%B,%A'));
// 			log_txt(" dt=".arrstr($dt));
			$v['prescribe'] = "<div class=\"day\">{$dt[1]}</div><div class=\"month\">{$dt[2]}</div><div class=\"wday\">{$dt[3]}</div>";
			$a['_'.$k]  = 
			"<div class=\"order\">{$v['order']}</div>".
			"<div class=\"date\" ".(($dt[0]>5)? 'style="color:red"' : '').">{$v['prescribe']}</div>".
			"<div class=\"employers\">{$v['employers']}</div>".
			"<div class=\"claims\">{$v['claims']}</div>";
		}
		$ls=array(
			'result'=>'OK',
			'form'=>array(
				'name'=>'woselect',
				'class'=>'normal',
				'style'=>'width:600px',
				'fields'=>array(
					'go'=>array('type'=>'hidden','value'=>'stdform'),
					'do'=>array('type'=>'hidden','value'=>'edit'),
					'table'=>array('type'=>'hidden','value'=>'workorders'),
					'id'=>array(
						'label'=>'',
						'type'=>'artselect',
						'list'=>$a,
						'style'=>'width:600px;max-height:600px;overflow-y:scroll',
						'value'=>''
					),
					'claims'=>array(
						'label'=>'',
						'type'=>'hidden',
						'value'=>$_REQUEST['claims'],
					)
				),
				'id'=>'worder_1',
				'footer'=>array('cancelbutton'=>array('txt'=>'отменить')),
			)
		);
		stop($ls);
		break;

	case 'get_employers':
		if($in['woid']>0) $wdate = $q->select("SELECT prescribe FROM workorders WHERE woid='{$in['woid']}'",4);
		else $wdate = date('Y-m-d');
		$employers = $q->fetch_all("
			SELECT eid, fio FROM (SELECT eid, fio, worder FROM employers e 
			LEFT OUTER JOIN workpeople wp ON wp.employer=e.eid AND 
			wp.worder IN (SELECT woid FROM workorders WHERE prescribe='$wdate') 
			WHERE e.blocked=0 HAVING worder is null) as tmp;
		",'eid');
		$employers = array_merge(array(''), $employers);
		foreach($employers as $id=>$name) $employers[$id] = shortfio($name);
		$out['result']='OK';
		$out['employers']=$employers;
		stop($out);
		break;
}
?>
