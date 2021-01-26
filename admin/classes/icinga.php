<?php
include_once "utils.php";
include_once "log.php";
include_once 'snmpclass.php';

class Icinga2{

	function __construct($cfg=null){
		global $config;
		$this->q = new sql_query($config['db']);
		$this->error='';
		$this->errors=array();
	    $this->api = 'v1';
	    $this->url = ICINGA_URL."/".$this->api;
	    $this->login = ICINGA_LOGIN;
	    $this->password = ICINGA_PASSWORD;
		$this->domain = ICINGA_DOMAIN;
		$this->code = 0;
		$this->error = "";
		$this->logo = "<img src=\"pic/icinga-logo.png\" style=\"width:40px;height:40px;float:left;margin:3px\">";
	}

	function notify($message, $type='',$err=true){ // посылает уведомление через ws_server
		global $opdata;
		$data = array("wstype"=>'notify','to'=>$opdata['login'],"message"=>"{$this->logo} ".$message);
		if($type) $data['type'] = $type;
		if($type=='error' && $err) $data['message'] .= "\n".$this->error;
		if($type!='error') log_db("",0,"изменение мониторинга",$message);
		use_ws_server($data);
	}

    function send($url, $data="", $headers=array(), $opts=array()){ // запрашивает данные черес api
		global $DEBUG, $curlopt;
		$this->code = 0; $this->error = "";
		$opt = array(
			CURLOPT_HTTPHEADER => array('Accept: application/json'),
			CURLOPT_HTTPAUTH => 1,
			CURLAUTH_BASIC => 1,
			CURLOPT_USERPWD => $this->login.":".$this->password,
		);
		if(count($headers)>0) foreach($headers as $h) array_push($opt[CURLOPT_HTTPHEADER],$h);
		if(count($opts)>0) foreach($opts as $k=>$v) $opt[$k] = $v;
		if($data){
			$this->lastdata = $data;
			$json = json_encode($data,JSON_UNESCAPED_UNICODE);
			array_push($opt[CURLOPT_HTTPHEADER],'Content-Length: '.strlen($json));
			if(!@$opt[CURLOPT_CUSTOMREQUEST]) $opt[CURLOPT_POST] = 1;
			$opt[CURLOPT_POSTFIELDS] = $json;
		}
		if($DEBUG>0){
			log_txt(__METHOD__.": defined url:".$this->url.$url.(isset($json)?" data:$json":""));
			return true;
		}
		$res = $this->lastresult = request_http($this->url.$url, false, $opt);
		if(isset($res['results']) && (!isset($res['results'][0]['code']) || $res['results'][0]['code'] == 200 && count($res['results'])>1)){
			return $res['results'];
		}elseif(isset($res['results']) && isset($res['results'][0]['code']) && $res['results'][0]['code'] == 200 && count($res['results'])==1){
			$this->code = $res['results'][0]['code'];
			return $res['results'][0];
		}else{
			$this->errordata = $data;
			if(isset($res['results'][0]['code'])){
				$this->code = $res['results'][0]['code'];
				$this->status = $res['results'][0]['status'];
				$this->error = "ERROR {$this->code}, ".preg_replace('/[\r\n]/',' ',implode(', ',$res['results'][0]['errors']))."\n\t{$this->status}";
			}elseif(isset($res['error'])){
				$this->code = $res['error'];
				$this->error = "ERROR {$res['error']}, {$res['status']}";
			}else
				$this->error = "ERROR ?, result=".arrstr($res);
			return false;
		}
	}

	function nameService($service=null){ // формирует имя сервиса (idXXX или portXXX)
		if(is_numeric($service))
			return "id".$service;
		elseif(is_array($service)){
			$fld = array('id'=>0,'object'=>0,'rootid'=>0,'rootport'=>0,'address'=>0);
			if(count(array_intersect_key($service,$fld))==5){
				return ($service['object']=="port")? "port".$service['id'] : "id".$service['id'];
			}elseif(key_exists('porttype',$service)){
				return "port".$service['id'];
			}
		}elseif(preg_match('/^(id\d+\..*\!)?((id|port)\d+)$/',$service,$m))
			return $m[2];
		log_txt("некорректное имя сервиса. service: ".arrstr($service));
		return "";
	}

