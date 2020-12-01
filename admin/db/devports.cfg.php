<?php
include_once("classes.php");
if(!$q) $q = sql_query($config['db']);

$tables['devports']=array(
	'name'=>'devports',
	'title'=>'Порты устройства',
	'target'=>"form",
	'limit'=>'no',
	'module'=>"stdform",
	'key'=>'id',
	'class'=>'normal',
	'delete'=>'yes',
	'header'=>"",
	'table_query'=>"
		SELECT
			id,
			device,
			number,
			node,
			link,
			link1,
			porttype,
			module,
			color,
			coloropt,
			bandle,
			divide,
			note,
			modified
		FROM
			devports
		WHERE 1 :FILTER:
		ORDER BY :SORT:
		",
	'form_query'=>"
		SELECT
			id,
			device,
			number,
			node,
			link,
			link1,
			porttype,
			module,
			color,
			coloropt,
			bandle,
			divide,
			note,
			modified
		FROM
			devports
		",
	'filters'=>array(
	),
	'defaults'=>array(
		'sort'=>'device number',
		'filter'=>'build_filter_for_devports',
	),
	'table_triggers'=>array(
		'color'=>'get_color'
	),
	'form_triggers'=>array(
		'device'=>'get_devport_device',
		'node'=>'get_devport_node',
		'porttype'=>'get_devport_porttype',
	),
	'form_autocomplete'=>array(
		'attribute'=>'reply_auto_attribute',
		'op'=>'reply_auto_op',
	),
	'before_new'=>'before_new_devport',
	'before_edit'=>'before_edit_devport',
	'before_save'=>'before_save_devport',
	'checks'=>'checks_devport',
	'group'=>'',

	// поля
	'fields'=>array(
		'id'=>array(
			'label'=>'id',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'device'=>array(
			'label'=>'устройство',
			'type'=>'nofield',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'number'=>array(
			'label'=>'номер',
			'type'=>'nofield',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'node'=>array(
			'label'=>'узел',
			'type'=>'nofield',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'link'=>array(
			'label'=>'соединение',
			'type'=>'select',
			'list'=>'get_devport_links',
			'style'=>'width:200px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'link1'=>array(
			'label'=>'2е соединение',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'porttype'=>array(
			'label'=>'тип порта',
			'type'=>'select',
			'list'=>$porttype,
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'module'=>array(
			'label'=>'модуль',
			'type'=>'select',
			'list'=>'select_module',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'color'=>array(
			'label'=>'цвет',
			'type'=>'select',
			'list'=>'select_port_color',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'coloropt'=>array(
			'label'=>'метки',
			'type'=>'select',
			'list'=>array('dashed'=>'есть','solid'=>'нет'),
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'bandle'=>array(
			'label'=>'цвет связки',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'divide'=>array(
			'label'=>'затухание',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'note'=>array(
			'label'=>'примечание',
			'type'=>'textarea',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'modified'=>array(
			'label'=>'изменено',
			'type'=>'nofield',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
	),
);

function before_new_devport($f) {
	global $config, $q, $DEBUG;
	return $f;
}

function before_edit_devport($f) {
	global $config, $q, $devtype, $DEBUG;
	$p = $q->get('devports',$f['id']);
	if($p){
		$d = $q->get('devices',$p['device']);
		switch ($d['type']) {
			case 'cable':
				$filter = array('module','divide','link1');
				if($d['numports']<=$d['bandleports']) unset($f['fields']['bandle']);
				if($d['numports']<=12 || $d['bandleports']<=12) unset($f['fields']['coloropt']);
				$f['fields']['porttype']['type'] = 'nofield';
				break;
			case 'divisor':
			case 'splitter':
				$filter = array('module','bandle','coloropt','link1');
				break;
			case 'switch':
				$filter = array('divide','bandle','coloropt','color');
				break;
			default:
 				$filter = array('divide','bandle','module','coloropt','color','link1');
				break;
		}
		foreach($filter as $n) unset($f['fields'][$n]);
	}
	return $f;
}

function select_port_color($p) {
	global $config, $q, $devtype, $DEBUG;
	$pcolor = $q->fetch_all("SELECT distinct color as id, rucolor FROM devprofiles ORDER BY rucolor");
	return $pcolor;
}


function get_devport_links($p) {
	global $config, $q, $devtype, $DEBUG;
	$list = array('_'=>"");
	$node = $q->get('map',$p['node']);
	$links = $q->select("SELECT * FROM devports WHERE (node='{$p['node']}' AND (porttype='{$p['porttype']}' OR porttype='cuper') AND link is NULL) OR id='{$p['link']}' ORDER BY device, porttype, number");
	if($links){
		$devs = array();
		$nodes = array();
		foreach($links as $l){
			if(!isset($devs[$l['device']])) $devs[$l['device']] = count($devs);
		}
		$devs = $q->fetch_all("SELECT * FROM devices WHERE id in (".implode(',',array_flip($devs)).")");
		foreach($devs as $k=>$d){
			if($d['node1'] == $p['node'] && $d['node2'] && !isset($nodes[$d['node2']])) $nodes[$d['node2']] = count($nodes);
			elseif($d['node2'] == $p['node'] && $d['node1'] && !isset($nodes[$d['node1']])) $nodes[$d['node1']] = count($nodes);
		}
		if($nodes) $nodes = $q->fetch_all("SELECT * FROM map WHERE id in (".implode(',',array_flip($nodes)).")");
		$pcolor=$q->fetch_all("SELECT distinct color, rucolor FROM devprofiles ORDER BY color",'color');
		foreach($pcolor as $k=>$c) $pcolor[$k] = preg_replace(array('/ый|ой/','/ий/'),array('ая','яя'),$c);
		foreach($links as $k=>$l) {
			$dname = $devtype[$devs[$l['device']]['type']];
			$d = $devs[$l['device']];
			switch ($d['type']) {
				case 'cable':
					if($d['node1'] == $p['node'] && $d['node2'] && isset($nodes[$d['node2']])) $addr = $nodes[$d['node2']]['address'];
					elseif($d['node2'] == $p['node'] && $d['node1'] && isset($nodes[$d['node1']])) $addr = $nodes[$d['node1']]['address'];
					$dname .= "({$devs[$l['device']]['numports']}ж) ".(($addr)?" на $addr ":"");
					$list[$l['id']] = $dname.$pcolor[$l['color']].($l['bandle']? " в ".$pcolor[$l['bandle']]:"")." жила";
					break;
				case 'divisor':
				case 'splitter':
					$dname .= "({$devs[$l['device']]['subtype']}) ";
					$list[$l['id']] = $dname.$pcolor[$l['color']]." жила ({$l['number']})";
					break;
				case 'switch':
					$dname .= "({$devs[$l['device']]['numports']}п) ";
					$list[$l['id']] = $dname."Порт ".(($l['number']<10)?"0".$l['number']:$l['number']);
					break;
				default:
					$dname .= "({$devs[$l['device']]['numports']}) ";
					$list[$l['id']] = $dname."Порт ".(($l['number']<10)?"0".$l['number']:$l['number']);
					break;
			}
		}
	}
	return $list;
}

function get_devport_porttype($v) {
	global $porttype;
	return ($porttype[$v])? $porttype[$v] : $v;
}

function get_devport_node($v,$r) {
	global $config, $q, $devtype;
	$node = $q->get('map',$v);
	return ($node)? $node['address']:$v;
}

function get_devport_device($v,$r) {
	global $config, $q, $devtype;
	$d = $q->get('devices',$v);
	if(isset($r[0])) $d = $d[0];
	if($d){
		if($d['type']=='cable'){
			if($d['node1'] == $r['node'] && $d['node2']) $n = $d['node2'];
			elseif($d['node2'] == $r['node'] && $d['node1']) $n = $d['node1'];
			$addr = isset($n)? " &rarr; ".$q->select("SELECT address FROM map WHERE id='$n'",4) : "";
			$name = $devtype[$d['type']]."(".$d['numports']."ж)".$addr;
		}
		if($d['type']=='switch'){
			$name = $devtype[$d['type']]."(".$d['numports']."п)";
		}
		if($d['type']=='divisor' || $d['type']=='splitter'){
			$name = $devtype[$d['type']]."(".$d['subtype'].")";
		}
	}
	return ($name)? $name : $v;
}

function reply_auto_device() {
	global $config, $q;
	$req = (isset($_REQUEST['req']))? str($_REQUEST['req']) : '';
	$out['result'] = 'OK';
	$out['complete'] = $q->select("
		SELECT distinct name as label FROM devices
		WHERE name like '%$req%'
		HAVING label!=''
		ORDER BY op
	");
	return $out;
}
?>
