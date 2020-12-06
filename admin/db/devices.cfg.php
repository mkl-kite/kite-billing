<?php
include_once("classes.php");
include_once("geodata.php");
include_once("ports.cfg.php");
$newcable=false;
$modcable=false;
$movedevice=false;
$append_ports=false;

$dev_fields_filter=array(
	'cable'=>array('numports','colorscheme','n2address','bandleports','subtype','node2'),
	'switch'=>array('name','numports','ip','community','login','password','bandleports','macaddress'),
	'onu'=>array('name','numports','macaddress'),
	'mconverter'=>array('name','numports'),
	'server'=>array('name','numports','ip','community','login','password','bandleports'),
	'patchpanel'=>array('name','numports'),
	'divisor'=>array('colorscheme','subtype'),
	'splitter'=>array('colorscheme','subtype','bandleports'),
	'wifi'=>array('name','subtype','ip','community','ssid','psk','login','password','macaddress'),
	'ups'=>array('name','ip','community'),
);

$dev_fields_label = array(
	'client'=>array('name','логин'),
	'server'=>array('bandleports','порт uplink'),
	'switch'=>array('bandleports','порт uplink'),
	'cable'=>array('numports','кол-во жил'),
);

$tables['devices']=array(
	'title'=>'Устройство',
	'target'=>"form",
	'module'=>"devices",
	'key'=>'id',
	'query'=>"
		SELECT 
			d.id,
			d.object,
			d.type,
			d.subtype,
			d.ip,
			d.community,
			d.ssid,
			d.psk,
			d.login,
			d.password,
			d.name,
			d.colorscheme,
			d.node1,
			d.node2,
			d.macaddress,
			m1.address as n1address,
			m2.address as n2address,
			d.numports,
			d.bandleports,
			d.note
		FROM
			devices d LEFT OUTER JOIN map m1 ON d.node1=m1.id
			LEFT OUTER JOIN map m2 ON d.node2=m2.id
		",
	'class'=>'normal',
	'delete'=>'no',
	'defaults'=>array(
		'numports'=>1,
		'sort'=>'type'
	),
// 	'footer'=>array(),
	'checks'=>array(
		'save'=>'check_device_for_save'
	),
	'table_triggers'=>array(
	),
	'fields_filter'=>'device_by_type',
	'before_edit'=>'before_edit_device',
	'before_save'=>'before_save_device',
	'form_onsave'=>'onsave_device',
	'allow_delete'=>'allow_delete_device',
	'form_delete'=>'delete_device',
	'sort'=>'',
	'group'=>'',
	'form_triggers'=>array(
		'type'=>'device_type'
	),

	// поля
	'fields'=>array(
		'id'=>array(
			'label'=>'id',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'devid'=>array(
			'label'=>'id',
			'type'=>'text',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>3,'g'=>'map')
		),
		'object'=>array(
			'label'=>'object',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'type'=>array(
			'label'=>'тип',
			'type'=>'select',
			'list'=>'list_of_device_type',
			'onselect'=>'reloadsubtype',
			'onchange'=>'',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'subtype'=>array(
			'label'=>'подтип',
			'type'=>'select',
			'list'=>'list_of_subtype',
			'onchange'=>"
				var v=$(this).val(),
					f=$(this).parents('form'),
 					t=f.find('[name=type]').get(0),
 					type=(t.tagName=='SELECT')? $(t).val() : t.oldvalue, 
 					b=f.find('[name=bandleports]').get(0);
				if(type=='splitter'){
					var d = v.split('x');
					b.prevvalue = $(b).val()
					if(d[1]<8||d.length<2) $(b).val(24).parents('.form-item').hide();
					else $(b).parents('.form-item').show();
					if(d[1]<=24 && d[1]>=8) $(b).val(d[1]/2);
					else if(d[1]>24) $(b).val(8);
				}else if(b && b.prevvalue) delete(b.prevvalue)
			",
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'name'=>array(
			'label'=>'название',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'numports'=>array(
			'label'=>'кол-во портов',
			'style'=>'width:40px;text-align:right',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map'),
			'onchange'=>"
				var v=$(this).val(),
					f=$(this).parents('form'),
 					t=f.find('[name=type]').get(0),
 					type=(t.tagName=='SELECT')? $(t).val() : t.oldvalue,
					p=f.find('#field-bandleports');
				if(type=='cable' && v>=12 || type=='switch' || type=='server') p.show(); else p.hide();
			"
		),
		'ip'=>array(
			'label'=>'ip адрес',
			'style'=>'width:120px',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'community'=>array(
			'label'=>'community',
			'style'=>'width:120px',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'ssid'=>array(
			'label'=>'SSID',
			'style'=>'width:120px',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'psk'=>array(
			'label'=>'PSK',
			'style'=>'width:120px',
			'type'=>'password',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'login'=>array(
			'label'=>'логин',
			'type'=>'text',
			'class'=>'fio',
			'style'=>'width:120px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'password'=>array(
			'label'=>'пароль',
			'type'=>'password',
			'style'=>'width:120px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'colorscheme'=>array(
			'label'=>'цв.схема',
			'type'=>'select',
			'list'=>'list_of_color_shema',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'node'=>array(
			'label'=>'узел',
			'type'=>'text',
			'native'=>false,
			'access'=>array('r'=>1,'w'=>5)
		),
		'node1'=>array(
			'label'=>'узел 1',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'node2'=>array(
			'label'=>'узел 2',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'n1address'=>array(
			'label'=>'узел 1',
			'type'=>'autocomplete',
			'style'=>'width:190px',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'n2address'=>array(
			'label'=>'узел 2',
			'type'=>'autocomplete',
			'style'=>'width:190px',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'bandleports'=>array(
			'label'=>'жил в связке',
			'style'=>'width:40px;text-align:right',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'macaddress'=>array(
			'label'=>'мак адрес',
			'style'=>'width:130px',
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
	)
);
$tables['devices']['fields']['type']['onchange'] = js_type_onchange($dev_fields_filter,$dev_fields_label);

function before_edit_device($f) {
	global $DEBUG, $config, $q, $devtype, $dev_fields_filter, $dev_fields_label;
	if($DEBUG>0) log_txt(__function__.": start for {$f['name']}[{$f['id']}]");
	$f['fields']['type']['type']='hidden';
	if(isset($f['id'])){
		if(!$q) $q = new sql_query($config['db']);
		$r = $q->get('devices',$f['id']);
		// выборка отображаемых полей при перекрёстном использовании
		$all=array();
		foreach($dev_fields_filter as $k=>$v) $all=array_unique(array_merge($all,$v));
		if(@$dev_fields_filter[$r['type']]) foreach(array_diff($all,$dev_fields_filter[$r['type']]) as $fname) $un[] = $fname;
		if(isset($un)) foreach($un as $fn) unset($f['fields'][$fn]);
		if($DEBUG>0) log_txt(__FUNCTION__.": убраны поля: ".arrstr($un));
		// изменяемые рус.названия полей при перекрёстном использовании
		if($a = @$dev_fields_label[$r['type']]) $f['fields'][$a[0]]['label'] = $a[1];
		$f['header'] = $devtype[$r['type']];
	}
	return $f;
}

function device_type($v) {
	global $devtype;
	return isset($devtype[$v])? $devtype[$v] : $v;
}

function device_name($v,$r,$f) {
	$fld = array('type','subtype','name','numports','node1','node2','ip','a1','a2');
	$d = array_combine($fld,preg_split('/:/',$v));
	if(isset($_REQUEST['node'])) $node = numeric($_REQUEST['node']);
	elseif(isset($_REQUEST['nodeid'])) $node = numeric($_REQUEST['nodeid']);
	elseif(isset($_REQUEST['id'])) $node = numeric($_REQUEST['id']);
	$dn = get_devname($d,$node);
	return $dn;
}

function list_of_color_shema(){
	global $config;
	$q=new sql_query($config['db']);
	$r=$q->fetch_all("select distinct name as id, name from devprofiles order by name;",'id');
	$tmp = array_merge(array(''=>'не определена'),$r);
	return $tmp;
}

function list_of_device_type(){
	global $devtype;
	$r = $devtype; unset($r['unknown']); unset($r['client']);
	return $r;
}

function node1_address($r){
	global $config;
	$q=new sql_query($config['db']);
	$tmp = $q->get("map",$r['node1'],'address');
	if(is_string($tmp)) return $tmp; else return "";
}

function node2_address($r){
	global $config;
	$q=new sql_query($config['db']);
	$tmp = $q->get("map",$r['node2'],'address');
	if(is_string($tmp)) return $tmp; else return "";
}

function list_of_nodes($r){
	global $config;
	$q=new sql_query($config['db']);
	foreach(array('node1','node2') as $n) if($r[$n]>0) $nodes[]=$r[$n];
	$r=$q->fetch_all("select id, address from map where type='node' OR id in (".implode(",",$nodes).") order by address;");
 	$tmp[0] = '';
 	foreach($r as $k=>$v) $tmp[$k]=$v;
	return $tmp;
}

function device_by_type($r) {
	global $dev_fields_filter, $tables;
	$res=array();
	$all=array();
	foreach($dev_fields_filter as $k=>$v) $all=array_unique(array_merge($all,$v));
	if(key_exists(@$r['type'],$dev_fields_filter))
		$res=array_flip(array_diff($all,$dev_fields_filter[$r['type']]));
	else 
		$res=array_flip($all);
	return $res;
}

function before_save_device($c,$o,$my) {
	global $DEBUG, $config, $newcable, $modcable, $movedevice, $modwifi, $dev_fields_filter;
	$q = new sql_query($config['db']);
	$r=array_merge($o,$c);

	if(isset($c['object'])) {
		if($r['type']=='cable') {
			if($r['object']>0) {
				if($m=$q->select("SELECT * FROM map WHERE id={$r['object']}",1)){
					if($m['type']!='cable') stop(array('result'=>'ERROR','desc'=>'Объект в таблице map имеет другой тип'));
				}
			}elseif($r['id']=='new' && $r['node1']>0 && $r['node2']>0){ // если объекта на карте нет, то кабель создаётся по нач. кон. узлам
				if($r['object']=$q->insert('map',array('name'=>$r['name'],'type'=>$r['type'],'gtype'=>'LineString'))){
					$c['object'] = $r['object'];
					if($DEBUG>0) log_txt(__function__.": добавлено map[{$c['object']}]: {$r['type']}");
					$a = $q->select("
						SELECT {$r['object']} as object, 0 as slice, 0 as num, x, y 
						FROM map_xy 
						WHERE object={$r['node1']} or object={$r['node2']}
					");
					foreach($a as $k=>$v) $a[$k]['num'] = $k;
					if(count($a)<2) stop(array('result'=>'ERROR','desc'=>'Один из узлов не имеет координат!'));
					$q->insert('map_xy',$a);
					$newcable=true;
				}else{
					stop(array('result'=>'ERROR','desc'=>'Не удалось добавить объект!'));
				}
			}
		}
	}

	if(isset($c['node1']) || isset($c['node2'])) { // изменение адреса начального или конечного узла
		foreach(array('node1','node2') as $n) if(@$o[$n]>0) $nodes[] = $o[$n];
		if(count($nodes)>0 && ($lnk = $q->select("SELECT * FROM devports WHERE device='{$r['id']}' AND node in (".implode(",",$nodes).") AND link>0"))){
			log_txt(__FUNCTION__.": SQL: ".$q->sql);
			stop(array('result'=>"ERROR",'desc'=>"На этом Устройстве есть неразорванные соединения!"));
		}
		if($r['type']=='cable' && @$r['object']>0){
			if($r['id']!='new' && ((@$r['node1']>0 && $o['node1']!=$r['node1']) || (@$r['node2']>0 && $o['node2']!=$r['node2']))) {
				$a=$q->select("
					SELECT {$r['object']} as object, 0 as slice, 0 as num, x, y 
					FROM map_xy 
					WHERE object={$r['node1']} or object={$r['node2']}
				");
				foreach($a as $k=>$v) $a[$k]['num']=$k;
				if(count($a)<2) stop(array('result'=>'ERROR','desc'=>'Один из узлов не имеет координат!'));
				$q->query("DELETE FROM map_xy WHERE object='{$r['object']}'");
				$q->insert('map_xy',$a);
				$modcable=true;
			}
		}elseif(isset($c['node1']) && $r['type']!='cable'){
			if($DEBUG>0) log_txt(__function__.": разорваны соединения для {$r['type']}[{$r['id']}]");
			$movedevice = $r['id'];
			if($r['type']=='wifi'){
				$cl_wifi = $q->fetch_all("SELECT distinct p2.node FROM devports p1, devports p2 WHERE p1.device='{$o['id']}' AND p1.porttype='wifi'  AND p1.porttype='wifi' AND p2.link=p1.id AND p2.porttype='wifi'");
				if($cl_wifi) {
					$q->query("UPDATE map SET connect='{$c['node1']}' WHERE id in (".implode(',',$cl_wifi).")");
					$modwifi = $cl_wifi;
				}
			}
		}
		if(isset($c['node1']) && $c['node1']==0) $c['node1']=null;
		if(isset($c['node2']) && $c['node2']==0) $c['node2']=null;
	}

	if(isset($c['colorscheme']) || isset($c['bandleports'])) {
		if(!isset($dev_fields_filter[$r['type']]) || array_search('colorscheme',$dev_fields_filter[$r['type']])===false) { 
			if($o['colorscheme'] != '') $c['colorscheme']=''; 
			if(array_search('bandleports',$dev_fields_filter[$r['type']])===false) $c['bandleports']=0; 
		}elseif($r['colorscheme']!='') {
			if($r['bandleports']==0 || $r['bandleports']=='') $r['bandleports']=$c['bandleports']=24;
			$bandleports = ($r['type']=='splitter' && $r['numports']==9)? 4 : $r['bandleports']; // для сплиттеров Lancore
			update_colorscheme($r['id'],$r['colorscheme'],$bandleports);
			if($DEBUG>0) log_txt(__function__.": замена цвет.схемы для {$r['type']}[{$r['id']}] на {$r['colorscheme']}");
		}else{ 
			$q->query("UPDATE devports SET color='', coloropt='solid', bandle='' WHERE device='{$r['id']}'");
		}
	}

	if(key_exists('node2',$r)) {
		if($r['type']!='cable') $c['node2']=null;
		if($DEBUG>0) log_txt(__function__.": очистка node2 для {$r['type']}[{$r['id']}]");
	}

	if($r['type']=='wifi' && $r['numports']<2) $c['numports']=2;
	if($r['type']=='onu' && $r['numports']!=2) $c['numports']=2;
	if($r['type']=='ups' && $r['numports']!=1) $c['numports']=1;
	if($r['type']=='divisor' && $r['numports']!=3) $c['numports']=3;

	if($r['type']=='splitter' && $r['subtype']!='') {
		$c['numports'] = preg_replace('/1x/','',$r['subtype'])+1;
	}

	if(isset($c['macaddress']) && $r['type']=='onu' && $o['macaddress']!='') {
		if(ICINGA_URL){
			$port = $q->select("SELECT id FROM devports WHERE device='{$r['id']}' AND porttype='fiber' and link is not NULL",4);
			if($port){
				$mon = new Icinga2();
				$mon->deleteServices($port['id']);
			}
		}elseif(CONFIGURE_NAGIOS>0){
			$port = $q->select("SELECT id FROM devports WHERE device='{$r['id']}' AND porttype='fiber' and link is not NULL",4);
			if($port && !($h = n3removeservice($port,'yes'))){
				if(isset($NAGIOS_ERROR)) log_txt(arrstr($NAGIOS_ERROR));
			}
		}
	}
	return $c;
}

function update_colorscheme($dev_id,$colorscheme,$bandle=24) {
	global $config;
	$q=new sql_query($config['db']);
	if($bandle=='') $bandle=24;
	$q->query("
		UPDATE 
			devports p 
			LEFT JOIN devices d ON p.device=d.id 
			LEFT JOIN map o ON d.object=o.id 
			LEFT OUTER JOIN devprofiles as dp ON dp.name='{$colorscheme}' AND dp.port=mod(p.number-1,{$bandle})+1
			LEFT OUTER JOIN devprofiles as dps ON dps.name='{$colorscheme}' AND dps.port=mod(p.number-2,{$bandle})+1
			LEFT OUTER JOIN devprofiles as dp1 ON dp1.name='{$colorscheme}' AND dp1.port=floor((p.number-1)/{$bandle})+1
		SET 
			p.color=if(d.type!='splitter',dp.color,if(p.number=1,'white',dps.color)), 
			p.bandle=if(d.numports<={$bandle} OR d.type!='cable','',dp1.color), 
			coloropt=if(d.type!='splitter',dp.option,if(p.number=1,'solid',dps.option))
		WHERE
			p.device='{$dev_id}'
	");
}

function check_device_for_save($r){
	global $config;
	if($r['numports']<1 || $r['numports']>192){
		stop(array('result'=>'ERROR','desc'=>'Кол-во портов должно быть от 1 до 192 !'));
	}
	if($r['type']=='divisor' && $r['subtype']=='') {
		stop(array('result'=>'ERROR','desc'=>'Не указан тип делителя!'));
	}
	if($r['type']=='onu' && $r['macaddress']=='') {
		stop(array('result'=>'ERROR','desc'=>'Не указан мак адрес!'));
	}
}

function onsave_device($id,$save,$my) {
	global $DEBUG, $config, $_REQUEST, $newcable, $dev_fields_filter, $tables, $devtype, $modcable, $movedevice, $modwifi, $N3MODIFY, $NAGIOS_ERROR;
	$pass = array('divisor'=>0,'splitter'=>1,'switch'=>2,'wifi'=>3,'onu'=>4,'mconverter'=>5); // типы пасивных устройств
	$fldmon = array('name'=>0,'ip'=>1,'community'=>2,'node1'=>3); // поля, при изменеии которых включается мониторинг
	if($DEBUG>0) log_txt(__FUNCTION__.": id={$save['id']} SAVE: ".arrstr($save));
	$action=($_REQUEST['id']=='new')? 'append' : 'modify';
	$q=new sql_query($config['db']);
	$old = $my->row;

	$device = $q->select("SELECT * FROM devices WHERE id='{$save['id']}'",1);
	$ports = $q->fetch_all("SELECT * FROM devports WHERE device='{$save['id']}' ORDER BY number, porttype");

	if(!isset($pass[$device['type']])) {
		// проверка кол-ва портов каждого типа (если бы небыло в алгоритме ошибок, то впринципе не нужно)
		$portslice=array();
		$pold = array('number'=>'','porttype'=>'','node'=>'');
		if($ports) foreach($ports as $k=>$v) { // ищем задвоенные порты (3 парам. совпадает)
			if($v['number'] == $pold['number'] && $v['porttype'] == $pold['porttype'] && $v['node'] == $pold['node']){
				if(isset($pold['id'])){
					// проверяем старый линк на др. порт, и если связь обоюдная то рубим текущий, если нет - старый
					if($pold['link']>0 && $q->select("SELECT link FROM devports WHERE id='{$pold['link']}'",4) == $pold['id']){
						$delports[] = $v['id'];
					}else{
						$delports[] = $pold['id'];
					}
				}
			}else{
				@$portslice[$v['porttype'].'_'.$v['node']]++;
			}
			$pold = $v;
		}
		if(isset($delports)) { // удаляем задвоенные порты
			log_txt(__FUNCTION__.": удаление задвоенных портов ".arrstr($delports));
			$q->query("DELETE FROM devports WHERE id in (".implode(',',$delports).")");
			unset($delports);
		}
		$numports = 0;
		foreach($portslice as $k => $v) { // находим наименьшее кол-во портов по типу и узлу
			if($numports == 0) $numports = $v;
			elseif($v < $numports) $numports = $v;
		}
	}else
		$numports = count($ports);
	
	if(isset($save['colorscheme'])){
		$colorschemes = $q->fetch_all("SELECT name, max(port) as m FROM devprofiles GROUP BY name",'name');
		if(key_exists($device['colorscheme'],$colorschemes)){
			update_colorscheme($device['id'],$device['colorscheme'],$device['bandleports']);
			if($DEBUG>0) log_txt(__FUNCTION__.": update_colorscheme ".arrstr($device['colorscheme']));
		}
	}
	if($device['numports']!=$numports && $device['type'] == 'splitter') {
		$q->query("UPDATE devports SET divide=".(100/($device['numports']-1))." WHERE device={$device['id']} AND number != 1");
		if($DEBUG>0) log_txt(__FUNCTION__.": update devports[divide]: ".arrstr(100/($device['numports']-1)));
	}
	if($device['type'] == 'divisor' && key_exists('subtype',$save)) {
		$a = preg_split('/\//',$save['subtype']);
		if(@count($a)!=2) $a = array(50,50);
		$q->query("UPDATE devports SET divide=if(number=2,{$a[0]},if(number=3,{$a[1]},null)) WHERE device={$device['id']}");
		if($DEBUG>0) log_txt(__FUNCTION__.": update devports[divide]: $a[0]/$a[1]");
	}

	if($device['type']=='switch' && count($changes = array_intersect_key($save,$fldmon))>0 && ICINGA_URL){
		$changes = array_intersect_key($save,array('name'=>0,'ip'=>1,'community'=>2,'node1'=>3));
		if(count($changes)>0){
			if(isset($changes['node1'])) $device['address'] = $q->select("SELECT address FROM map WHERE id='{$changes['node1']}'",4);
			$mon = new Icinga2();
			if(!$mon->updateHost($device)){
				if($mon->code == 404 && !$mon->createHost($device))
					log_txt("Icinga2 ошибка добавления свича: ".$mon->error);
			}
			updateDbPorts($device);
		}
	}
	if($device['type']=='onu' && isset($save['macaddress']) && $save['macaddress']!='') {
		$acct = $q->select("
			SELECT * FROM radacct WHERE acctstoptime is NULL AND username=callingstationid AND 
			connectinfo_start like '%0206".(preg_replace('/[^A-F0-9]/i','',$save['macaddress']))."%'
		",1);
		if($acct){
			$r = array_merge($old,$save);
			$client = $q->select("SELECT u.* FROM users u, map m WHERE m.name=u.user AND m.id='{$r['node1']}'",1);
			if($client){
				if($client['opt82']!=''){
					$q->query("UPDATE users SET opt82='' WHERE uid='{$client['uid']}'");
					$client['opt82'] = '';
				}
				$u = new user($client);
				if($loc = $u->localization($acct['username'],$acct['connectinfo_start'])) send_coa($client,$acct);
			}
		}
		if(ICINGA_URL){
			$port = $q->select("SELECT id FROM devports WHERE device='{$device['id']}' AND porttype='fiber' and link is not NULL",4);
			if($port){
				$mon = new Icinga2();
				$mon->createServices($port['id']);
			}
		}elseif(CONFIGURE_NAGIOS>0){
			$port = $q->select("SELECT id FROM devports WHERE device='{$device['id']}' AND porttype='fiber' and link is not NULL",4);
			if($port && !($h = n3createservice($port,'yes'))){
				if(isset($NAGIOS_ERROR)) log_txt(arrstr($NAGIOS_ERROR));
			}
		}
	}

	$out['result']='OK';
	$mynode = isset($_REQUEST['selectednode'])? $_REQUEST['selectednode'] : 0;
	if($device['type']=='cable') {
		$object = $q->get("map",$device['object']);
		if($object){
			foreach($device as $k=>$v) $object['dev_'.$k]=$v;
			$f = getFeatureCollection($object['id']);
			$out['feature']=$f['features'][0];
			if($newcable) $out['append']['GeoJSON'][] = $f;
			if($modcable) $out['modify']['GeoJSON'][] = $f;
		}else{
			log_txt(__FUNCTION__.": WARNING map object for cable({$device['id']}) not found");
		}
		if($movedevice) $out['remove']['device'][] = $movedevice;
	}
	if($modwifi) foreach($modwifi as $k=>$v) {
		$out['modify']['GeoJSON'][] = getFeatureCollection($v);
	}
	$device['name'] = get_devname($device,$mynode,false);
	$out[(($id=='new')?'append':'modify')]['devices']=array($device);
	if(isset($save['colorscheme']) || isset($save['bandleports'])){
		$out['modify']['ports']=get_modified_ports($device['id']);
	}
	if(isset($append_ports)) $out['append']['ports']=get_modified_ports($append_ports);
	if(isset($deleted_ports)) $out['remove']['port']=$deleted_ports;
	if(isset($N3MODIFY)) $out['modify']['GeoJSON'][]=getFeatureCollection($N3MODIFY);
	return $out;
}

function allow_delete_device($r) {
	global $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	$out = array();
	if($r['id']){
		if(!is_array($r)) $r = $q->get("devices",$r);
		$links = ($r['type']!='wifi')? $q->select("SELECT count(*) FROM devports WHERE link is NOT NULL AND device='{$r['id']}'",4):0;
		$clwifi = ($r['type']=='wifi')? $q->select("SELECT count(*) FROM devices d, devports p1 devports p2 WHERE p1.device='{$r['id']}' AND p1.porttype='wifi' AND p2.link=p1.id",4):0;
		if($links>0) $out[] = "устройство имеет $links неразорванных связей!";
		if($clwifi>0) $out[] = "К устройству подключено $clwifi клиентов!";
		if(count($out)>0) return implode('<br>',$out);
	}else{
		return 'Указанное устройство не найдено в базе!';
	}
	return 'yes';
}

function delete_device($dev,$my) {
	global $devtype, $config, $modified;
	$out=array();
	if($dev['object']>0) {
		$out['delete']['objects'] = deleteFeatures($dev['object']);
	}elseif($dev['type']=='wifi') {
		$mc = $my->q->fetch_all("SELECT id FROM map WHERE type='client' AND subtype='wifi' AND connect='{$dev['node1']}'");
		if($mc){
			$my->q->query("UPDATE map SET connect=NULL WHERE type='client' AND id in (".implode(',',$mc).")");
			$my->q->query("UPDATE devports p1, devports p2 SET p1.link=NULL, p2.link=NULL WHERE p1.device='{$dev['id']}' AND p1.porttype='wifi' AND p1.id=p2.link AND p2.porttype='wifi'");
			if(@is_array($modified)) $modified = array_unique(array_merge($modified,$mc)); else $modified = $mc;
		}
	}
	// обрываем все связи
	clearDeviceLinks($dev,$my->q);
	// удаляем устройство
	if($my->q->query("DELETE FROM devices WHERE id='{$dev['id']}'")){
		$out['remove']['device'] = array($dev['id']);
		if($dev['type']=='switch' && $dev['community'] && $dev['ip']){
			if(ICINGA_URL){
				$mon = new Icinga2();
				if(!$mon->deleteHost($dev['id'])) log_txt("Icinga2 ошибка уделения свича ({$dev['ip']}): ".$mon->error);
			}
		}
	}

	if(!isset($out['delete']) && !isset($out['remove'])) return false;
	if(isset($modified)){
		$m = getFeatureCollection($modified);
		$out['modify']['GeoJSON'] = $m['features'];
	}
	$out['result'] = 'OK';
	return $out;
}

function clearDeviceLinks($dev, $q){
	if(ICINGA_URL){
		if($dev['type']=='cable' || $dev['type']=='patchpanel')
			$links = $q->select("
				SELECT p1.* FROM devports p1, devports p2 
				WHERE p1.device=p2.device AND p1.device='{$dev['id']}' AND
					p1.number = p2.number AND p1.id<p2.id AND
					p1.link is NOT NULL AND p2.link is NOT NULL
			");
		elseif($dev['type']=='divisor' || $dev['type']=='splitter')
			$links = $q->select("SELECT * FROM devports WHERE link is NOT NULL AND number=1 AND device='{$dev['id']}'");
		else
			$links = $q->select("SELECT * FROM devports WHERE link is NOT NULL AND device='{$dev['id']}'");
		if($links){
			$mon = new Icinga2();
			foreach($links as $k=>$port) $mon->deleteServices($port['id']);
		}
	}
	$q->query("UPDATE devports d1, devports d2 SET d1.link=null, d2.link=null WHERE d1.link=d2.id AND d1.device='{$dev['id']}'");
}

function delete_devices($ids) {
	global $devtype, $config, $modified;
	$q = new sql_query($config['db']);
	if(!is_array($ids)) $ids = preg_split('/,/',preg_replace('/[^0-9,]/','',$ids));
	$all = $q->select("SELECT * FROM devices WHERE id in (".implode(',',$ids).")");
	if(!$all) return array('result'=>'ERROR','desc'=>'Объекты не найдены в базе!');

	$d = array(); $o = array(); $w = array(); $sw = array();
	foreach($all as $k=>$v) {
		if($v['object']>0) $o[] = $v['object']; else $d[] = $v['id'];
		if($v['type']=='wifi' && $v['subtype']=='ap') $w[] = $v['node1'];
		if($v['type']=='switch' && $v['community'] && $v['ip']) $sw[$v['id']] = $v;
	}
	// удаляем, если есть, кабеля
	if(count($o)>0) $out['delete']['objects'] = deleteFeatures($o);
	// снимаем привязку wifi клиентов
	if(count($w)>0){
		$mc = $q->fetch_all("SELECT id FROM map WHERE type='client' AND subtype='wifi' AND connect in (".implode(',',array_unique($w)).")");
		if($mc){
			$q->query("UPDATE map SET connect=NULL WHERE id in (".implode(",",$mc).")");
			$q->query("UPDATE devports p1, devports p2 SET p1.link=NULL, p2.link=NULL WHERE p1.porttype='wifi' AND p1.link=p2.id AND p1.node in (".implode(",",$mc).")");
			if(@is_array($modified)) $modified = array_unique(array_merge($modified,$mc)); else $modified = $mc;
		}
	}
	if(count($d)>0){
		$dd = implode(',',$d);
		// выбираем все устройства
		foreach($q->select("SELECT d.id, d.type, d.name, d.numports, m.address FROM devices d, map m WHERE d.node1=m.id AND d.id in ($dd)") as $k=>$v)
			$logdev[] = "{$v['address']} {$devtype[$v['type']]}[{$v['id']}] {$v['numports']}п {$v['name']}";
		// обрываем все связи
		foreach($d as $n=>$dev){
			clearDeviceLinks($dev,$q);
		}
		// удаляем все устройства
		if($q->query("DELETE FROM devices WHERE id in ($dd)"))
			log_db('удалил устройства',implode(', ',$logdev));
		$out['remove'] = array('device'=>$d);
	}
	if(count($sw)>0){
		if(ICINGA_URL){
			$mon = new Icinga2();
			foreach($sw as $k=>$dev) if(!$mon->deleteHost($dev)) log_txt("Icinga2 ошибка уделения свича ({$dev['ip']}): ".$mon->error);
		}
	}
	if(!isset($out['delete']) && !isset($out['remove'])) return array('result'=>'ERROR','desc'=>'Объекты не удалены!');
	if(isset($modified)){
		$m = getFeatureCollection($modified);
		$out['modify']['GeoJSON'] = $m['features'];
	}
	$out['result'] = 'OK';
	return $out;
}


function list_of_subtype($r) {
	global $config;
	$a = array(''=>'');
	if($r['type']=='divisor') {
		foreach($config['fading']['data'] as $k=>$v) if($v['div']) $a[$v['div']] = $v['div'];
	}elseif($r['type']=='splitter') {
		foreach($config['fading']['data'] as $k=>$v) $a[$k] = $k;
	}elseif($r['type']=='cable'){
		foreach($config['map']['cabletypes'] as $k=>$v) $a[$k] = $v;
	}elseif($r['type']=='wifi'){
		foreach($config['map']['typewifi'] as $k=>$v) $a[$k] = $v;
	}
	return $a;
}

function cut_cable($id,$Point,$node=array()){ // разрезает кабель в указанной точке, создает в ней узел, соединяет жилы частей бывшего кабеля
	global $config, $DEBUG, $DEVICE_ERROR;
	if(!is_numeric($id) || $id<=0 || !is_array($Point) || !isset($Point['x']) || !isset($Point['y'])){
		$DEVICE_ERROR = "ошибка входных данных!";
		log_txt(__FUNCTION__.": $DEVICE_ERROR: id:$id Point:".arrstr($Point)." node:".arrstr($node));
		return false;
	}
	$q = new sql_query($config['db']);

	$cable = $q->get('devices',$id);
	if(!$cable || $cable['type']!='cable'){
		$DEVICE_ERROR = "ошибка входных данных!";
		log_txt(__FUNCTION__.": $DEVICE_ERROR cable:".arrstr($cable));
		return false;
	}
	$node1 = $q->get('map',$cable['node1']);
	$node2 = $q->get('map',$cable['node2']);
	$xy = $q->select("SELECT num, x, y FROM map_xy WHERE object={$cable['object']} ORDER BY num",2,'num');
	$c0_length = lineLength($xy);
	if(count($xy)<2) { $DEVICE_ERROR = ""; return false; }
	// ищем сегмент кабеля где резать
	foreach($xy as $n=>$p) {
		if($n==0) continue;
		if($n==1){
			$seg = 1;
			$l = abs(Distance($xy[1],$Point) + Distance($xy[0],$Point) - Distance($xy[0],$xy[1]));
		}
		if($n>1){
			$ln = abs(Distance($xy[$n],$Point) + Distance($xy[$n-1],$Point) - Distance($xy[$n-1],$xy[$n]));
			if($ln<$l){ $seg = $n; $l = $ln; }
		}
	}
	if(!isset($seg) || $l>5) { $DEVICE_ERROR = "координаты точки не подходят!"; return false; }

	// создаём узел
	$node = array_merge(array('type'=>'node','gtype'=>'Point','name'=>'node_cut'.$cable['id'],'note'=>"разрез кабеля ".@$node1['address']." - ".@$node2['address']),$node);
	$node['id'] = $q->insert('map',$node); // создаём узел в точке разреза
	$node_xy = $q->insert('map_xy',array('object'=>$node['id'],'x'=>$Point['x'],'y'=>$Point['y'])); // добавляем координаты узла
	// делаем координаты 1 кусока от начала для определения длины
	for($i=0;$i<$seg;$i++) $c1_xy[$i] = $xy[$i];
	$c1_xy[$i] = $Point;
	$c1_xy = makePoints(0,$cable['object'],$c1_xy);
	$c1_length = lineLength($c1_xy);
	// создаём объект кабеля 2
	$map_c2 = array('type'=>'cable','name'=>'cable_'.$cable['id']."_2",'gtype'=>'LineString','length'=>$c0_length-$c1_length);
	$map_c2['id'] = $q->insert('map',$map_c2);
	// создаём устройство кабель 2
	$c_name = preg_match('/^(.*)_(\d+)$/',$cable['name'],$m)? $m[1] : $cable['name'];
	$new_n = $q->select("SELECT max(replace(name,'{$c_name}_',''))+1 as n FROM devices WHERE type='cable' AND name rlike '{$c_name}_[0-9]*'",4);
	$new_name = ($new_n)? $c_name."_".$new_n : $cable['name']."_1";
	$cable2 = $q->get('devices',$map_c2['id'],'object');
	$cable2 =array_merge($cable2,array('subtype'=>$cable['subtype'], 'name'=>$new_name, 'colorscheme'=>$cable['colorscheme'], 'node1'=>$node['id'], 'node2'=>$cable['node2'], 'numports'=>$cable['numports'], 'bandleports'=>$cable['bandleports']));
	$q->update_record('devices',$cable2);
	// меняем узел на конечных портах кабеля 1
	$q->update_record('devices',array('id'=>$cable['id'],'node2'=>$node['id']));

	// берем цвет жилы, метки и цвет связки у первого кабеля для второго
	$ports = $q->select("SELECT p1.* FROM devports p1, devports p2 WHERE p1.device=p2.device AND p1.number=p2.number AND p1.id<p2.id AND p1.device={$cable['id']}",2,'id');
	for($ports as $k=$p) $q->query("UPDATE devports SET color='{$p['color']}', coloropt='{$p['coloropt']}', bandle='{$p['bandle']}'  WHERE device='{$cable2['id']}' AND number='{$p['number']}'");

	// соединяем порты в точке разреза
	$q->query("UPDATE devports p1, devports p2 SET p1.link=p2.id, p2.link=p1.id WHERE p1.number=p2.number AND p1.device={$cable['id']} AND p2.device={$cable2['id']} AND p1.node=p2.node AND p1.node={$node['id']}");
	// изменяем длину и конечный узел начального кабеля
	$q->update_record('map',array('id'=>$cable['object'],'length'=>$c1_length));
	// разделяем координаты на 2 кабеля
	$q->query("UPDATE `map_xy` SET `object`={$cable2['object']}, `num`=`num`-$seg+1 WHERE `object`={$cable['object']} AND num >= $seg ");
	$q->insert('map_xy',array('object'=>$cable2['object'],'slice'=>0,'num'=>0,'x'=>$Point['x'],'y'=>$Point['y']));
	$q->insert('map_xy',array('object'=>$cable['object'],'slice'=>0,'num'=>$seg,'x'=>$Point['x'],'y'=>$Point['y']));

	return array('node'=>$node['id'],'cable1'=>$cable['object'],'cable2'=>$cable2['object']);
}
?>