    function getHost($host=null){ // возвращает данные по хосту (требуется id)
		if(!$host) return false;
		$hostid = is_array($host)? $host['id'] : $host;
		$url = "/objects/hosts/id{$hostid}.{$this->domain}?pretty=1";
		return $this->send($url,null);
	}

    function getSwitches(){ // запрашивает данные по всем свичам
		$url = "/objects/hosts?pretty=1";
		$data = array('filter'=>"host.vars.device_type==\"switch\"");
		$r = $this->send($url,$data,array('X-HTTP-Method-Override: GET'));
		if(!$r) $r = array();
		return $r;
	}

	function getService($service=null){ // возвращает первый найденный сервис для хоста
		if(!$service) return false;
		$s = $this->getServices($service);
		if(is_array($s) && ($c = count($s))>1){
			foreach($s as $d) $o[] = $d['attrs']['display_name'];
			$this->notify("Нашлось $c объектов: \n".implode(",\n",$o));
		}
		return isset($s[0])? $s[0] : $s;
    }

	function getServices($service=null){ // ищет сервисы с одинаковым именем
		if(!$service) return false;
		if(!($serviceid = $this->nameService($service))) return false;
		$url = "/objects/services?pretty=1";
		$data = array('filter'=>"service.name==\"{$serviceid}\"");
		return $this->send($url,$data,array('X-HTTP-Method-Override: GET'));
	}

	function getObjectServices($port=null){ // ищет сервис по устройсвту и номеру порта
		return $this->getPortService($port,false);
    }

    function getPortService($port=null,$snmpid=true){ // ищет сервис по устройсвту и номеру порта
		if(!is_array($port)) $port = $this->q->get('devports',$port);
		if(!$port || count(array_intersect_key($port,array('device'=>1,'number'=>1)))!=2){
			log_txt(__METHOD__.": Ошибка данных!"); return false;
		}
		$url = "/objects/services?pretty=1";
		$data = array('filter'=>"host.name==\"id{$port['device']}.{$this->domain}\" && ".
			"service.vars.device_port==\"{$port['number']}\" && ".($snmpid? "":"!")."service.vars.snmp_portid");
		return $this->send($url,$data,array('X-HTTP-Method-Override: GET'));
    }

	function getHostServices($host=null){ // возвращает все сервысы по устройству
		if(!$host) return array();
		if(is_numeric($host)){
			$name = "id{$host}.{$this->domain}";
		}elseif(is_string($host) && preg_match('/^id/',$host)){
			$name = $host;
		}elseif(is_array($host) && isset($host['id'])){
			$name = "id{$host['id']}.{$this->domain}";
		}
		if(!isset($name)) return array();
		$url = "/objects/services?pretty=1";
		$data = array('filter'=>"host.name==\"$name\" && (match(\"id*\", service.name) || match(\"port*\", service.name))");
		return $this->send($url,$data,array('X-HTTP-Method-Override: GET'));
    }

    function createHost($host=null){ // включает мониторнг хоста
		$host = $this->prepareHost($host);
		if(!$host || !isset($host['id'])) return false;
		$url = "/objects/hosts/id{$host['id']}.{$this->domain}?pretty=1";
		$model = ($host['firmname'])? $host['firmname'] : preg_replace('/\s+.*/','',$host['name']);
		$product = ($host['firmname'])? preg_replace('/'.$host['firmname'].'[- ]/','',$host['name']) : preg_replace('/.*\s+/','',$host['name']);
		$data = array(
			"attrs"=>array(
				"address"=>$host['ip'],
				"display_name"=>$model." ".$host['ip']." ".$host['address'],
				"vars.location"=>$host['address'],
				"vars.device_model"=>$model,
				"vars.device_product"=>$product,
				"vars.snmp_community"=>$host['community']
			),
			"templates"=>array('generic-host')
		);
		if($host['type']=='switch') $data['templates'][] = 'generic-switch';
		elseif($host['type']=='server') $data['templates'][] = 'generic-server';
		if(!($res = $this->send($url,$data,null,array(CURLOPT_CUSTOMREQUEST=>'PUT')))){
			$this->notify("Не получилось добавить: ".get_devname($host,0,0),"error");
		}else $this->notify("<b>".get_devname($host,0,0)."</b> установлен на отслеживание");
		return $res;
	}

