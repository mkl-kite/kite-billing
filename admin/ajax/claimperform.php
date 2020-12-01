<?php
include_once("classes.php");
include_once("form.php");
include_once("claimperform.cfg.php");
$tname = 'claimperform';

$in['go'] = (isset($_REQUEST['go']))? strict($_REQUEST['go']) : 'claims';
$in['do'] = (isset($_REQUEST['do']))? strict($_REQUEST['do']) : '';
$in['id'] = (isset($_REQUEST['id']))? numeric($_REQUEST['id']) : 0;
$in['woid'] = (isset($_REQUEST['woid']))? numeric($_REQUEST['woid']) : 0;
$in['unique_id'] = (isset($_REQUEST['unique_id']))? flt($_REQUEST['unique_id']) : 0;
if($in['unique_id']>0) $in['id'] = $in['unique_id'];

$q = new sql_query($config['db']);
$t = $tables[$tname];
$t['name'] = $tname;
// if($in['id']>0) $t['header'] = "Задание к наряду &#8470; ".$in['id'];
$form = new form($config);

switch($in['do']) {

	case 'add':
	case 'new':
		if($in['id']!='new'){
			$wo = $q->get("workorders",$in['id']);
			if(!$wo) stop(array('result'=>'ERROR','desc'=>"Наряд {$in['id']} не найден в базе!"));
			$id = $wo['woid'];
		}else{
			$wo = array('operator'=>$opdata['login'],'status'=>0,'createtime'=>date2db(),'prescribe'=>date2db(false,false));
			$id = $wo['id'] = $q->insert('workorders',$wo);
			$newwo = array_merge($wo,array('status'=>'_0','manager'=>'_0'));
			log_txt("Создал наряд N {$wo['id']}");
		}
		if(!$wo) stop(array('result'=>'ERROR','desc'=>"Наряд {$in['id']} не найден в базе!"));
		if($wo['status']>=2) stop(array('result'=>'ERROR','desc'=>"Наряд уже {$worder_status[$wo['status']]}!"));
		$claims = $q->select("
			SELECT 
				unique_id as id, 
				type, 
				claimtime,
				r_name as rayon, 
				address,
				content
			FROM claims c, rayon r 
			WHERE c.rid=r.rid AND c.status=0
			ORDER BY rayon, type, address
		");
		if(!$claims) stop(array('result'=>'ERROR','desc'=>"В базе отсутствуют открытые заявки!"));
		foreach($claims as $k=>$v) {
			$claims[$k] = array(
				'id'=>$v['id'],
				'label'=>
					'<span style="width:90px">'.$claim_types[$v['type']].'</span>'.
					'<span style="width:120px">'.$v['address'].'</span>'.
					'<span style="width:150px">'.$v['rayon'].'</span>'.
					'<span style="width:300px">'.$v['content'].'</span>',
				'value'=>0
			);
		}

		$out = array('result'=>'OK','form'=>array(
			'name'=>'claimperform',
			'id'=>'',
			'class'=>'normal',
			'style'=>'width:780px;height:450px',
			'fields'=>array(
				'claims'=>array(
					'label'=>' ',
					'type'=>'checkset',
					'style'=>'width:750px;height:380px',
					'list'=>array(
						'opt'=>array('id'=>'chkset','class'=>'chsclass'),
						'set'=>$claims
					),
					'value'=>""
				),
				'go'=>array(
					'type'=>'hidden',
					'value'=>'claimperform'
				),
				'do'=>array(
					'type'=>'hidden',
					'value'=>'addclaims'
				),
				'worder'=>array(
					'type'=>'hidden',
					'value'=>$id
				),
			),
			'layout'=>array(
				'cl'=>array(
					'legend'=>'Открытые заявки',
					'type'=>'fieldset',
					'fields'=>array('claims')
				)
			)
		));
		if(isset($newwo)) $out['form']['setNewVal'] = $newwo;
		stop($out);
		break;

	case 'addclaims':
		$claims = (isset($_REQUEST['claims']))? preg_split('/,/',preg_replace('/[^0-9,]/','',$_REQUEST['claims'])) : '';
		if(!is_array($claims) || count($claims)==0) return(array('result'=>'ERROR','desc'=>"Задания не выбраны!"));
		$woid = (is_numeric($_REQUEST['worder']))? numeric($_REQUEST['worder']) : false;
		if(!$woid) return(array('result'=>'ERROR','desc'=>"Наряд ($woid) не найден!"));
		foreach($claims as $k=>$v){
			 $form->save($t,array('unique_id'=>'new','cid'=>$v,'woid'=>$woid),array('cid'=>'','woid'=>''));
		}
		$t=array_merge($t,array(
			'type'=>'table',
			'limit'=>'no',
			'filter'=>"AND cp.woid='$woid' AND cp.cid in (".implode(',',$claims).")",
		));
		$c = new Table($t);
		stop(array('result'=>'OK','append'=>$c->data));
		break;

	case 'get':
		$wo = $q->select("SELECT o.* FROM workorders o, claimperform p WHERE o.woid=p.woid AND p.unique_id='{$in['id']}'");
		if($wo['status']>1) stop(array('result'=>'ERROR','desc'=>"Наряд {$in['id']} уже ".$worder_status[$wo['status']]." !"));
		$t['filter'] = "AND cp.woid=".$in['id'];
		$t['table_key'] = 'id';
		$table = new Table($t);
		stop(array('result'=>'OK','table'=>$table->get()));
		break;
	
	case 'edit':
		stop($form->get($t));
		break;

	case 'save':
		$t['id'] = $in['id'];
		stop($form->save($t));
		break;

	case 'delete':
	case 'remove':
		$t['id'] = $in['id'];
		$cp = $q->select("SELECT * FROM `{$t['name']}` WHERE {$t['key']}='{$in['id']}'",1);
		$wo = $q->get("workorders",$cp['woid']);
		if(!$wo) stop(array('result'=>'ERROR','desc'=>"Наряд {$in['id']} не найден в базе!"));
		if($wo['status']>=2) stop(array('result'=>'ERROR','desc'=>"Наряд уже {$worder_status[$wo['status']]}!"));
		if($cp){
			stop($form->confirmForm($in['id'],'realremove',"Вы действительно хотите удалить это задание?"));
		}else{
			stop(array('result'=>'ERROR','desc'=>'Это задание отсутствует в базе!'));
		}
		break;

	case 'realremove':
		$t['id'] = $in['id'];
		stop($form->delete($t));
		break;

	case 'user':
		if($in['id']==0) stop(array('result'=>'ERROR','desc'=>'Не указано задание!'));
		if(!($cp = $q->select("SELECT * FROM claimperform WHERE unique_id='{$in['id']}'",1)))
			stop(array('result'=>'ERROR','desc'=>'Задание не найдено!'));
		if(!($cl = $q->select("SELECT * FROM claims WHERE unique_id='{$cp['cid']}'",1)))
			stop(array('result'=>'ERROR','desc'=>'Заявление не найдено!'));
		if(!$cl['user'] || !$cl['uid']) stop(array('result'=>'ERROR','desc'=>'Заявление не содержит данные пользователя!'));
		if($cl['uid']>0) $add = "&uid=".$cl['uid'];
		else $add = "&user=".$cl['user'];
		stop(array('result'=>'USER','action'=>'reload','to'=>"users.php?go=usrcard".$add));
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
