<?php
include_once("classes.php");
include_once("table.php");

$tables['orders']=array(
	'title'=>'Платежные ведомости',
	'target'=>"html",
	'name'=>"orders",
	'module'=>"stdform",
	'key'=>'oid',
	'class'=>'normal',
	'delete'=>'yes',
	'limit'=>'yes',
	'add'=>'no',
// 	'footer'=>array(),
	'table_query'=>"
		SELECT 
			o.oid,
			o.oid as id,
			o.open,
			o.operator,
			o.close,
			o.accept,
			o.acceptor,
			count(p.oid) as countpaid,
			o.summa
		FROM orders o
			LEFT OUTER JOIN pay p ON o.oid = p.oid
		WHERE 1 :FILTER: :PERIOD:
		GROUP BY o.oid
		ORDER BY :SORT:
		",
	'form_query'=>"
		SELECT 
			oid,
			operator,
			open,
			close,
			accept,
			acceptor,
			summa
		FROM orders
		",
	'table_menu'=>array(
		'edit'=>array('label'=>"<img src=\"pic/gtk-edit16.png\"> изменить",'to'=>'form','query'=>"go=stdform&do=edit&table=orders"),
		'delete'=>array('label'=>"<img src=\"pic/del.png\"> удалить",'to'=>'form','query'=>"go=stdform&do=delete&table=orders"),
		'print'=>array('label'=>"<img src=\"pic/gtk-print.png\"> печатать",'to'=>'window','target'=>"docpdf.php",'query'=>"type=pay_list"),
	),
	'table_triggers'=>array(
		'operator' => 'get_orders_operator',
		'open' => 'cell_atime',
		'close'=> 'get_orders_checkbox',
		'accept'=> 'get_orders_checkbox',
		'acceptor' => 'get_orders_operator',
		'summa'=> 'cell_summ',
	),
	'form_triggers'=>array(
	),
	'group'=>'',
	'defaults'=>array(
		'operator'=>$opdata['id'],
		'sort'=>'open desc',
		'filter'=>'build_filter_for_orders',
		'period'=>'build_period_for_orders'
	),
	'filters'=>array(
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
			'value'=>cyrdate(strtotime('first day of'))
		),
	),

	// поля
	'fields'=>array(
		'id'=>array(
			'label'=>'&#8470;',
			'type'=>'text',
			'class'=>'ctxt',
			'native'=>false,
			'access'=>array('r'=>2,'w'=>5)
		),
		'oid'=>array(
			'label'=>'id',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>5)
		),
		'operator'=>array(
			'label'=>'Оператор',
			'type'=>'select',
			'list'=>'get_orders_operators',
			'class'=>'ctxt',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'open'=>array(
			'label'=>'Создана',
			'type'=>'date',
			'class'=>'date',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'close'=>array(
			'label'=>'Закрыта',
			'type'=>'date',
			'class'=>'date',
			'style'=>'width:80px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'accept'=>array(
			'label'=>'Принята',
			'type'=>'date',
			'class'=>'date',
			'style'=>'width:80px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'acceptor'=>array(
			'label'=>'Проверил',
			'type'=>'text',
			'class'=>'ctxt',
			'style'=>'width:180px',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>3)
		),
		'countpaid'=>array(
			'label'=>'записей',
			'type'=>'text',
			'class'=>'summ',
			'native'=>false,
			'access'=>array('r'=>2,'w'=>2)
		),
		'summa'=>array(
			'label'=>'Сумма',
			'type'=>'text',
			'class'=>'summ',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
	),
	'before_new'=>'before_new_orders',
	'before_edit'=>'before_edit_orders',
	'before_save'=>'before_save_orders',
	'form_save_new'=>'save_new_orders',
	'form_onsave'=>'onsave_orders',
	'allow_delete'=>'allow_delete_orders',
	'before_delete'=>'before_delete_orders',
);

function get_orders_operators() {
	return list_operators();
}

function get_orders_operator($v,$r=null,$fn=null) {
	$op = list_operators();
	return isset($op[$v])? $op[$v] : $v;
}

function get_orders_checkbox($v,$r,$fn) {
	$img = ($v)? "pic/stop.png" : "pic/off.png";
	return "<span class=\"linkform\" add=\"go=stdform&do=switch&field=$fn&id={$r[0]}&table=orders\"><img src=\"{$img}\"></span>";
}

