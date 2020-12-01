<?php
include_once("classes.php");
include_once("table.php");

$work_state = array('','рабочий день','выход на работу','отпуск','больничный');

$tables['workdays']=array(
	'title'=>'район',
	'target'=>"form",
	'module'=>"workdays",
	'key'=>'id',
	'action'=>'references.php',
	'class'=>'normal',
	'style'=>'min-width:515px',
	'delete'=>'yes',
	'defaults'=>array(
		'worktime'=>8,
		'overtime'=>0
	),
// 	'footer'=>array(),
	'table_query'=>"
		",
	'form_query'=>"
		SELECT 
			id, 
			eid, 
			`date`, 
			work, 
			worktime, 
			overtime, 
			note 
		FROM workdays 
		",
	'table_triggers'=>array(
	),
	'form_triggers'=>array(
		'eid'=>'get_employer'
	),
	'layout'=>array(
		'short'=>array(
			'type'=>'fieldset',
			'legend'=>'',
			'style'=>'width:250px;height:130px;float:left',
			'fields'=>array('eid','date','work','worktime','overtime')
		),
		'notes'=>array(
			'type'=>'fieldset',
			'legend'=>'',
			'style'=>'width:200px;height:130px;float:left',
			'fields'=>array('note')
		),
	),
	'checks'=>array(
		'save'=>'workdays_check',
	),
	'form_onsave'=>'workdays_onsave',
	'sort'=>'',
	'group'=>'',

	// поля
	'fields'=>array(
		'id'=>array(
			'label'=>'id',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'date'=>array(
			'label'=>'дата',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'eid'=>array(
			'label'=>'Служащий',
			'type'=>'select',
			'style'=>'width:150px',
			'list'=>'list_of_employers',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'work'=>array(
			'label'=>'выход',
			'type'=>'select',
			'style'=>'width:150px',
			'list'=>$work_state,
			'native'=>true,
			'access'=>array('r'=>3,'w'=>4)
		),
		'worktime'=>array(
			'label'=>'рабочее время',
			'type'=>'text',
			'style'=>'width:50px;text-align:right',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>4)
		),
		'overtime'=>array(
			'label'=>'сверхурочные',
			'type'=>'text',
			'style'=>'width:50px;text-align:right',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>4)
		),
		'note'=>array(
			'label'=>'Примечание',
			'type'=>'textarea',
			'style'=>'width:185px;height:95px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		)
	)
);

function get_employer($id,$r=null,$fn=null){
	global $config, $cache;
	if(!isset($cache['employers'])) {
		if(!$q) $q=new sql_query($config['db']);
		$cache['employers'] = $q->fetch_all("select eid, fio from employers where blocked=0 order by fio",'eid');
		foreach($cache['employers'] as $k=>$v) $cache['employers'][$k] = shortfio($v);
	}
	return $cache['employers'][$id];
}

function before_save_workdays($c,$o) {
	return $c;
}

function workdays_check($r) {
	global $config;
	log_txt(__function__.": ".arrstr($r));
	if($r['id']=='new'){
		$q = new sql_query($config['db']);
		if($q->select("SELECT * FROM workdays WHERE eid='{$r['eid']}' AND date='{$r['date']}'")){
			stop(array('result'=>'ERROR','desc'=>"Изменено другим оператором, обновите!"));
		}
	}
}

function workdays_onsave($id) {
	global $config, $cache, $year, $month;
	$q = new sql_query($config['db']);
	if($e = $q->select("SELECT id, eid, `date`, work, worktime, overtime, note FROM workdays WHERE id='$id'",1)){
		$e['day'] = date('d',strtotime($e['date'])) + 0;
		$w = $q->select("
			SELECT 
			sum(if(work=2 && (DATE_FORMAT(`date`,'%w') not in (0,6)),1,0)) as wd,
			sum(if(work=2 && (DATE_FORMAT(`date`,'%w') in (0,6)),1,0)) as fd, 
			sum(overtime) as ot,
			sum(if(work=3,1,0)) as vd,
			sum(if(work=4,1,0)) as sl
			FROM workdays WHERE eid='{$e['eid']}'AND `date` between '$year-$month-01' AND '$year-$month-31'
		",1);
		return array('result'=>'OK','modify'=>array(array_merge($e,$w)));
	}else{
		return false;
	}
}
?>
