<?php
include_once("map.cfg.php");
include_once("workpeople.cfg.php");
include_once("classes.php");
include_once("form.php");

$in['go'] = (isset($_REQUEST['go']))? strict($_REQUEST['go']) : 'workpeople';
$in['do'] = (isset($_REQUEST['do']))? strict($_REQUEST['do']) : '';
$in['id'] = (isset($_REQUEST['id']))? numeric($_REQUEST['id']) : 0;
$pers = (isset($_REQUEST['pers']))? preg_replace('/[^0-9,]/','',$_REQUEST['pers']) : '';
$in['pers'] = ($pers)? preg_split('/,/',$pers) : array();

$q = new sql_query($config['db']);
$t = $tables['workpeople'];
$t['name']='workpeople';

switch($in['do']){

	case 'get':
		if($in['id']!='new'){
			$wo = $q->get("workorders",$in['id']);
			if(!$wo) stop(array('result'=>'ERROR','desc'=>"Наряд {$in['id']} не найден в базе!"));
		}else{
			$wo = array('operator'=>$opdata['login'],'status'=>0,'createtime'=>date2db(),'prescribe'=>date2db(false,false));
			$wo['id'] = $q->insert('workorders',$wo);
			$newwo = array_merge($wo,array('status'=>'_0','manager'=>'_0','prescribe'=>cyrdate($wo['prescribe'])));
			log_txt("Создал наряд N {$wo['id']} назначен на ".cyrdate($wo['prescribe']));
		}
		if($wo['status']>=2) stop(array('result'=>'ERROR','desc'=>"Наряд уже {$worder_status[$wo['status']]}!"));
		$filter = (count($in['pers'])>0)? "OR eid in (".implode(',',$in['pers']).")" : "";
		if(!($employers = $q->select("SELECT * FROM employers WHERE blocked=0 $filter"))){
			stop(array('result'=>'ERROR','desc'=>"Служащие не найдены"));
		}
		foreach($employers as $k=>$v) $e[] = array(
				'id'=>$v['eid'],
				'fio'=>shortfio($v['fio']),
				'photo'=>photo_link($v['photo'])
		);
		$out = array('result'=>'OK','form'=>array(
			'name'=>'employers',
			'id'=>'',
			'class'=>'normal',
			'style'=>'width:680px;height:230px',
			'fields'=>array(
				'employers'=>array(
					'label'=>' ',
					'type'=>'photoselect',
					'class'=>'photoselect',
					'style'=>'width:640px;',
					'list'=>$e,
					'size'=>'110x135',
					'value'=>implode(',',$in['pers'])
				),
				'go'=>array(
					'type'=>'hidden',
					'value'=>'workpeople'
				),
				'do'=>array(
					'type'=>'hidden',
					'value'=>'save'
				),
				'worder'=>array(
					'type'=>'hidden',
					'value'=>$wo['woid']
				),
			),
			'layout'=>array(
				'ephoto'=>array(
					'legend'=>'Служащие',
					'type'=>'fieldset',
					'fields'=>array('employers')
				)
			)
		));
		if(isset($newwo)) $out['form']['setNewVal'] = $newwo;
		stop($out);
		break;

	case 'add':
	case 'new':
	case 'edit':
		$wo = $q->get("workorders",$in['id']);
		if(!$wo) stop(array('result'=>'ERROR','desc'=>"Наряд {$in['id']} не найден в базе!"));
		$selected = $q->fetch_all("SELECT employer FROM workpeople WHERE worder = '{$wo['woid']}'");
		$filter = ($selected)? "OR eid in (".implode(',',$selected).")" : "";
		if(!($employers = $q->select("SELECT * FROM employers WHERE blocked=0 $filter"))){
			stop(array('result'=>'ERROR','desc'=>"Служащие не найдены"));
		}
		foreach($employers as $k=>$v) $e[] = array(
			'id'=>$v['eid'],
			'fio'=>shortfio($v['fio']),
			'photo'=>photo_link($v['photo'])
		);
		stop(array('result'=>'OK','form'=>array(
			'name'=>'employers',
			'id'=>'',
			'class'=>'normal',
			'style'=>'width:680px;height:230px',
			'fields'=>array(
				'employers'=>array(
					'label'=>' ',
					'type'=>'photoselect',
					'class'=>'photoselect',
					'style'=>'width:640px;',
					'list'=>$e,
					'size'=>'110x135',
					'value'=>implode(',',$selected)
				),
				'go'=>array(
					'type'=>'hidden',
					'value'=>'workpeople'
				),
				'do'=>array(
					'type'=>'hidden',
					'value'=>'save'
				),
				'worder'=>array(
					'type'=>'hidden',
					'value'=>$wo['woid']
				),
			),
			'layout'=>array(
				'ephoto'=>array(
					'legend'=>'Служащие',
					'type'=>'fieldset',
					'fields'=>array('employers')
				)
			)
		)));
		break;

	case 'save':
		if(!isset($_REQUEST['worder'])) stop(array('result'=>'ERROR','desc'=>'Не указан наряд!'));
		if(!isset($_REQUEST['employers'])) stop(array('result'=>'ERROR','desc'=>'Ошибка полученных данных!'));
		$worder = numeric($_REQUEST['worder']);
		$old_employers = isset($_REQUEST['old_employers'])? preg_replace('/[^0-9,]/','',$_REQUEST['old_employers']) : '';
		$WP = preg_split('/,/',$old_employers);
		$e = (isset($_REQUEST['employers']))? preg_replace('/[^0-9,]/','',$_REQUEST['employers']) : '';
		$employers = ($e)? preg_split('/,/',$e) : array();
		$cmp = array_diff($WP,$employers);
		$cmp1 = array_diff($employers,$WP);
		$ins = array();
		if(count($cmp1)>0) foreach($cmp1 as $k=>$v) $ins[] = array('worder'=>$worder,'employer'=>$v);
		$e = (count($cmp1)>0)? $q->select("SELECT eid as id, fio, photo FROM employers WHERE eid in (".implode(',',$cmp1).")") : array();
		foreach($e as $k=>$v) {
			$e[$k]['fio'] = shortfio($v['fio']);
			$e[$k]['photo'] = photo_link($v['photo']);
		}
		stop(array('result'=>'OK','_del'=>$cmp, '_add'=>$e));
		break;

	case 'delete':
	case 'remove':
	case 'realremove':
		stop(array('result'=>'OK','desc'=>'No operation!'));
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