function orders_created($r) {
	$ct = is_array($r)? $r['created'] : $r;
	return cyrdate($ct,'%d-%m-%y <em>%H:%M</em>');
}

function orders_expired($r) {
	$ct = is_array($r)? $r['created'] : $r;
	return cyrdate($ct,'%d/%m %Y');
}

function before_new_orders(){
	stop(array('result'=>'ERROR','desc'=>"Ведомость создаётся автоматически!"));
}

function before_save_orders($c,$o,$my) {
	global $DEBUG, $config, $q, $opdata;
	$r = array_merge($o,$c);
	if(key_exists('close',$c)){
		if($opdata['level']<4 && $r['operator'] != $opdata['login'])
			stop(array('result'=>'ERROR','desc'=>"Нельзя ".(($c['close'])? "закрывать":"отрывать")." чужие ведомости!"));
		if($opdata['level']<5 && $r['operator'] != $opdata['login'] && (strtotime($r['open']) >= strtotime(date('Y-m-d'))))
			stop(array('result'=>'ERROR','desc'=>"День ещё не закончился!"));
		if(!$c['close'] && $opdata['level']<5 && strtotime($r['open']) < strtotime('-3 day'))
			stop(array('result'=>'ERROR','desc'=>"Ведомость закрыта!"));
		if($opdata['level']<5 && (!$c['close'] && ($r['acceptor'] == 'auto' && strtotime($r['open']) < strtotime('-7 day') || $r['acceptor'] != 'auto' && $r['acceptor'] != $opdata['login'])))
			stop(array('result'=>'ERROR','desc'=>"Ведомость уже проверена!"));
		if(!isset($r['oid'])) stop(array('result'=>'ERROR','desc'=>"Не определён номер ведомости!"));
		if($c['close']){
			$c['summa'] = $q->select("SELECT sum(`summ`) FROM `pay` WHERE oid='{$r['oid']}'",4);
			if($c['summa'] != $r['summa']) log_txt(__function__.": WARNING сумма в ведомости не соответствует сумме платежей!");
		}
		if(!$c['close']) {
			$c['acceptor'] = "";
			$c['accept'] = null;
		}
	}
	if(key_exists('accept',$c) && !key_exists('acceptor',$c)){
		if($c['accept']){
			if($opdata['level']<4 && $r['operator'] == $opdata['login'])
				stop(array('result'=>'ERROR','desc'=>"Проверить должен другой!"));
			if($opdata['level']<5 && strtotime($r['open']) < strtotime('-1 year'))
				stop(array('result'=>'ERROR','desc'=>"Ведомость закрыта!"));
		}else{
			if($opdata['level']<5 && $r['acceptor'] != $opdata['login'])
				stop(array('result'=>'ERROR','desc'=>"Ведомость проверена не Вами!"));
			if($opdata['level']<5 && strtotime($r['accept']) < strtotime('-3 day'))
				stop(array('result'=>'ERROR','desc'=>"Убрать провеку уже поздно!"));
		}
		if(!$r['close']) stop(array('result'=>'ERROR','desc'=>"Невозможно принять ведомость если она открыта!"));
		$c['acceptor'] = ($c['accept'])? $opdata['login'] : "";
	}
	return $c;
}

function allow_delete_orders($id) {
	global $opdata, $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	if(!$id || !($order = $q->get('orders',$id))) return "Ведомость не найдена!";
	if($order['close'])  return "Ведомость закрыта!";
	if(($cnt = $q->select("SELECT count(*) FROM pay WHERE oid='{$order['oid']}'",4))>0) return "Ведомость содержит платежи!";
	if($opdata['level'] < 5 && (strtotime($order['open']) < strtotime('-3 day')))  return "Время возможного удаления истекло!";
	if($opdata['level'] < 5 && $opdata['login'] != $order['operator']) return "Вы не можете удалять<br> ведомости другого оператора!";
	if($opdata['level'] < 3) return "Доступ запрещён!";
	return 'yes';
}

function before_delete_orders($o) {
	global $opdata, $config, $q;
	if(($cnt = $q->select("SELECT count(*) FROM pay WHERE oid='{$o['oid']}'",4))>0)
		stop(array('result'=>'ERROR','desc'=>"Ведомость содержит платежи!"));
	return true;
}

function get_orders($id) {
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

function orders_operator($op) {
	return shortfio(get_operators($op));
}

function build_period_for_orders($t) {
	return period2db('orders','open');
}

function build_filter_for_orders($t) {
	return filter2db('orders');
}

?>
