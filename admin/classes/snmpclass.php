<?php
include_once("geodata.php");
include_once("map.cfg.php");
include_once("devices.cfg.php");
include_once("classes.php");

$snmp_conf = array( // настройки для работы со свичами
	'version' => SNMP::VERSION_2c,
	'cachetimeout' => 300,
	'swChank'=>array('unknown'=>1),
	'walk_timeout'=>10000000,
	'walk_retries'=>1,
	'gat_timeout'=>3000000,
	'get_retries'=>1,
	'oids' => array(
		'ObjectID'=>array( // sysObjectID.0 (.1.3.6.1.2.1.1.2.0)
			'.1.3.6.1.4.1.17409' => 'C-DATA',
			'.1.3.6.1.4.1.3320.1' => 'BDCOM',
			'.1.3.6.1.4.1.171' => 'DLINK',
			'.1.3.6.1.4.1.1411' => 'BLADE',
			'.1.3.6.1.4.1.3955' => 'LinkSys',
			'.1.3.6.1.4.1.10456' => 'PLANET',
			'.1.3.6.1.4.1.6486.800' => 'Alcatel',
			'.1.3.6.1.4.1.259.6' => 'Edge-Core',
			'.1.3.6.1.4.1.1991' => 'TurboIron-X24',
		),
		'ALL' => array(
			'sysname'	=> array('label' =>'Название свича','OID'=>'.1.3.6.1.2.1.1.5.0','timeout'=>86400),
			'sysdescr'	=> array('label' =>'Данные свича','OID'=>'.1.3.6.1.2.1.1.1.0','timeout'=>86400),
			'model'	=> array('label' =>'Модель свича','OID'=>'.1.3.6.1.2.1.1.2.0','timeout'=>86400),
			'name'	=> array('label' =>'Название порта','OID'=>'.1.3.6.1.2.1.31.1.1.1.1.PORT','timeout'=>300),
			'descr'	=> array('label' =>'Описание порта','OID'=>'.1.3.6.1.2.1.2.2.1.2.PORT'),
			'alias'	=> array('label' =>'Примечание','OID'=>'.1.3.6.1.2.1.31.1.1.1.18.PORT'),
			'type'	=> array('label' =>'Тип порта','OID'=>'.1.3.6.1.2.1.2.2.1.3.PORT'),
			'status'=> array('label' =>'Статус порта','OID'=>'.1.3.6.1.2.1.2.2.1.8.PORT'),
			'adminstatus' => array('label' =>'Адм.статус','OID'=>'.1.3.6.1.2.1.2.2.1.7.PORT'),
			'mac_on_vlan' => array('label'=>'Список MAC адресов','OID'=>'.1.3.6.1.2.1.17.7.1.2.2.1.2','timeout'=>300),
			'mac_on_port' => array('label'=>'Список MAC адресов','OID'=>'.1.3.6.1.2.1.17.4.3.1.2'),
			'lldp_mac' => array('label'=>'LLDP MAC адреса','OID'=>'.1.0.8802.1.1.2.1.4.1.1.5'),
			'lldp_port_desc' => array('label'=>'Названия портов','OID'=>'.1.0.8802.1.1.2.1.4.1.1.8'),
			'lldp_system' => array('label'=>'Имена систем','OID'=>'.1.0.8802.1.1.2.1.4.1.1.9'),
			'vlan_name' => array('label'=>'Название VLAN','OID'=>'.1.3.6.1.2.1.17.7.1.4.3.1.1.VLAN','type'=>'STRING'),
			'vlan_port' => array('label'=>'Порты VLAN','OID'=>'.1.3.6.1.2.1.17.7.1.4.3.1.2.VLAN'),
			'vlan_forb' => array('label'=>'Запрещённые порты VLAN','OID'=>'.1.3.6.1.2.1.17.7.1.4.3.1.3.VLAN'),
			'vlan_untg' => array('label'=>'Порты без тега VLAN','OID'=>'.1.3.6.1.2.1.17.7.1.4.3.1.4.VLAN'),
		),
		'BDCOM' => array(
			'onu'	=> array('label' =>'Мак адрес ONU','OID'=>'.1.3.6.1.4.1.3320.101.10.1.1.3.PORT','type'=>'Hex-STRING'),
			'vlan_onu' => array('label'=>'пользовательский влан','OID'=>'.1.3.6.1.4.1.3320.101.12.1.1.3.PORT.1'),
			'tx_power' => array('label'=>'уровень сигнала на передачу','OID'=>'.1.3.6.1.4.1.3320.101.10.5.1.6.PORT','div'=>10),
			'rx_power' => array('label'=>'уровень сигнала на приём','OID'=>'.1.3.6.1.4.1.3320.101.10.5.1.5.PORT','div'=>10),
			'distance' => array('label'=>'дистанция','OID'=>'.1.3.6.1.4.1.3320.101.10.1.1.27.PORT')
		),
		'C-DATA' => array(
			'name'	=> array('label' =>'Название порта','OID'=>'.1.3.6.1.2.1.2.2.1.2.PORT','timeout'=>300),
			'hw_version'	=> array('label' =>'Название порта','OID'=>'.1.3.6.1.4.1.17409.2.3.1.3.1.1.7.1.0','timeout'=>300),
			'sw_version'	=> array('label' =>'Название порта','OID'=>'.1.3.6.1.4.1.17409.2.3.1.3.1.1.9.1.0','timeout'=>300),
			'mac_on_vlan' => array('label'=>'Список MAC адресов','OID'=>'.1.3.6.1.4.1.17409.2.3.2.4.2.1.4','timeout'=>300),
			'onu'	=> array('label' =>'Мак адрес ONU','OID'=>'.1.3.6.1.4.1.17409.2.3.4.1.1.7.PORT','type'=>'Hex-STRING'),
			'vlan_onu' => array('label'=>'пользовательский влан','OID'=>'.1.3.6.1.4.1.17409.2.3.7.3.1.1.3.PORT.0.1'),
			'tx_power' => array('label'=>'уровень сигнала на передачу','OID'=>'.1.3.6.1.4.1.17409.2.3.4.2.1.5.PORT.0.0','div'=>100),
			'rx_power' => array('label'=>'уровень сигнала на приём','OID'=>'.1.3.6.1.4.1.17409.2.3.4.2.1.4.PORT.0.0','div'=>100),
			'onu_temp' => array('label'=>'температура','OID'=>'.1.3.6.1.4.1.17409.2.3.4.2.1.8.PORT.0.0','div'=>100),
			'distance' => array('label'=>'дистанция','OID'=>'.1.3.6.1.4.1.17409.2.3.4.1.1.15.PORT'),
			'inOctet' => array('label'=>'дистанция','OID'=>'.1.3.6.1.4.1.17409.2.3.10.1.1.4.PORT'),
			'outOctet' => array('label'=>'дистанция','OID'=>'.1.3.6.1.4.1.17409.2.3.10.1.1.26.PORT'),
			'vlan_name' => array('label'=>'Название VLAN','OID'=>'.1.3.6.1.4.1.17409.2.3.7.2.1.1.3.VLAN.16777216','type'=>'STRING'),
			'vlan_port' => array('label'=>'Порты VLAN','OID'=>'.1.3.6.1.4.1.17409.2.3.7.2.1.1.4.VLAN.16777216'),
			'vlan_untg' => array('label'=>'Порты без тега VLAN','OID'=>'.1.3.6.1.4.1.17409.2.3.7.2.1.1.5.VLAN.16777216'),
		),
		'BLADE' => array(),
	),
);

