<?php
include_once("classes.php");
include_once("map.cfg.php");
$mon_types = array('switch'=>'свич','linux'=>'сервер Linux','mikrotik'=>'роутер Mikrotik');

$tables['monitoring']=array(
	'title'=>'Объект',
	'name'=>'monitoring',
	'key'=>'id',
	'query'=>"
		SELECT 
			'new' as id,
			'' as type,
			'' as servicetype,
			'' as object,
			'' as display_name,
			'' as ip,
			'' as location,
			'' as community,
			'' as parents,
			'' as operation,
			'' as realsave,
			'' as restart,
			'' as actions
		FROM map
		",
	'class'=>'normal',
	'style'=>'width:500px',
// 	'footer'=>array(),
	'class'=>'normal',
	'before_check'=>'before_check_monitoring',
	'before_new'=>'before_new_monitoring',
	'before_save'=>'before_save_monitoring',
	'before_edit'=>'before_edit_monitoring',
	'form_save'=>'save_monitoring',
	'form_onsave'=>'onsave_monitoring',
	'form_delete'=>'disable_monitoring',
	// поля
	'fields'=>array(
		'id'=>array(
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'actions'=>array(
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'type'=>array(
			'label'=>'тип',
			'type'=>'nofield',
			'style'=>'width:130px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'servicetype'=>array(
			'label'=>'тип сервиса',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'host_name'=>array(
			'label'=>'хост',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'service_name'=>array(
			'label'=>'сервис',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'device'=>array(
			'label'=>'устройство',
			'type'=>'nofield',
			'style'=>'width:350px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'host'=>array(
			'label'=>'хост',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'device_port'=>array(
			'label'=>'порт',
			'type'=>'text',
			'style'=>'width:40px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'service'=>array(
			'label'=>'сервис',
			'type'=>'nofield',
			'style'=>'width:350px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'location'=>array(
			'label'=>'Адрес',
			'type'=>'hidden',
			'style'=>'width:350px;overflow:hidden;white-space:nowrap;',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'display_name'=>array(
			'label'=>'Название',
			'type'=>'text',
			'style'=>'width:340px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'ip'=>array(
			'label'=>'ip',
			'type'=>'text',
			'style'=>'width:120px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'community'=>array(
			'label'=>'community',
			'type'=>'text',
			'style'=>'width:120px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'snmp_portid'=>array(
			'label'=>'snmp port id',
			'type'=>'text',
			'style'=>'width:120px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'macaddress'=>array(
			'label'=>'mac адрес',
			'type'=>'text',
			'style'=>'width:130px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'parents'=>array(
			'label'=>'родитель',
			'type'=>'select',
			'list'=>'get_host_names',
			'style'=>'width:210px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'operation'=>array(
			'label'=>'выполнить',
			'type'=>'select',
			'style'=>'width:200px',
			'list'=>array(''=>'','delete'=>'удаление всех сервисов','create'=>'создание сервисов (авто)','disable'=>'отключение мониторинга'),
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'realsave'=>array(
			'label'=>'запись',
			'type'=>'select',
			'style'=>'width:70px',
			'list'=>array('yes'=>'да','no'=>'нет'),
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'restart'=>array(
			'label'=>'пезагрузить',
			'type'=>'select',
			'style'=>'width:70px',
			'list'=>array('no'=>'нет','yes'=>'да'),
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		)
	),
);

function before_check_monitoring($f,$my) {
	if(ICINGA_URL && isset($_REQUEST['do']) && $_REQUEST['do'] == 'save'){
		$f['record'] = $my->separate($f['fields'],'old_');
		log_txt(__FUNCTION__.": record".arrstr($f['record']));
	}
	if(isset($_REQUEST['do']) && $_REQUEST['do'] == 'realremove'){
		$f['record'] = $my->separate(array('id'=>1,'display_name'=>1,'host_name'=>1,'service_name'=>1));
	}
	return $f;
}

function before_new_monitoring($f,$my) {
	global $q, $DEBUG, $config, $opdata;
	if(!is_object($q)) $q = new sql_query($config['db']);
	$id = (isset($_REQUEST['id']))? numeric($_REQUEST['id']) : "";
	$to = array('device'=>0,'port'=>0,'client'=>0);
	$type = $_REQUEST['servicetype'];
	$port = $to['port'] = ($type == 'port')? $q->get('devports',$id) : false;
	$client = $to['client'] = ($type == 'client')? $q->get('map',$id) : false;
	$clport = ($client)? $q->select("SELECT d.type, d.macaddress, p.* FROM devices d, devports p WHERE d.node1='{$client['id']}' AND d.id=p.device AND d.type in ('onu','mconverter') AND p.porttype='fiber' AND p.link>0",1) : false;
	$clservice = ($clport)? arrfld(cutClients($clport['id']),0) : false;
	$dev = null;
	if($client && preg_match('/^id([0-9]+)\./',$client['hostname'],$m)){
		$dev = $q->get('devices',$m[1]);
		if($dev && $clservice && $dev['id']!=$clservice['rootid']){
			log_txt(__FUNCTION__.": несовпадает устройство подключения hostname[{$m[1]}] и service[{$clservice['rootid']}]");
			$q->query("UPDATE map SET hostname='', service='' WHERE id='{$client['id']}'");
			$clservice['hostname']=''; $clservice['service']='';
			$dev = $q->get('devices',$clservice['rootid']);
		}
	}elseif($client && $clport && $clservice){
		$dev = $q->get('devices',$clservice['rootid']);
	}elseif(!$client) $dev = $q->get('devices',($port? $port['device'] : $id));
	if(!$dev){
		log_txt(__FUNCTION__.": client: {$client['name']} port: ".($port?$port['id']:$clport['id'])." service: ".arrstr($clservice));
		stop("Не найдено устройство подключения!");
	}
	$to['device'] = $dev;
	if($dev['ip']=='' || !preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/',$dev['ip']) || $dev['community']=='' || $dev['name']==''){
		log_txt(__FUNCTION__.": устройство подключения ".($dev? get_devname($dev):"").($client? $client['hostname']:""));
		stop("Устройство подключения не является управляемым свичём!");
	}
	if(ICINGA_URL){
		foreach(array('realsave','restart','parents') as $n) unset($f['fields'][$n]);
		$mon = new Icinga2();
		$f['force_submit'] = 1;
		$f['header']='Изменение данных мониторинга';
		$host = $mon->getHost($dev);
		if($host && isset($host[0])) $host = $host[0];
		if(!$host && $_REQUEST['do'] != 'realnew'){
			$form = new form($config);
			$out = $form->confirmForm('new','realnew',"Мониторинг для ".get_devname($dev,0,0)." не включен!<br>Хотите включиить?",'monitoring');
			$out['form']['fields']['id']=array('type'=>'hidden','value'=>$to[$type]['id']);
			$out['form']['fields']['servicetype']=array('type'=>'hidden','value'=>strong($type));
			stop($out);
		}
		if(!$host && $_REQUEST['do'] == 'realnew'){
			if(!$mon->createHost($dev)) stop("Не удалось создать объект мониторинга");
			if(!($host = $mon->getHost($dev))) stop("Не удалось получить объект мониторинга");
			if(!isset($host[0])) stop("Ошибка Icinga");
			$host = $host[0];
		}
		if($type == 'device'){
			$f['id'] = $dev['id'];
			$f['record'] = array('id'=>$dev['id'], 'type'=>$dev['type'], 'servicetype'=>$type, 'host_name'=>$host['name'], 'display_name'=>$host['attrs']['display_name'], 'ip'=>$host['attrs']['address'], 'location'=>$host['attrs']['vars']['location'], 'community'=>$host['attrs']['vars']['snmp_community'], 'operation'=>'');
		}elseif($port && $host){
			$service = $mon->getPortService($port);
			if($service && isset($service[0])) $service = $service[0];
			if(!$service && $_REQUEST['do'] != 'realnew'){
				$form = new form($config);
				$out = $form->confirmForm('new','realnew',"Мониторинг для {$port['number']} порта не включен!<br>Хотите включиить?",'monitoring');
				$out['form']['fields']['id']=array('type'=>'hidden','value'=>$port['id']);
				$out['form']['fields']['servicetype']=array('type'=>'hidden','value'=>strong($type));
				stop($out);
			}
			if(!$service && $_REQUEST['do'] == 'realnew'){
				if($port['link']){
					if(!$port['name']){
						$cl = cutClients($port['id']); $all = count($cl);
						if($all==1) $mon->createServices($cl);
						else $mon->createPortService($port);
					}else{
						if(preg_match('/pon/i',$port['name'])) $mon->createPortService($port);
						elseif(!$mon->createServices($port['id'])) $mon->createPortService($port);
					}
				}else $mon->createPortService($port);
				$service = $mon->getPortService($port);
				if(!$service) stop("Не найден сервис!<br>".implode("<br>",$mon->errors));
				if(isset($service[0])) $service = $service[0];
			}
			$f['id'] = $dev['id'];
			$f['record'] = array('id'=>$port['id'], 'host'=>$service['attrs']['host_name'], 'type'=>$service['attrs']['vars']['service_type'], 'servicetype'=>$type, 'host_name'=>$service['attrs']['host_name'], 'service_name'=>$service['attrs']['name'], 'device'=>get_devname($dev), 'location'=>$service['attrs']['vars']['location'], 'device_port'=>$service['attrs']['vars']['device_port'], 'display_name'=>$service['attrs']['display_name']);
			if(isset($service['attrs']['vars']['snmp_portid'])) $f['record']['snmp_portid'] = $service['attrs']['vars']['snmp_portid'];
			if(isset($service['attrs']['vars']['macaddress'])) $f['record']['macaddress'] = $service['attrs']['vars']['macaddress'];
		}elseif($client){
			$service = $mon->getService($client['id']);
			if(!$service && $_REQUEST['do'] != 'realnew'){
				$form = new form($config);
				$out = $form->confirmForm('new','realnew',"Мониторинг для клиента {$client['address']} ".(($clport && $clport['type']=='onu')?"(onu: {$clport['macaddress']})":"")." не включен!<br>Хотите включиить?",'monitoring');
				$out['form']['style'] .= "max-width:550px";
				$out['form']['fields']['id']=array('type'=>'hidden','value'=>$client['id']);
				$out['form']['fields']['servicetype']=array('type'=>'hidden','value'=>strong($type));
				stop($out);
			}
			if(!$service && $_REQUEST['do'] == 'realnew'){
				$mon->createServices($clport);
				$service = $mon->getService($client['id']);
			}
			$f['id'] = $client['id'];
			$f['record'] = array('id'=>$client['id'], 'host'=>$service['attrs']['host_name'], 'type'=>$service['attrs']['vars']['service_type'], 'servicetype'=>$type, 'host_name'=>$service['attrs']['host_name'], 'service_name'=>$service['attrs']['name'], 'device'=>get_devname($dev), 'location'=>$service['attrs']['vars']['location'], 'device_port'=>$service['attrs']['vars']['device_port'], 'display_name'=>$service['attrs']['display_name']);
			if(isset($service['attrs']['vars']['snmp_portid'])) $f['record']['snmp_portid'] = $service['attrs']['vars']['snmp_portid'];
			if(isset($service['attrs']['vars']['macaddress'])) $f['record']['macaddress'] = $service['attrs']['vars']['macaddress'];
		}else{
			stop("Ошибка данных!");
		}
		if($type != 'device'){
			$f['footer'] = array(
				"actionbutton"=>array("txt"=>'Отключить','style'=>"margin-right:30%",'onclick'=>"
					var f = $(this).parents('form'),
					id = f.find('input[name=id]').val(),
					dn = f.find('[name=display_name]').val(),
					hn = f.find('[name=host_name]').val(),
					sn = f.find('input[name=service_name]').val();
					if(ldr && id) ldr.get({
						data: 'id='+id+'&servicetype='+type+'&host_name='+hn+'&service_name='+sn+'&display_name='+dn+'&go=stdform&do=realremove&table=monitoring',
						onLoaded: function(d){
							f.find('#cancelbutton').click();
						}
					})
				"),
				"cancelbutton"=>array("txt"=>'Отменить'),
				"submitbutton"=>array("txt"=>'Сохранить')
			);
		}
	}elseif(NAGIOS_URL){
		unset($f['fields']['operation']);
		if(!($h = get_nagios("do=get_objects&objects=address:{$dev['ip']}",'hosts'))){
			if(isset($NAGIOS_ERROR)) stop($NAGIOS_ERROR);
		}
		if($h && count($h)>0){
			$host = reset($h);
			unset($host['services']);
			$f['defaults']=array_merge(array('type'=>'host','use'=>'generic-host','community'=>$dev['community']),$host);
			$f['defaults']['restart'] = 'yes';
			$f['header']='Изменение свича';
			$f['fields']['actions']['value']='mod_ng_switch';
		}else{
			$dev = n3switch_new($dev);
			$f['defaults']=array_merge(array('type'=>'host','community'=>$dev['community']),$dev);
			$f['defaults']['restart'] = 'yes';
			$f['header']='Добавление свича';
			$f['fields']['actions']['value']='new_ng_switch';
		}
		$f['record'] = array('type'=>$dev['type'], 'host_name'=>$host['name'], 'display_name'=>$host['display_name'], 'ip'=>$host['attrs']['address'], 'community'=>$host['attrs']['vars']['snmp_community'], 'parents'=>$host['attrs']['vars']['parents'], 'operation'=>'');
	}
	return $f;
}

function before_edit_monitoring($f,$my) {
	return before_new_monitoring($f,$my);
}

function before_save_monitoring($c,$o,$my) {
	return $c;
}

function save_monitoring($s,$my) {
	return 1;
}

function onsave_monitoring($id,$s,$my) {
	global $q, $DEBUG, $config, $opdata, $tables;
	log_txt(__FUNCTION__.": save = ".arrstr($s));
	if(!is_object($q)) $q = new sql_query($config['db']);
	if(ICINGA_URL){
		$type = $_REQUEST['servicetype'];
		$mon = new Icinga2();
		$fld=array('id'=>1,'address'=>1,'display_name'=>1,'host_name'=>1,'service_name');
		if($DEBUG>0) log_txt(__FUNCTION__.": save ".arrstr($s));
		if($type == 'device'){
			$object = $q->get('devices',$id);
			if($s['operation'] == 'delete'){
				$mon->deleteHostServices($object);
			}elseif($s['operation'] == 'create'){
				$ports = $q->get('devports',$object['id'],'device');
				foreach($ports as $k=>$p) if($p['link']) $mon->createServices($p['id']);
			}elseif($s['operation'] == 'disable'){
				$mon->deleteHost($object);
			}
		}elseif($type == 'port' || $type == 'client'){
			if(key_exists('operation',$s)) unset($s['operation']);
			$service = array();
			foreach($tables['monitoring']['fields'] as $k=>$v){
				if($v['type']=='text' && isset($s[$k]))
					if($k != 'display_name') $service['vars.'.$k] = $s[$k]; else $service[$k] = $s[$k];
			}
			$service['host_name'] = $my->row['host_name'];
			$service['service_name'] = $my->row['service_name'];
			if(!isset($service['display_name'])) $service['display_name'] = $my->row['display_name'];
			if(!($res = $mon->updateService($service))){
				log_txt(__FUNCTION__.": ошибка обновления сервиса: ".arrstr($service)." результат: ".arrstr($res));
			}
		}
		return true;
	}elseif(NAGIOS_URL){
		if($s['actions'] == 'new_ng_switch'){
			$new_sw = array('host_name'=>1,'alias'=>2,'address'=>3,'community'=>4,'parents'=>5);
			$dev = array_intersect_key($_REQUEST,$new_sw);
			if(CONFIGURE_NAGIOS>0)
			if(!($ng = get_nagios("do=switch_add&switch=".urlencode(json_encode($dev)).
				"&realsave={$s['realsave']}&restart={$s['restart']}","data")))
				stop($NAGIOS_ERROR);
			return array('result'=>'OK', 'desc'=>(CONFIGURE_NAGIOS>0)?"конфигурация NAGIOS обновлена!":"");
		}elseif($s['actions']=='mod_ng_switch'){
			if($opdata['status']<5) stop(array('result'=>'ERROR','desc'=>'Недостаточно прав!'));
			$switch = $q->get('devices',$in['id']);
			$dev = array_intersect_key($_REQUEST,$ng_switch);
			$oldsw = array();
			foreach($ng_switch as $nf=>$v) if(key_exists("old_".$nf,$_REQUEST)) $oldsw[$nf] = $_REQUEST["old_".$nf];
			$modsw = $q->compare($oldsw,$dev);
			if(count($modsw)==0) stop(array('result'=>'OK', 'desc'=>"Изменения не требуются!"));

			// выбираем неизменившийся параметр для однозначного определения свича
			if(!isset($modsw['address'])) $obj = $ip = "address:".urlencode($_REQUEST['address']);
			elseif(!isset($modsw['host_name'])) $obj = $hn = "host_name:".urlencode($_REQUEST['host_name']);
			else stop(array('result'=>'ERROR', 'desc'=>'изменены сразу 2 ключевых параметра!'));
			// проверяем если ли уже такой свич
			if(isset($modsw['address']) && $q->get('devices',$modsw['address'],'ip'))
				stop(array('result'=>'ERROR', 'desc'=>"Свич с IP={$modsw['address']} уже есть!"));
			if(isset($modsw['host_name']) && $q->get('map',$dev['host_name'],'hostname'))
				stop(array('result'=>'ERROR', 'desc'=>"Свич HOST_NAME={$modsw['host_name']} уже есть!"));
			// собственно обновление информации по свичу в nagios
			if(CONFIGURE_NAGIOS>0) {
				if(!($ng = get_nagios("do=switch_mod&objects=$obj&switch=".urlencode(json_encode($modsw)).
					"&realsave={$_REQUEST['realsave']}&restart={$_REQUEST['restart']}",'data'))){
					stop(array('result'=>'ERROR', 'desc'=>preg_replace('/\n/','<br>',$NAGIOS_ERROR['desc'])));
				}
				if(isset($hn)) $q->query("UPDATE map SET hostname='{$ng['hostname']}' WHERE hostname={$hn}");
				if(isset($ip)) $q->query("UPDATE devices SET ip='{$ng['address']}' WHERE type='switch' AND ip='{$ip}'");
			}
			stop(array('result'=>'OK', 'desc'=>(CONFIGURE_NAGIOS>0)?"конфигурация NAGIOS обновлена!":""));
		}
	}
}

function disable_monitoring($s,$my){
	log_txt(__FUNCTION__.": save = ".arrstr($s));
	if(!@$s['host_name'] || !@$s['service_name'] || !@$s['display_name']) return 0;
	$objects = array('name'=>$s['host_name']."!".$s['service_name'],'attrs'=>array('display_name'=>$s['display_name']));
	$mon = new Icinga2();
	$mon->safeRemoveServices(array($objects));
	return 1;
}
?>
