<?php
include_once("classes.php");
include_once("form.php");
include_once("claims.cfg.php");

$tname = 'claims';
$q = new sql_query($config['db']);
$t = $tables[$tname];

$in['go'] = (isset($_REQUEST['go']))? strict($_REQUEST['go']) : 'claims';
$in['do'] = (isset($_REQUEST['do']))? strict($_REQUEST['do']) : '';
$in['uid'] = (isset($_REQUEST['uid']))? numeric($_REQUEST['uid']) : false;
$in['user'] = (isset($_REQUEST['user']))? numeric($_REQUEST['user']) : false;
if($in['user'] && !$in['uid']) $in['uid'] = $q->select("SELECT uid FROM users WHERE user='{$in['user']}'",4);
$in['id'] = (isset($_REQUEST['id']))? numeric($_REQUEST['id']) : 0;
if(@$_REQUEST['id']=='new') $in['id'] = 'new';
$in['unique_id'] = (isset($_REQUEST['unique_id']))? flt($_REQUEST['unique_id']) : 0;
if($in['unique_id']>0) $in['id'] = $in['unique_id'];
$in['begin'] = (isset($_REQUEST['begin']))? date2db($_REQUEST['begin']) : strftime('%Y-%m-01',strtotime('-1 year'));
$in['end'] = (isset($_REQUEST['end']))? date2db($_REQUEST['end']) : strftime('%Y-%m-%d',strtotime('1 day'));
$in['sort'] = (isset($_REQUEST['sort']))? strict($_REQUEST['sort']) : 'claimtime';
$in['limit'] = (isset($_REQUEST['limit']) && $_REQUEST['limit']!='no')? 'yes' : 'no';
$in['pagerows'] = (isset($_REQUEST['pagerows']))? numeric($_REQUEST['pagerows']) : 0;
$in['page'] = (isset($_REQUEST['page']))? numeric($_REQUEST['page']) : 1;
$in['operator'] = (isset($_REQUEST['operator']))? strict($_REQUEST['operator']) : false;
$in['status'] = (isset($_REQUEST['status']))? preg_replace('[^0-9]','',$_REQUEST['status']) : '0';
$in['type'] = (isset($_REQUEST['type']))? preg_replace('[^0-9]','',$_REQUEST['type']) : '';
$claims = isset($_REQUEST['claims'])? preg_replace('/[^0-9,]/','',$_REQUEST['claims']) : false;
$note = isset($_REQUEST['note'])? str($_REQUEST['note']) : "";

$form = new form($config);