if(!class_exists('SNMP')) {
	class SNMP {
		const 
			VERSION_1  = 1,
			VERSION_2C = 2,
			VERSION_2c = 2,
			VERSION_3  = 3;

		function __construct($version, $host, $community) {
			snmp_set_oid_output_format(SNMP_OID_OUTPUT_NUMERIC);
			if(is_numeric($version) && $version>0 && $version<4) $this->version = $version; 
			else $this->version=2;
			$g = array(1=>'snmp_get',2=>'snmp2_get',3=>'snmp3_get');
			$w = array(1=>'snmp_real_walk',2=>'snmp2_real_walk',3=>'snmp3_real_walk');
			$this->doget = $g[$this->version];
			$this->dowalk = $w[$this->version];
			$this->host = $host;
			$this->community = $community;
		}

		public function get($oid) {
			snmp_set_enum_print(1);
			$this->result = false;
			$get = $this->doget;
			if(is_array($oid)) {
				foreach($oid as $k=>$OID) {
					$r = $get($this->host, $this->community, $OID);
					if(!$r) { $this->error = $r; return $r; }
					$this->result[$OID] = $r;
				}
			} else {
				$this->result = $get($this->host, $this->community, $oid);
			}
			return $this->result;
		}

		public function walk($oid) {
			snmp_set_enum_print(1);
			$this->result = false;
			$walk = $this->dowalk;
			$this->result = $walk($this->host, $this->community, $oid);
			if(!$this->result) $this->error = $this->result;
			return $this->result;
		}
		
		public function getErrno() {
			if(isset($this->error)) {
				log_txt(__METHOD__.": ERROR: ".arrstr($this->error));
				return $this->error;
			}
			return 0;
		}
		
		public function getError() {
			if(isset($this->error)) {
				log_txt(__METHOD__.": ERROR: ".arrstr($this->error));
				return $this->error;
			}
			return '';
		}
	}
}

class switch_snmp {

    function __construct($obj) {
		global $DEBUG, $config, $snmp_conf;
		snmp_set_oid_output_format(SNMP_OID_OUTPUT_NUMERIC);
		$cfg = $this->cfg = $snmp_conf;
		if(isset($cfg['walk_timeout'])) $this->walk_timeout = $cfg['walk_timeout']; else $this->walk_timeout = 3000000;
		if(isset($cfg['walk_retries'])) $this->walk_retries = $cfg['walk_retries']; else $this->walk_retries = 2;
		if(isset($cfg['get_timeout'])) $this->gat_timeout = $cfg['get_timeout']; else $this->get_timeout = 1000000;
		if(isset($cfg['get_retries'])) $this->get_retries = $cfg['get_retries']; else $this->get_retries = 3;
		if(isset($cfg['cachetimeout'])) $this->timeout = $cfg['cachetimeout']; else $this->timeout = 300;
		if(isset($cfg['cachedir'])) $this->cachedir = $cfg['cachedir']; else $this->cachedir = '/tmp/';
		$this->ip = isset($obj['ip'])? preg_replace('/[^0-9A-Za-z\.\-]/','',$obj['ip']) : '127.0.0.1';
		$this->community = isset($obj['community'])? preg_replace('/[^0-9A-Za-z\-]/','',$obj['community']) : 'public';
		$this->numports = $obj['numports'];
		$this->oids = $this->cfg['oids']['ALL'];
		$this->version = (isset($this->cfg['version']))? $this->cfg['version'] : SNMP::VERSION_2c;
		$this->q = new sql_query($config['db']);
		$this->online = true;
		$this->sw_version = "";
		$this->model = $this->objecttype();
		$this->qmax = isset($cfg['swChank'][$this->model])? $cfg['swChank'][$this->model] : 64;
		if(isset($this->cfg['oids'][$this->model]))
			$this->oids = array_merge($this->oids,$this->cfg['oids'][$this->model]);
		if($this->model=='C-DATA'){
			$this->sw_version = preg_replace(array('/"/','/_[0-9]*$/'),array('',''),arrfld($this->get('sw_version',false),'sw_version'));
		}elseif($this->model=='BDCOM'){
			$descr = arrfld($this->get('sysdescr',false),'sysdescr');
			if(preg_match('/bdcom[^ ]*\s+([^ ]+)\s+.*version\s+([^ ]+)\s+build\s+([^ \n]+)/i',$descr,$m)){
				$this->sw_version = $m[2]; $this->product = $m[1]; $this->build = $m[3];
			}
		}
		if($DEBUG>0) log_txt(__METHOD__.": свич: {$this->model} {$this->ip}");
    }

    private function log($message) {
		$err = $message;
		log_txt(preg_replace('/<[^>]*>/','',$err));
		$this->error=$err;
		$this->errors[]=$err;
    }

    public function objecttype() {	// проверяет доступность и возвращает модель свича
		global $cache, $DEBUG;
		if(isset($cache['snmp'][$this->ip]['model'])){
			if(!$this->sysname) $this->sysname = $cache['snmp'][$this->ip]['sysname'];
			return $cache['snmp'][$this->ip]['model'];
		}
		if(!($sysname = $this->get('sysname',false))) $this->online = false;
		else $this->sysname = reset($sysname);
		$res = $this->get('model',false);
		if(isset($res['model'])){
			foreach($this->cfg['oids']['ObjectID'] as $oid=>$m) {
				if(preg_match('/^'.$oid.'/',$res['model'])){
					$cache['snmp'][$this->ip]['model'] = $model = $m;
					$cache['snmp'][$this->ip]['sysname'] = $this->sysname;
					return $model;
				}
			}
			if($DEBUG>0) log_txt("switch: {$this->ip}  model: {$res['model']}");
		}
		return '';
    }

    private function parse_portname($index,$portname) {
		if(!is_numeric($index)) {
			return false;
		}
		if($this->model=='C-DATA'){
			if(preg_match('/^(pon|ge|xge)(\d+)\/(\d+)\/(\d+):?(\d+)?\b/i',$portname,$m)) {
				$unit = 1; $port = (!$m[5])? $m[4] : 26 + $m[4] * $m[5];
				if($m[1]=='ge') $port += 16; elseif($m[1]=='xge') $port += 24;
			}
		}elseif($this->model=='BDCOM'){
			if(preg_match('/^(epon|gi|tgi)[^0-9]*(\d+)\/(\d+):?(\d+)?\b/i',strtolower($portname),$m)) {
				$unit = 1; $port = (!$m[4])? $m[3] : $index;
				if($this->numports > 10){ if($m[1]=='gi') $port += 8; elseif($m[1]=='tgi') $port += 16; }
				else{ if($m[1]=='epon') $port += 6; }
			}
		}else{
			if(preg_match('/\bport\s*[ :]?(\d+)/i',$portname,$m)) $port = $m[1]; else $port = '';
			if(preg_match('/\bunit\s*[ :](\d+)/i',$portname,$m)) $unit = $m[1]; else $unit = 1;
			if(preg_match('/\b(\d+)[\/:](\d+)\b/i',$portname,$m)) { $unit = $m[1]; $port = $m[2]; }
			if(preg_match('/epon(\d+)\/(\d+)\b/i',$portname,$m)) { $unit = 1; $port = $index; }
			if(preg_match('/^e(\d+)$/',$portname,$m)) { $unit = 1; $port = $m[1]; }
			if(preg_match('/^g(\d+)$/',$portname,$m)) { $unit = 1; $port = $m[1]+24; }
			if(preg_match('/ethernet(\d+)\/(\d+)\b/i',$portname,$m)) { $unit = 1; $port = $m[2]; }
			if(preg_match('/ethernet(\d+)$/i',$portname,$m)) { $unit = 1; $port = $m[1]; }
		}
		if($port=='' && preg_match('/\b(\d{1,2})\b/',$portname,$m)) { $unit = 1; $port = $m[1]; }
		$data = ($port>0)? array('unit'=>$unit,'number'=>$port, 'name'=>$portname) : false;
		return $data;
    }

