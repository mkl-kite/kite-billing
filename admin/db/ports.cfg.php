<?php
include_once("classes.php");
include_once("icinga.php");
$modified_ports=false;

$port_fields_filter = array(
	'cable'=>array('numports','colorscheme','n2address','bandleports'),
	'switch'=>array('numports','ip','community'),
	'onu'=>array('numports','macaddress'),
	'server'=>array('numports','ip','community'),
	'patchpanel'=>array('numports'),
	'divisor'=>array('colorscheme','subtype'),
	'splitter'=>array('colorscheme','subtype')
);

$tables['ports']=array(
	'title'=>'Устройство',
	'target'=>"form",
	'module'=>"port_form",
	'key'=>'id',
	'query'=>"
		SELECT 
			id,
			device,
			number,
			node,
			0 as linkdevice,
			link,
			porttype,
			snmp_id,
			'' as vlan,
			name,
			module,
			color,
			coloropt,
			divide,
			bandle,
			note,
			0 as allports
		FROM
			devports
		",
	'form_triggers'=>array(
		'porttype'=>'porttype'
	),
	'before_save'=>'before_save_port',
	'before_edit'=>'before_edit_port',
	'form_onsave'=>'out_save_link_result',
	'class'=>'normal',
	'delete'=>'yes',
// 	'footer'=>array(),
	'table_triggers'=>array(
	),
	'sort'=>'',
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
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'node'=>array(
			'label'=>'узел',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'name'=>array(
			'label'=>'название',
			'type'=>'text',
			'style'=>'width:100px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'number'=>array(
			'label'=>'номер порта',
			'type'=>'text',
			'style'=>'width:50px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'snmp_id'=>array(
			'label'=>'snmp id',
			'type'=>'text',
			'style'=>'width:50px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'vlan'=>array(
			'label'=>'VLAN',
			'type'=>'text',
			'native'=>false,
			'style'=>'width:250px',
			'style'=>'max-width:400px;white-space:auto;',
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'porttype'=>array(
			'label'=>'тип',
			'type'=>'nofield',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'monitoring'=>array(
			'label'=>'мон-нг порта',
			'type'=>'checkbox',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'mon_client'=>array(
			'label'=>'мон-нг объектов',
			'type'=>'checkbox',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'module'=>array(
			'label'=>'модуль',
			'type'=>'select',
			'list'=>'select_module_sfp',
			'style'=>'min-width:150px;max-width:250px;',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'allports'=>array(
			'label'=>'соединить все',
			'type'=>'hidden',
			'list'=>array('нет','да'),
			'native'=>false,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'linkdevice'=>array(
			'label'=>'устройство',
			'type'=>'select',
			'list'=>'list_of_node_devices',
			'onselect'=>'reloadlink',
			'style'=>'min-width:150px;max-width:250px;',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'link'=>array(
			'label'=>'вкл.порт',
			'type'=>'select',
			'list'=>array(),
			'style'=>'min-width:150px;max-width:250px;',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map'),
			'onchange'=>"
				var c=$(this), color;
				c.find('option').css({'background-color':'white'});
				if(color=c.find('option[value='+c.val()+'] span.color').css('background-color'))
					c.css({'background-color':$.bright($.rgb2hex(color),0.6)})
				return false;
			"
		),
		'link1'=>array(
			'label'=>'вкл.порт2',
			'type'=>'select',
			'list'=>array(),
			'style'=>'min-width:150px;max-width:250px;',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'color'=>array(
			'label'=>'цвет',
			'type'=>'select',
			'list'=>'list_of_colors',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map'),
			'onchange'=>"
				var c=$(this), color;
				c.find('option').css({'background-color':'white'});
				if(color=$.colourNameToHex(c.val()))
					c.css({'background-color':$.bright(color,0.6)})
				return false;
			"
		),
		'divide'=>array(
			'label'=>'процент',
			'type'=>'nofield',
			'style'=>'width:50px;text-align:right;padding-right:5px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'coloropt'=>array(
			'label'=>'метки',
			'type'=>'select',
			'list'=>array('solid'=>'нет','dashed'=>'есть'),
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'bandle'=>array(
			'label'=>'цвет пучка',
			'type'=>'select',
			'list'=>'list_colors_of_bandles',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map'),
			'onchange'=>"
				var c=$(this), color;
				c.find('option').css({'background-color':'white'});
				if(color=$.colourNameToHex(c.val()))
					c.css({'background-color':$.bright(color,0.6)})
				return false;
			"
		),
		'note'=>array(
			'label'=>'примечание',
			'type'=>'textarea',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		)
	)
);

function before_edit_port($f) {
	global $config;
	$q = new sql_query($config['db']);
	$id = numeric($_REQUEST['id']);
	$p=$q->get("devports",$id);
	if(!$p) stop(array('result'=>'ERROR','desc'=>"Порт не найден!"));
	$device = $q->get("devices",$p['device']);
	if($id>0 && $_REQUEST['do']=='connect'){
		if($p['porttype'] == 'wifi' && $device['subtype'=='ap'])
			stop(array('result'=>'ERROR','desc'=>"Этот порт не может быть присоединён!"));
		if($p['link'] === null || $p['link']==0){
			$in['nodeid']=$p['node'];
			$in['devid']=$p['device'];
			$f['name']='ports';
			if($p['porttype']=='fiber') $f['fields']['allports']['type']='select';
			$f['header']=get_devname($device,numeric($_REQUEST['selectednode']));
			foreach(array('color','coloropt','bandle','note','divide','module') as $fn) unset($f['fields'][$fn]);
		}else{
			stop(array('result'=>'ERROR','desc'=>"Порт уже имеет соединение!"));
		}
	}
	if($id>0 && $_REQUEST['do']=='disconnect'){
		if($p['link'] !== null){
			$form = new form($config);
			stop($form->confirmForm($in['id'],'realdisconnect','Разорвать соединение?'));
		}else{
			stop(array('result'=>'ERROR','desc'=>"Порт не имеет соединения!"));
		}
	}
	if($id>0 && $_REQUEST['do']=='edit' && $device['type']=='switch' && $device['community']!=''){
		$p['linkdevice']=0;
		unset($p['link1']);
		if(ICINGA_URL){
			$mon = new Icinga2();
			$service = $mon->getPortService($p);
			if($service){
				$f['fields']['monitoring']['title'] = $service[0]['attrs']['display_name'];
				$p['monitoring'] = 1;
			}else{
				$p['monitoring'] = 0;
			}
		}
		if(preg_match('/pon/i',$p['name'])){
			$f['footer'] = array(
				"actionbutton"=>array("txt"=>'Сбросить','onclick'=>"
					var f = $(this).parents('form'), id = f.find('input[name=id]').val();
					if(ldr) ldr.get({
						data: 'go=devices&do=clearonu4port&id='+id,
						onLoaded: function(d){}
					})
					f.find('#cancelbutton').click();
				"),
				"cancelbutton"=>array("txt"=>'Отменить'),
				"submitbutton"=>array("txt"=>'Сохранить')
			);
		}
		$p['vlan'] = ids(getPortVlans($p));
		$f['record'] = $p;
	}else foreach(array('monitoring','name','snmp_id','vlan') as $n) unset($f['fields'][$n]);
	return $f;
}

function port_by_devtype($r) { // нигде не используется
	global $port_fields_filter, $tables;
	$res=array();
	$all=array();
	foreach($dev_fields_filter as $k=>$v) $all=array_unique(array_merge($all,$v));
	if(key_exists(@$r['type'],$dev_fields_filter))
		$res=array_flip(array_diff($all,$dev_fields_filter[$r['type']]));
	else 
		$res=array_flip($all);
	return $res;
}

function list_of_node_devices($rec){
	global $config, $device, $devtype, $q;
	if(!isset($q)) $q = new sql_query($config['db']);
	$d=array(); $p[0] = '';
	if($rec['porttype']=='fiber' && $device['type']=='patchpanel') $d=array('cable');
	if($rec['porttype']=='coupler') $d=array('patchpanel','switch','splitter');
	if($rec['porttype']!='wifi') {
		$condition = (count($d)>0)? "AND d.type in ('".implode("','",$d)."')" : "";
		$condition1 = (count($d)>0)? "" : "AND d.id!={$rec['device']}";
		$r = $q->fetch_all("
			SELECT d.*, n1.address as a1, n2.address as a2 
			FROM devices d 
				LEFT OUTER JOIN map as n1 ON n1.id = d.node1
				LEFT OUTER JOIN map as n2 ON n2.id = d.node2
			WHERE (node1={$rec['node']} OR node2={$rec['node']}) $condition $condition1
			ORDER BY d.type, d.ip, d.name
		");
		foreach($r as $k=>$v) $p[$k] = get_devname($v,$rec['node']);
	}else{
		if($device['type']=='wifi' && $device['subtype']=='bridge') $d=array('bridge');
		if($device['type']=='wifi' && $device['subtype']=='station') $d=array('ap');
		$condition = (count($d)>0)? "AND d.subtype in ('".implode("','",$d)."')" : "";
		$r = $q->fetch_all("
			SELECT d.*, n1.address as a1, '' as a2
			FROM devices d LEFT OUTER JOIN map as n1 ON n1.id = d.node1
			WHERE d.type='wifi' $condition
			ORDER BY d.subtype, d.ip, d.name
		");
		foreach($r as $k=>$v) $p[$k] = get_devname($v,$rec['node'])." ".$v['a1'];
	}
	return $p;
}

function select_module_sfp($r) {
	global $q, $config;
	if(!is_object($q)) $q = new sql_query($config['db']);
	$a = array();
	$dev = $q->get('devices',$r['device']);
	if((preg_match('/BDCOM/',$dev['name']) && $r['number']>=7)||
	(preg_match('/C[-]?DATA/',$dev['name']) && $r['number']>=1 && $r['number']<=16)){
		foreach($config['map']['modules'] as $k=>$v) {
			if($k == 'unknown') $a['_'] = '';
			else $a[$k] = $k;
		}
	}else{
		$a = $config['map']['sfp'];
	}
	return $a;
}

function porttype($v,$r){
	global $porttype;
	$ptypes = (is_array($porttype))? $porttype : array('coupler'=>'каплер','fiber'=>'оптика','cuper'=>'медь');
	return $ptypes[$v];
}

function list_of_colors($rec){
	global $config, $in;
	$r=array('nocolor'=>'');
	$q=new sql_query($config['db']);
	$c=$q->fetch_all("SELECT distinct color, rucolor FROM devprofiles ORDER BY port","color");
	foreach($c as $k=>$v) {
		$r[$k]=$v."<span class=\"color\" style=\"background-color:$k\">&nbsp;</span>";
	}
	return $r;
}

function list_colors_of_bandles($rec){
	global $config, $in;
	$r=array('nocolor'=>'');
	$q=new sql_query($config['db']);
	$device=$q->select("SELECT * FROM devices WHERE id='{$rec['device']}'",1);
	if($device['numports']>=12) {
		$c=$q->fetch_all("SELECT distinct color, rucolor FROM devprofiles ORDER BY port","color");
		foreach($c as $k=>$v) {
			$r[$k]=$v."<span class=\"color\" style=\"background-color:$k\">&nbsp;</span>";
		}
	}
	return $r;
}

function bandlecolor($a) {
	if(!is_array($a)) return array();
	foreach($a as $k=>$v) {
		$c=preg_split('//',$v);
		if($c[1]=='#'&&count($c)==6){
			$a[$k]='rgba('.hexdec($c[2].$c[2]).','.hexdec($c[3].$c[3]).','.hexdec($c[4].$c[4]).',0.6)';
		}elseif($c[1]=='#'&&count($c)==9){
			$a[$k]='rgba('.hexdec($c[2].$c[3]).','.hexdec($c[4].$c[5]).','.hexdec($c[6].$c[7]).',0.6)';
		}
	}
	return $a;
}

function before_save_port($c,$o) {
	global $config, $modified_ports, $NAGIOS_ERROR, $N3MODIFY;
	$q = new sql_query($config['db']);
	$r = array_merge($o,$c);
	$modified_ports=false;
	$d = $q->select("SELECT * FROM devices WHERE id='{$o['device']}'",1);
	// изменение портов при изменении цвета связки жил в кабеле
	if($d && key_exists('bandle',$c) && $d['type']=='cable' && $d['bandleports']>0 && $d['numports']>$d['bandleports']) {
		$begin = $o['number']-floor(($o['number']-1)%$d['bandleports']);
		$end = $o['number']-floor(($o['number']-1)%$d['bandleports'])+$d['bandleports'];
		$q->query("UPDATE devports SET bandle='{$c['bandle']}' WHERE number>={$begin} AND number<{$end} AND device='{$d['id']}' AND id!={$o['id']}");
		$modified_ports=$q->fetch_all("SELECT id FROM devports WHERE number>={$begin} AND number<{$end} AND device='{$d['id']}' AND id!={$o['id']}");
	}
	if(key_exists('link',$c)) { // разъединение портов
		if(!$c['link']) {
			if($c['link'] !== null) $c['link'] = null;
			if($o['link']>0){
				if(ICINGA_URL){
					$mon = new Icinga2();
					$mon->deleteServices($o['link']);
				}elseif(CONFIGURE_NAGIOS==1){
					if(!n3removeservice($o['link'],'yes')) log_txt(__FUNCTION__.": remove service NAGIOS ERROR: ".$NAGIOS_ERROR['desc']);
				}
				$q->query("UPDATE devports SET link=null WHERE id={$o['link']}");
			}
		}
		if($d['type']=='wifi') {
			if(($obj = $q->get('map',$d['node1'])) && $obj['type']=='client') {
				$port = $q->get('devports',$c['link']);
				$dp = $q->get('devices',$port['device']);
				if($q->query("UPDATE map SET connect='{$port['node']}' WHERE id='{$d['node1']}'")){
					if(is_array($N3MODIFY)) $N3MODIFY[] =$dp['node1'];
					else $N3MODIFY = array($d['node1']);
				}
			}
		}		
	}
	if(ICINGA_URL && key_exists('monitoring',$c)){
		$mon = new Icinga2();
		if($c['monitoring']){
			$node = $q->get("map",$d['node1']);
			if($r['link']){
				if(!$r['name']){
					$cl = cutClients($r['id']); $all = count($cl);
					if($all==1) $mon->createServices($cl);
					else $mon->createPortService($r);
				}else{
					if(preg_match('/pon/i',$r['name'])) $mon->createPortService($r);
					elseif(!$mon->createServices($r['id'])) $mon->createPortService($r);
				}
			}else $mon->createService($r);
		}else{
			if($r['link']){
				if(!$r['name']){
					$cl = cutClients($r['id']); $all = count($cl);
					if($all==1) $mon->deleteServices($cl);
				}else{
					if(preg_match('/pon/i',$r['name'])) $mon->deletePortService($r);
					elseif(!$mon->deleteServices($r['id'])) $mon->deletePortService($r);
				}
			}else $mon->deletePortService($r);
		}
	}
	return $c;
}

function out_save_link_result($id,$saved,$my){
	global $config, $modified_ports, $NAGIOS_ERROR, $N3MODIFY;
	$fld=array('devid'=>'id', 'devtype'=>'type', 'subtype'=>'subtype', 'devname'=>'name', 'numports'=>'numports', 'ip'=>'ip', 'node1'=>'node1', 'node2'=>'node2', 'a1'=>'a1', 'a2'=>'a2');
	$old = $my->row;
	$q = new sql_query($config['db']);
	$r = $q->get("devports",$id);
	$d = $q->get("devices",$r['device']);
	if(@$d['type']=='cable') { // синхронизируем парный порт по цвету, меткам, связке
		$q->query("
			UPDATE devports 
			SET color='{$r['color']}',coloropt='{$r['coloropt']}',bandle='{$r['bandle']}' 
			WHERE device={$r['device']} AND number={$r['number']} AND id!='{$id}'"
		);
	}
	$portcolor=$q->fetch_all("SELECT distinct color, htmlcolor FROM devprofiles ORDER BY color",'color');
	$bcolor=bandlecolor($portcolor);
	if(!$portcolor) $portcolor=array();
	$sql = "
		SELECT 
			p1.id, p1.device, p1.number, p1.node, p1.porttype, p1.link, p1.color, p1.coloropt, p1.bandle, p1.note,
			p2.number as linkport, p2.color as linkcolor, p2.coloropt as linkcoloropt, p2.bandle as linkbandle, 
			d.id as devid, d.type as devtype, d.subtype, d.name as devname, d.numports, d.ip, d.node1, d.node2,
			n.id as nodeid, a1.address as a1, a2.address as a2
		FROM devports p1 
			LEFT OUTER JOIN devports p2 ON p1.link=p2.id 
			LEFT OUTER JOIN devices d ON p2.device=d.id 
			LEFT OUTER JOIN map n ON p2.node=n.id 
			LEFT OUTER JOIN map a1 ON d.node1=a1.id 
			LEFT OUTER JOIN map a2 ON d.node2=a2.id 
		WHERE p1.id in (:ID:)
	";
	if(is_array($modified_ports)) $modified_ports[]=$r['id']; else $modified_ports=array($id);
	$res = $q->select(preg_replace('/:ID:/',implode(',',$modified_ports),$sql));
	foreach($res as $i=>$p) {
		if($p['linkport']){
			foreach(array_intersect_key($p,$fld) as $k=>$v) $ld[$fld[$k]] = $v;
			$res[$i]['devname'] = get_devname($ld,$r['node']);
		}
		foreach($p as $k=>$v) {
			if(($k=='color'||$k=='linkcolor'||$k=='linkbandle')&&key_exists($v,$portcolor)) $res[$i][$k]=$portcolor[$v];
			if($k=='bandle' && key_exists($v,$bcolor)) $res[$i][$k]=$bcolor[$v];
		}
	}
	$out = array('result'=>'OK','modify'=>array('ports'=>$res));
	if($r['link']>0) { // изменяем link на соединённом порту
		$link = $q->select(preg_replace('/:ID:/',$r['link'],$sql),1);
		if($link['link']!=$r['id']){
			$peer = ($d['type']=='wifi')? $q->get("devices",$link['device']) : false;
			$newlink = ($peer && $peer['subtype']=='ap')? null : $r['id']; // изменять парную связь нельзя только на wifi ap
			$q->update_record('devports',array('id'=>$r['link'],'link'=>$newlink));
			if($r['porttype']=='wifi' && $newlink)
				$out['append']['GeoJSON'][] = getWiFiLinkFeatures($r['id']);
		}
	}elseif(key_exists('link',$saved) && $r['porttype']=='wifi') {
		$out['delete']['wifilinks'][] = ($old['id'] < $old['link'])? $old['id'] : $old['link'];
	}
	if(isset($saved['link']) && $saved['link']>0){ // соединение портов(сварка жил для объекта client)
		$cd = $config['map']['clientdevtypes'];
		$dev = ($d['type']!='cable')? $q->get("devices",$link['device']) : $d;
		if($dev['type']=='cable'){
			// находим клиента на карте по id узлов кабеля
			$client = $q->select("SELECT * FROM map WHERE type='client' AND (id='{$dev['node1']}' OR id='{$dev['node2']}') LIMIT 1",1);
			// если нету, находим клиента по кабелю подсоединённому к текущему порту 
			if(!$client) $client = $q->select("SELECT m.* FROM map m, devices d, devports p WHERE p.id='{$saved['link']}' AND p.device=d.id AND m.type='client' AND (m.id=d.node1 OR m.id=d.node2) LIMIT 1",1);
			if($client && $client['subtype']!='wifi' && $r['node']!=$client['id']){ // если кабель на клиента - то соединяем его с клиентским устройством
				// находим устр-во клиента
				$cldev = $q->select("SELECT * FROM devices WHERE type='{$cd[$client['subtype']]}' AND node1={$client['id']} LIMIT 1",1);
				if($cldev){
					// находим кабель клиента
					$clcab = $q->select("SELECT * FROM devices WHERE type='cable' AND (node1='{$client['id']}' OR node2='{$client['id']}') LIMIT 1",1);
					if(!$clcab) log_txt(__FUNCTION__.": не найден кабель клиента");
					// находим номер жилы в кабеле клиента на узле к которому клиент подсоединён
					$number = $q->select("SELECT number FROM devports WHERE device='{$clcab['id']}' AND node!='{$client['id']}' AND (id='{$r['id']}' OR id='{$r['link']}')",4);
					if(!$number) log_txt(__FUNCTION__.": не найден номер жилы в кабеле клиента");
					// берём жилу кабеля на стороне клиента
					$cbport = $q->select("SELECT * FROM devports WHERE device='{$clcab['id']}' AND node='{$client['id']}' AND number='$number'",1);
					if(!$cbport) log_txt(__FUNCTION__.": не найдена жила кабеля на стороне клиента");
					// берём порт устройства клиента
					$clport = $q->select("SELECT * FROM devports WHERE device='{$cldev['id']}' AND porttype='fiber'",1);
					if(!$clport) log_txt(__FUNCTION__.": не найден порт устройства клиента");
					if($clport && $cbport){
						if($clport['link']!=$cbport['id'] || $clport['id']!=$cbport['link']){ // если связь не совпадает
							// разрываем связи на жиле кабеля со стороны клиента
							$q->query("UPDATE devports d1, devports d2 SET d1.link=NULL, d2.link=NULL WHERE d1.link=d2.id AND d1.node=d2.node AND d1.id={$clport['id']}");
							// связываем жилу кабеля с портом устр-ва клиента
							$q->query("UPDATE devports d1, devports d2 SET d1.link=d2.id, d2.link=d1.id WHERE d1.id='{$cbport['id']}' AND d2.id='{$clport['id']}'");
							if($q->modified()>0) log_txt(__FUNCTION__.": пересоединили порт на устройстве клиента!");
							if($q->modified()!=2) log_txt(__FUNCTION__.": ERROR Подключено ".$q->modified()." портов!");
						}else log_txt(__FUNCTION__.": соединение с клиентским устройством уже есть!");
					}else log_txt(__FUNCTION__.": не найден порт клиента или жила кабеля!");
				}else log_txt(__FUNCTION__.": не найдено устройство клиента");
			} // клиент не найден
		}
		if(ICINGA_URL){
			$mon = new Icinga2();
			log_txt(__FUNCTION__.": Icinga2 createServices!!!");
			$mon->createServices($id);
		}elseif(CONFIGURE_NAGIOS==1){
			if(!n3createservice($id,'yes')) log_txt(__FUNCTION__.": create service NAGIOS ERROR: ".$NAGIOS_ERROR['desc']);
		}
	}
	if(isset($N3MODIFY)){
		$f = getFeatureCollection($N3MODIFY);
		$out['modify']['GeoJSON']=$f['features'];
	}
	return $out;
}

function get_modified_ports($req) {
	global $config, $modified_ports;
	$selectednode = (isset($_REQUEST['selectednode']))? numeric($_REQUEST['selectednode']) : 0;
	$q = new sql_query($config['db']);
	$fld=array('devid'=>'id', 'devtype'=>'type', 'subtype'=>'subtype', 'devname'=>'name', 'numports'=>'numports', 'ip'=>'ip', 'node1'=>'node1', 'node2'=>'node2', 'a1'=>'a1', 'a2'=>'a2');
	if(is_numeric($req) && $req>0) $filter="p1.device='$req'";
	elseif(is_array($req) && count($req)>0 && $req[0]>0) $filter="p1.id in (".implode(',',$req).")";
	else return false;
	if(isset($_REQUEST['selectednode']) && is_numeric($_REQUEST['selectednode']) && $_REQUEST['selectednode']>0)
		$filter .= " AND p1.node={$_REQUEST['selectednode']}";
	$portcolor=$q->fetch_all("SELECT distinct color, htmlcolor FROM devprofiles ORDER BY color",'color');
	if(!$portcolor) $portcolor=array();
	$bcolor = bandlecolor($portcolor);
	$out=array();
	$first_pass = true;
	$res = $q->query("
		SELECT 
			p1.id, p1.device, p1.number, p1.node, p1.porttype, p1.color, p1.coloropt, p1.bandle, p1.note,
			p2.number as linkport, p2.color as linkcolor, p2.coloropt as linkcoloropt, p2.bandle as linkbandle,
			d.id as devid, d.type as devtype, d.subtype, d.name as devname, d.numports, d.ip, d.node1, d.node2,
			n.id as nodeid, a1.address as a1, a2.address as a2
		FROM devports p1 
			LEFT OUTER JOIN devports p2 ON p1.link=p2.id 
			LEFT OUTER JOIN devices d ON p2.device=d.id 
			LEFT OUTER JOIN map n ON p2.node=n.id
			LEFT OUTER JOIN map a1 ON d.node1=a1.id 
			LEFT OUTER JOIN map a2 ON d.node2=a2.id 
		WHERE $filter AND p1.modified>date_add(now(),interval -5 second)
		ORDER BY p1.number, p1.node, p1.porttype
	");
	foreach($res as $i=>$p){
		if($p['linkport']){
			foreach(array_intersect_key($p,$fld) as $k=>$v) $d[$fld[$k]] = $v;
			$p['devname'] = get_devname($d,$selectednode);
		}
		foreach(array('a1','a2','node1','node2') as $n) unset($p[$n]);
		foreach($p as $k=>$v){
			if(($k=='color'||$k=='linkcolor'||$k=='linkbandle') && key_exists($v,$portcolor)) $p[$k]=$portcolor[$v];
			if($k=='bandle' && key_exists($v,$bcolor)) $p[$k]=$bcolor[$v];
		}
		$out[]=$p;
	}
 	return $out;
}

function getPortVlans($port) {
	global $config, $q;
	if(!($q instanceof sql_query)) $q = new sql_query($config['db']);
	return $q->fetch_all("SELECT id, vlan FROM vlans WHERE port='{$port['id']}' ORDER BY vlan");
}

?>
