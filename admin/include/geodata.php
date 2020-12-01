<?php
include_once("defines.php");
include_once("utils.php");
include_once("classes.php");
include_once("devices.cfg.php");
$pondevice = array("divisor"=>1,"splitter"=>2);

if(!isset($devtype)) $devtype=array('unknown' => '','cable' => 'Кабель','switch' => 'Свич','patchpanel' => 'Патч панель','divisor' => 'Делитель','splitter' => 'Сплиттер');
if(!isset($objecttype)) $objecttype=array('unknown' => '','home' => 'Дом','node' => 'Узел','cable' => 'Кабель');
if(!isset($porttype)) $porttype=array('unknown' => 'не определен','cuper' => 'RJ45','fiber' => 'Волокно','coupler' => 'Каплер','access' => 'Access','trunk' => 'Trunk');

function makePoints($slice,$id,$line){ // функция формирующая масив координат для внесения в базу
	$xy=array();
	if(is_array($line)){
		foreach($line as $k=>$v) {
			$x = (isset($v['x']))? $v['x'] : $v[0];
			$y = (isset($v['y']))? $v['y'] : $v[1];
			$xy[]=array(
				'object'=>$id,
				'slice'=>$slice,
				'num'=>$k,
				'x'=>$x,
				'y'=>$y
			);
		}
	}
	return $xy;
}

function lineLength($xy=array()) { // функция вычисляющая длину кабеля
	if(!is_array($xy) || !isset($xy[0])) return 0;
	$length = 0;
	foreach($xy as $k=>$v) if($k>0)
		if(isset($xy[$k][0])) $length += Distance(array($xy[$k-1][0],$xy[$k-1][1]),array($v[0],$v[1]));
		else $length += Distance(array($xy[$k-1]['x'],$xy[$k-1]['y']),array($v['x'],$v['y']));
	return round($length);	
}

function getCenter($xy=array()) { // функция вычисляющая длину кабеля
	$center = false;
	if(!is_array($xy) || !isset($xy[0])) return $center;
	if(count($xy)==1){
		$center = array($xy[0]['x'],$xy[0]['y']);
	}elseif(count($xy)>1){
		$x = 0; $y = 0; $c = count($xy) - 1;
		foreach($xy as $k=>$v){
			if($k == 0) continue;
			$x += $v['x']; $y += $v['y'];
		}
		$center = array($x / $c, $y / $c);
	}
	return $center;	
}

class Trace { // объект, формирующий трассу на остове связей по портам устройств

	function __construct(){
		global $config;
		$this->fd = $config['fading'];
		$this->q = new sql_query($config['db']);
		foreach($this->fd['data'] as $k=>$v){
			$this->fd['divisor'][$v['div']]['1310nm'] = preg_split("/\//",$v['1310nm']);
			$this->fd['divisor'][$v['div']]['1550nm'] = preg_split("/\//",$v['1550nm']);
			$this->fd['splitter'][$k] = $v['fade'];
		}
		$this->clearData();
    }

	private function clearData() {
		global $pondevice;
		$this->gpon = false;
		$this->chain = array(); // все цепочки получаемые при трассировке
		$this->chainfading = array(); // затухание для цепочек
		$this->chainCap = array(); // координаты начала и конца цепочек
		$this->chaintail = array();
		$this->cables = array();
		$this->nodes = array(); // массив всех узлов в ветвящемся дереве
		$this->devices = array(); // массив всех устройств в ветвящемся дереве с его иерархией (parent, childs)
		$this->pon = $pondevice; // типы пасcивных устройств дающие ветвление дерева трассировки
		$this->features = array(); // GeoJSON объекты всех линий
		$this->root = false;
		$this->clients = 0;
	}

	private function chline($id){
		$r = array();
		if(isset($this->chain[$id])) foreach($this->chain[$id] as $k=>$p) {
			$r[$k] = $p['type'].":".$p['device']."[".$p['number']."]";
		}
		return $r;
	}

	public function traceChain($id,$side=0){ // Выполняе трассировку соединенных портов на устройствах и кабелях
		// берём начальный порт
		if(!($port=$this->q->select("SELECT p.*, if(p.node=d.node1 or p.node is null and d.node1 is null,0,1) as dir, d.type, d.object, d.name as devname, d.numports, d.ip FROM devports p, devices d WHERE p.device=d.id and p.id='{$id}'",1))) return false;
		$i=0; 
		$p[$i]=$port;
		if($side!=2) {
		// прослеживаем цепочку связанных портов сперва в одну сторону
		while($p[$i]){
			$p[$i+1]=$this->q->select("SELECT p.*, if(p.node=d.node1 or p.node is null and d.node1 is null,0,1) as dir, d.type, d.object, d.name as devname, d.numports, d.ip FROM devports p, devices d WHERE p.device=d.id and p.id='{$p[$i]['link']}'",1);$i++;
			if($p[$i]) { $p[$i+1]=$this->q->select("SELECT p.*, if(p.node=d.node1 or p.node is null and d.node1 is null,0,1) as dir, d.type,d .object, d.name as devname, d.numports, d.ip FROM devports p, devices d WHERE p.device=d.id and p.device='{$p[$i]['device']}' AND p.number='{$p[$i]['number']}' AND p.id!='{$p[$i]['id']}'",1);$i++; }
		}
		if($p[$i]===false) { unset($p[$i]); $i--; }
		if($side==1) return $p;
		}
		$first = count($p);
		// а затем в другую
		$p=array_reverse($p);
		while($p[$i]){
			$p[$i+1]=$this->q->select("SELECT p.*, if(p.node=d.node1 or p.node is null and d.node1 is null,0,1) as dir, d.type, d.object, d.name as devname, d.numports, d.ip FROM devports p, devices d WHERE p.device=d.id and p.device='{$p[$i]['device']}' AND p.number='{$p[$i]['number']}' AND p.id!='{$p[$i]['id']}'",1);$i++;
			if($p[$i]) { $p[$i+1]=$this->q->select("SELECT p.*, if(p.node=d.node1 or p.node is null and d.node1 is null,0,1) as dir, d.type, d.object, d.name as devname, d.numports, d.ip FROM devports p, devices d WHERE p.device=d.id and p.id='{$p[$i]['link']}'",1);$i++; }
		}
		if($p[$i]===false) { unset($p[$i]); $i--; }
//		log_txt(__METHOD__.": id:$id  {$p[0]['type']}:{$p[0]['number']} -> {$p[$i]['type']}:{$p[$i]['number']}");
		if($side==0 && (isset($this->pon[$p[0]['type']]) && $p[0]['number']==1 || isset($this->pon[$p[$i]['type']]) && $p[0]['number']>1) ||
		(!isset($this->pon[$p[0]['type']]) && !isset($this->pon[$p[$i]['type']]) && $first > count($p)-$first)){
			$p=array_reverse($p);
//			log_txt(__METHOD__.": id:$id chain reversed");
		}
		return $p;
	}