    private function read_from_cache($file) {
		$result = array();
		if(!($pfile = fopen ($file,"r"))) {
			$this->log(__METHOD__.": Невозможно открыть файл: $file!");
			return false;
		}
		while($str = fgets($pfile)) {
			$str = preg_replace('/[\n\r]/','',$str);
			$s = preg_split('/\s*=\s*/',$str,2);
			$result[$s[0]] = $s[1];
		}
		if(!feof($pfile)) {
			$this->log(__METHOD__.": unexpected fgets() fail");
		}
		fclose($pfile);
		return $result;
	}

    private function read_from_snmp($r) {
		global $DEBUG;
		if($DEBUG>2) log_txt(__METHOD__." {$this->ip}");
		if(!$this->online) return false;
		$result = array();
		$pfile = false;
		if(!isset($r['OID']) || !is_string($r['OID'])) {
			$this->log(__METHOD__.": Не указан OID!");
			return false;
		}
		if(preg_match('/^([0-9.]*)\.([A-Z]+)?\.?([^.].*)?/',$r['OID'],$m)){
			$rg = array('/^iso/',"/{$m[1]}./"); $rg1 = array('.1',''); $oid = $m[1];
			if($m[2] && $m[3]){ $rg[2] = "/.{$m[3]}$/"; $rg1[2] = ""; $oid = $m[1]; }
			elseif(!$m[2] && $m[3]){ $rg[1] = "/{$m[1]}.{$m[3]}./"; $oid = $m[1].".".$m[3]; }
		}else{$rg = array('/^iso/',"/{$r['OID']}/"); $rg1 = array('.1',''); $oid = $r['OID']; }
		$session = new SNMP($this->version, $this->ip, $this->community,$this->walk_timeout,$this->walk_retries);
 		$session->oid_increasing_check = false;
		$session->oid_output_format = SNMP_OID_OUTPUT_NUMERIC;
		$tree = @$session->walk($oid);
		if(!is_array($tree)) {
			if($DEBUG>0) $this->log(__METHOD__.": Ошибка SNMP {$this->ip} oid='{$r['oid']}' (".$session->getErrno().") ".$session->getError());
			return false;
		}
		if(isset($r['file']) && !($pfile = fopen ($r['file'],"w"))) {
			$this->log(__METHOD__.": Невозможно открыть файл: {$r['file']} для записи!");
		}
		foreach($tree as $k=>$v) {
			$key = trim(preg_replace($rg,$rg1,$k));
			if(isset($r['SNMPTYPE']) && preg_match('/([A-Za-z\-_][0-9A-Za-z\-_]*):\s*["]?(.*[^"])["]?$/',$v,$m)){
				$v = $this->convert($m[1],$r['SNMPTYPE'],$m[2]);
			}
			if($DEBUG>0) log_txt(__METHOD__.": value = '$v' preg_match = ".arrstr($m));
			$val = trim(preg_replace(array('/^.*[A-Z][0-9A-Za-z]*:\s+/','/^"/','/"$/','/[\r\n]/'),array('','','',''),$v));
			if(isset($r['filter']) && preg_match($r['filter'],$val)) continue;
			if($pfile) fputs($pfile,"$key=$val\n");
			$result[$key] = $val;
		}
		if($pfile) fclose($pfile);
		return $result;
    }

    public function walk($r) {	// snmp запрос с проверкой таймаута из настроек (если таймаут не вышел то данные берутся из кэша)
		global $DEBUG, $cache;
		$timeout = $this->timeout;
		if(isset($cache['snmp'][$this->ip][$r['oid']])){
			if($DEBUG>2) log_txt(__METHOD__." {$this->ip} возврат из кэша");
			return $cache['snmp'][$this->ip][$r['oid']];
		}
		if(!isset($r['OID']) && isset($r['oid'])) {
			if(isset($this->oids[$r['oid']]['OID'])){
				$r['OID'] = preg_replace('/\.PORT.*/','',$this->oids[$r['oid']]['OID']);
				if(isset($this->oids[$r['oid']]['type'])) $r['SNMPTYPE'] = $this->oids[$r['oid']]['type'];
			}else $r['OID'] = $r['oid'];
		}
		if($DEBUG>2) log_txt(__METHOD__." {$this->ip} ".((isset($r['oid']))? "{$r['oid']} ":'').$r['OID']);
		if(!isset($r['file'])) {
			foreach($this->oids as $k=>$v) {
				$oid = preg_replace('/\.PORT.*/','',$v['OID']);
				if($r['OID']==$oid) {
					$r['file'] = $this->cachedir."SW-{$this->ip}-{$k}.log";
					if(isset($v['timeout'])) $timeout = $v['timeout'];
					break;
				}
			}
		}
		if(isset($r['timeout'])) $timeout = $r['timeout'];
		if($DEBUG>2) log_txt(__METHOD__." {$this->ip} file {$r['file']} сущ: ".(file_exists($r['file'])?"да":"нет")." timeout: $timeout просрочен: ".((time() - filemtime($r['file']) > $timeout)?"да":"нет"));
		if(isset($r['file']) && file_exists($r['file']) && time() - filemtime($r['file']) < $timeout) {
			if($DEBUG>2) log_txt(__METHOD__." {$this->ip} читаю OID ({$r['oid']}) из файла {$r['file']}");
			$p = $this->read_from_cache($r['file']);
		}else{
			if($DEBUG>2) log_txt(__METHOD__." {$this->ip} читаю OID ({$r['oid']}) по SNMP");
			$p = $this->read_from_snmp($r);
		}
		if(isset($r['oid'])) $cache['snmp'][$this->ip][$r['oid']] = $p;
		return $p;
	}

	public function get($r,$show_err=true) {	// snmp запрос без использования кэша
		global $DEBUG;
		if(!$this->online) return false;
		if(!isset($this->model)) $this->qmax = 1;
		if(isset($r['oid'])) {
			if(!is_array($r['oid'])) $r['oid'] = array($r['oid']);
		}elseif(is_string($r)){
			$r = array('oid'=>array($r));
		}else{
			$this->log(__METHOD__.": error input param: ".arrstr($r));
		}
		if($DEBUG>2) log_txt(__METHOD__." {$this->ip}  {$r['oid'][0]}");
		$short = (@$r['oid'][0] == 'sysname')? true : false;
		if(isset($r['oid'])) foreach($r['oid'] as $k=>$v) {
				$oid = $v;
				// если $v - ключ в масиве $snmp_conf[oids], то берем его значение
				if(isset($this->oids[$v]['OID']))
					$oid = preg_replace('/PORT/',@$r['port'],$this->oids[$v]['OID']);
				$r['OID'][$k] = $oid;
				if($oid != $v) $o[$oid] = $v;
		}
		if(!isset($r['OID'])) { $this->log(__METHOD__.": Не найден OID для {$this->ip}"); return false; }
		if($short) $session = new SNMP($this->version, $this->ip, $this->community, $this->get_timeout, $this->get_retries);
		else $session = new SNMP($this->version, $this->ip, $this->community);
		$session->oid_output_format = SNMP_OID_OUTPUT_NUMERIC;
		$chanks=0; $l = count($r['OID']); $i=0; $tree = array();
		foreach($r['OID'] as $n=>$oid) {
			if($i>=$this->qmax) { $chanks++; $i = 0; }
			$chank[$chanks][$i] = $r['OID'][($chanks*$this->qmax)+$i];
			$i++;
		}
		foreach($chank as $i=>$ch) 
			if($t = @$session->get($ch)) {
				$tree = array_merge($tree,$t);
			} else {
				$tree = false;
				break;
			}
		if(!$tree) {
			if($show_err && ($err = $session->getError()))
				$this->log(__METHOD__.": Ошибка SNMP! oid:".(isset($r['oid'])?$r['oid'][0]:$r['OID'][0])." vers:{$this->version} ip:{$this->ip} comm:{$this->community} $err");
			return false;
		}
		foreach($tree as $k=>$v) {
			$key = trim(preg_replace('/^iso/','.1',$k));
			if(isset($this->oids[@$o[$key]]['type']) && preg_match('/([A-Za-z\-_][A-Za-z\-_]*):\s*["]?(.*[^"])["]?$/',$v,$m)){
				$v = $this->convert($m[1],$this->oids[@$o[$key]]['type'],$m[2]);
			}
			$val = trim(preg_replace('/^\s*[A-Z][A-Z]*: /','',$v));
			if(isset($this->oids[@$o[$key]]['div'])) $val = $val / $this->oids[$o[$key]]['div'];
			$reply[$key] = $val;
		}
		foreach($r['OID'] as $oid) {
			if(isset($o[$oid])) $key = $o[$oid]; else $key = $oid;
			$result[$key] = false;
			if(isset($reply[$oid])) $result[$key] = $reply[$oid];
		}
		return $result;
	}

