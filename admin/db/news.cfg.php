<?php
include_once("classes.php");
include_once("table.php");

$tables['news']=array(
	'title'=>'Новости компании',
		'target'=>"form",
	'name'=>"news",
	'module'=>"news",
	'key'=>'id',
	'class'=>'normal',
	'delete'=>'yes',
	'limit'=>'yes',
// 	'footer'=>array(),
	'table_query'=>"
		SELECT 
			id,
			n.created,
			n.expired,
			concat(n.uid,':',u.address) as uid,
			n.name,
			n.operator
		FROM news n LEFT OUTER JOIN users u ON n.uid=u.uid
		WHERE n.expired > now() :FILTER:
		ORDER BY :SORT:
		",
	'form_query'=>"
		SELECT 
			id, 
			uid,
			created,
			expired,
			content,
			name,
			operator
		FROM news
		",
	'table_triggers'=>array(
		'uid' => 'news_client',
		'operator' => 'news_operator',
		'created' => 'news_created',
		'expired'=> 'news_expired'
	),
	'form_triggers'=>array(
	),
	'group'=>'',
	'defaults'=>array(
		'operator'=>$opdata['id'],
		'sort'=>'expired DESC',
		'filter'=>'build_filter_for_news'
	),
	'filters'=>array(
		'address'=>array(
			'type'=>'text',
			'label'=>'адрес',
			'style'=>"width:110px",
			'title'=>'выбор по адресу',
			'value'=>''
		),
		'end'=>array(
			'type'=>'date',
			'label'=>'конец',
			'style'=>'width:80px',
			'title'=>'дата конца',
			'value'=>cyrdate()
		),
		'begin'=>array(
			'type'=>'date',
			'label'=>'начало',
			'keep'=>true,
			'style'=>'width:80px',
			'title'=>'дата начала',
			'value'=>cyrdate(strtotime('-1 month'))
		),
	),

	// поля
	'fields'=>array(
		'id'=>array(
			'label'=>'id',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>5)
		),
		'created'=>array(
			'label'=>'Дата создания',
			'type'=>'date',
			'class'=>'date',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'expired'=>array(
			'label'=>'Дата окончания',
			'type'=>'date',
			'class'=>'date',
			'style'=>'width:80px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'uid'=>array(
			'label'=>'клиент',
			'type'=>'text',
			'class'=>'ctxt',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>3)
		),
		'name'=>array(
			'label'=>'Название',
			'type'=>'text',
			'style'=>'width:250px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'content'=>array(
			'label'=>'Содержимое',
			'type'=>'textarea',
			'style'=>'width:250px;height:200px',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>3)
		),
		'operator'=>array(
			'label'=>'Оператор',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
	)
);

function news_created($r,$a=null,$fn=null) {
	$ct = is_array($r)? $r['created'] : $r;
	return cyrdate($ct,'%d-%m-%y <em>%H:%M</em>');
}

function news_expired($r,$a=null,$fn=null) {
	$ct = is_array($r)? $r['created'] : $r;
	return cyrdate($ct,'%d/%m %Y');
}

function before_save_news($c,$o) {
	return $c;
}

function before_delete_news($id) {
	return true;
}

function get_news($id) {
	return true;
}

function get_operators($id=false,$r=null,$fn=null) {
	global $cache, $config, $q;
	if(!isset($cache['operators'])) {
		if(!$q) $q = new sql_query($config['db']);
		$cache['operators'] = $q->fetch_all("SELECT unique_id, fio FROM operators",'unique_id');
	}
	if($id !== false) return $cache['operators'][$id];
	return $cache['operators'];
}

function news_operator($op,$r=null,$fn=null) {
	return shortfio(get_operators($op));
}

function news_client($c,$r=null,$fn=null) {
	$u = preg_split('/:/',$c);
	return "<a href=\"users.php?go=usrstat&uid={$u[0]}\">{$u[1]}</a>";
}

function build_filter_for_news($t) {
	global $tables, $_REQUEST;
	$r = array(); $s = '';
	$a = $tables[$t]['filters'];
	if(is_array($a)){
		foreach($a as $k=>$v){
			if($k == 'begin'){
				$val = (isset($_REQUEST[$k]))? date2db($_REQUEST[$k],false):date('Y-m-d',strtotime('-1 month'));
				$d1 = "OR (`created`>'$val'";
			}elseif ($k == 'end') {
				$val = (isset($_REQUEST[$k]))? date2db($_REQUEST[$k],false):date('Y-m-d 23:59:59');
				$d2 = "AND `created`<'$val')";
			}else{
				if(isset($_REQUEST[$k]) && $v!='') $r[] = "AND `$k`='".strict($_REQUEST[$k])."'";
			}
		}
		if(isset($d1) && isset($d2)) $s = "$d1 $d2 ";
		$s .= implode(' ',$r);
	}
	log_txt(__function__.": return: $s");
	return $s;
}

?>