	private function trace($portid,$side=0) { // собираем в один масив все цепочки
		global $DEBUG;
		$ch = $this->traceChain($portid,$side);
		$amount = count($ch);
		if($amount<1 && $side==0) return false;
		if($amount==1 && $side==0){
			if(isset($this->devices[$ch[0]['device']])) $this->devices[$ch[0]['device']]['off'][$ch[0]['number']] = $ch[0];
			return false;
		}
		
		// считаем затухание для цепочки
		$prev = false; $welding = 0; $couplers=0; $length=0; $lenfading=0;
		foreach($ch as $k=>$p) {
			if($p['type']=='cable' && (!$prev || $prev['device'] != $p['device'])) $cables[] = $p['object'];
			if($prev && $prev['device'] != $p['device']){
				if($prev['porttype']=='fiber' && $p['porttype']=='fiber') $welding++;
				if($prev['porttype']=='coupler' xor $p['porttype']=='coupler') $couplers++;
			}
			$prev = $p;
		}
		$fading = 0;
		$fading += $welding * $this->fd['welding']; // затухание по сваркам
		$fading += $couplers * $this->fd['coupler']; // затухание по каплерам
		if(isset($cables[0])){
			foreach($this->q->fetch_all("SELECT id, length FROM map WHERE id in (".implode(',',$cables).")") as $k=>$len){
				$length += $this->cables[$k] = $len;
				$lenfading += $len/1000 * $this->fd['cable']; // затухание по кабелю
				$fading += $len/1000 * $this->fd['cable']; // общее затухание
			}
		}

		$cap = array(reset($ch), end($ch)); // начальный и конечный порт в цепочке
		$devs = array($this->q->get('devices',$cap[0]['device']),$this->q->get('devices',$cap[1]['device'])); // начальное и конечное устройство в цепочке
		$devs[0]['port'] = $cap[0]['number']; $devs[1]['port'] = $cap[1]['number'];
		foreach($cap as $c=>$v) if($cap[$c]['module'] != '') $devs[$c]['module'] = $cap[$c]['module'];
		$dkeys = array_flip(array_keys($devs[0]));
		$key = count($this->chain);
		$devs[0]['chain'] = $key; $devs[1]['chain'] = $key; $devs[0]['side'] = 0; $devs[1]['side'] = 1;
		// если на первой цепочке сидит клиент - проводить трассировку только для uplink Уже не актуально.
		if($key==0){ $this->fork = true; /* if(!$side && ($devs[0]['type']=='onu' || $devs[1]['type']=='onu')) $this->fork = false; */ }
		$this->chain[$key] = $ch;
		$this->chainfading[$key] = $fading; // записываем затухание для цепочки
 		if($DEBUG>2) log_txt(__METHOD__.": ch[$key]: сварки:$welding длина:$length каплеры:$couplers затухание:$fading по длине:$lenfading");
		foreach($devs as $i=>$dev) {
			if($pass = !isset($this->devices[$dev['id']])) $this->devices[$dev['id']] = $dev; // устр. ещё не встречалось
			if(!isset($this->chaintail[$key][$i])) $this->chaintail[$key][$i] = $dev['id']; // записываем id устройсва на концах цепочки
			$opp = ($i==0)? 1 : 0;
			// если dev == разветвитель и порт uplink или dev != разветвитель и парное устройсво разветвитель и его порт != uplink
			if((isset($this->pon[$dev['type']]) && (is_null($cap[$i]['divide']) || $cap[$i]['divide']==100)) || 
					(!isset($this->pon[$dev['type']]) && ($cap[$opp]['divide'] > 0 && $cap[$opp]['divide'] < 100)) ||
						($dev['type']=='switch' && $dev['bandleports']==$cap[$i]['number'])) {
				if($devs[$i]['id']!=$devs[$opp]['id']) $this->devices[$dev['id']]['parent'] = $devs[$opp]['id']; // указываем родителя
				$this->devices[$dev['id']]['input'] = $key; // номер цепочки портов ведущей к родителю
				$this->devices[$dev['id']]['inport'] = $cap[$i]['id']; // номер порта ведущего к родителю
				$this->devices[$dev['id']]['out'] = ($cap[0]['node'] != $cap[1]['node']); // концы цепочки в разных узлах?
				$this->devices[$dev['id']]['cap'] = $i;
			// если dev == разветвитель и его порт != uplink или dev != разветвитель и парное устройсво разветвитель и его порт == uplink
			}elseif((isset($this->pon[$dev['type']]) && ($cap[$i]['divide'] > 0 && $cap[$i]['divide'] < 100)) ||
					(!isset($this->pon[$dev['type']]) && isset($this->pon[$devs[$opp]['type']]) && (is_null($cap[$opp]['divide']) || $cap[$opp]['divide']==100)) || ($dev['type']=='switch' && $dev['bandleports']  && $dev['bandleports']!=$cap[$i]['number'])){
				$this->devices[$dev['id']]['childs'][$cap[$i]['number']] = array('device'=>$devs[$opp]['id'],'chain'=>$key); // указываем потомка
			}else{
				// если начальное и конечное устр. разные
				if($devs[$i]['id']!=$devs[$opp]['id']){
					if($devs[$i]['type']=='switch' && $devs[$i]['bandleports'] == $cap[$i]['number'] || $devs[$i]['type']=='onu'){
						$this->devices[$dev['id']]['parent'] = $devs[$opp]['id']; // указываем родителя
						$this->devices[$dev['id']]['input'] = $key; // номер цепочки портов ведущей к родителю
					}elseif($i==1 && !key_exists('parent',$this->devices[$devs[$opp]['id']])){
						$this->devices[$devs[0]['id']]['parent'] = $devs[1]['id']; // указываем родителя
						$this->devices[$devs[0]]['input'] = $key; // номер цепочки портов ведущей к родителю
					}
				}
			}
			if($DEBUG>2) log_txt(__METHOD__.": ch[$key]($amount) cap[$i] = {$cap[$i]['id']} ".(($i==0)?'начало:':'конец:')." {$dev['type']}:{$dev['id']} ".
				arrstr(array_diff_key($this->devices[$dev['id']],$dkeys))." (".(($pass)?'+':'-').")");
			if($pass && isset($this->pon[$dev['type']])) { // текущее устр. == разветвитель
				$dports = $this->q->fetch_all("SELECT id, number FROM devports WHERE device={$dev['id']} ORDER BY number"); // порты в устройстве
				if($DEBUG>2) log_txt(__METHOD__.": ch[$key]($amount) cap[$i] = {$cap[$i]['id']} {$dev['type']}:{$dev['id']} dports = ".arrstr($dports));
				foreach($dports as $p=>$n) {
					// !($side==1 && $i==0) - не трассировать ветки на разветвителях, кроме начальной
					// $this->fork || ($side==0 && $n==1) - трассировать для кл. ветки только uplink
					if($p != $cap[$i]['id'] && !($side==1 && $i==0) && ($this->fork || ($side==0 && $n==1))) $this->trace($p);
				}
			}
		}
		return true;
	}

	private function Feature($key){ // Формирует GeoJSON линию последовательных соединений для отображения на карте и подсчёта расстояний
		global $DEBUG;
		$ch = $this->chain[$key];
		if(!is_array($ch) || !isset($ch[0]['id'])) return false;
		$len = count($this->chain);
		$d0 = $this->devices[$ch[0]['device']];
		$in = isset($d0['input'])? $d0['input'] : -1;
		$portup = ($d0['type']=='switch' && $d0['bandleports'])? $d0['bandleports'] : -1;
		if($rev = ($len>1 && $in == $key || $portup == $ch[0]['number'] || $d0['type']=='switch' && !$d0['community'])) $ch = array_reverse($ch);
		// выбираем все кабеля
		$cprev=0;
		foreach($ch as $k=>$v) {
			if($v['type']=='cable' && $cprev!==$v['object']) $cables[] = $cprev = $v['object'];
		}
		
		// выбираем координаты всех кабелей
		$o=array();
		if(isset($cables)) {
			$cab=$this->q->query("SELECT * FROM map_xy WHERE object in (".implode(',',$cables).") ORDER BY object, slice, num",2);
			while($r=$cab->fetch_assoc()) $o[$r['object']][$r['num']]=array($r['x'],$r['y']);
		}
		// строим линию
		$dold=0; $length=0; $line=array(); $z=0;
		foreach($ch as $k=>$port){ // идем по цепочке
			if($port['type'] != 'cable' || $dold == $port['device']) { $dold = $port['device']; continue; }
			$cline = $o[$port['object']];
			if(!$cline) log_txt(__METHOD__.": WARNINIG empty coordinates for port: ".arrstr($port));
			// если порт конечный - цепочку переворачиваем
			if($port['dir']==1) $cline = array_reverse($cline);
			if($DEBUG>2) log_txt(__METHOD__.": ch[$k] = ".arrstr($port));

			$prev = $cline[0]; $clength = 0;
			if($length == 0 && $prev) $line[] = $prev;
			if($cline) foreach($cline as $i => $s) {
				if($i == 0) continue;
				$clength += $di = Distance($prev,$s);
				if($DEBUG>3) log_txt(__METHOD__.": \$s=[{$s[0]},{$s[1]}] \$len[$z]=".$di);
				$line[] = $prev = $s;
				$z++;
			}
			$length += $clength;
			$this->features[] = array('type'=>'Feature','properties'=>array('type'=>'traceline','cable'=>$port['object'],
				'port'=>$port['id'],'length'=>round($clength),'style'=>1),"geometry"=>array("type"=>'LineString','coordinates'=>$cline));
			$dold = $port['device'];
		}
		// если цепочка не содержит кабелей то вставляем координаты первого узла
		if(count($line) == 0 && count($ch)>0) {
			$line[]=array($this->q->select("SELECT x, y FROM map_xy WHERE object = {$ch[0]['node']} ORDER BY object, slice, num",1));
		}
		$this->chainCap[$key] = $rev? array(end($line), $line[0]) : array($line[0], end($line));
		return true;
	}

	private function nodesRead(){ // для указания адресов устройств, читаются данные по всем встретившимся узлам
		global $DEBUG;
		$prevnode=-1; $nodes=array();
		foreach($this->chain as $key=>$ch){
			foreach($ch as $k=>$v) {
				if($prevnode!=$v['node'] && $v['node']>0) $nodes[] = $prevnode = $v['node'];
			}
		}
		$nodes = array_unique($nodes);
		$this->nodes = (count($nodes)>0)? $this->q->fetch_all("SELECT * FROM map WHERE id in (".implode(',',$nodes).") ORDER BY id") : array();
		foreach($this->devices as $k=>$dev) if($dev['type'] == 'onu') $this->clients++;
	}

	public function dumpChains(){
		if(!$this->chain || !$this->nodes || !$this->devices) return false;
		$out = "";
		foreach($this->chain as $k=>$ch){
			$b = reset($ch); $e = end($ch);
			$out .= sprintf("\n\n%3d)\tначало: %16s: [ узел:%5d порт:%3d id:%6d link:%6d ] %-20s\n",$k,$b['type']."(".$b['device'].")",$b['node'],$b['number'],$b['id'],$b['link'],$this->nodes[$b['node']]['address']);
			$out .= sprintf("\t конец: %16s: [ узел:%5d порт:%3d id:%6d link:%6d ] %-20s\n\n",$e['type']."(".$e['device'].")",$e['node'],$e['number'],$e['id'],$e['link'],$this->nodes[$e['node']]['address']);
			foreach($ch as $i=>$p){
				$out .= sprintf("%5s) %16s [узел:%5d порт:%3d id:%6d link:%6d] %-20s\n",$i,$p['type']."(".$p['device'].")",$p['node'],$p['number'],$p['id'],$p['link'],$this->nodes[$p['node']]['address']);
			}
		}
		return $out;
	}

	public function checkside($id,$side){
		$this->clearData();
		$this->trace($id,$side);
		$this->nodesRead();
		$this->root = false;
		if(count($this->chain)==1){
			$end = end($this->chain[0]);
			$this->devices[$end['device']]['port'] = $end['number'];
			$this->root = $this->devices[$end['device']];
		}else{
			foreach($this->devices as $k=>$d) if(!key_exists('parent',$d)){
				if($d['type']=='switch'){
					$ch = $this->chain[$d['chain']];
					$d['port'] = ($d['side']===0)? $ch[0]['number'] : arrfld(end($ch),'number');
				}
				$this->root = $d;
				break;
			}
		}
		return $this->root;
	}