	private function getNumPorts($p){
		$i = 0;
		foreach($p as $k=>$v){
			if(stripos($v,':')!==false||stripos($v,'vlan')!==false||stripos($v,'802')!==false||stripos($v,'tag')!==false) continue;
			$i++;
		}
		return $i;
	}

    public function ports($r=array(),$unit=false,$filter=true) {	// возвращает масив [индекс порта] = название порта с фильтрацией по типу порта
		global $DEBUG;
		$res = array();
		if($DEBUG>2) log_txt(__METHOD__." {$this->ip}");
		$r['oid'] = 'type';
		$ptypes = $this->walk($r);
		if($DEBUG>0) log_txt(__METHOD__." {$this->ip} найдено ".count($ptypes)." портов по ifType");
		if(!$ptypes) return false;
		$r['oid'] = 'name';
		$pdescr = $this->walk($r);
		if($DEBUG>0) log_txt(__METHOD__." {$this->ip} найдено ".count($pdescr)." портов по ifName");
		if(!$pdescr){
			$r['oid'] = 'descr';
			$pdescr = $this->walk($r);
			if(!$pdescr) {
				if($DEBUG>0) log_txt(__METHOD__." {$this->ip} портов по ifDescr не найдено!");
				return false;
			}
		}
		if($this->model=='C-DATA'){
			$v = preg_match('/^V1\.[5-9]\.[1-9]/',$this->sw_version);
			$r['oid'] = 'onu';
			$onu = $this->walk($r);
			foreach($onu as $index=>$mac) {
				$np = $v? ($index & 0xFFFFFF00)/256-65536 : $index & 0xFFFFFF00;
				$pdescr[$index] = $pdescr[$np].":".($index & 0xFF);
				$ptypes[$index] = 1;
			}
		}
		if($filter === true) $filter = array(1,6,117);
		foreach($ptypes as $index=>$type) {
			if(!is_numeric($type)) $ptypes[$index] = $type = preg_replace('/[^0-9]/','',$type);
			$exclude = true;
			if(is_array($filter)) {
				foreach($filter as $f) if($type == $f) { $exclude = false; break; }
			}
			if($exclude && isset($pdescr[$index])) unset($pdescr[$index]);
		}
		$this->myPorts = $pdescr;
		if($unit !== false) {
			if(!$this->numports) $this->numports = $this->getNumPorts($pdescr);
			foreach($pdescr as $index=>$portname) {
				$port = $this->parse_portname($index,$portname);
				if($port && ($unit == $port['unit'] || $unit === 0)) { $port['type'] = @$ptypes[$index]; $res[$index] = $port; }
				if(preg_match('/TenGigabitEthernet/',$portname)) $res[$index]['number'] = $index;
			}
		}else{
			$res = $pdescr;
		}
		return $res;
	}

	public function shortfdb($r=array()) {	// возвращает масив [мак адрес] => номер порта
		global $DEBUG, $cache;
		if($DEBUG>0) log_txt(__METHOD__." {$this->ip}");
		if(!$this->online) return false;
		if(isset($cache['snmp'][$this->ip]['fdb'])) {
			if($DEBUG>0) log_txt(__METHOD__." {$this->ip} ресурс в кэше");
			$res = $cache['snmp'][$this->ip]['fdb'];
		}else{
			$res = array();
			$ports = $this->ports($r,0);
			if(!$ports || count($ports)==0) log_txt(__METHOD__." {$this->ip} портов не найдено!");
			$r['oid'] = 'mac_on_vlan';
			$tree = $this->walk($r);
			if(!$tree || count($tree)==0) log_txt(__METHOD__." {$this->ip} макадресов не найдено!");
			if($DEBUG>0) log_txt(__METHOD__." {$this->ip} найдено ".count($tree)." макадресов");
			if(!$tree) return false;
			$v = $this->sw_version && preg_match('/^V1\.[5-9]\.[1-9]/',$this->sw_version);
			foreach($tree as $vlan_mac=>$snmpindex) {
				$np = $v? ($snmpindex & 0xFFFFFF00)/256-65536 : $snmpindex;
				if(!isset($ports[$np])) continue;
				$s = preg_split('/\./',$vlan_mac);
				if($this->model == 'C-DATA') {
					$vlan = $s[6];
					$mac = preg_replace("/ /","0",sprintf("%2x:%2x:%2x:%2x:%2x:%2x",$s[0],$s[1],$s[2],$s[3],$s[4],$s[5]));
				}else {
					$vlan = $s[0];
					$mac = preg_replace("/ /","0",sprintf("%2x:%2x:%2x:%2x:%2x:%2x",$s[1],$s[2],$s[3],$s[4],$s[5],$s[6]));
				}
				$port = (isset($ports[$np]['number']))? $ports[$np]['number'] : $ports[$np];
				$res[$mac] = $port;
			}
			if(count($res)>0) $cache['snmp'][$this->ip]['fdb'] = $res;
		}
		return $res;
	}

	public function onufdb($r=array()) {	// возвращает масив [мак адрес ONU] => название порта (для OLT)
		global $DEBUG, $cache;
		if($DEBUG>2) log_txt(__METHOD__." {$this->ip}");
		if(isset($cache['snmp'][$this->ip]['onufdb']))
			return $cache['snmp'][$this->ip]['onufdb'];
		if(!$this->online) return false;
		$res = array();
		$oid = $this->oids['onu']['OID'];
		if(!$oid) log_txt(__METHOD__.": unknown switch type!");
		$ports = $this->ports($r);
		$r['oid'] = 'onu';
		$tree = $this->walk($r);
		if(!$tree) return false;
		foreach($tree as $snmpindex=>$onu_mac) {
			if($this->model=='C-DATA' && !isset($ports[$snmpindex])){
				$v = preg_match('/^V1\.[5-9]\.[1-9]/',$this->sw_version);
				$np = $v? ($snmpindex & 0xFFFFFF00)/256-65536 : $snmpindex & 0xFFFFFF00;
				$ports[$snmpindex] = $ports[$np].":".($snmpindex & 0xFF);
			}
			if(!isset($ports[$snmpindex])) continue;
			$mac = preg_replace("/\s+/",":",$onu_mac);
			$port = $ports[$snmpindex];
			$res[$mac] = $port;
		}
		if(count($res)>0) $cache['snmp'][$this->ip]['onufdb'] = $res;
		return $res;
	}