switch($in['do']) {

	case 'get':
		$t['id'] = $in['id'];
		$t['name'] = $tname;
		if($in['pagerows']>0) {
			$t['limit'] = 'yes';
			$t['currentpage'] = $in['page'];
			$t['pagerows'] = $in['pagerows'];
		}
		$t['begin'] = $in['begin'];
		$t['end'] = $in['end'];
		$t['sort'] = $in['sort'];
		foreach($t['fields'] as $k=>$v) if(isset($v['style'])) unset($t['fields'][$k]['style']);
		$t['fields']['type']['style'] = 'width:105px';
		$t['fields']['claimtime']['style'] = 'width:100px';
		$t['fields']['status']['style'] = 'width:70px';
		$t['fields']['address']['style'] = 'width:250px';
		$claims = new Table($t);
		foreach($in as $k=>$v) if($v === false) unset($in[$k]);
		$out = array('result'=>'OK', 'table'=>$claims->get());
		stop($out);
		break;

	case 'add':
	case 'realnew':
	case 'new':
		$out = $form->getnew($t);
// 		if($_REQUEST['do']=='realnew') $out['nosubmit'] = true;
		stop($out);
		break;

	case 'edit':
		$out = $form->get($t);
		stop($out);
		break;

	case 'realsave':
	case 'save':
		$out = $form->save($t);
		stop($out);
		break;

	case 'delete':
	case 'remove':
		if($claim = $q->select("SELECT * FROM claims WHERE unique_id={$in['id']}",1)){
			if(isset($t['allow_delete']) && function_exists($t['allow_delete']) && ($a = $t['allow_delete']($claim))!='yes')
				stop(array('result'=>'ERROR','desc'=>$a));
			stop($form->confirmForm($in['id'],'realremove',"Вы действительно хотите удалить это заявление?"));
		}else{
			stop(array('result'=>'ERROR','desc'=>'Это заявление отсутствует в базе!'));
		}
		break;

	case 'realremove':
		stop($form->delete($t));
		break;

	case 'reject':
		if(!$claims) stop(array('result'=>'ERROR','desc'=>'Не определён список заявок!'));
		stop(array('result'=>'OK', 'form'=>array(
			'name'=>'confirm',
			'class'=>'normal',
			'style'=>'width:280px',
			'header'=>"причина отказа",
			'fields'=>array(
				'go'=>array('type'=>'hidden','value'=>"claims"),
				'do'=>array('type'=>'hidden','value'=>"realreject"),
				'claims'=>array('type'=>'hidden','value'=>$claims),
				'note'=>array('type'=>'textarea','value'=>"",'style'=>'width:250px;height:80px'),
			)
		)));
		break;

	case 'close':
		if($in['id']>0 && !$claims) $claims = $in['id'];
		if(!$claims) stop(array('result'=>'ERROR','desc'=>'Не определён список заявок!'));
		if(!($cl = $q->select("SELECT * FROM claims WHERE unique_id in ($claims)")))
			stop(array('result'=>'ERROR','desc'=>'Заявки не найдены!'));
		foreach($cl as $c) if($c['status']>1) 
			stop(array('result'=>"ERROR",'desc'=>"Указанные заявления уже отработаны!"));;
		stop(array('result'=>'OK', 'form'=>array(
			'name'=>'confirm',
			'class'=>'normal',
			'style'=>'width:280px',
			'header'=>"причина закрытия",
			'fields'=>array(
				'go'=>array('type'=>'hidden','value'=>"claims"),
				'do'=>array('type'=>'hidden','value'=>"realclose"),
				'claims'=>array('type'=>'hidden','value'=>$claims),
				'note'=>array('type'=>'textarea','value'=>"",'style'=>'width:250px;height:80px'),
			)
		)));
		break;

	case 'hold':
		$status = 1;
		if($in['id']>0 && !$claims) $claims = $in['id'];
		if(!$claims) stop(array('result'=>'ERROR','desc'=>'Не определён список заявок!'));
		if(!($cl = $q->select("SELECT * FROM claims WHERE unique_id in ($claims)")))
			stop(array('result'=>'ERROR','desc'=>'Заявки не найдены!'));
		foreach($cl as $c) if($c['status']>1) 
			stop(array('result'=>"ERROR",'desc'=>"Указанные заявления уже отработаны!"));;
		if($cl[0]['status'] == 1) $status = 0;
		if(!$q->query("UPDATE claims SET status=$status, perform_note='$note', perform_operator='{$opdata['login']}' WHERE unique_id in ($claims)"))
			stop(array('result'=>'ERROR','desc'=>'Ошибка обработки данных!'));
		stop(array('result'=>'OK','reload'=>1));
		break;

	case 'user':
		if($in['id']==0) stop(array('result'=>'ERROR','desc'=>'Не указано заявление!'));
		if(!($cl = $q->select("SELECT * FROM claims WHERE unique_id='{$in['id']}'",1)))
			stop(array('result'=>'ERROR','desc'=>'Заявление не найдено!'));
		if(!$cl['user'] || !$cl['uid']) stop(array('result'=>'ERROR','desc'=>'Заявление не содержит данные пользователя!'));
		if($cl['uid']>0) $add = "&uid=".$cl['uid'];
		else $add = "&user=".$cl['user'];
		stop(array('result'=>'USER','action'=>'reload','to'=>"users.php?go=usrstat".$add));
		break;

	case 'realreject':
		if(!$claims) stop(array('result'=>'ERROR','desc'=>'Не определён список заявок!'));
		if(!($cl = $q->select("SELECT * FROM claims WHERE unique_id in ($claims)")))
			stop(array('result'=>'ERROR','desc'=>'Заявки не найдены!'));
		$claims = array();
		foreach($cl as $k=>$c){
			if($c['status']>1) stop(array('result'=>'ERROR','desc'=>'Заявки уже отработаны!'));
			$claims[] = array('unique_id'=>$c['unique_id'],'status'=>3, 'perform_note'=>$note, 'perform_operator'=>$opdata['login']);
		}
		if(count($claims)>0) foreach($claims as $c) $form->save($t,$c);
		stop(array('result'=>'OK','reload'=>1));
		break;

	case 'realclose':
		if(!$claims) stop(array('result'=>'ERROR','desc'=>'Не определён список заявок!'));
		if(!($cl = $q->select("SELECT * FROM claims WHERE unique_id in ($claims)")))
			stop(array('result'=>'ERROR','desc'=>'Заявки не найдены!'));
		$claims = array();
		foreach($cl as $k=>$c){
			if($c['status']>1) stop(array('result'=>'ERROR','desc'=>'Заявки уже отработаны!'));
			$claims[] = array('unique_id'=>$c['unique_id'],'status'=>5, 'perform_note'=>$note, 'perform_operator'=>$opdata['login']);
		}
		if(count($claims)>0) foreach($claims as $c) $form->save($t,$c);
		stop(array('result'=>'OK','reload'=>1));
		break;

	case 'auto_address':
	case 'auto_user':
	case 'auto_fio':
		$n = preg_replace('/auto_/','',$in['do']);
		if(function_exists($t['form_autocomplete'][$n])) stop($t['form_autocomplete'][$n]());
		else stop("Не найдена функция!");
		break;

	case 'rayon_xy':
		if(!($in['id']>0)) stop(array('result'=>'ERROR','desc'=>'Не указан район!'));
		$out['result'] = 'OK';
		$rayon = $q->get("rayon",$in['id']);
		if($rayon['latitude'] == '' || $rayon['longitude'] =='') $out['desc'] = "Не найдены координаты района!";
		else $out['rayon'] = "{$rayon['latitude']},{$rayon['longitude']},{$rayon['zoom']}";
		stop($out);
		break;

	case 'get4map':
		$out['result'] = 'OK';
		array_push($claim_types,'отложено');
		$out['claimtypes'] = $claim_types;
		$out['claimicons'] = array('gray','green','red','violet','deepblue','blue','gray');
		$out['claims'] = $q->select("
			SELECT c.unique_id as id, if(c.status=1,".(count($out['claimicons'])-1).",c.type) as ctype, c.fio, c.address, c.location, c.content,
				w.woid, w.prescribe, DATE_FORMAT(p.begintime,'%H:%i') as begintime
			FROM claims c
				LEFT OUTER JOIN claimperform p ON c.unique_id=p.cid AND p.status<2
				LEFT OUTER JOIN workorders w ON p.woid=w.woid AND w.status<2
			WHERE c.status<3 AND c.location != ''
		");
		foreach($out['claims'] as $k=>$c) {
			if($c['woid']==0) $out['claims'][$k]['woid'] = null;
			$out['claims'][$k]['prescribe'] = $c['prescribe']? cyrdate($c['prescribe'],'%d %B') : '';
		}
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