    function updateHost($host=null){ // обновляет значения ip display_name device_product location snmp_community для хоста
		if(!$host) return false;
		if(is_numeric($host)) $host = $this->q->select("SELECT d.*,m.address FROM devices d, map m WHERE d.node1=m.id AND d.id='$host'",1);
		$url = "/objects/hosts/id{$host['id']}.{$this->domain}?pretty=1";
		$model = ($host['firmname'])? $host['firmname'] : preg_replace('/\s+.*/','',$host['name']);
		$product = ($host['firmname'])? preg_replace('/'.$host['firmname'].'[- ]/','',$host['name']) : preg_replace('/.*\s+/','',$host['name']);
		$data = array("attrs"=>array(
			"address"=>$host['ip'],
			"display_name"=>$model." ".$host['ip']." ".$host['address'],
			"vars.device_product"=>$product,
			"vars.location"=>$host['address'],
			"vars.snmp_community"=>$host['community']
		));
		if(!($res = $this->send($url,$data))){
			if($this->code==404) $res = $this->createHost($host);
		}else $this->notify("Данные по объекту <b>".get_devname($host,0,0)."</b> Обновлены!","error");
		return $res;
	}

	function prepareHost($host=null){ // берёт данные из таблицы devices (нужен id)
		if(is_numeric($host)) $host = $this->q->select("SELECT d.*,m.address FROM devices d, map m WHERE d.node1=m.id AND d.id='$host'",1);
		if(is_array($host) && isset($host['node1']) && !key_exists('address',$host))
			$host['address'] = $this->q->select("SELECT address FROM map WHERE id='{$host['node1']}'",4);
		if(!is_array($host) || !key_exists('address',$host)) return false;
		return $host;
	}

	function serviceData($service=null){ // формурует данные для создания сервиса
		$data = array("attrs"=>array(),"templates"=>array('generic-service'));
		if($service['object']=='port'){
			$data['attrs']["display_name"] = sprintf("Порт %02d ",$service['rootport']);
			if($service['portname']) $data['attrs']["display_name"] .= $service['portname'];
			$data['templates'][]='generic-port';
		}
		if($service['object']=='home' && $service['type']!='onu'){
			$data['attrs']["display_name"] = sprintf("Порт %02d ",$service['rootport']).$service['address'];
			$data['templates'][]='generic-building';
		}
		if($service['object']=='client' && $service['type']=='onu'){
			$data['attrs']["vars.macaddress"]=$service['macaddress'];
			$data['attrs']["display_name"] = "клиент {$service['address']} (onu:{$service['macaddress']})";
			$data['templates'][]='generic-ponclient';
		}
		if($service['object']=='home' && $service['type']=='onu'){
			$data['attrs']["vars.macaddress"]=$service['macaddress'];
			$data['attrs']["display_name"] = "Дом по {$service['address']} (onu:{$service['macaddress']})";
			$data['templates'][]='generic-ponconnect';
		}
		if($service['object']=='client' && $service['type']=='mconverter'){
			$data['attrs']["display_name"] = sprintf("Порт %02d клиент ",$service['rootport']).$service['address'];
			$data['attrs']['vars.login'] = $service['user'];
			$data['templates'][]='generic-ftthclient';
		}
		if(isset($service['address'])) $data['attrs']['vars.location'] = $service['address'];
		if($service['rootport']) $data['attrs']["vars.device_port"] = "{$service['rootport']}";
		return $data;
	}

	function makePortService($port){ // подготавливает данные для мониторинга порта
		$fld = array('id'=>0,'device'=>0,'number'=>0,'porttype'=>0);
		if(!$port || !is_array($port) || count(array_intersect_key($port,$fld))!=4){
			log_txt(__METHOD__.": некорректные данные: ".arrstr());
			return false;
		}
		$device = $this->q->select("SELECT * FROM devices WHERE id='{$port['device']}'",1);
		$node = ($device)? $this->q->get("map",$device['node1']) : null;
		$service = array('id'=>$port['id'],'object'=>'port','address'=>$node['address'],'root'=>$device['ip'],
			'rootid'=>$port['device'],'rootname'=>$device['name'],'rootport'=>$port['number']);
		if(preg_match('/pon/i',$port['name'])) $service['portname'] = $port['name'];
		return $service;
	}