	public function capdevices($id,$side=0){ // выдаёт начальное и конечное устройство для цепочки по id порта
		$this->clearData();
		$this->trace($id,$side);
		$this->nodesRead();
		$line = $this->clientLine();
		if(!$line || count($line)<2) return false;
		$r['ports'] = array('begin' => reset($line),'end' => end($line));
		$r['nodes'] = array('begin' => $this->nodes[$r['ports']['begin']['node']],'end' => $this->nodes[$r['ports']['end']['node']]);
		$r['begin'] = $this->devices[$r['ports']['begin']['device']];
		$r['end'] = $this->devices[$r['ports']['end']['device']];
		$r['clients'] = $this->clients;
		return $r;
	}

	public function clientLine($key=0){ // собирает полную цепочку портов в клиентской линии 
		global $DEBUG;
		if($DEBUG>0) log_txt(__METHOD__.": key=$key");
		if(!isset($this->chain[$key])) return array();
		$line = $this->chain[$key];
		if($DEBUG>0) log_txt(__METHOD__.": line[".count($line)."]");
		$prev0 = $this->devices[arrfld(reset($line),'device')];
		$prev1 = $this->devices[arrfld(end($line),'device')];
		$tmp = false;
		if(isset($prev0['input']) && $prev0['input'] != $key){
			$tmp = $prev0; $prev0 = $prev1; $prev1 = $tmp;
			$line = array_reverse($this->chain[$key]);
		}elseif(isset($prev1['input']) && $prev1['input'] != $key){
			$tmp = $prev1;
		}
		$iteration = 0; if($tmp) $nextchain = $tmp['input'];
		// добавляем в цепочку все цепочки соединённые через делители|сплиттеры ведущие к корню
		while($tmp && $nextchain && $iteration<1000) {
			$ch = $this->chain[$nextchain];
			$dev0 = $this->devices[arrfld(reset($ch),'device')];
			$dev1 = $this->devices[arrfld(end($ch),'device')];
			if($tmp['parent'] == $dev1['id']) {
				foreach($ch as $i=>$p) $line[] = $p; // прямая последовательность
			}elseif($tmp['parent'] == $dev0['id']){
				foreach(array_reverse($ch) as $i=>$p) $line[] = $p; // обратная последовательность
			}
			$tmp = (isset($this->devices[$tmp['parent']]))? $this->devices[$tmp['parent']] : false;
			$nextchain = (isset($tmp['input']))? $tmp['input'] : false;
			$iteration++;
		}
		if($iteration >= 1000) log_txt(__METHOD__.": Превышен порог итераций!");
		if($iteration>0) $this->gpon = true;
		return $line;
	}

	public function clLine($id){ // выдаёт линию на клиента с затуханиями
		$this->clearData();
		$this->trace($id);
		$this->nodesRead();
		$line = $this->sequence(0,'color');
		return $line;
	}

	public function traceFormat($format=0) { // Создаёт масив для формирования таблицы отображающей цепочку подключений
		$devnamed = array('client'=>0, 'patchpanel'=>1, 'switch'=>2);
		$r = array();
		$line = $this->sequence(0,'color');
		if($format == 0) $line = array_reverse($line); 
		$rucolor = $this->q->fetch_all("SELECT distinct color, rucolor FROM devprofiles ORDER BY color",'color');
		$prevnode = $prevdev = -1; $nodes = array(); $devs = array();
		foreach($line as $k=>$v) {
			$dev['id'] = $v['id'];
			$dev['port'] = $v['number'];
			if($v['type']=='cable') {
				$bandle = ($v['bandle'] != '')? "border:3px solid {$v['bandle']}" : "border-color:rgba(0,0,0,0)";
				$title = "цвет жилы: {$rucolor[$v['color']]}";
				$title .= ($v['bandle'] != '')? "\rцвет связки: {$rucolor[$v['bandle']]}" : "";
				$dev['port'] = "<span style=\"display:inline-block;background-color:{$v['color']};border-radius:10px;height:8px;width:8px;line-height:0.3;$bandle\" title=\"$title\"> </span>";
			}
			$name='';
			$dev['name'] = $v['device'];
			if($format == 1 && isset($devnamed[$v['type']])) $dev['name'] .= " ".$v['devname'];
			$dev['address'] = $v['address'];
			$r[] = $dev;
		}
		return $r;
	}

	public function treePorts($key, $fading=false, $sheme='htmlcolor', $freq = '1310nm'){ // последовательность устройств для <ul> все цепочки
		global $DEBUG, $devtype, $config;
		$ch = $this->chain[$key]; $rev=false;
		$len = count($this->chain);
		$d0 = $this->devices[$ch[0]['device']];
		$in = isset($d0['input'])? $d0['input'] : -1;
		$portup = ($d0['type']=='switch' && $d0['bandleports'])? $d0['bandleports'] : -1;
		if($rev = ($len>1 && $in == $key || $portup == $ch[0]['number'] || $d0['type']=='switch' && !$d0['community'])) $ch = array_reverse($ch);
		if($DEBUG>0) log_txt(__METHOD__.": chain $key  d0 {$d0['type']}({$d0['id']})   reverse ".arrstr($rev));

		$caps = array(reset($ch), end($ch)); // начальный и конечный порт в цепочке
		$devs = array($this->devices[$caps[0]['device']],$this->devices[$caps[1]['device']]);

		foreach($caps as $k=>$s) if(preg_match('/PON/',$s['module'])) $module = $s['module'];
		if(isset($module)) $this->fd['sfp'] = $config['map']['modules'][$module];

		$out = array();
		if(!isset($this->pcolor)) $this->pcolor=$this->q->fetch_all("SELECT distinct color, $sheme as mycolor FROM devprofiles ORDER BY color",'color');
		if(!is_array($this->pcolor)) $this->pcolor=array();

		if($fading === false) $fading = (isset($this->fd['sfp']))? $this->fd['sfp']: 4.0;
		$prev = false; $welding = 0; $couplers = 0; $all = count($ch);
		for($i=0; $i<$all; $i++) { // расчет затуханий в цепочке
			if($DEBUG>0 && !isset($ch[$i])) log_txt(__METHOD__.": no line[$i] всего:$all");
			if(!($p = @$ch[$i])) continue;
			$f = 0.0; $lf = 0.0;
			if($p['type']=='cable' && (!$prev || $prev['device'] != $p['device'])) $f += $lf = $this->cables[$p['object']]/1000 * $this->fd['cable'];
			if($prev && $prev['device'] != $p['device']){
				if($prev['porttype']=='fiber' && $p['porttype']=='fiber') $f += $this->fd['welding'];
				if($prev['porttype']=='coupler' xor $p['porttype']=='coupler') $f += $this->fd['coupler'];
			}
			if(isset($this->pon[$p['type']])) {
				$subtype = $this->devices[$p['device']]['subtype'];
				if($p['type']=='divisor') {
					if($p['number']>1) $f += $this->fd['divisor'][$subtype][$freq][$p['number']-2];
				} else { 
					if($p['number']>1) $f += $this->fd['splitter'][$subtype];
				}
			}
			$this->chain[$key][($rev? $all-$i-1 : $i)]['fading'] = $ch[$i]['fading'] = $fading += $f;
			if($p['type']=='cable' && isset($ch[$i+1]) && $ch[$i+1]['device'] == $p['device']) $ch[$i+1]['fading'] = $ch[$i]['fading'];
			if($DEBUG>2) log_txt(__METHOD__.": line[$i]={$p['id']}: {$p['type']}:{$p['device']}:[{$p['number']}] f=".sprintf("%.5f",$f)."  lf=".sprintf("%.5f",$lf)."  S=".sprintf("%.5f",$fading));
			$prev = $p;
		}
		foreach($ch as $k=>$v){
			if(isset($ch[$k-1]) && $ch[$k-1]['device']==$v['device'] && $ch[$k-1]['number']==$v['number']){
				if($v['id'] == $this->queryport) $this->queryport = $ch[$k-1]['id'];
				continue;
			}
			if(isset($this->pon[$v['type']]) && $v['number']==1){
				$d = $this->devices[$v['device']];
				$dev=$v;
				$dev['dev_id']=$dev['device'];
				if($v['color']!='') $dev['color']=@$this->pcolor[$v['color']];
				$dev['address']=$this->nodes[$v['node']]['address'];
				$dev['device']=get_devname($d);
				if(isset($dev['fading'])) $dev['device'] = sprintf("%.2f",$dev['fading'])." ".$dev['device'];
				for($np=2;$np<$v['numports']+1;$np++){ // проход по всем портам разветвителя
					if(!isset($d['childs'][$np])){
						$sp = $d['off'][$np];
						$sp['dev_id'] = $sp['device'];
						$sp['device'] = "";
						if($sp['color']!='') $sp['color'] = @$this->pcolor[$sp['color']];
					}else{
						$nextchain = $d['childs'][$np]['chain'];
						$sp = ($this->chain[$nextchain][0]['device'] == $v['device'])? $this->chain[$nextchain][0] : end($this->chain[$nextchain]);
						if($DEBUG>0) log_txt(__METHOD__.": device: {$d['id']}  port: {$v['id']}   {$d['type']}:$np   next chain: $nextchain");
						if($sp['color']!='') $sp['color'] = @$this->pcolor[$sp['color']];
						$sp['device']="";
						if(isset($sp['fading'])) $sp['device'] = sprintf("%.2f",$sp['fading'])." ".$sp['device'];
						$sp['sequence'] = $this->treePorts($nextchain,$fading,$sheme,$freq);
					}
					$dev['sequence'][] = $sp;
				}
			}elseif(isset($this->pon[$v['type']]) && $v['number']>1){
				continue;
			}else{
				if(isset($ch[$k+1]) && $ch[$k+1]['device']==$v['device'] && $ch[$k+1]['number']==$v['number'] && !$v['note'] && $ch[$k+1]['note'])
					$v['note'] = $ch[$k+1]['note'];
				$dev=$v;
				$dev['dev_id']=$dev['device'];
				if($v['color']!='') $dev['color']=@$this->pcolor[$v['color']];

				$name = "({$v['numports']})";
				if(($v['type']=='switch' || $v['type']=='server') && $v['ip']!='') $name = "({$v['ip']})";
				if($v['type']=='onu' || $v['type']=='mconverter') $name = "";
				if(isset($this->pon[$v['type']])) $name = "(".$this->devices[$v['device']]['subtype'].")";
				
				$dev['device']=$devtype[$v['type']]." ".$name;
				if(isset($v['fading'])) $dev['device'] = sprintf("%.2f",$v['fading'])." ".$dev['device'];
				$dev['address']=$this->nodes[$v['node']]['address'];
				if($v['type']=='cable' && isset($ch[$k+1])) $dev['address'].=' '.$this->nodes[$ch[$k+1]['node']]['address'];
				unset($dev['dir']);
			}
			$out[]=$dev;
		}
		return $out;
	}

