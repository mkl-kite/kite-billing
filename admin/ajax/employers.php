<?php
include_once("classes.php");
include_once("form.php");
include_once("employers.cfg.php");

$tname = 'employers';
$q = new sql_query($config['db']);
$t = $tables[$tname];

$in['go'] = (key_exists('go',$_REQUEST))? strict($_REQUEST['go']) : 'claims';
$in['do'] = (key_exists('do',$_REQUEST))? strict($_REQUEST['do']) : '';
$in['id'] = (key_exists('id',$_REQUEST))? numeric($_REQUEST['id']) : 0;
$in['woid'] = (key_exists('woid',$_REQUEST))? numeric($_REQUEST['woid']) : 0;
$in['begin'] = (key_exists('begin',$_REQUEST))? cyrdate($_REQUEST['begin']) : strftime('%Y-%m-%d 00:00:00');
$in['end'] = (key_exists('end',$_REQUEST))? cyrdate($_REQUEST['end']) : strftime('%Y-%m-%d 23:59:59',strtotime('7 day'));
$in['sort'] = (key_exists('sort',$_REQUEST))? strict($_REQUEST['sort']) : 'claimtime';
$in['limit'] = (key_exists('limit',$_REQUEST) && $_REQUEST['limit']!='no')? 'yes' : 'no';
$in['pagerows'] = (key_exists('pagerows',$_REQUEST))? numeric($_REQUEST['pagerows']) : 0;
$in['page'] = (key_exists('page',$_REQUEST))? numeric($_REQUEST['page']) : 1;
$out['log'][] = "worders: in[begin]={$in['begin']} in[end]={$in['end']}";

$t['name'] = $tname;
$t['id'] = $in['id'];
$t['style'] = 'width:570px;';

$form = new form($config);

switch($in['do']) {
	case 'get':
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

	case 'add':
	case 'new':
		$t['id'] = 'new';
		$t['header'] = '<h3>Новый служащий</h3>';
// 		$t['defaults'] = array('photo'=>'');
		$out = $form->getnew($t);
		stop($out);
		break;

	case 'edit':
		if(!($e = $q->get("employers",$in['id'])))
			stop(array('result'=>'ERROR','desc'=>'Этот служащий отсутствует в базе!'));
		stop($form->get($t));
		break;

	case 'save':
		stop($form->save($t));
		break;

	case 'delete':
	case 'remove':
		if($у = $q->get("employers",$in['id'])){
			stop($form->confirmForm($in['id'],'realremove',"Вы действительно хотите удалить этого служащего?"));
		}else{
			stop(array('result'=>'ERROR','desc'=>'Этот служащий отсутствует в базе!'));
		}
		break;

	case 'realremove':
		stop($form->delete($t));
		break;

	case 'auto_address':
		$req = (key_exists('req',$_REQUEST))? str($_REQUEST['req']) : '';
		$out['result'] = 'OK';
		$out['complete'] = $q->select("
			SELECT DISTINCT address as label
			FROM users 
			WHERE address like '%$req%'
			HAVING label!='' 
			ORDER BY address
			LIMIT 20
		");
		stop($out);
		break;
}
?>