	function createPortService($port){ // включает мониторинг порта на устройсвте
		if(!($service = $this->makePortService($port))) return false;
		return $this->createServices(array($service));
	}

	private function createService($service=null){ // создаёт сервис
		global $DEBUG;
		if(!$service || !is_array($service)){
			log_txt(__METHOD__.": некорректные данные: ".arrstr($service));
			return false;
		}
		if(!($serviceid = $this->nameService($service))) return false;
		if($DEBUG>0) log_txt(__METHOD__.": service: ".sprint_r($service));
		$data = $this->serviceData($service);
		$url = "/objects/services/id{$service['rootid']}.{$this->domain}!{$serviceid}?pretty=1";
		if($service['type']!='onu'){
			$n = $this->q->select("SELECT snmp_id FROM devports WHERE device='{$service['rootid']}' AND number='{$service['rootport']}' LIMIT 1",4);
			if(!$n){
				$this->error = "не найден snmp_id для {$service['rootport']} порта на ".get_devname($service['rootid'],0,0);
				$this->errordata = $data;
				return false;
			}
			$data['attrs']["vars.snmp_portid"] = "$n";
		}
		$result = $this->send($url,$data,null,array(CURLOPT_CUSTOMREQUEST=>'PUT'));
		if($result) $this->lastCreatedService = $data;
		return $result;
	}

    function updateService($service=null){ // обновляет значения ip display_name device_product location snmp_community для хоста
		global $objecttype;
		if(!$service) return false;
		if(!is_array($service) || !isset($service['host_name']) || !isset($service['service_name'])) return false;
		$url = "/objects/services/{$service['host_name']}!{$service['service_name']}?pretty=1";
		foreach(array('host_name','service_name') as $n) unset($service[$n]);
		$data = array("attrs"=>$service);
		if(!($res = $this->send($url,$data))){
			$this->notify("Ошибка обновления данных для <b>{$service['display_name']}</b>!","error");
		}else $this->notify("Данные по объекту <b>{$service['display_name']}</b> Обновлены!");
		return $res;
	}

	function removeHost($hostname){
		$this->lastdeleted = $hostname;
		$url = "/objects/hosts/{$hostname}?cascade=1&pretty=1";
		return $this->send($url,null,array('X-HTTP-Method-Override: DELETE'));
	}

    function deleteHost($id){ // выключает мониториг хоста и его сервисов
		global $N3MODIFY;
		$device = $this->prepareHost($id);
		if(!$device){
			$this->errors[] = "Не найдено устройстов базе!";
			if(!is_numeric($id) || !($host = $this->getHost($id))){
				$this->errors[] = "Не найден хост!";
				return false;
			}
			$device=array('id'=>$id,'address'=>$host['attrs']['vars']['location']);
			$dev_name = $host['attrs']['display_name'];
		}else $dev_name = get_devname($device,0,0);
		if(!($res = $this->removeHost("id{$device['id']}.{$this->domain}"))){
			if($this->code==404 && preg_match('/No objects found/',$this->error)) $this->error = "объект не найден";
			$this->notify("Не получилось удалить <b>$dev_name</b> на <b>{$device['address']}</b>","error");
		}else{
			$N3MODIFY = $this->q->fetch_all("SELECT id FROM map WHERE hostname='{$this->lastdeleted}'");
			$this->q->query("UPDATE map SET hostname='', service='' WHERE hostname='{$this->lastdeleted}'");
			$this->notify("<b>$dev_name</b> на <b>{$device['address']}</b> удалён вместе со всеми сервисами");
		}
		return $res;
	}