	public function onusignal($r=array()) {	// возвращает масив [мак адрес ONU] => уровень сигнала (для BDCOM)
		global $DEBUG, $cache;
		if($DEBUG>2) log_txt(__METHOD__." {$this->ip}");
		if(!$this->online) return false;
		if(isset($cache['snmp'][$this->ip]['onusignal'])) {
			$res = $cache['snmp'][$this->ip]['onusignal'];
		}else{
			$res = array();
			$r['oid'] = 'rx_power';
			$tree = $this->walk($r);
			if(!$tree) return false;
			$onu = $this->onufdb($r); // [macONU]=>portname
			if(!$onu) return false;
			$oid = $this->oids['rx_power']['OID'];
			if(!$oid) log_txt(__METHOD__.": unknown switch type!");
			$ports = array_flip($this->ports($r)); // [portname]=>portindex
			foreach($tree as $index=>$signal) $power[(preg_replace('/\..*/','',$index))] = $signal;
			foreach($onu as $mac=>$portname) {
				$snmpindex = @$ports[$portname];
				if(!isset($power[$snmpindex])) continue;
				$res[$mac] = $power[$snmpindex];
				if($this->oids['rx_power']['div']) $res[$mac] = $res[$mac]/$this->oids['rx_power']['div'];
				else $res[$mac] = $res[$mac]/10;
			}
			if(count($res)>0) $cache['snmp'][$this->ip]['onusignal'] = $res;
		}
		return $res;
	}

	public function macport($r,$mac) {	// возвращает номер порта
		global $DEBUG;
		if($DEBUG>2) log_txt(__METHOD__." {$this->ip} $mac");
		$res = $this->shortfdb($r);
		if(count($res)==0) log_txt(__METHOD__." {$this->ip} адресов не найдено");
		if(!isset($res[$mac])) return false;
		return $res[$mac];
	}

	public function onuport($r,$mac) {	// возвращает название порта по маку ONU (для BDCOM)
		global $DEBUG;
		if($DEBUG>2) log_txt(__METHOD__." {$this->ip} $mac");
		$res = $this->onufdb($r);
		if(!isset($res[$mac])) return false;
		return $res[$mac];
	}
	
	private function oid2mac($str){
		$s = preg_split('/\./',$str);
		if($this->model == 'C-DATA') {
			$vlan = $s[6];
			$mac = preg_replace("/ /","0",sprintf("%2x:%2x:%2x:%2x:%2x:%2x",$s[0],$s[1],$s[2],$s[3],$s[4],$s[5]));
		}else {
			$vlan = $s[0];
			$mac = preg_replace("/ /","0",sprintf("%2x:%2x:%2x:%2x:%2x:%2x",$s[1],$s[2],$s[3],$s[4],$s[5],$s[6]));
		}
		return array($vlan,$mac);
	}

	private function hex2vlan($s){
		$ports = array();
		if(!$s) return $ports;
		$a = preg_split('/\s+/',$s);
		$len = count($a);
		if($this->model == "C-DATA"){
			$v = preg_match('/^V1\.[5-9]\.[1-9]/',$this->sw_version);
			$len = $len/4;
			for($i=0; $i<$len; $i++){
				$n = $i*4;
				$l = $a[$n].$a[$n+1].$a[$n+2].$a[$n+3];
				$snmpid = arrfld(unpack("N",pack("H8",$l)),1);
				if(($snmpid - 16777216) / 256 > 200) continue;
				$ports[] = $v? ($snmpid - 16777216) / 256 : $snmpid;
			}
		}else{
			for($i=0; $i<$len; $i++){
				$chank = arrfld(unpack("C",pack("H2",$a[$i])),1);
				if($chank == 0) continue;
				for($n=7; $n>=0; $n--) if($chank & (1<<$n)) $ports[] = 8*($i+1)-$n;
			}
		}
		return $ports;
	}

	public function vlans(){
		$ports = array();
		$fld = array('vlan_name','vlan_port','vlan_untg');
		$out = array();
		$p = $this->ports(array(),1);
		if(!$p) return false;
		foreach($p as $id=>$v) $port[$id] = $v['number'];
		foreach($fld as $oid) {
			$r['oid'] = $oid;
			$data = $this->walk($r);
			if(!$data) continue;
			if($oid=='vlan_name') foreach($data as $vlan=>$s){
				$names[$vlan] = $s;
			}elseif($oid=='vlan_port') foreach($data as $vlan=>$s) {
				foreach($this->hex2vlan($s) as $id){
					$out[$id][$vlan] = array('port'=>$p[$id]['number'],'portname'=>$p[$id]['name'],'name'=>$names[$vlan],'vlan'=>$vlan,'tagged'=>1);
				}
			}elseif($oid=='vlan_untg') foreach($data as $vlan=>$s) {
				foreach($this->hex2vlan($s) as $id){
					$out[$id][$vlan] = array('port'=>$p[$id]['number'],'portname'=>$p[$id]['name'],'name'=>$names[$vlan],'vlan'=>$vlan,'tagged'=>0);
				}
			}
		}
		return $out;
	}

	public function portvlans(){
		$ports = array();
		$fld = array('vlan_name','vlan_port','vlan_untg');
		$out = array();
		foreach($fld as $oid) {
			$r['oid'] = $oid;
			$vlans = $this->walk($r);
			if(!$vlans) continue;
			if($oid=='vlan_name') foreach($vlans as $vlan=>$s)
				$out[$vlan][$oid] = $s;
			else foreach($vlans as $vlan=>$s) {
				$out[$vlan][$oid] = $this->hex2vlan($s);
			}
		}
		return $out;
	}

	public function fdb($r=array()) {	// возвращает отсортированный масив [unit,port,vlan,portname,portindex,mac,uid,username,address,online]
		global $DEBUG;
		if($DEBUG>2) log_txt(__METHOD__." {$this->ip}");
		if(!$this->online) return false;
		$vCDATA = $this->sw_version && preg_match('/^V1\.[5-9]\.[1-9]/',$this->sw_version);
		$vBDCOM = $this->model == 'BDCOM' && $this->product == 'P3608B';
		$ports = $this->ports($r,0);
		if(!$vBDCOM){
			$r['oid'] = 'mac_on_vlan';
			$tree = $this->walk($r);
			if(!$tree) {
				$r['oid'] = 'mac_on_port';
				$tmp = $this->walk($r);
				if(!$tmp) return false;
				foreach($tmp as $k=>$v) $tree['0.'.$k] = $v;
			}
		}
		if(!$vBDCOM){
			foreach($tree as $vlan_mac=>$snmpindex) {
				$np = $vCDATA? ($snmpindex & 0xFFFFFF00)/256-65536 : $snmpindex;
				if(!isset($ports[$np])) continue;
				$s = $this->oid2mac($vlan_mac);
				$vlan = $s[0];
				$mac = $s[1];
				$sunit[] = $unit = $ports[$np]['unit'];
				$sport[] = $port = $ports[$np]['number'];
				$name = $ports[$np]['name'];
				$res[] = array('unit'=>$unit,'port'=>$port,'vlan'=>$vlan,'portname'=>$name,'portindex'=>$np,'mac'=>$mac);
			}
		}else{
			include_once "telnet.php";
			$data = getTelnetFdbBDCOM($this->ip);
			foreach($ports as $snmpindex=>$p) $rports[$p['name']] = $snmpindex;
			foreach($data as $v) {
				if($DEBUG>0) log_txt(__METHOD__.": BDCOM ".arrstr($v));
				if(!isset($rports[$v['portname']])) continue;
				$snmpindex = $rports[$v['portname']];
				$sunit[] = $unit = 1;
				$sport[] = $port = $ports[$snmpindex]['number'];
				$res[] = array('unit'=>$unit,'port'=>$port,'vlan'=>$v['vlan'],'portname'=>$v['portname'],'portindex'=>$snmpindex,'mac'=>$v['mac']);
			}
		}
		array_multisort($sunit, SORT_ASC, $sport, SORT_ASC, $res);
		foreach($res as $k=>$v) {
			$SQL="
				SELECT u.address, a.uid, a.username, if(acctstoptime is null,framedipaddress,'') as online
				FROM radacct a, users u 
				WHERE a.username=u.user AND callingstationid='{$v['mac']}' 
				ORDER BY acctstarttime DESC LIMIT 1
			";
			if($usr = $this->q->select($SQL,1)) {
				$client = $this->q->select("SELECT * FROM map WHERE type='client' AND name='{$usr['username']}'",1);
				$dev = $client? $this->q->select("SELECT macaddress FROM devices WHERE type!='cable' AND node1='{$client['id']}' LIMIT 1",4) : "";
				$res[$k] = array_merge($v,array(
					'uid' => $usr['uid'],
					'username' => $usr['username'],
					'address' => $usr['address'],
					'online' => $usr['online'],
					'device' => $dev
				));
			}elseif($dev =  $this->q->select("SELECT * FROM devices WHERE macaddress='{$v['mac']}'",1)) {
				$node = false; if($dev['node1']) $node = $this->q->get('map',$dev['node1']);
				$res[$k] = array_merge($v,array(
					'uid' => "", 'username' => "",
					'address' => $dev['name'].($node? " ".$node['address'] : ""),
					'online' => $dev['ip'],
					'device' => $dev['macaddress']
				));
			}else{
				$res[$k]['device'] = $res[$k]['online'] = $res[$k]['address'] = $res[$k]['username'] = $res[$k]['uid'] = '';
			}
		}
		return $res;
	}