	public function sequence($key, $sheme='htmlcolor', $freq = '1310nm'){ // последовательность устройств для <ul> в одной цепочке
		global $DEBUG, $devtype, $config;
		$line = $this->clientLine($key);
		$caps = array(reset($line),end($line));
		if(preg_match('/PON/',$caps[0]['module'])) $module = $caps[0]['module'];
		elseif(preg_match('/PON/',$caps[1]['module'])) $module = $caps[1]['module'];
		if(isset($module)) $this->fd['sfp'] = $config['map']['modules'][$module];
		$out = array();
		$portcolor=$this->q->fetch_all("SELECT distinct color, $sheme as mycolor FROM devprofiles ORDER BY color",'color');
		if(!$portcolor) $portcolor=array();
		if($this->gpon){
			$prev = false; $welding = 0; $couplers = 0; $fading = (isset($this->fd['sfp']))? $this->fd['sfp']: 4.0; $all = count($line)-1;
			for($i=$all; $i>=0; $i--){ // расчет затуханий в цепочке
				if($DEBUG>0 && !isset($line[$i])) log_txt(__METHOD__.": no line[$i] всего:$all");
				if(!($p = @$line[$i])) continue;
				$f = 0.0; $lf = 0.0;
				if($p['type']=='cable' && (!$prev || $prev['device'] != $p['device'])) $f += $lf = $this->cables[$p['object']]/1000 * $this->fd['cable'];
				if($prev && $prev['device'] != $p['device']){
					if($prev['porttype']=='fiber' && $p['porttype']=='fiber') $f += $this->fd['welding'];
					if($prev['porttype']=='coupler' xor $p['porttype']=='coupler') $f += $this->fd['coupler'];
				}
				if(isset($this->pon[$p['type']])) {
					$subtype = $this->devices[$p['device']]['subtype'];
					if($p['type']=='divisor') {
						if($p['number']>1) $f += $this->fd['divisor'][$subtype][$freq][$p['number']-2];
					} else { 
						if($p['number']>1) $f += $this->fd['splitter'][$subtype];
					}
				}
				$line[$i]['fading'] = $fading += $f;
 				if($p['type']=='cable' && isset($line[$i+1]) && $line[$i+1]['device'] == $p['device']) $line[$i+1]['fading'] = $line[$i]['fading'];
				if($DEBUG>2) log_txt(__METHOD__.": line[$i]={$p['id']}: {$p['type']}:{$p['device']}:[{$p['number']}] f=".sprintf("%.5f",$f)."  lf=".sprintf("%.5f",$lf)."  S=".sprintf("%.5f",$fading));
				$prev = $p;
			}
		}
		foreach($line as $k=>$v){
			if(isset($line[$k-1]) && $line[$k-1]['device']==$v['device'] && $line[$k-1]['number']==$v['number']) continue;
			if(isset($this->pon[$v['type']]) && $v['number']==1) continue;
			if(isset($line[$k+1]) && $line[$k+1]['device']==$v['device'] && $line[$k+1]['number']==$v['number'] && !$v['note'] && $line[$k+1]['note'])
				$v['note'] = $line[$k+1]['note'];
			$dev=$v;
			$dev['dev_id']=$dev['device'];
			if($v['color']!='') $dev['color']=@$portcolor[$v['color']];

			$name = "({$v['numports']})";
			if(($v['type']=='switch' || $v['type']=='server') && $v['ip']!='') $name = "({$v['ip']})";
			if($v['type']=='onu' || $v['type']=='mconverter') $name = "";
			if(isset($this->pon[$v['type']])) $name = "(".$this->devices[$v['device']]['subtype'].")";
			
			$dev['device']=$devtype[$v['type']]." ".$name;
			if(isset($v['fading'])) $dev['device'] = sprintf("%.3f",$v['fading'])." ".$dev['device'];
			$dev['address']=$this->nodes[$v['node']]['address'];
			if($v['type']=='cable' && isset($line[$k+1])) $dev['address'].=' '.$this->nodes[$line[$k+1]['node']]['address'];
			unset($dev['dir']);
			$out[]=$dev;
		}
		return $out;
	}
	
	public function get($id) { // Создаёт масив для отсылки GeoJSON линии и масива последовательности для html UL
		global $DEBUG, $config;
		if($DEBUG>0) log_txt(__METHOD__.": START! id=$id");
		$this->queryport = $id;
		$out=array();
		$icons = array();
		$this->clearData();
		$this->trace($id);
		$this->nodesRead();
		$len = count($this->chain);
		if($len==0) {
			if($DEBUG>0) log_txt(__METHOD__.": id=$id кол-во цепочек 0");
			return false;
		}elseif($len==1) {
			if($DEBUG>0) log_txt(__METHOD__.": id=$id кол-во цепочек 1");
			$this->Feature(0);
			foreach($this->features as $k=>$f) $f['properties']['style'] = 7;
			$out['geodata']=array('type'=>'FeatureCollection','features'=>$this->features);
			$out['sequence']=$this->treePorts(0);
		}else{
			foreach($this->chain as $k=>$ch) $this->Feature($k);
			// ищем корневое устройство
			foreach($this->devices as $k=>$dev){
				if(!isset($dev['parent'])) { // нужно определить частоту SFP модуля
					$freq = (isset($dev['freq']))? $dev['freq'] :'1310nm';
					if(isset($dev['module']) && preg_match('/PON/',$dev['module'])) $power = $config['map']['modules'][$dev['module']];
					$this->root = $k; // начальное устройсво
					$rch = array($this->chain[$this->devices[$k]['chain']][0],end($this->chain[$this->devices[$k]['chain']]));
					$this->devices[$k]['inport'] = $rch[$this->devices[$k]['side']]['id'];
					break;
				}
			}
			$out['sequence']=$this->treePorts($this->devices[$this->root]['chain']);
			$out['queryport'] = $this->queryport;
			foreach($this->devices as $k=>$dev){
				if($k == $this->root) { // делаем бирку для начального устройства
					if($DEBUG>0) log_txt(__METHOD__.": root: {$dev['type']}[$k] {$dev['name']}");
					$point = $this->q->get('map_xy',$dev['node1'],'object');
					if($point) {
						$xy = array($point['x'],$point['y']);
						if($DEBUG>0) log_txt(__METHOD__.": root xy: ".arrstr($xy));
						$icons[] = array(
							'type'=>'Feature',
							'properties'=>array('type'=>'divicon','fade'=>sprintf("%s к.",$this->clients),'port'=>$dev['inport'],
							'style'=>$this->numStyle($this->clients,$this->fd['clamount'])),
							'geometry'=>array("type"=>'Point','coordinates'=>$xy)
						);
					}
					continue;
				}
				$fading = false;
				if(isset($dev['input']) && $dev['out']){
					$i = ($this->chain[$dev['input']][0]['device'] == $dev['id'])? 0 : count($this->chain[$dev['input']])-1;
					$fading = $this->chain[$dev['input']][$i]['fading'];
					$this->features[$dev['input']]['properties']['style'] = $this->numStyle($fading);
				}
				// если есть координаты для устройства добавляем бирку с затуханием в features
				$xy = isset($dev['input'])? $this->chainCap[$dev['input']][$dev['cap']] : false;
				if($xy && $dev['out']) {
					if(!$fading) log_txt(__METHOD__.": ");
					$icons[] = array(
						'type'=>'Feature',
						'properties'=>array('type'=>'divicon','fade'=>sprintf("%.1f",$fading),'port'=>$dev['inport'],
						'style'=>$this->numStyle($fading)),'geometry'=>array("type"=>'Point','coordinates'=>$xy)
					);
				}
			}
			foreach($this->features as $k=>$f) if($f === false) unset($this->features[$k]);
			$out['geodata']=array('type'=>'FeatureCollection','features'=>array_merge($this->features,$icons));
			if($DEBUG>0) log_txt(__METHOD__.": root=".arrstr($this->root));
		}
		return $out;
	}

	private function numStyle($fading, $numstyle=false){
		if(!$numstyle) {
			$numstyle = $this->fd['numstyle'];
			foreach($numstyle as $k=>$v) if($fading > $v) return $k+1;
		}else{
			foreach($numstyle as $k=>$v) if($fading < $v) return $k+1;
		}
		return count($this->fd['numstyle'])+1;
	}
}