	function deleteHostServices($host=null){ // удаляет все сервысы по устройству
		if(!$host) return array();
		if(is_numeric($host)){
			$device = $this->q->select("SELECT d.*,m.address FROM devices d, map m WHERE d.node1=m.id AND d.id='$host'",1);
			$name = "id{$device['id']}.{$this->domain}";
		}elseif(is_string($host) && preg_match('/^id(\d+)/',$host,$m)){
			$device = $this->q->select("SELECT d.*,m.address FROM devices d, map m WHERE d.node1=m.id AND d.id='$m[1]'",1);
			$name = $host; $devid = $m[1];
		}elseif(is_array($host) && isset($host['id'])){
			$name = "id{$host['id']}.{$this->domain}"; $device = $host;
		}
		if(!isset($name)) return array();
		$devname = get_devname($device,0,0);
		$url = "/objects/services?cascade=1&pretty=1";
		$data = array('filter'=>"host.name==\"$name\" && (match(\"id*\", service.name) || match(\"port*\", service.name))");
		$res = $this->send($url,$data,array('X-HTTP-Method-Override: DELETE'));
		if($this->error && !$res){
			$this->notify("Ошибка удаления сервисов для <b>$devname</b> ! ".arrstr($this->error),"error");
			return false;
		}
		
		$deleted = 0; $errors = 0;
		if(!$res)$this->notify("Для <b>$devname</b> сервисов не найдено!");
		if($res && !isset($res[0])) $res = array($res);
		foreach($res as $k=>$r)
			if(isset($r['code']) && $r['code']==200 && preg_match('/id(\d+).*\!((id|port)\d+)/',$r['name'],$m)){
				$this->q->query("UPDATE map SET hostname='', service='' WHERE service='{$m[2]}'");
				$deleted++;
			}elseif(isset($r['code'])){
				if(!$errors) $this->error = implode("\n",$r['errors']);
				$errors++;
			}
		if($errors>1) $this->notify("Возникло <b>$errors</b> ошибок при удалении сервисов <b>$devname</b>!","error");
		if($errors==1) $this->notify("Ошибка при удалении сервисов <b>$devname</b>! ".$this->error,"error");
		if($deleted) $this->notify("Удалено <b>$deleted</b> сервисов для <b>$devname</b> !");
		
		return $res;
    }

	function deletePortService($data){ // выключает мониториг порта
		if(!($s = $this->makePortService($data))) return false;
		return $this->deleteServices(array($s));
    }

    function removeService($servicename){ // удаляет сервис по имени
		if(!$servicename) return false;
		$this->lastdeleted = $servicename;
		$url = "/objects/services/{$servicename}?cascade=1&pretty=1";
		return $this->send($url,null,array('X-HTTP-Method-Override: DELETE'));
    }

    function safeRemoveServices($list){ // удаляет набор сервисов
		if(!$list || !is_array($list)) return false;
		$errors = $del = 0;
		foreach($list as $k=>$s) {
			if(!($res = $this->removeService($s['name']))){
				log_txt("Ошибка удаления сервиса {$s['attrs']['display_name']}: ".$this->error);
				$this->errors[] = $s['attrs']['display_name'];
				$errors++;
			}else{
				$del++;
				$cl = $s['attrs']['display_name'];
				preg_match('/^(id([0-9]+).*)\!([a-z]+([0-9]+).*)/',$s['name'],$m);
				$rd = ($del == 1 && $device)? get_devname($m[2],0,0) : null;
				$this->q->query("UPDATE map SET hostname='', service='' WHERE hostname='{$m[1]}' AND service='{$m[3]}'");
// 				log_txt(__METHOD__.": SQL: ".sqltrim($this->q->sql));
			}
		}
		if($errors) $this->notify("Следующие объекты не были удалены:\n".implode(",\n",$this->errors),"error",false);
		if($del) $this->notify("Удалено отслеживание ".(($del>1)?"<b>$del</b> объектов":"<b>$cl</b>")." на <b>$rd</b>");
		return $del;
    }

	private function deleteService($service=null){ // удаляет сервис с проверкой имени
		if(!$service) return false;
		if(!($serviceid = $this->nameService($service))) return false;
		return $this->removeService("id{$service['rootid']}.{$this->domain}!{$serviceid}");
    }