	private function convert($from,$to,$v){
		global $DEBUG;
		if($from == 'STRING' && $to == 'Hex-STRING'){
			$val = strtoupper(preg_replace('/(..)(..)(..)(..)(..)(..)/','$1 $2 $3 $4 $5 $6',bin2hex($v)));
		}elseif($from == 'Hex-STRING' && $to == 'STRING'){
			$val = pack("H*", preg_replace(array('/\s*00\s*$/','/[^A-F0-9]/i'),array('',''),$v));
		}else $val = $v;
		if($DEBUG>3) log_txt(__METHOD__.": from: '$from' to: '$to' val: '$val'");
		return $val;
	}

	public function portindex($number){
		global $cache;
		if(!isset($this->ip) || !isset($this->community)) return false;
		if(!isset($cache['snmp'][$this->ip]['portindex'])){
			$pid = $this->ports(array(),1);
			if(!$pid) return false;
			foreach($pid as $index=>$p) {
				$cache['snmp'][$this->ip]['portindex'][$p['number']] = $index;
			}
		}
		return $cache['snmp'][$this->ip]['portindex'][$number];
	}
}

class switch_search {
	function __construct() {
		global $config;
		$this->tr = new Trace();
		$this->traceline = array();
		$this->trace = array();
		$this->snmp = array();
		$this->q = new sql_query($config['db']);
	}

    private function log($message) {
		$err = $message;
		log_txt(preg_replace('/<[^>]*>/','',$err));
		$this->error=$err;
		$this->errors[]=$err;
    }

    private function getSNMP($switch){
		if(!isset($switch['ip'])) return false;
		if(!isset($this->snmp[$switch['ip']])) $this->snmp[$switch['ip']] = new switch_snmp($switch);
		return $this->snmp[$switch['ip']];
    }

    private function macport($switch,$mac) {	// возвращает номер порта
		global $DEBUG;
		$snmp = $this->getSNMP($switch);
		$port = $snmp->macport($switch,$mac);
		if($DEBUG>0) log_txt(__METHOD__.": результат ".arrstr($port));
		return $port;
	}

    private function getUser($data) {
		global $DEBUG;
 		if($DEBUG>0) log_txt(__METHOD__.": data = ".arrstr($data));
		if(!is_array($data)) { // ищем клиента по разным данным
			if(is_numeric($data)) {
				if(!($user = $this->q->select("SELECT * FROM `users` WHERE contract='{$data}' OR uid='{$data}'",1)) || ($rows=$this->q->rows()) != 1) {
					$this->log(__METHOD__.":Не удалось найти пользователя по uid или contract");
					return false;
				}
			}elseif(is_string($data) && !preg_match('/:/',$data)) {
				$data = $this->q->escape_string($data);
				if(!($user = $this->q->select("SELECT * FROM `users` WHERE user='{$data}' OR email='{$data}' OR phone='{$data}'",1)) || ($rows=$this->q->rows()) != 1) {
					$this->log(__METHOD__.": Не удалось найти пользователя по username, email или phone");
					return false;
				}
			}elseif(preg_match('/([0-9A-F]{2,}[:-][0-9A-F]{2,}[:-][0-9A-F]{2,}[:-][0-9A-F]{2,}[:-][0-9A-F]{2,}[:-][0-9A-F]{2,})/i',$data,$m)) {
				$mac = $m[1];
				$connect = $this->q->select("SELECT * FROM `radacct` WHERE callingstationid='$mac' AND uid>0 ORDER BY acctstarttime desc LIMIT 1",1);
				if($connect) $user = $this->q->get("users",$connect['uid']);
			}else{
				$this->log(__METHOD__.": Не удалось идентифицировать запрос!");
				return false;
			}
		}else{
			$data = $this->q->escape_string($data);
			if($data['uid']) $user = $this->q->get("users",$data['uid']);
			elseif($data['contract']) $user = $this->q->select("SELECT * FROM `users` WHERE contract='{$data['contract']}'",1);
		}
		if(!isset($user) || !$user) {
			$this->log(__METHOD__.":Не удалось найти пользователя");
			return false;
		}
		if(!isset($connect) || !$connect) $connect = $this->q->select("SELECT * FROM `radacct` WHERE username='{$user['user']}' AND nasipaddress!='' ORDER BY acctstarttime desc LIMIT 1",1);
		$user['nas'] = @$connect['nasipaddress'];
		$user['mac'] = strtolower(@$connect['callingstationid']);
		return $user;
    }
    
    private function getDevPorts($switch){
		// $server['bandleports'] - port UpLink
		if(!($ports = $this->q->fetch_all("SELECT number, link FROM devports WHERE device='{$switch['id']}' AND number!='{$switch['bandleports']}' AND link is NOT NULL",'number'))){
			$this->log(__METHOD__.":Не найдено подключенных портов");
			return false;
		}
		return $ports;
    }
    
    private function getDevPort($switch){
		$port = $this->q->select("SELECT * FROM devports WHERE device='{$switch['id']}' AND number='{$switch['port']}'",1);
		if(!$port) $this->log(__METHOD__.": не найден порт '{$switch['port']}' для {$switch['type']}[{$sw['id']}]");
		return $port;
    }
    
    private function getDevice($data=''){
		global $DEBUG;
		if(is_numeric($data)){
			$filter = "id='$data'";
		}elseif(preg_match('/^[0-9A-F]{2,}[:-][0-9A-F]{2,}[:-][0-9A-F]{2,}[:-][0-9A-F]{2,}[:-][0-9A-F]{2,}[:-][0-9A-F]{2,}$/i',$data)){
			$filter = "macaddress='$data'";
		}elseif(preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/i',$data)){
			$filter = "ip='$data'";
		}else{
			$this->log(__METHOD__.": ошибка в параметре \$data=$data");
			return false;
		}
		return $this->q->select("SELECT * FROM devices WHERE $filter",1);
    }
    
    private function getRootSwitch($user){
		global $DEBUG;
		if(!$user['mac']){
			$this->log(__METHOD__.":Не удалось найти macaddress пользователя");
			return false;
		}
		if(isset($this->cfg['root_switch'])){
			if(!is_array($this->cfg['root_switch'])) return array($this->cfg['root_switch']);
			else return $this->cfg['root_switch'];
		}
		// пробуем найти корневой свич через базу карты
		if(!($server = $this->getDevice($user['nas']))) {
			$this->log(__METHOD__.":Не удалось найти сервер доступа");
			return false;
		}
		if($ports = $this->getDevPorts($server)) {
			foreach($ports as $p=>$link) {
				$server['port'] = $p;
				if($s = $this->search_by_map($server)){
					$root_switch[] = $s;
				}
			}
		}
		if(!isset($root_switch)) {
			$this->log(__METHOD__.":Не удалось найти свичи подключенные к серверу доступа");
			return false;
		}
		if($DEBUG>0){
			foreach($root_switch as $sw) $tmp[] = $sw['ip'].":".$sw['community'];
			log_txt(__METHOD__.": найден \$root_switch = ".implode(', ',$tmp));
		}
		return $root_switch;
    }