function nearBuild($node, $q) {
	global $DEBUG;
	$n = $q->select("SELECT x,y FROM map_xy WHERE object='$node' AND num=0",1);
	$h = $q->select("SELECT m.id, m.type, m.address, m.hostname, m.service, distance({$n['x']},{$n['y']},sum(h.x)/count(h.x),sum(h.y)/count(h.y)) as dist FROM map m, map_xy as h WHERE m.type='home' AND h.object=m.id AND h.num!=0 GROUP BY m.id ORDER BY dist LIMIT 1",1);
	if($DEBUG>0) log_txt(__FUNCTION__.": home = ".arrstr($h));
	return ($h['dist']<70)? $h : false;
}


function cutClients($id){ // выдаёт cписок отсоединяемых клиентов
	global $DEBUG;
	$out = false; $r = false;
	$tr1 = new Trace();
	$tr2 = new Trace();
	$tr = array($tr1,$tr2);
	$rd=array($tr1->checkside($id,1),$tr2->checkside($id,2));
	$together = $rd[0]['node1'] == $rd[1]['node1'];
	$pon = (count($tr1->chain)>1 || count($tr2->chain)>1)?1:0;
	$c = array(count($tr1->chain),count($tr2->chain)); // кол-во цепочек в разных участках ветви
	$up = array((isSw($rd[0],1) && $rd[0]['bandleports']==$rd[0]['port'])?1:0,(isSw($rd[1],1) && $rd[1]['bandleports']==$rd[1]['port'])?1:0);

	if($DEBUG>0){
		log_txt(__FUNCTION__.":\n\n сторона 1:".$tr1->dumpChains()."\n\n сторона 2:".$tr2->dumpChains()."\n");
		$sw = array(isSwitch($rd[0])?1:0,isSwitch($rd[1])?1:0);
		log_txt(__FUNCTION__.": id:$id pon=$pon sw=".arrstr($sw)." цепочек=".arrstr($c)." uplink=".arrstr($up)." вместе=".arrstr($together));
		log_txt(__FUNCTION__.": side 1) ".get_devname($rd[0])."  id:{$rd[0]['id']}  port:{$rd[0]['port']}  uplink:{$rd[0]['bandleports']}");
		log_txt(__FUNCTION__.": side 2) ".get_devname($rd[1])."  id:{$rd[1]['id']}  port:{$rd[1]['port']}  uplink:{$rd[1]['bandleports']}");
	}

	if($pon){
		if($rd[0]['id']!=$rd[1]['id']){
			if(isSwitch($rd[0]) && !isSwitch($rd[1])) $r=0;
			elseif(!isSwitch($rd[0]) && isSwitch($rd[1])) $r=1;
		}else{
			if($c[0]>1) $r=1;
			elseif($c[1]>1) $r=0;
		}
		if($r === false) log_txt(__FUNCTION__.": не найден корневой свич (pon)");
	}else{
		if($together) $r = false;
		elseif(isSw($rd[0],1) && !isSw($rd[1],1)) $r=0;
		elseif(!isSw($rd[0],1) && isSw($rd[1],1)) $r=1;
		elseif(isSw($rd[0],1) && isSw($rd[1],1) && $up[0] && !$up[1]) $r=1;
		elseif(isSw($rd[0],1) && isSw($rd[1],1) && !$up[0] && $up[1]) $r=0;
		elseif(isSwitch($rd[0]) && isSw($rd[1],1) && !isSwitch($rd[1])) $r=0;
		elseif(isSwitch($rd[1]) && isSw($rd[0],1) && !isSwitch($rd[0])) $r=1;
		if($r === false) log_txt(__FUNCTION__.": не найден корневой свич (flat)");
	}
	if($r === false) return false;

	if($DEBUG>0) log_txt(__FUNCTION__.": вариант ".($r+1).") ".($pon?"pon":"flat")." id:{$rd[$r]['id']}");
	$o = intval(!$r);
	$i = 0;
	foreach($tr[$o]->devices as $k=>$d) {
		$el = false;
		if((!$pon || !isset($d['childs'])) && $d['type']!='cable'){
			$i++;
			if($tr[$o]->nodes[$d['node1']]['type'] == 'client') {
				$el = array(
					'id'=>$tr[$o]->nodes[$d['node1']]['id'],
					'object'=>$tr[$o]->nodes[$d['node1']]['type'],
					'device'=>$d['id'],
					'type'=>$d['type'],
					'user'=>$tr[$o]->nodes[$d['node1']]['name'],
					'address'=>$tr[$o]->nodes[$d['node1']]['address'],
					'hostname'=>$tr[$o]->nodes[$d['node1']]['hostname'],
					'service'=>$tr[$o]->nodes[$d['node1']]['service'],
					'macaddress'=>$d['macaddress'],
				);
			}elseif(($d['type']=='switch' && (!$d['community'] || $d['bandleports']==$d['port']) || $d['type']=='onu') &&
				($home = nearBuild($d['node1'],$tr[$o]->q)) &&
				(preg_match('/'.$home['address'].'/i',$tr[$o]->nodes[$d['node1']]['address']) || $home['dist']<20)) {
				$el = array(
					'id'=>$home['id'],
					'object'=>'home',
					'device'=>$d['id'],
					'type'=>$d['type'],
					'address'=>$home['address'],
					'hostname'=>$home['hostname'],
					'service'=>$home['service'],
					'macaddress'=>$d['macaddress'],
				);
			}
			if($el) {
				$el['root'] = $rd[$r]['ip'];
				$el['rootid'] = $rd[$r]['id'];
				$el['rootname'] = $rd[$r]['name'];
				$el['community'] = $rd[$r]['community'];
				$el['rootport'] = $rd[$r]['port'];
				$out[] = $el;
			}
		}
	}
	return $out;
}