    function createServices($data){ // добавляет сервисы найденные по cutClients
		global $N3MODIFY, $devtype, $objecttype;
		$this->errors = array();
		$errors = $append = 0; $err = array();
		if(is_numeric($data)) $id=$data;
		elseif(is_array($data) && key_exists('porttype',$data)) $id = $data['id'];
		elseif(is_array($data) && isset($data[0]['object'])) $all = $data;
		else{
			log_txt(__METHOD__.": некорректные данные: ".arrstr($data));
			return false;
		}
		if(isset($id) && !($all = cutClients($id))){
			log_txt(__METHOD__.": Не найдены объекты на порту $id!");
			return false;
		}
		$i = 0;
		while(isset($all[$i])){
			$s = $all[$i];
			if($s['hostname'] && $s['service'] && preg_match('/^id/',$s['hostname']) && preg_match('/^id/',$s['service'])){
				$this->deleteService($s['hostname']."!".$s['service']);
			}
			if(!$this->createService($s)){
				if($this->code == 500 && preg_match('/Host. does not exist/i',$this->error) && $i==0){
					if(!$this->createHost($s['rootid'])) return false;
					continue;
				}
				$this->errors[$errors] = "<b>{$this->errordata['attrs']['display_name']}</b>";
				if($this->code==500 && preg_match('/already exists/',$this->error)) $this->errors[$errors] .= " (уже существует)";
				if($this->code==0) $this->errors[$errors] .= " ".$this->error;
				$errors++;
			}else{
				$N3MODIFY[] = $s['id'];
				$this->q->query("UPDATE map SET hostname='id{$s['rootid']}.{$this->domain}', service='id{$s['id']}' WHERE id={$s['id']}");
				$append++;
				if(!isset($rd)) $rd = get_devname($s['rootid'],0,0);
				if(!isset($cl)) $cl = $this->lastdata['attrs']['display_name'];
				if(!isset($rp)){
					$rp = $this->q->select("SELECT name FROM devports WHERE device='{$s['rootid']}' AND number={$s['rootport']}",4);
					if(!$rp) $rp = $s['rootport'];
				}
			}
			$i++;
		}
		if($errors){
			$this->notify("Следующие объекты не были добавлены:\n".implode(",\n",$this->errors),"error",false);
			if($errors!=1){
				log_txt("Icinga2: не получилось добавить $errors объектов");
			}else log_txt("Icinga2: не получилось добавить объект. ".$this->errors[0]);
		}
		if($append) $this->notify((($append>1)?"По порту <b>$rp</b> д":"Д")."обавлено отслеживание ".
			(($append>1)?"<b>$append</b> объектов!":"<b>$cl</b>")." на <b>$rd</b>");
		if(isset($N3MODIFY)) $N3MODIFY = array_unique($N3MODIFY);
		return $append;
    }

    function deleteServices($data){ // удаляет сервисы найденные по cutClients
		global $N3MODIFY, $devtype, $objecttype;
		$this->errors = array();
		$errors = $del = 0;
		if(is_numeric($data)) $id=$data;
		elseif(is_array($data) && isset($data[0]['object'])) $all = $data;
		else return false;
		if(isset($id) && !($all = cutClients($id))){
			log_txt(__METHOD__.": Не найдены объекты на порту $id!");
			return false;
		}
		foreach($all as $i=>$s){
			if(!$this->deleteService($s)){
				log_txt("deleteService ".$this->error);
				$this->errors[$errors] = "<b>{$s['attrs']['display_name']}</b>";
				if($this->code==404 && preg_match('/No objects found/',$this->error)) $this->errors[$errors] .= " объект не найден ".$this->lastdeleted;
				$errors++;
			}else{
				if($s['object'] != 'port'){
					$N3MODIFY[] = $s['id'];  $ds[] = "'id{$s['id']}'";
				}
				$del++;
				if(!isset($rd)) $rd = get_devname($s['rootid'],0,0);
				if(!isset($cl)){ $cl = $this->serviceData($s); $cl = $cl['attrs']['display_name']; }
			}
		}
		if(count($ds)) $this->q->query("UPDATE `map` SET hostname='', service='' WHERE service in (".implode(',',$ds).")");
		if($errors) $this->notify("Следующие объекты не были удалены:\n".implode(",\n",$this->errors),"error",false);
		if($del) $this->notify("Удалено отслеживание ".(($del>1)?"<b>$del</b> объектов":"<b>$cl</b>")." на <b>$rd</b>");
		if(isset($N3MODIFY)) $N3MODIFY = array_unique($N3MODIFY);
		return $del;
    }
}
?>