    public function userport($data) { // поиск клиентского порта на свичах
		global $DEBUG;
 		if($DEBUG>0) log_txt(__METHOD__.": start - ".arrstr($data));
		$root_switch = array();
		if(!($user = $this->getUser($data))) return false;
		$this->user = $user;
		if(!($root_switch = $this->getRootSwitch($user))) return false;

		if($DEBUG>0) log_txt(__METHOD__.": client[{$user['user']}:{$user['uid']}] {$user['mac']}");

		foreach($root_switch as $switch){
			if($switch['community']=='') {
				$this->log(__METHOD__.": Для свича {$switch['name']} ({$switch['ip']}) не определён community");
				continue;
			}
			$this->trace = array($switch);
			if($switch['port'] = $this->macport($switch,$user['mac'])) {
				if($DEBUG>0) log_txt(__METHOD__.": пользователь найден {$switch['ip']}:{$switch['port']}");
				while($switch = $this->next_switch($switch, $user['mac'])) {
					if(isset($switch[0])) {									// вариант при отсутствии данных карты и данных lldp
						$switch = $this->get_last_switch($switch,$user['mac']);
					}elseif($switch['ip'] != '' && $switch['community'] != ''){
						$switch['port'] = $this->macport($switch,$user['mac']);
					}elseif($switch['ip'] == '' || $switch['community'] == ''){
						foreach($this->tr->traceFormat(1) as $k=>$v) array_push($this->traceline,$v);
						break;
					}else break;
					if($switch) $this->trace[] = $switch;
					if(!isset($switch['port']) || $switch['port']=='') {
						$this->log(__METHOD__.": ERROR Не найден мак {$user['mac']} на свиче {$switch['id']} {$switch['name']} {$switch['ip']} !");
						return false;
					}
					if($DEBUG>0) log_txt(__METHOD__.": ".count($this->trace)." найден {$this->tmp} {$switch['id']} {$switch['name']} {$switch['ip']}:{$switch['port']}");
				}
				$sw = end($this->trace);
				if($sw) return $sw;
			}
		}
		if(!isset($sw)) {
			$this->log("Пользователь не найден в сети!<br> свич: {$switch['name']} ({$switch['ip']}) <br> мак: <span style=\"font-family:monospace;color:#aef\">{$user['mac']}<span>"); return false;
		}elseif(!$sw) return false;
		else return $sw;
	}

	private function next_switch($switch, $mac) {
		global $DEBUG;
		if($DEBUG>0) log_txt(__METHOD__.": start for {$switch['type']}[{$switch['id']} {$switch['ip']}]:{$switch['port']}");
		if(!isset($switch['port']) || $switch['port']=='') {
			$this->log(__METHOD__.": ".count($this->trace)." Не указан порт свича [{$switch['id']}] {$switch['ip']}!");
			return false;
		}
		$out = false;
		if($res = $this->search_by_map($switch)) {					//	1 вариант: ищем по базам карты
			$this->tmp = "по карте"; $out = $res;
		}elseif($res = $this->search_by_lldp($switch)) {			//	2 вариант: ищем по lldp
			$this->tmp = "по lldp"; $out = $res;
		}elseif($res = $this->search_by_all($switch)) {				//	3 вариант: ищем по всем известным
			$this->tmp = "по всем"; $out = $res;
		}
		return $out;
	}

	private function search_by_map($switch) { // поиск свича на противоположном конце линии
		global $DEBUG;
		if($DEBUG>0) log_txt(__METHOD__.": ищем по карте {$switch['type']}[{$switch['id']}] {$switch['ip']}:{$switch['port']}");
		if(!($port = $this->getDevPort($switch))) return false;

		if(!($caps = $this->tr->capdevices($port['id']))){
			if($DEBUG>0) log_txt(__METHOD__.": соединение не найдено!");
			return false;
		}
		$sw = $caps['end'];
		if($sw['type']!='switch') {
			if($DEBUG>0) log_txt(__METHOD__.": конечное устройство: {$sw['type']}[{$sw['id']}] {$sw['name']} {$sw['ip']}:{$switch['port']}");
			return false;
		}
		if(!isset($sw['address'])) $sw['address'] = $this->q->select("SELECT address FROM map WHERE id='{$sw['node1']}'",4);
		if($DEBUG>0) log_txt(__METHOD__.": найден {$sw['type']}[{$sw['id']}] {$sw['ip']}:{$sw['port']}");
		return $sw;
	}

	private function search_by_lldp($switch) { // поиск свича (dlink) на порту по протоколу lldp
		global $DEBUG;
		if($DEBUG>0) log_txt(__METHOD__.": ищем по lldp {$switch['type']}[{$switch['id']}] {$switch['ip']}:{$switch['port']}");
		$switch['oid'] = 'lldp_mac';
		$lldp = $mac = $res = false;
		$sw = new switch_snmp($switch);
		if($m = $sw->walk($switch)) {
			foreach($m as $k=>$v) {
				$s = preg_split('/\./',$k);
				if(isset($s[1])) {
					$lldp[mb_strtolower(preg_replace('/ /',':',trim($v)))] = $s[1];
				}
			}
		}
		if(is_array($lldp)) foreach($lldp as $k=>$v) if($v == $switch['port']) { $mac=$k; break; }
		if($mac) $res = $this->q->select("SELECT d.*, m.address FROM devices d, map m WHERE d.node1=m.id AND d.type='switch' AND macaddress='$mac'",1);
		return $res;
	}

	private function search_by_all($switch) { // берёт все маки на порту и выбирает по ним из базы данные по свичам
		global $DEBUG;
		if($DEBUG>0) log_txt(__METHOD__.": ищем по всем {$switch['type']}[{$switch['id']}] {$switch['ip']}:{$switch['port']}");
		$snmp = $this->getSNMP($switch);
		$fdb = $snmp->shortfdb();
		$macs = array();
		foreach($fdb as $mac=>$port) if($port == $switch['port']) $macs[] = "'$mac'";
		if(count($macs)==0) return false;
		$res = $this->q->select("SELECT d.*, m.address FROM devices d, map m WHERE d.node1=m.id AND d.type='switch' AND macaddress in (".implode(',',$macs).")");
		if($DEBUG>0 && $res) { foreach($res as $i=>$v) $tmp[] = "{$v['type']}[{$v['id']}] {$v['name']} {$v['ip']}"; log_txt(__METHOD__.": FOUND ".implode(", ",$tmp)); }
		return $res;
	}

	private function get_last_switch($switches,$mac) {	// находим все свичи где в FDB есть мак пользователя
		global $DEBUG;
		foreach($switches as $k=>$sw) {
			if($port = $this->macport($sw,$mac)) {
				$sw['port'] = $port;
				$sw_have_usrmac[] = $sw;
				$sw_macs[] = $sw['macaddress'];
			}
		}
		if(@count($sw_have_usrmac)>1) { // выстраиваем их по кол-ву найденных маков др. свичей
			$sw_seqvence = array();
			foreach($sw_have_usrmac as $sw) {
				$i = 0;
				foreach($sw_macs as $m) if($sw['port'] == $this->macport($sw,$m)) $i++;
				if(key_exists($i,$sw_seqvence)) {
					$this->log(__METHOD__.": Ошибка при определении последовательности свичей!");
					return false;
				}
				$sw_seqvence[$i] = $sw;
			}
			if(count($sw_seqvence)>0) krsort($sw_seqvence);
			foreach($sw_seqvence as $k=>$sw) if($k != 0) $this->trace[] = $sw;
			$switch = $sw_seqvence[0];
		}elseif(@count($sw_have_usrmac) == 1) {
			$switch = array_shift($sw_have_usrmac);
		}else{
			$this->log(__METHOD__.": Мак адрес пользователя не найден ни в одном из свичей!");
			return false;
		}
		return $switch;
	}
}

