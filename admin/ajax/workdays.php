<?php
$call="workdays";
$w=array('выходной','рабочий день','явка на работу');
include_once("workdays.cfg.php");
include_once("classes.php");
include_once("form.php");
$q = new sql_query($config['db']);

$in['go'] = (array_key_exists('go',$_REQUEST))? strict($_REQUEST['go']) : 'rayons';
$in['do'] = (array_key_exists('do',$_REQUEST))? strict($_REQUEST['do']) : '';
$user=(key_exists('user',$_REQUEST))? strict($_REQUEST['user']) : '';
$month=(key_exists('month',$_REQUEST) && $_REQUEST['month']!='')? numeric($_REQUEST['month']) : date("m");
$year=(key_exists('year',$_REQUEST) && $_REQUEST['year']!='')? numeric($_REQUEST['year']) : date("Y");
if(isset($_REQUEST['id']) && preg_match('/d/',$_REQUEST['id'])) {
	$e=preg_split('/d/',str($_REQUEST['id']));
	$eid=numeric($e[0]);
	$day=numeric($e[1]);
	if(!($id = $q->select("SELECT id FROM workdays WHERE `date`='$year-$month-$day' AND eid='$eid'",4))) {
		$id = 'new';
	}
}else{
	$id=(key_exists('id',$_REQUEST))? numeric($_REQUEST['id']) : 0;
}
if(!isset($id)) $id='new';

$t = $tables['workdays'];
$t['name'] = 'workdays';

switch($in['do']) {
	case 'click':
		if($opdata['level']<=4 && strtotime(date('Y-m-d'))-strtotime("$year-$month-$day")>86400*7) {
			stop(array('result'=>'ERROR','desc'=>"Вам разрешено изменять только последние 7 дней!"));
		}
		if($eid==0) {
			stop(array('result'=>'ERROR','desc'=>"Пользователь не опеределен!"));
		}
		$employer = $q->select("SELECT * FROM employers WHERE eid=$eid",1);
		$employer['fio'] = shortfio($employer['fio']);
		if($workday = $q->select("SELECT id, eid, day(`date`) as `day`, work, worktime, overtime, note FROM workdays WHERE eid=$eid AND `date`='$year-$month-$day'",1)) {
			if($workday['work']==2 || strtotime(date('Y-m-d'))-strtotime("$year-$month-$day")<0 && $workday['work']>=1) 
				$workday['work']=0;
			else 
				$workday['work']++;
			$q->update_record('workdays',array('id'=>$workday['id'],'work'=>$workday['work']));
			unset($workday['id']);
		}else{
			$workday = array_merge(array('eid'=>$eid, 'date'=>"$year-$month-$day", 'work'=>1, 'note'=>''),$t['defaults']);
			$id = $q->insert('workdays',$workday);
		}
		nibs_log($user,0,"изменил табель выходов",$employer['fio']." $day-$month-$year -> ".$w[$workday['work']]);
		stop(workdays_onsave($id));
		break;

	case 'edit':
		$t['id'] = $id;
		if($id == 'new' && $eid > 0) {
			$t['defaults']['eid'] = $eid;
			$t['defaults']['date'] = "$year-$month-$day";
		}
		if(isset($day)) $t['fields']['eid']['disabled'] = true;
		$form = new form($config);
		$t['name']='workdays';
		stop($form->get($t));
		break;

	case 'save':
		$t['key']='id';
		$form = new form($config);
		stop($form->save($t));
		break;

	case 'delete':
	case 'remove':
		if($workday=$q->select("SELECT * FROM workdays WHERE id=$id",1)){
			$form = new form($config);
			stop($form->confirmForm($in['id'],'realremove',"Вы действительно хотите удалить запись ?"));
		}else{
			stop(array('result'=>'ERROR','desc'=>'Указанная запись отсутствует в базе!'));
		}
		break;

	case 'realremove':
		$form = new form($config);
		$t['id']=$id;
		stop($form->delete($t));
		break;
}
?>