function getFeatureCollection($id=0){
	global $config;
	$q = new sql_query($config['db']);
	$q1 = new sql_query($config['db']);
	$cldevices = array('onu'=>'pon','mconverter'=>'gtth','wifi'=>'wifi');
	if(is_array($id)) {
		$mapfilter="and m.id in (".implode(',',$id).")"; 
		$devfilter="and object in (".implode(',',$id).")";
		$clfilter="and node1 in (".implode(',',$id).")";
	}elseif(is_numeric($id)&&$id>0) {
		$mapfilter="and m.id=$id";
		$devfilter="and object=$id";
		$clfilter="and node1=$id";
	}else{ 
		$mapfilter='';
		$devfilter='';
		$clfilter='';
	}
	$a['type']='FeatureCollection';
	
	$cables = $q->fetch_all("SELECT id,object,type,subtype,name,colorscheme,node1,node2,numports,bandleports,note FROM devices WHERE type='cable' $devfilter ",'object');

	$result = $q->query("
		SELECT 
			m.id, m.type, m.subtype, m.gtype, m.name, m.rayon, m.address, m.length, m.hostname, m.service, m.mrtg, m.connect, m.note,
			u.last_connection, xy.slice, xy.num, xy.x, xy.y
		FROM map m LEFT OUTER JOIN users u ON m.type='client' AND m.name=u.user, map_xy xy 
		WHERE xy.object=m.id $mapfilter
		ORDER BY m.address, m.id, xy.slice, xy.num
	");
	$old_id=-1;
	while($o = $result->fetch_assoc()) {
		foreach(array('slice','num','x','y') as $n){ $xy[$n] = $o[$n]; unset($o[$n]); }
		if($old_id!=$o['id']) {
			if($old_id!=-1) $a['features'][]=$f;
			if($o['type']=='cable') {
				foreach(array('address','hostname','service','mrtg','connect') as $v) unset($o[$v]);
				if(isset($cables[$o['id']])) foreach($cables[$o['id']] as $k=>$v) $o['dev_'.$k]=$v;
			}elseif($o['type']=='client' || $o['type']=='reserv') {
				foreach(array('mrtg','length') as $v) unset($o[$v]);
				if($o['subtype']=='wifi') {
					$o['ap'] = $q1->select("SELECT p2.device FROM devports p1, devports p2, devices d WHERE d.type='wifi' AND d.node1='{$o['id']}' AND p1.device=d.id AND p1.porttype='wifi' AND p2.id=p1.link LIMIT 1",4);
				}
			}
			if($o['type']!='client') unset($o['last_connection']);
			$f=array(
				'type'=>'Feature',
				"properties"=>$o,
				"geometry"=>array(
					"type"=>$o['gtype'],
				)
			);
			if($o['gtype']=='Polygon') {
				$f['geometry']['coordinates'][$xy['slice']][$xy['num']]=array($xy['x'],$xy['y']);
			}elseif($o['gtype']=='LineString'){
				$f['geometry']['coordinates'][$xy['num']]=array($xy['x'],$xy['y']);
			}elseif($o['gtype']=='Point'){
				$f['geometry']['coordinates']=array($xy['x'],$xy['y']);
				if($o['type']=='node'){
					$f['geometry']['properties']=array('point_type'=>'circle','radius'=>10);
				}elseif($o['type']=='client'){
					$f['geometry']['properties']=array('point_type'=>'marker');
				}elseif($o['type']=='reserv'){
					$f['geometry']['properties']=array('point_type'=>'reserv');
				}
			}
		}else{ // для объектов типа Point нужна только одна точка
			if($o['gtype']=='Polygon') {
				$f['geometry']['coordinates'][$xy['slice']][$xy['num']]=array($xy['x'],$xy['y']);
			}elseif($o['gtype']=='LineString'){
				$f['geometry']['coordinates'][$xy['num']]=array($xy['x'],$xy['y']);
			}
		}
		$old_id=$o['id'];
	}
	if($old_id!=-1) $a['features'][]=$f;
	if(!$id) { // формируем Wi-Fi коммуникации
		$a['features'] = array_merge($a['features'],getWiFiLinkFeatures());
	}
	return $a;
}

function getWiFiLinkFeatures($portid=0){
	global $config;
	$q = new sql_query($config['db']);
	$a = array();
	$filter = "";
	if(is_numeric($portid) && $portid>0) $filter = "AND (p1.id='$portid' OR p2.id='$portid')";
	elseif(is_array($portid)) $filter = "AND (p1.id in (".implode(',',$portid).") OR p2.id in (".implode(',',$portid)."))";
	$result = $q->query("
		SELECT 
			p1.id, concat(m1.address, ' - ', m2.address) as address,
			x1.x as x1,  x1.y as y1,  x2.x as x2,  x2.y as y2
		FROM devports p1, devports p2, devices d1, devices d2, map m1, map m2, map_xy x1, map_xy x2
		WHERE 
			p1.link = p2.id AND p2.id=p1.link AND p1.porttype='wifi' AND 
			p2.porttype='wifi' AND p1.device=d1.id AND p2.device=d2.id AND 
			d1.node1=m1.id AND d2.node1=m2.id AND d1.subtype='bridge' AND 
			d2.subtype='bridge' AND p1.id<p2.id AND x1.object=m1.id AND
			x2.object=m2.id $filter
	");
	while($w = $result->fetch_assoc()) {
		$o = array('id'=>$w['id'],'type'=>'wifilink','address'=>$w['address']);
		$f=array('type'=>'Feature',"properties"=>$o,"geometry"=>array("type"=>'LineString'));
		$f['geometry']['coordinates']=array(array($w['x1'],$w['y1']),array($w['x2'],$w['y2']));
		$a[]=$f;
	}
	return $a;
}

function save_new_geodata($f) { // создание отдельного объекта в базе
	global $config, $opdata, $objecttype, $devtype;
	$q = new sql_query($config['db']);
	$m = false; $client = false;
	if(!(ingrp('map') || $opdata['status']>=5)) stop(array('result'=>'ERROR','desc'=>"Доступ запрещен (new)!"));
	if(!is_array($f) || !isset($f['type']) || $f['type']!='Feature') {
		stop(array('result'=>'ERROR','desc'=>'Данные не соответствуют формату GeoJSON!'));
	}
	$p = $f['properties'];
	$center = isset($p['center'])? "(".implode(',',$p['center']).")" : '';
	$address = isset($p['address'])? $p['address'] : '';
	$map=array_intersect_key($p,$q->table_fields('map'));
	$map['gtype']=$f['geometry']['type'];
	$device=false;
	if($p['type']=='cable'){
		$device=array('type'=>'cable');
		foreach(array_intersect_key($p,$q->table_fields('devices','dev_')) as $k=>$v) {
			$device[preg_replace('/dev_/','',$k)]=$v;
		}
		$nodes = $q->fetch_all("SELECT * FROM map WHERE id in ('{$device['node1']}','{$device['node2']}')");
		// если кабель клиентский - изменяем название кабеля
		if($nodes && $nodes[0]['type']=='client') $client = $nodes[0];
		elseif($nodes && isset($nodes[1]) && $nodes[1]['type']=='client') $client = $nodes[1];
		if($client) $map['name'] = $device['name'] = 'cable_'.$client['name'];
		foreach($nodes as $n) $address .= (($address)?' - ':''). $n['address'];
	}
	if(isset($f['geometry']) && $f['geometry']['type']!='' && $f['geometry']['coordinates']!='') {
		if(is_array($f['geometry']['coordinates'][0])&&is_array($f['geometry']['coordinates'][0][0])){
			foreach($f['geometry']['coordinates'] as $slice=>$line) $xy=makePoints($slice,0,$line);
		}elseif(is_array($f['geometry']['coordinates'][0])){
			$xy=makePoints(0,0,$f['geometry']['coordinates']);
		}else{
			$xy=makePoints(0,0,array($f['geometry']['coordinates']));
		}
		if($map['gtype']=='LineString') $map['length'] = lineLength($xy);
	}else{
		log_txt(__FUNCTION__.': ERROR Отсутствуют геоданные!');
		return false;
	}
	if($p['type']!='cable'){
		$c = getCenter($xy);
		$map['rayon'] = $q->select("SELECT rid, Distance(longitude, latitude, '{$c[0]}', '{$c[1]}') as len FROM rayon WHERE latitude>0 and longitude>0 ORDER BY len LIMIT 1",4);
	}
	if(!($m = $map['id'] = $q->insert('map',$map))){
		log_txt(__FUNCTION__.': ERROR Не удалось записать данные map!');
		return false;
	}
	if($device) {
		$device['object']=$m;
		if(!$client) $device['name']=$device['type'].'_'.$m;
		$q->update_record('devices',$device,'object');
		$last_log = log_db("добавил объект[{$map['id']}]","id:({$device['id']}) тип:{$devtype[$device['type']]} название:{$map['name']} находится:$address");
	}else{
		$last_log = log_db("добавил объект[{$map['id']}]","тип:{$objecttype[$map['type']]} адрес:{$address} {$center}");
	}
	// т.к. длина вычисляется до вставки в map нужно проставить id объекта в записях координат
	foreach($xy as $k=>$v) $xy[$k]['object'] = $m;
	if(($map_xy = $q->insert('map_xy',$xy)) === false) {
		$q->query("DELETE FROM map WHERE id=$m; DELETE FROM `log` WHERE unique_id='$last_log'");
		log_txt(__FUNCTION__.': ERROR Не удалось записать данные map_xy!');
		return false;
	}
	return $m;
}

function save_feature($f) { // изменение отдельного объекта в базе
	global $config, $opdata, $devtype;
	$fields = array('node1'=>0,'node2'=>1);
	$q = new sql_query($config['db']);
	$m = false;
	// разные проверки
	if(!(ingrp('map') || $opdata['status']>=5)) stop(array('result'=>'ERROR','desc'=>"Доступ запрещен!"));
	if(!is_array($f) || !isset($f['type']) || !($f['type']=='Feature')) {
		log_txt(__FUNCTION__.": Неверные входные данные!\n feature=".sprint_r($f)); return false;
	}
	$p = (isset($f['properties']))? $f['properties'] : false;
	if(!$p || !isset($p['id'])) {
		log_txt(__FUNCTION__.": Отсутствует properties!\n feature=".sprint_r($f)); return false;
	}
	if(!($mapold=$q->select("SELECT * FROM map WHERE id={$p['id']}",1))) {
		log_txt(__FUNCTION__.": В базе не найден объект id:{$p['id']} !\n"); return false;
	}
	$m = $p['id'];
	$map=array_intersect_key($p,$q->table_fields('map'));
	// разбираемся с геометрией
	$map['gtype']=$f['geometry']['type'];
	if(isset($f['geometry']) && $f['geometry']['type']!='' && is_array($f['geometry']['coordinates'])) {
		$old_xy=$q->fetch_all("SELECT `object`,`slice`,`num`,`x`,`y` FROM map_xy WHERE object={$p['id']} ORDER BY slice, num",'num');
		if(is_array($f['geometry']['coordinates'][0]) && is_array($f['geometry']['coordinates'][0][0])){
			foreach($f['geometry']['coordinates'] as $slice=>$line) $xy=makePoints($slice,$map['id'],$line);
		}elseif(is_array($f['geometry']['coordinates'][0])){
			$xy=makePoints(0,$map['id'],$f['geometry']['coordinates']);
		}else{
			$xy=makePoints(0,$map['id'],array($f['geometry']['coordinates']));
		}
		if($map['gtype']=='LineString') $map['length'] = lineLength($xy);
	}
	if(!is_array($xy)) {
		log_txt(__FUNCTION__.": ошибка входных гео координат!\n");
		return false;
	}
	$obj = $q->compare($mapold,$map);
	if($q->del("map_xy",$map['id'],'object') === false) log_txt(__FUNCTION__.": Старые координаты не удалены!");
	if($q->insert('map_xy',$xy) === false) log_txt(__FUNCTION__.": Данные координат не сохранены!");
	$device=false;
	if($p['type']=='cable'){
		foreach(array_intersect_key($p,$q->table_fields('devices','dev_')) as $k=>$v) $cable[preg_replace('/dev_/','',$k)]=$v;
		if($DEBUG>0) log_txt(__FUNCTION__.": пришло device: ".arrstr($cable));
		$devold=$q->select("SELECT * FROM devices WHERE object={$p['id']}",1);
		if($devold) $pold=$q->select("SELECT p1.node as n1, p2.node as n2 FROM devports p1, devports p2 WHERE p1.number=1 AND p2.number=1 AND p1.device=p2.device AND p1.id<p2.id AND p1.device='{$devold['id']}'",1); else $pold = false;
		$ops = array("<",">");
		if($pold && ($pold['n1'] == $devold['node2'] || $pold['n2'] == $devold['node1'])) $ops = array(">","<");
		$cmp = $q->compare($devold,$cable);
		$cable = array_merge($devold,$cable);
		// обновляем порты при изменении начала или конца кабеля
		foreach($cmp as $k=>$v) {
			if(isset($fields[$k])) { // если $k == node1 || node2
				$value = ($v=='' || $v==0)? "NULL" : $v;
				// конец кабеля перемещён на клиента
				if(($n = $q->get('map',$v)) && $n['type']=='client'){
					if(!($cl = $q->select("SELECT * FROM devices WHERE type!='cable' AND node1='{$n['id']}'",1))){
						$cl = make_client_device($q,$n);
					}
				}
			}
		}
		if(is_array($nodes = array_values(array_intersect_key($cmp,$fields))) && count($nodes)>0){
			foreach($nodes as $k=>$v) $nodes[$k] = "'$v'";
			$client = $q->select("SELECT * FROM map WHERE type='client' AND id in (".implode(',',$nodes).")",1);
			if($client) $cmp['name'] = 'cable_'.$client['name'];
		}
		if(count($cmp)>0) {
			$cmp['id']=$devold['id'];
			if($DEBUG>0) log_txt(__FUNCTION__.": изменил {$devtype[$devold['type']]}[{$devold['id']}]: ".arrstr($cmp));
			if($q->update_record('devices',$cmp)) dblog('devices',$cmp,$devold);
		}
	}
	if(count($obj)>0) {
		$obj['id']=$p['id'];
		if(isset($client)) $obj['name'] = 'cable_'.$client['name'];
		if($DEBUG>0) log_txt(__FUNCTION__.": обновляется map: {$mapold['type']}[{$mapold['id']}]: ".arrstr($obj));
		if($q->update_record('map',$obj)) dblog('map',$obj);
	}
	return $m;
}

function save_geodata($object) {
	global $config, $opdata, $DEBUG;
	$q = new sql_query($config['db']);
	$firsttype = array('cable'=>1,'node'=>1,'client'=>1);
	if(!(ingrp('map') || $opdata['status']>=5)) stop(array('result'=>'ERROR','desc'=>"Доступ запрещен!"));
	$m = false;
	if($DEBUG>0) log_txt('save_geodata: '.sprint_r($object));
	if($object['type']=='Feature') {
		if($tmp = save_feature($object)) $m[] = $tmp;
	}elseif($object['type']=='FeatureCollection') {
		if($fc = $object['features']) {
			// сперва сохраняем данные узлов, т.к. при сохранении остального необходима опора на эти данные
			foreach($fc as $f) {
				if(isset($firsttype[@$f['properties']['type']])) if($tmp=save_feature($f)) $m[]=$tmp;
			}
			foreach($fc as $f) {
				if(!isset($firsttype[@$f['properties']['type']])) if($tmp=save_feature($f)) $m[]=$tmp;
			}
		}
	}
	return $m;
}

function deleteFeatures($ids) {
	global $config, $opdata, $objecttype, $DEBUG, $errors, $modified;
	if(!(ingrp('map') || $opdata['status']>=5)) stop(array('result'=>'ERROR','desc'=>"Доступ запрещен!"));
	if(is_numeric($ids)) $ids = array($ids);
	if(!is_array($ids)||count($ids)==0){
		$errors[] = "Объектов для удаления не найдено!";
		return false;
	}
	$q = new sql_query($config['db']);
	$del_ids=implode(',',$ids);
	$f=$q->select("SELECT * FROM map WHERE id IN ($del_ids)");
	$cables=array(); $nodes=array(); $mcables = array(); $clients = array(); $clientlog = array(); $nodelog = array(); $wifi = array();
	foreach($f as $k=>$v) {
		$del[]=$v['id'];
		if($v['type']=='cable') $cables[] = $v['id'];
		if($v['type']=='node') { $nodes[] = $v['id']; $nodelog[] = "{$v['address']} ({$v['id']})"; }
		if($v['type']=='client') { $clients[] = $nodes[] = $v['id']; $clientlog[] = "{$v['name']}: {$v['address']}"; }
	}
	if(count($clients)>0) {
		$clcables = $q->fetch_all("SELECT c.object FROM map m, devices c WHERE m.id in (".implode(',',$clients).") AND c.type='cable' AND (c.node1 = m.id OR c.node2 = m.id)");
		foreach($clcables as $k=>$v) if(!array_search($v,$del)) $del[] = $cables[] = $v;
	}
	// удаление устройств на узлах
	if(count($nodes)>0) {
		// ести в списке есть узлы - удаляем устройства на них
		$n=implode(',',$nodes);
		$node_devices=$q->select("SELECT * FROM devices WHERE node1 in ($n) or node2 in ($n)");
		foreach($node_devices as $k=>$v) {
			if($v['type']=='cable') {
				if(!array_search($v['object'],$del)) { // если кабель заходит на узел но его нет в удаляемых
					$mcables[]=$v['id'];
				}
			}else{
				if($v['type']=='wifi' && $v['connect']) $wifi[] = $v['connect'];
				$devices[]=$v['id'];
				$devlog[] = "{$objecttype[$v['type']]}[{$v['id']}]({$v['name']})";
			}
		}
		if(count($devices)>0) {
			// устройства не кабели - связи разрывать не нужно
			$d=implode(',',$devices);
			$q->query("DELETE FROM devports WHERE device in ($d)");
			$q->query("DELETE FROM devices WHERE id in ($d)");
			log_db("удалил устройства",implode(',',$devlog));
		}
	}
	// разрываем связи не удаляемых кабелей
	if(count($mcables)>0) {
		$d=implode(',',$mcables);
		$q->query("UPDATE devices SET node1=null WHERE node1 in ($n) and id in($d)");
		$q->query("UPDATE devices SET node2=null WHERE node2 in ($n) and id in($d)");
		$q->query("UPDATE devports SET link=null, node=null WHERE node in ($n) and device in($d)");
		$modified = (isset($modified)&&is_array($modified))? array_merge($modified,$mcables) : $mcables;
	}
	// удаление кабелей
	if(count($cables)>0) {
		if($DEBUG>0) log_txt(__FUNCTION__.": cables=".arrstr($cables));
		$ct = $config['map']['cabletypes'];
		// ести в списке есть устройства убираем все их соединения
		$c=implode(',',$cables); $cc = array();
		$cable_devices = $q->fetch_all("SELECT d.id, d.numports, m.subtype, m1.id n1, m1.type t1, m1.address a1, m2.id n2, m2.type t2, m2.address a2 FROM devices d LEFT OUTER JOIN map m1 ON d.node1=m1.id LEFT OUTER JOIN map m2 ON d.node2=m2.id, map m WHERE d.object=m.id AND d.object in ($c)");
		foreach($cable_devices as $k=>$v) {
			$cd[] = $v['id'];
			if($v['t1']=='client') $cc[] = $v['n1'];
			if($v['t2']=='client') $cc[] = $v['n2'];
			$log[] = "{$ct[$v['subtype']]}[{$v['id']}] {$v['numports']}ж {$v['a1']} - {$v['a2']}";
		}
		if(count($cc)>0) $q->query("UPDATE map SET connect=NULL WHERE id in (".implode(',',$cc).")");
		if($cd = implode(',',$cd)){
			log_db('удалил кабели',implode(', ',$log));
		}
	}
	if(count($wifi)>0){
		$a = $q->fetch_all("SELECT id FROM map WHERE type='client' AND subtype='wifi' AND connect in (".implode(',',$wifi).")");
		$q->query("UPDATE map SET connect=NULL WHERE id in (".implode(',',$a).")");
		$modified = (isset($modified)&&is_array($modified))? array_merge($modified,$a) : $a;
	}
	if(count($cc)>0){
		$q->query("UPDATE map SET connect=NULL WHERE type='client' AND id in (".implode(',',$cc).")");
		$modified = (isset($modified)&&is_array($modified))? array_merge($modified,$cc) : $cc;
	}
	// теперь можно удалить все объекты GeoData
	if($del && count($del)>0){
		$d=implode(',',$del);
		$q->query("DELETE FROM map WHERE id in ($d)");
		if($clientlog) log_db('удалил клиентов',implode(', ',$clientlog));
		if($nodelog) log_db('удалил узлы',implode(', ',$nodelog));
		return $del; 
	}else{
		$errors[] = "Объектов для удаления не найдено!";
		return false;
	}
}

function make_client_device($q,$c,$d=false) { // создание клиентского устройства
	global $config;
	$dtype = $config['map']['clientdevtypes'][$c['subtype']];
	$cldev = array('type'=>$dtype,'subtype'=>(($c['subtype']=='wifi')?'station':''),'node1'=>$c['id'],'node2'=>null);
	if($d) $cldev = array_merge($cldev,$d);
	$cldev['id'] = $q->insert("devices",$cldev);
	return $cldev;
}

function n3removeservice($port,$save='no',$restart='no'){ // удаление сервиса из Nagios при рассоединении
	global $config, $NAGIOS_ERROR, $N3MODIFY;
	$q = new sql_query($config['db']);
	if(!is_numeric($port)){
		$NAGIOS_ERROR = array('result'=>'ERROR','desc'=>"некорректный запрос! ($port)");
		return false;
	}
	$id=$port;
	if(!($all = cutClients($id))){
		$NAGIOS_ERROR = array('result'=>'ERROR','desc'=>"не найдены объекты на порту $id!");
		return false;
	}
	foreach($all as $k=>$v){
		if($v['hostname'] && $v['service'])
			$hs[$v['hostname']][$v['service']] = $v['id'];
	}
	foreach($hs as $k=>$v) $r[$k] = array_keys($v);
	$req = "do=service_del&objects=".urlencode(json_encode($r))."&realsave=$save&restart=$restart";
	if(!($res = get_nagios($req,'data'))) return false;
	$ids = array();
	foreach($res['deleted'] as $k=>$s) {
		foreach($s as $i=>$v) {
			if(!isset($hs[$k][$v])){ log_txt(__FUNCTION__.": не нахожу id для {$k}[{$v}]"); return false; }
			$ids[] = $hs[$k][$v];
		}
	}
	if(count($ids)==0){ log_txt(__FUNCTION__.": нет объектов для изменения"); return false; }
	$sql = "UPDATE `map` SET hostname='', service='' WHERE id in (".implode(',',$ids).")";
	if(isset($res['realsave']) && $res['realsave'] != 'no'){
		if($q->query($sql)) { if(@is_array($N3MODIFY)) $N3MODIFY = array_merge($N3MODIFY,$ids); else $N3MODIFY = $ids; }
		log_txt(__function__.": отключено ".$q->modified()." клиентов");
		return $res;
	}else{
		log_txt(__FUNCTION__.": realsave = no, SQL: $sql");
		return false;
	}
}

function n3createservice($id,$save='no',$restart='no'){ // создание сервиса Nagios при соединении
	global $config, $NAGIOS_ERROR, $N3MODIFY;
	$q = new sql_query($config['db']);
	if(!($all = cutClients($id))){
		$NAGIOS_ERROR = array('result'=>'ERROR','desc'=>"не найдены объекты на порту $id!");
		return false;
	}
	include_once 'snmpclass.php';
	$sw = new switch_snmp($q->get("devices",$all[0]['device']));
	if($ports = $sw->ports(null,0)) foreach($ports as $k=>$v) {
		if($v['number'] == $all[0]['rootport']) $rootport = $k;
	}
	
	foreach($all as $i=>$o){
		$s = array(
			"id"=>$o['id'],
			"address"=>preg_replace('/.*(ул\.|пер\.|пл\.|пр\.|мн\.|м-н\.|пс\.|п\.)/','',$o['address']),
			"community"=>$o['community']
		);
		if($o['type']=='switch' || $o['type']=='mconverter'){
			$s["port"] = isset($rootport)? $rootport : $o['rootport'];
			if($o['object']=='client') $s["check_command"]="snmp_client!{$o['community']}!{$s["port"]}";
			else $s["check_command"]="snmp_switch_port!{$o['community']}!{$s["port"]}";
		}elseif($o['type']=='onu'){
			$s["check_command"]="snmp_mac_onu_signal!{$o['community']}!{$o['macaddress']}"; // ????? как мониторинг дома отличается от клиента
			$s["mac"]=$o['macaddress'];
		}
		if($o['object']=='client'){
			$s['address'] = 'client '.$s['address'];
			$s['use'] = "generic-service-client";
		}
		$hs[$o['root']][] = $s;
		$r[$s['address']] = $o['id'];
	}
//	log_txt(__FUNCTION__.": hs = ".sprint_r($hs));
	$req = "do=service_add&objects=".urlencode(json_encode($hs))."&realsave=$save&restart=$restart";
	if(!($res = get_nagios($req,'data'))) return false;
	foreach($res['created'] as $sw=>$list) {
		foreach($list as $s=>$id) {
//			log_txt(__FUNCTION__.": установка hostname и service в `map` для ".arrstr($id));
			$sql = "UPDATE map SET hostname='$sw', service='$s' WHERE id='$id'";
			if(isset($res['realsave']) && $res['realsave'] != 'no'){
				$q->query($sql);
				$ids[] = $id;
			}else{
				log_txt(__FUNCTION__.": realsave = no, SQL: $sql");
			}
		}
	}
	if(@is_array($N3MODIFY)) $N3MODIFY = array_merge($N3MODIFY,$ids); else $N3MODIFY = $ids;
	return $ids;
}

function switch_services($switch) {
	global $config, $NAGIOS_ERROR;
	if(!is_array($switch)){
		$NAGIOS_ERROR = array('result'=>'ERROR','desc'=>__FUNCTION__.': неверные входные параметры!');
		return false;
	}
	$q = new sql_query($config['db']);

	$tr = new Trace();
	$ports = $q->get('devports',$switch['id'],'device');
	foreach($ports as $k=>$port){
		$s = false;
		$caps = $tr->capdevices($port['id']);
		foreach(array('begin','end') as $k=>$n) {
			if($caps[$n]['type']=='switch' && $caps[$n]['community']=='') $caps[$n]['type'] = 'sw';
			$cp[$caps[$n]['type']] = $n;
		}
		if(!isset($cp['switch']) && (!isset($cp['client']) || !isset($cp['sw']))) continue;
		if($caps[$cp['switch']]['ip']!='' && $caps[$cp['switch']]['community']!='' && isset($addr[$caps[$cp['switch']]['ip']])){
			$h['parents'] = $addr[$caps[$cp['switch']]['ip']];
		}
		if(isset($cp['switch']) && isset($cp['client'])){ // если конечные уст-ва свич и клиент
			$s = array(
				"address"=>"client ".preg_replace('/^(ул\.|пер\.|пл\.|пр\.|мн\.|м-н\.|пс\.|п\.)/','',$caps['nodes'][$cp['client']]['address']),
				"community"=>$caps[$cp['switch']]['community']
			);
			if($caps[$cp['client']]['macaddress']!='')
				$s["mac"] = $caps[$cp['client']]['macaddress'];
			else
				$s["port"] = $caps['ports'][$cp['switch']]['number'];
		}elseif(isset($cp['switch']) && isset($cp['sw'])){ // если конечные уст-ва свич и тупой свич
			if(!($obj = $q->select("SELECT * FROM map WHERE type='home' AND address='{$caps['nodes'][$cp['sw']]['address']}'",1))) continue;
			$s = array(
				"address"=>$caps['nodes'][$cp['sw']]['address'],
				"community"=>$caps[$cp['switch']]['community'],
				"check_command"=>"snmp_switch_port!{$caps[$cp['switch']]['community']}!{$caps['ports'][$cp['switch']]['number']}"
			);
		}
		if($s) $services[] = $s;
	}
	return $services;
}

function n3switch_new($switch){ // создание конфигурации свича в Nagios
	global $config, $NAGIOS_ERROR;
	if(!is_array($switch)){
		$NAGIOS_ERROR = array('result'=>'ERROR','desc'=>__FUNCTION__.': неверные входные параметры!');
		return false;
	}
	$q = new sql_query($config['db']);
	$node = $q->get('map',$switch['node1']);
	if(!($addr = get_nagios("do=get_hosts&key=address",'hosts'))) return false;
	if($switch['ip']!='' && isset($addr[$switch['ip']])){
		$NAGIOS_ERROR = array('result'=>'ERROR','desc'=>'присутствует конфигурация свича!');
		return false;
	}
	$adr = strRuEng(preg_replace('/^(ул\.|пер\.|пл\.|пр\.|мн\.|м-н\.|пс\.|п\.)([^ ]{1,3}[бвгджзклмнпрстфхцчшщ]{1,2})\w* (\d+)/','$2$3',$node['address']));
	$h = array(
		'use'=>'generic-switch',
		'host_name'=>"SW-{$switch['name']}-".strtoupper($adr)."-".preg_replace('/.*\./','',$switch['ip']),
		'alias'=>"Switch {$switch['name']} {$adr}",
		'address'=>$switch['ip'],
	);
	return $h;
}

function n3switch_del($switch){ // удаление конфигурации свича в Nagios
	global $config, $NAGIOS_ERROR;
	if(!is_array($switch)) return false;
	$q = new sql_query($config['db']);
	$node = $q->get('map',$switch['node1']);
	$ports = $q->get('devports',$switch['id'],'device');
	if(!($addr = get_nagios("do=get_hosts&key=address",'hosts'))) return false;
	$h = array(
		'use'=>'generic-switch',
		'host_name'=>"SW-{$switch['name']}-".preg_replace('/.*\./','',$switch['ip']),
		'alias'=>"Switch {$switch['name']} {$node['address']}",
		'address'=>$switch['ip'],
		'parents'=>$switch['parents'],
	);
	return $h;
}

function updateDbPorts($device) {
	global $q, $errors, $opdata;
	if(!$device || !$q) return false;
	if(is_numeric($device)) $device = $q->get("devices",$device);
	$sw = new switch_snmp($device); $counter = 0;
	if(!$sw->online){
		if($opdata && key_exists('login',$opdata)) use_ws_server(array("wstype"=>'notify','type'=>"error",
			'to'=>$opdata['login'],"message"=>get_devname($device,0,0)." не отвечает!"));
		return false;
	}
	$vlans = $sw->vlans();
	$swports = $sw->ports(array(),1);
	if($swports){
		$dbports = $q->fetch_all("SELECT number, id FROM devports WHERE device='{$device['id']}' ORDER BY number",'number');
		if(count($dbports) && $vlans) $q->query("DELETE FROM vlans WHERE port in (".implode(',',$dbports).")");
		foreach($swports as $k=>$p){
			$c = 0;
			if(isset($dbports[$p['number']]) && ($portid = $dbports[$p['number']])){
				$c = $q->query("UPDATE devports SET name='{$p['name']}', snmp_id='{$k}' WHERE id='$portid'");
				if(isset($vlans[$k])) foreach($vlans[$k] as $vl=>$v) {
					$q->insert("vlans",array("port"=>$portid,"vlan"=>$v['vlan'],"tagged"=>$v['tagged']));
				}
			}
			$counter += $c;
		}
	}
	return $counter;
}

function isSwitch($dev){
	global $q;
	if(is_numeric($dev)) $dev = $q->get('devices',$dev);
	return ($dev['type']=='switch' && $dev['ip']!='' && $dev['community']!='')? $dev : false;
}

function isSw($dev,$strong=0){
	global $q;
	if(is_numeric($dev)) $dev = $q->get('devices',$dev);
	if($strong) $sw = ($dev['type']=='switch')? $dev : false;
	else $sw = ($dev['type']=='switch' || $dev['type']=='mconverter')? $dev : false;
	return $sw;
}
?>