function show_switch($switch,$portsingroup=8) { // показывает схематическую картинку свича и активность портов
	global $config, $snmp_conf;
	$usrport = '';
	$filter = '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/';
	if(is_string($switch) && preg_match($filter,$switch)) $ip = $switch;
	elseif(is_array($switch) && preg_match($filter,$switch['ip'])) { 
		$ip = $switch['ip'];
		if(isset($switch['port'])) $usrport = $switch['port'];
	}elseif(is_object($switch) && get_class($switch)=='switch_snmp') {
		$sw = $switch;
		$ip = $switch->ip; 
	}else return false;

	$oidstatus = $snmp_conf['oids']['ALL']['status']['OID'];
	$q = new sql_query($config['db']);
	$d = $q->select("SELECT * FROM devices WHERE type='switch' AND ip='{$ip}'",1);
	if(!$d) return false;

	$req = array('ip'=>$d['ip'],'community'=>$d['community']);
	if(!isset($sw)) $sw = new switch_snmp($req);
	$ports = $sw->ports(null,0);
	if($ports) {
		foreach($ports as $snmpindex=>$port) {
 			if(preg_match('/:/',$port['name'])) continue;
			$oid = preg_replace('/PORT/',$snmpindex,$oidstatus);
			$ans[$oid] = $port['number'];
			if($sw->model=='BDCOM' || $sw->model=='C-DATA') $pn[$port['number']] = $port['name'];
			$req['oid'][] = $oid;
		}
		$res = $sw->get($req);
		foreach($res as $k=>$v) {
			$pstate[$ans[$k]] = (is_numeric($v))? $v:preg_replace('/[^0-9]/','',$v);
		}
	}
	$sw_style = ($d['numports']>28 && $d['numports']<53)? "style=\"width:1300px\"" : "";
	$swp_style = ($d['numports']>28 && $d['numports']<53)? "style=\"width:1100px\"" : "";
	if($d['numports']<16) $portrows = 1; else $portrows = 2;
	for($np=0;$np<floor($d['numports']/$portsingroup);$np++) $groups[] = $portsingroup;
	$groups[] = $d['numports']%$portsingroup;
	$html = "<center><div class=\"switch\" switch=\"{$d['id']}\" $sw_style><div class=\"switchlabel\">";
	$html .= "<div class=\"switchip\">{$d['ip']}</div>";
	$html .= "<span class=\"swname\">{$d['name']}</span></div><div class=\"switchports\" $swp_style>\n";
	$port = 0;
	foreach($groups as $gports) {
		$html .= "<div class=\"portgroup\"".(($portrows==1)?' style="width:'.($gports*40+10).'px"':'').">\n";
		for($i=1;$i<=$gports;$i=$i+$portrows) {
			$portclass = "port"; if(@$pstate[$port+$i]==1) $portclass .=' portup'; if($usrport == $port+$i) $portclass .=' userport';
			$pname = isset($pn)? " pname=\"".$pn[$port+$i]."\"" : '';
			$html .= "<div class=\"$portclass\" port=\"".($port+$i)."\" $pname>".($port+$i)."</div>";
		}
		if($portrows>1) {
			for($i=2;$i<=$gports;$i=$i+$portrows) {
				$portclass = "port".((@$pstate[$port+$i]==1)?' portup':'').(($usrport==$port+$i)?' userport':'');
				$pname = isset($pn)? " pname=\"".$pn[$port+$i]."\"" : '';
				$html .= "<div class=\"$portclass\" port=\"".($port+$i)."\" $pname>".($port+$i)."</div>";
			}
		}
		$html .= "</div>\n";
		$port = $port + $gports;
	}
	$html .= "</div></div></center>";
	return $html;
}

function pon_user_data($uid) { // формирует данные для таблички о состоянии ONU клиента
	global $config, $snmp_conf, $errors, $DEBUG;
	$q = new sql_query($config['db']);
	if(!($user = $q->get('users',$uid))){ $errors[] = "отсутствует клиент в базе!"; return false; }
	if(isset($user[0])){ $errors[] = "множественные записи в базе (users)!"; return false; }
	// поиск оборудования пользователя
	$client = $q->select("SELECT * FROM map WHERE type='client' AND name='{$user['user']}'");
	if(!$client) return false;
	elseif(count($client)>1){ $errors[] = "множественные записи в базе (devices)!"; return false; }
	$client = $client[0];
	$dtype = ($client)? $config['map']['clientdevtypes'][$client['subtype']] : false;
	$dev = ($client)? $q->select("SELECT * FROM devices WHERE type='$dtype' AND node1='{$client['id']}'",1) : false;
	if($DEBUG>0) log_txt(__function__.": {$client['type']}[{$client['id']}] {$client['name']} {$dev['macaddress']}");
	if(!$client){ $errors[] = "отсутствует клиент на карте!"; return false; }
	$cable = $q->select("SELECT * FROM devices WHERE type='cable' AND (node1='{$client['id']}' OR node2='{$client['id']}')",1);
	if(!$cable){ $errors[] = "не найден кабель подключения клиента!"; return false; }
	$node = ($cable['node1']==$client['id'])? $cable['node2'] : $cable['node1'];
	$node = $q->get('map',$node);
	if(!$node){ $errors[] = "не найден узел подключения клиента!"; return false; }
	if($node['id']!=$client['connect']) $q->update_record("map",array('id'=>$client['id'],'connect'=>$node['id']));
	$port = $q->select("SELECT id FROM devports WHERE device='{$dev['id']}' AND porttype='fiber' AND link is not null",4);
	if(!$port){ $errors[] = "не найдено подключение клиента!"; return false; }
	$tr = new Trace();
	$caps = $tr->capdevices($port);
	foreach(array('begin','end') as $k=>$n) {
		if($caps[$n]['type']=='switch' && $caps[$n]['community']=='') $caps[$n]['type'] = 'sw';
		$cp[$caps[$n]['type']] = $n;
	}
	if(!isset($cp['switch'])){ $errors[] = "не найден свич подключения клиента!"; return false; }
	$switch = $caps[$cp['switch']];
	$req = array('ip' => $switch['ip'],'community'=>$switch['community']);
	$sw = new switch_snmp($req);
	$oids = array_intersect_key($snmp_conf['oids'][$sw->model],array('onu'=>0,'vlan_onu'=>0,'tx_power'=>0,'rx_power'=>0,'distance'=>0));

	$data['table'][0] = array('param'=>'Устройство подключения','value'=>$client['hostname']);
	$data['table'][1] = array('param'=>'IP адрес устройства','value'=>$switch['ip']);
	$data['table'][2] = array('param'=>'Название интерфейса','value'=>'Нет данных...');
	foreach($oids as $k=>$v) {
		$data['table'][] = array('param'=>$v['label'],'value'=>'Нет данных...');
		$req['oid'][] = $k;
	}
	$data['info'] = array('ip'=>$switch['ip'], 'port'=>null, 'mac'=>$dev['macaddress']);

	$port = $sw->onuport($req,$dev['macaddress']);
	if($port){
		$data['table'][2]['value'] = $port;
		$ports = array_flip($sw->ports($req));
		if($ports) {
			$data['info']['port'] = $ports[$port];  // OID номер порта
			$req['port'] = $ports[$port];
			$p = $sw->get($req); $i = 2;
			if(is_array($p)) foreach($p as $k=>$v) {
				$i++;
				if($v === false) continue;
				if($k=='onu') $v=preg_replace('/ /',':',$v);
				$data['table'][$i] = array('param'=>$oids[$k]['label'],'value'=>$v);
			}
		}
	}
	return $data;
}
?>
