<?php
include_once("classes.php");
include_once("geodata.php");
include_once("homes.cfg.php");
include_once("entrances.cfg.php");

$newclientcable=false;
$modclientcable=false;

$map_client_fields = array('id','type','gtype','subtype','name','address','connect','hostname','service','note','macaddress');
$home_fields = array('id','floors','entrances','apartments','boxplace');
$map_home_fields = array('id','name','type','rayon','address','gtype','hostname','service','mrtg','note');

$epattern = array( // шаблон для домов с неравным кол-вом кв-р в подъездах
	'6x100'=>array(array(1,20),array(21,35),array(36,50),array(51,65),array(66,80),array(81,100)),
);

$tables['map']=array(
	'title'=>'Объект',
	'target'=>"form",
	'limit'=>'yes',
	'module'=>"stdform",
	'key'=>'id',
	'table_query'=>"
		SELECT 
			id,
			type,
			name,
			address,
			hostname,
			service,
			mrtg,
			note
		FROM
			map
		ORDER BY :SORT:
		",
	'form_query'=>"
		SELECT 
			id,
			type,
			subtype,
			gtype,
			name,
			rayon,
			address,
			hostname,
			service,
			mrtg,
			note
		FROM
			map
		",
	'header_home'=>"<div class=\"button\" id=\"ext\" style=\"position:relative;height:30px;width:30px;float:right;background:url(pic/next.png) no-repeat center\" title=\"Дополнительные данные\"></div>",
	'layout_home'=>array(
		'mapobject'=>array(
			'type'=>'fieldset',
			'legend'=>"строение",
			'style'=>'width:370px;height:320px;float:left;',
			'fields'=>array('type','rayon','address','hostname','service','mrtg', 'note')
		),
		'homedata'=>array(
			'type'=>'fieldset',
			'legend'=>'Данные по дому',
			'class'=>'closable',
			'style'=>'display:none;width:300px;height:145px;float:left;',
			'fields'=>array('floors','entrances','apartments','boxplace','homenote')
		),
		'entrances'=>array(
			'type'=>'fieldset',
			'legend'=>'Данные по подъездам',
			'class'=>'closable',
			'style'=>'display:none;width:300px;height:155px;float:left;',
			'fields'=>array('allentranc')
		),
	),
	'filters'=>array(
		'address'=>array(
			'type'=>'text',
			'label'=>'адрес',
			'style'=>"width:110px",
			'title'=>'фильтр по адресу',
			'value'=>''
		),
		'type'=>array(
			'type'=>'select',
			'label'=>'Тип объекта',
			'style'=>"width:110px",
			'list'=>all2array($objecttype),
			'title'=>'фильтр по типам объектов',
			'value'=>''
		),
	),
	'defaults'=>array(
		'filter'=>'build_filter_for_map',
		'sort'=>'id'
	),
	'class'=>'normal',
	'delete'=>'yes',
 	'table_footer'=>array(
		'mapid'=>'Всего:',
		'cab'=>'fcount',
 	),
	'table_triggers'=>array(
		'type'=>'type_of_object'
	),
	'form_triggers'=>array(
		'type'=>'type_of_object'
	),
	'before_edit'=>'before_edit_object',
	'before_save'=>'before_save_object',
	'form_onsave'=>'onsave_mapobject',
	'form_delete'=>'delete_mapobject',
	'group'=>'',

	// поля
	'fields'=>array(
		'id'=>array(
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3,'g'=>'map')
		),
		'mapid'=>array(
			'label'=>'id',
			'type'=>'text',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>3)
		),
		'sw'=>array(
			'label'=>'свичей',
			'type'=>'text',
			'class'=>'summ',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>3)
		),
		'pp'=>array(
			'label'=>'патчпанелей',
			'type'=>'text',
			'class'=>'summ',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>3)
		),
		'cab'=>array(
			'label'=>'кабелей',
			'type'=>'text',
			'class'=>'summ',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>3)
		),
		'gtype'=>array(
			'label'=>'тип',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'type'=>array(
			'label'=>'тип',
			'type'=>'nofield',
			'style'=>'width:120px',
			'active'=>false,
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'subtype'=>array(
			'label'=>'подтип',
			'type'=>'select',
			'list'=>$config['map']['clienttypes'],
			'onselect'=>"reload_connect",
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'name'=>array(
			'label'=>'название',
			'type'=>'hidden',
			'style'=>'width:160px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3,'g'=>'map')
		),
		'rayon'=>array(
			'label'=>'район',
			'type'=>'select',
			'style'=>'max-width:220px',
			'list'=>'list_of_rayons',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'address'=>array(
			'label'=>'адрес',
			'type'=>'autocomplete',
			'style'=>'width:240px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3,'g'=>'map')
		),
		'connect'=>array( // используется только для типа 'client'
			'label'=>'узел подкл-я',
			'type'=>'select',
			'list'=>array(),
			'style'=>'width:250px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'hostname'=>array(
			'label'=>'хост',
			'type'=>'select',
			'list'=>'get_host_names',
			'style'=>'width:250px',
			'onselect'=>'reloadservices',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'service'=>array(
			'label'=>'сервис',
			'type'=>'select',
			'style'=>'width:250px',
			'list'=>'get_service_names',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5,'g'=>'map')
		),
		'macaddress'=>array( // используется только для типа 'client'
			'label'=>'мак для ONU',
			'type'=>'text',
			'style'=>'width:170px',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>3,'g'=>'map')
		),
		'mrtg'=>array(
			'label'=>'диаграмма',
			'type'=>'text',
			'style'=>'width:250px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3,'g'=>'map')
		),
		'note'=>array(
			'label'=>'примечания',
			'type'=>'textarea',
			'style'=>'width:250px;height:70px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3,'g'=>'map')
		),
# --------------------------------------------------------------------------
		'home'=>array( // поля из таблицы homes
			'label'=>'home_id',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3,'g'=>'map')
		),
		'floors'=>array( // поля из таблицы homes
			'label'=>'этажей',
			'type'=>'text',
			'style'=>'width:40px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3,'g'=>'map')
		),
		'entrances'=>array(
			'label'=>'подъездов',
			'type'=>'text',
			'style'=>'width:40px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3,'g'=>'map'),
			'onkeyup'=>jsFunc('entrances')
		),
		'apartments'=>array(
			'label'=>'квартир',
			'type'=>'text',
			'style'=>'width:40px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3,'g'=>'map'),
			'onkeyup'=>jsFunc('apartments')
		),
		'boxplace'=>array(
			'label'=>'подъезд с ц.ящиком',
			'type'=>'text',
			'style'=>'width:40px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3,'g'=>'map')
		),
		'note'=>array(
			'label'=>'примечания',
			'type'=>'textarea',
			'style'=>'width:250px;height:70px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3,'g'=>'map')
		),
		'allentranc'=>array(
			'label'=>'подъезды',
			'type'=>'subform',
			'tname'=>'entrances',
			'style'=>'height:141px',
			'sub'=>'get_subform_of_entrances',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3,'g'=>'map')
		),
	),
);

function jsFunc($field){
	return "
	var ch = (typeof event.which === \"number\") ? event.which : event.keyCode;
	if((ch > 47 && ch < 58)||(ch>95 && ch<106)||ch==8||ch==46){
		if(typeof keyUpTimeOut == 'undefined') keyUpTimeOut = false;
		var n=$(this).val().replace(/[^0-9]/g,''),
			f=$(this).parents('form'),
			t=f.find('table').get(0),
			tn=f.find('[name=table]').val(),
			id=f.find('[name=id]').val();
		if(n=='' || n == 0) return false;
		if(typeof(ldr) !== 'object') ldr = $.loader();
		if(window['formTimeOut']) clearTimeout(formTimeOut);
		formTimeOut = setTimeout(function() {
			ldr.get({
				data:'go=homes&do=modifyfield&field={$field}&table='+tn+'&id='+id+'&n='+n,
				onLoaded: function(d){
					var i, el = f.find('[name=$field]').get(0);
					if(('append' in d) && ('_append' in d.obj)) {
						$(d.obj).find('tbody').empty();
						for(i in d.append) d.obj._append(d.append[i]);
						if('oldvalue' in el) el.oldvalue = n;
						console.log('el.oldvalue = '+('oldvalue' in el));
					}
				},
				obj: t
			})
		},500)
	}
	";
}

function before_edit_object($f) {
	global $DEBUG, $config, $q, $home_fields, $map_home_fields;
	if($DEBUG>0) log_txt(__FUNCTION__.": {$f['name']}[{$f['id']}]");
	if(isset($f['id'])){
		if(!$q) $q = new sql_query($config['db']);
		$r = $q->get('map',$f['id']);
		if($r['type'] != 'client') unset($r['connect']);
		if($DEBUG>0) log_txt(__FUNCTION__.": edit = ".arrstr($r));
		if($r['type'] == 'client'){
			$dt = $config['map']['clientdevtypes'];
			$cldev = $q->select("SELECT * FROM devices WHERE type='{$dt[$r['subtype']]}' AND node1={$r['id']} LIMIT 1",1);
			$r['macaddress'] = $cldev['macaddress'];
			if($r['subtype']=='wifi') {
				$r['connect'] = $q->select("SELECT p2.device FROM devports p1, devports p2 WHERE p1.device='{$cldev['id']}' AND p1.porttype='wifi' AND p1.link=p2.id",4);
            }
			$f['record'] = $r;
		}
		if($r['type'] == 'node'){
			unset($f['fields']['subtype']);
		}
		if($r['type'] == 'home'){
			$r = array_intersect_key($r,array_flip($map_home_fields));
			$h = $q->get('homes',$r['id'],'object');
			if(!$h && $r['address'] != '') { // если в таблице homes нет записи
				$h = $q->get('homes',$r['address'],'address');
				if($h && $r['id'] != $h['object']) // если есть уже запись с таким адресом
					$q->query("UPDATE homes SET object = '{$r['id']}' WHERE id='{$h['id']}'");
				if(!$h){  // добавляем в таблицы дом и подъезд
					$id = $q->insert('homes',array('object'=>$r['id'],'address'=>$r['address']));
					setentrances($id,1);
					setapartments($id,1);
					$h = $q->get('homes',$id);
					if($DEBUG>0) log_txt(__FUNCTION__.": добавлен дом ".arrstr($h));
				}
			}
			if($h){
				$f['layout'] = $f['layout_home'];
				$f['header'] = $f['header_home'];
				$h = array_intersect_key($h,array_flip($home_fields));
				$h['home'] = $h['id']; unset($h['id']);
				$h['allentranc'] = '';
				$r = array_merge($r,$h);
				if($DEBUG>0) log_txt(__FUNCTION__.": найден дом ".arrstr($h));
			}
			$f['record'] = $r;
		}
		if(!$r['rayon'] && ($r['type']=='home' || $r['type']=='node' || $r['type']=='client')){
			$q->select("");
		}
	}
	return $f;
}

function get_objecttype($r) {
	global $objecttype;
	return (is_array($r))? $objecttype[$r['type']] : $objecttype[$r];
}


function list_of_houses() {
	global $config;
	$q=new sql_query($config['db']);
	$r=$q->fetch_all("
		SELECT DISTINCT 
			trim(substr(address,1,IF(locate('/',address)-1>0,locate('/',address)-1,CHAR_LENGTH(address)))) as address 
		FROM users 
		ORDER BY address
	");
	return $r;
}

function onsave_mapobject($id,$save,$my) {
	global $config, $_REQUEST, $DEBUG, $newclientcable, $modclientcable, $N3MODIFY;
	if(!is_numeric($id)) $id = $save['id'];	
	$action=($id=='new')? 'append' : 'modify';
	$q=new sql_query($config['db']);

	$object = $q->select("SELECT * FROM map WHERE id='$id'",1);
	$device = $q->select("SELECT * FROM devices WHERE object='$id'",1);
	if($device) foreach($device as $k=>$v) $object['dev_'.$k]=$v;
	if($object['type']=='home'){
		if(CONFIGURE_NAGIOS==1){
			if(isset($save['address'])){ // если изменён адрес - ищем на узле с таким адресом тупой свич и на нём соединённые порты
				if($node = $q->select("SELECT * FROM map WHERE type='node' AND address='{$object['address']}'",1)){
					if($sw = $q->select("SELECT * FROM devices WHERE type='switch' AND node1='{$node['id']}' AND community=''",1)){
						if($pors = $q->select("SELECT * FROM devports WHERE device='{$sw['id']}' AND link is NOT NULL")){
							foreach($pors as $i=>$p) if(n3createservice($p['id'],'yes')) break;
						}
					}
				}
			}
		}
	}elseif($object['type']=='client' && isset($save['rayon']) && $save['rayon']>0){
		$q->query("UPDATE users SET rid='{$save['rayon']}' WHERE user='{$object['name']}'");
	}
	$f = getFeatureCollection($object['id']);
	$out = array('result'=>'OK','feature'=>$f['features'][0]);
	if($newclientcable) $out['append']['GeoJSON'][]=getFeatureCollection($newclientcable);
	if($modclientcable) $out['modify']['GeoJSON'][]=getFeatureCollection($modclientcable);
	if(isset($N3MODIFY)) $out['modify']['GeoJSON'][]=getFeatureCollection($N3MODIFY);
	if($my->row['type']!='cable') $out[$action][$my->row['type']."s"] = array($object);
	return $out;
}

function get_host_names($row){
	$r['']='';
	if(ICINGA_URL){
		$mon = new Icinga2();
		foreach($mon->getSwitches() as $k=>$h) $r[$h['name']]=$h['attrs']['display_name'];
	}elseif(NAGIOS_URL){
		foreach(get_nagios('do=get_hosts','hosts') as $k=>$v) $r[$v]=$v;
	}
	if(isset($row['hostname']) && $row['hostname']!='' && !isset($r[$row['hostname']])) $r[$row['hostname']]=$row['hostname'];
	asort($r);
	return $r;
}

function get_service_names($row,$r=null,$fn=null){
	$r=array();
	$r['']='';
	if(isset($row['hostname'])){ 
		if(ICINGA_URL){
			$mon = new Icinga2();
			foreach($mon->getHostServices($row['hostname']) as $k=>$s) $r[$s['attrs']['name']]=$s['attrs']['display_name'];
		}elseif(NAGIOS_URL){
			foreach(get_nagios('do=get_services&host='.$row['hostname'],'services') as $k=>$v) $r[$v]=$v;
		}
	}
	if(isset($row['service']) && $row['service']!='' && !isset($r[$row['service']])) $r[$row['service']] = $row['service'];
	asort($r);
	return $r;
}

function list_of_near_point($r) {
	if(isset($r['subtype']) && $r['subtype']=='wifi') return list_of_near_wifi($r);
	else return list_of_near_nodes($r);
}

function list_of_near_wifi($r){
	global $DEBUG, $config;
	$q=new sql_query($config['db']);
	if($DEBUG>4) log_txt(__FUNCTION__.": \$r = ".arrstr($r));
	$result = array(''=>'');
	if($xy = $q->select("SELECT x, y FROM map m, map_xy xy WHERE m.id=xy.object AND m.id={$r['id']} AND xy.num=0",1)){
		$res = $q->select("
			SELECT d.id, d.ip, d.name, m.address, distance(xy.x, xy.y, {$xy['x']}, {$xy['y']}) as dist 
			FROM map m, map_xy xy, devices d
			WHERE m.type='node' AND xy.object=m.id AND d.type='wifi' AND d.subtype='ap' AND d.node1=m.id
			ORDER BY dist
		");
		foreach($res as $k=>$v) $result[$v['id']] = $v['ip']." - ".$v['name']." - ".$v['address'];
		if($r['connect']>0 && !isset($result[$r['connect']])){
			$res = $q->select("SELECT id, address FROM map WHERE id='{$r['connect']}'",1);
			$result[$res['id']] = $res['address'];
		}
	}
	return $result;
}

function list_of_near_nodes($r){
	global $DEBUG, $config;
	$q=new sql_query($config['db']);
	if($DEBUG>4) log_txt(__FUNCTION__.": \$r = ".arrstr($r));
	$result = array(''=>'');
	if($xy = $q->select("SELECT x, y FROM map m, map_xy xy WHERE m.id=xy.object AND m.id={$r['id']} AND xy.num=0",1)){
		$res = $q->select("
			SELECT m.id, m.address, distance(xy.x, xy.y, {$xy['x']}, {$xy['y']}) as dist 
			FROM map m, map_xy xy
			WHERE m.type='node' AND xy.object=m.id
			ORDER BY dist LIMIT 30
		");
		foreach($res as $k=>$v) $result[$v['id']] = $v['address'];
		if($r['connect']>0 && !isset($result[$r['connect']])){
			$res = $q->select("SELECT id, address FROM map WHERE id='{$r['connect']}'",1);
			$result[$res['id']] = $res['address'];
		}
	}
	return $result;
}

function before_save_object($c,$o) {
	global $DEBUG, $config, $tables, $newclientcable, $modclientcable, $NAGIOS_ERROR;
	$r=array_merge($o,$c);
	if($DEBUG>0) log_txt(__FUNCTION__.": type='{$r['type']}' cmp = ".arrstr($c));
	if(key_exists('macaddress',$c) && $c['macaddress'] && !($c['macaddress'] = normalize_mac($c['macaddress'])))
		stop("Неверный mac адрес!");
	if($r['type']=='client') {  // Обработка при сохранении объекта 'client'
		$adt = $config['map']['clientdevtypes']; // тип клиентского устройства
		$dt = $adt[$r['subtype']]; // тип клиентского устройства
		$pt = ($r['subtype']!='wifi')? 'fiber':'wifi'; // тип порта на клиентском устройстве
		$np = isset($config['map']['client_cable_cores'])? $config['map']['client_cable_cores'] : 1; // кол-во портов по умолчанию
		$q = new sql_query($config['db']);
		if($DEBUG>0) log_txt(__FUNCTION__.": делаем всё по клиенту id={$r['id']}");
		$check_fld = array('user'=>$r['name'],'address'=>$r['address']);
		if(!($user = $q->get('users',$check_fld,'uid'))){
			stop(array('result'=>'ERROR','desc'=>"Пользователь не найден!"));
		}
		if(isset($c['name']) || isset($c['address'])){ // изменено имя или адрес
			if($clients = $q->get('map',array('type'=>'client','name'=>$r['name'],'address'=>$r['address']),'uid')){
				stop(array('result'=>'ERROR','desc'=>"Этот клиент уже есть в базе!")); // клиент присутствует на карте
			}
		}
		if(!$r['subtype']) $с['subtype'] = 'pon';
		$cldev = $q->select("SELECT * FROM devices WHERE type!='cable' AND node1='{$r['id']}'");
		if(!$cldev){ // если нет устройства клиента таблице 'devices'
			$d = array();
			if(isset($c['macaddress'])) $d['macaddress'] = $mac = $c['macaddress'];
			$cldev = make_client_device($q,$r,$d);
			$q->query("UPDATE devports SET link=NULL WHERE device!='{$cldev['id']}' AND node='{$c['id']}'");
		}else{
			if(($n = count($cldev))>1){
				for($i=1;$i<$n;$i++) $del[] = $cldev[$i]['id'];
			}
			if(isset($del)) $q->query("DELETE FROM devices WHERE id in (".implode(',',$del).")");
			$cldev = $cldev[0];
		}
		if($cldev['type'] != $dt){ // изменился тип клиента (wifi pon mconverter)
			$cldev['type'] = $dt; 
			$cldev['subtype'] = ($dt=='wifi')? "station":""; 
			$q->del("devices",$cldev['id']);
			$q->insert("devices",$cldev,true);
		}
		// проверка устройства клиента
		// если что-то изменяется по клиенту wifi
		if($r['id']>0 && isset($r['subtype']) && $r['subtype'] == 'wifi'){
			if($c['connect']) {
				if(!($wifi = $q->select("SELECT * FROM devices WHERE type='wifi' AND subtype='ap' AND id='{$c['connect']}'",1)))
					stop("Не найдена базовая станция Wi-Fi");
				$q->query("UPDATE devports d1, devports d2 SET d2.link=d1.id WHERE d1.device = {$wifi['id']} AND d2.device = {$cldev['id']} AND d1.porttype='$pt' AND d2.porttype='$pt'");
				$c['connect'] = $wifi['node1'];
			}
		// если что-то изменяется по клиенту ftth или pon
		}elseif($r['id']>0){
			$cable=$q->select("SELECT * FROM devices WHERE type='cable' AND (node2={$r['id']} OR node1={$r['id']})",1);
			if($o['subtype']=='wifi') {
				if($cable){
					if($cable['node1']==$r['id']){ $myp = 'node1'; $opp = 'node2'; }else{ $myp = 'node2'; $opp = 'node1'; }
					if(!$c['connect']){ // если есть кабель, то устанавливаем $c['connect'] =  opp
						if($cable[$opp]) $c['connect'] = $cable[$opp];
					}
					$port = $q->select("SELECT * FROM devports WHERE device='{$cable['id']}' AND node='{$cable[$opp]}' AND link>0",1);
					log_txt(__FUNCTION__.": смена типа на wifi port = ".arrstr($port));
					if($port){ $q->query("UPDATE devports p1, devports p2 SET p1.link=p2.id, p2.link=p1.id WHERE p1.node=p2.node AND p2.device='{$cldev['id']}' AND p2.porttype='fiber' AND p1.device='{$port['device']}' AND p1.number='{$port['number']}'");
					if($q->modified()==0) log_txt(__FUNCTION__.": не соединилось!\n  SQL: {$q->sql}");
					}else log_txt(__FUNCTION__.": смена типа на wifi port = {$q->sql}");
				}
			}

			// когда изменяется узел подключения клиента и на клиента уже есть кабель
			if(isset($c['connect']) && $cable && $o['subtype']!='wifi'){
				// убираем hostname service
				if($r['hostname']!='' && $r['service']!='' && !isset($c['hostname']) && !isset($c['service'])){
					if(CONFIGURE_NAGIOS==1){
						log_txt(__FUNCTION__.": remove service call");
						if(!n3removeservice($r,'yes')) log_txt(__FUNCTION__.": remove service NAGIOS ERROR: ".$NAGIOS_ERROR['desc']);
					}
				}
				if($DEBUG>0) log_txt(__FUNCTION__.": изменяем кабель id:{$cable['id']}");
				// очищаем соединения на кабеле
				$q->query("UPDATE devports d1, devports d2 SET d1.link=NULL, d2.link=NULL WHERE d1.link=d2.id AND d1.device={$cable['id']} AND d1.node!={$r['id']}");
				if($q->modified()==0) log_txt(__FUNCTION__.": соединения на кабеле [{$cable['id']}] не почистились");
				// изменяем гео координаты кабеля
				$q->del('map_xy',$cable['object'],'object');
				$xy=$q->select("
					SELECT object, 0 as slice, 0 as num, x, y FROM map_xy 
					WHERE object={$r['connect']} OR object={$r['id']}
				");
				if(count($xy)!=2) log_txt(__FUNCTION__.": ОШИБКА создания кабеля для клиента! ".arrstr($xy));
				if($xy[0]['object'] != $r['connect']) $xy = array_reverse($xy);
				foreach($xy as $k=>$v) {$xy[$k]['num']=$k; $xy[$k]['object']=$cable['object'];}
				$q->insert('map_xy',$xy);
				// изменяем начальный конечный узлы
				$q->update_record('devices',array('id'=>$cable['id'],'node1'=>$c['connect'],'node2'=>$r['id']));
				$modclientcable = $cable['object'];
			}
			// когда изменяется узел подключения клиента, но на клиента нет кабеля
			if(isset($c['connect']) && !$cable){
				if($DEBUG>0) log_txt(__FUNCTION__.": добавляем кабель");
				$xy=$q->select("
					SELECT object, 0 as slice, 0 as num, x, y FROM map_xy 
					WHERE object='{$r['connect']}' OR object='{$r['id']}'
				");
				if(count($xy)!=2) {
					log_txt(__FUNCTION__.": ОШИБКА создания кабеля для клиента! ".arrstr($xy));
				}else{
					$cable_id=$q->insert('map',array('name'=>'cable_'.$r['name'], 'type'=>'cable', 'subtype'=>'private', 'length'=>lineLength($xy), 'gtype'=>'LineString'));
					if($xy[0]['object'] != $r['connect']) $xy = array_reverse($xy);
					foreach($xy as $k=>$v) {$xy[$k]['num']=$k; $xy[$k]['object'] = $cable_id; }
					$q->insert('map_xy',$xy);
					$newclientcable = $cable_id;
					$cable = array('type'=>'cable','subtype'=>'private','object'=>$cable_id,'numports'=>$np,'node1'=>$r['connect'],'node2'=>$r['id']);
					if($cable = $q->get('devices',$cable_id,'object')){
						$cable['numports'] = $np; $cable['node1'] = $r['connect']; $cable['node2'] = $r['id'];
						$q->update_record('devices',$cable);
					}
				}
			}
			if(!isset($mac) && isset($c['macaddress'])){ // если изменён мак адрес
				if($DEBUG>0) log_txt(__FUNCTION__.": обновляем устройстро клиента macaddress={$c['macaddress']}");
				$cldev['macaddress'] = $c['macaddress']; $q->update_record('devices',$cldev);
				if($q->modified()==0) log_txt(__FUNCTION__.": мак не записан! SQL: ".$q->sql);
				if(CONFIGURE_NAGIOS>0){
					if($o['hostname']!='' && $o['service']!='' && !($h = n3removeservice($o,'yes'))){
						if(isset($NAGIOS_ERROR)) log_txt(arrstr($NAGIOS_ERROR));
					}
					if($c['macaddress']!=''){
						if($cldev){
							if($port = $q->select("SELECT * FROM devports WHERE device='{$cldev['id']}' AND porttype='fiber' AND link is NOT NULL",1)){
								$create_service = n3createservice($port['id'],'yes');
								if($DEBUG>0) log_txt(__FUNCTION__.": create_service: ".arrstr($create_service));
							}
						}
					}
				}
			}
		}
		if(key_exists('connect',$c) && $c['connect']==0) {
			$c['connect'] = null;
			if(isset($r['subtype']) && $r['subtype'] == 'wifi'){
				if($wifi = $q->select("SELECT * FROM devices WHERE type='wifi' AND subtype='station' AND node1='{$r['id']}'",1))
					$q->query("UPDATE devports SET link=null WHERE device='{$wifi['id']}' AND porttype='$pt'");
			}
		}
	}
	if($r['type']=='home'){  // Обработка при сохранении объекта 'home'
		if($DEBUG>0) log_txt(__FUNCTION__.": делаем всё по дому={$r['id']}");
		if(isset($c['entrances']) && $c['entrances'] > $config['map']['max_entrances'])
			stop(array('result'=>'ERROR','desc'=>"Кол-во подъездов не должно превышать {$config['map']['max_entrances']}"));
		if(isset($c['apartments']) && $c['apartments'] > $config['map']['max_apartments'])
			stop(array('result'=>'ERROR','desc'=>"Кол-во квартир не должно превышать {$config['map']['max_apartments']}"));
		$homes_fields = array_flip(array('address','floors','entrances','apartments','boxplace'));
		$del_fields = array('floors','entrances','apartments','home','boxplace');
		if(!isset($q)) $q = new sql_query($config['db']);
		$home = array_intersect_key($c,$homes_fields);
		if(count($home)>0 && $r['home']>0){
			$home['id'] = $r['home']; $home['object'] = $r['id'];
			if($DEBUG>0) log_txt(__FUNCTION__.": \$home=".arrstr($home));
			$form = new Form($config);
			$t = $tables['homes']; $t['id'] = $r['home'];
			$form->save($t,$home);
		}
		foreach($del_fields as $k) if(isset($c[$k])) unset($c[$k]);
		if($DEBUG>0) log_txt(__FUNCTION__.": c = ".arrstr($c));
	}
	if($r['type']=='node' && $c['address']==='') $c['address'] = 'узел '.$r['id'];
	if($r['type']=='cable' && $c['name']==='') $c['name'] = 'кабель '.$r['id'];
	if($DEBUG>0) log_txt(__FUNCTION__.": return: ".arrstr($c));
	return $c;
}

function delete_mapobject($old,$my) {
	global $modified;
	$out = false;
	if(deleteFeatures($old['id'])){
		$out = array('result'=>'OK','delete'=>array('objects'=>array($old['id'])),'desc'=>'Данные удалены!');
		if(isset($modified)) $out['modify']['GeoJSON'][] = getFeatureCollection($modified);
	}
	return $out;
}


function makeNgHost($r) {
	global $config, $DEBUG, $NAGIOS_ERROR;
	$q = new sql_query($config['db']);
	if(!is_array($r)){
		$NAGIOS_ERROR = array('result'=>'ERROR', 'desc'=>"запрос не содержит данных!");
		return false;
	}
	if($r['ip']=='' || !preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/',$r['ip']) || $r['community']=='' || 'name'==''){
		$NAGIOS_ERROR = array('result'=>'ERROR', 'desc'=>"не задан name, ip или community!");
		return false;
	}
	$ng = get_nagios("do=get_objects&objects=address:{$r['ip']}",'hosts');
	if(count($ng)>0){
		$NAGIOS_ERROR = array('result'=>'ERROR', 'desc'=>"такой свич уже присутствует в конфигурации!");
		return false;
	}
	$ports = $q->get('devports',$r['id'],'device');
	$t = new Trace();
	foreach($ports as $k=>$port){
		$ch = $t->traceChain($port['id']);
		$end = end($ch);
		if($end['type']!='switch') continue;
		$sw = $q->get('devices',$end['device']);
		if(!preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/',$sw['ip']) || $sw['community']=='') continue;
		$ng = get_nagios("do=get_objects&objects=address:{$sw['ip']}",'hosts');
		if(count($ng)==0) continue;
		$switch = reset($ng);
		if(isset($switch['host_name'])){
			$r['parents'] = $switch['host_name'];
			break;
		}
	}
	if(!isset($r['parents'])){
		$NAGIOS_ERROR = array('result'=>'ERROR', 'desc'=>"не удалось найти uplink!");
		return false;
	}
	$r['name'] = "SW-{$r['name']}-".preg_replace('/.*\./','',$r['ip']);
	$ng_url="do=switch_add&objects=address:{$r['ip']},name:{$r['name']},community:{$r['community']},parents:{$r['parents']}";
	if(!($ng = get_nagios($ng_url,'switch'))) return false;
	return $ng;
}

function select_cables_for_reserv($r) {
	global $config, $DEBUG;
	$q = new sql_query($config['db']);
	if($DEBUG>4) log_txt(__FUNCTION__.": \$r = ".arrstr($r));
	$result = array(''=>'');
	if($xy = $q->select("SELECT x, y FROM map m, map_xy xy WHERE m.id=xy.object AND m.id={$r['id']} AND xy.num=0",1)){
		$res = $q->select("
			SELECT m.id, min(distance(xy.x, xy.y, {$xy['x']}, {$xy['y']})) as dist 
			FROM map m, map_xy xy
			WHERE m.type='cable' AND xy.object=m.id
			GROUP BY m.id ORDER BY dist LIMIT 10
		");
		foreach($res as $k=>$v) $cab[] = $v['id'];
		if(isset($cab)){
			$cables = $q->fetch_all("
				SELECT d.object, m1.address as addr1, m2.address as addr2 
				FROM devices d LEFT OUTER JOIN map m1 ON m1.id=d.node1 LEFT OUTER JOIN map m2 ON m2.id=d.node2
				WHERE d.type='cable' and d.object in(".implode(',',$cab).")
			",'object');
			foreach($res as $k=>$v) {
				$result[$v['id']] = $cables[$v['id']]['addr1']." ".$cables[$v['id']]['addr2'];
			}
		}
	}
	return $result;
}

function type_of_object($v,$r,$fn=null) {
	global $objecttype;
	return $objecttype[$v];
}

function get_subform_of_entrances($id) {
	global $DEBUG, $config, $opdata, $tables;
	if($DEBUG>0) log_txt(__FUNCTION__.": id=$id");
	$q = new sql_query($config['db']);
	$home = $q->get('homes',$id,'object');
	// делаем выборку подъездов
	$tname = 'entrances';
	$t=array_merge($tables[$tname],array(
		'type'=>'table',
		'limit'=>'no',
		'module'=>'entrances',
		'filter'=>"AND home='{$home['id']}'",
		'style'=>'width:100%',
		'name'=>$tname
	));
	$c = new Table($t);
	return array(
		'class'=>'subform',
		'style'=>'width:100%;height:100%;overflow:auto;background:#F5EFE9',
		'table'=>$c->get()
	);
}

function setapartments($id, $apartments){
	global $DEBUG, $config, $q, $epattern;
	if(!$q) $q = new sql_query($config['db']);
	$entrances = $q->select("SELECT max(entrance) FROM entrances WHERE home='{$id}'",4);
	$i = 0;
	$key = "{$entrances}x{$apartments}";
	if(isset($epattern[$key])){
		foreach($epattern[$key] as $k=>$v)
			if($q->query("UPDATE entrances SET apartinit={$v[0]}, apartfinal={$v[1]} WHERE home='{$id}' AND entrance=$k+1")) $i++;
	}else{
		$ap4e = floor($apartments/$entrances);
		$q->query("UPDATE entrances SET apartinit=(entrance-1)*$ap4e+1, apartfinal=entrance*$ap4e WHERE home='{$id}'");
		$i = $q->modified();
	}
	if($DEBUG>0) log_txt(__FUNCTION__.": обновлено $i записей");
}

function setentrances($id,$n) {
	global $DEBUG, $config, $q, $opdata, $tables, $epattern;
	if(!$q) $q = new sql_query($config['db']);
	$old = $q->select("SELECT max(entrance) FROM entrances WHERE home='{$id}'",4);
	if(!old) $old = 0;
	if($old > $n){
		$q->query("DELETE FROM entrances WHERE home='{$id}' AND entrance > $n");
		if($DEBUG>0) log_txt(__FUNCTION__.": удалено ".$old-$n." записей");
	}elseif($old < $n){
		$e=array();
		for($i = $old; $i<$n; $i++) $e[] = array('home'=>$id,'entrance'=>$i+1);
		$q->insert("entrances",$e);
		if($DEBUG>0) log_txt(__FUNCTION__.": добавлено ".$n-$old." записей");
	}else{
		if($DEBUG>0) log_txt(__FUNCTION__.": изменения отсутствуют");
	}
	return $n-$old;
}

function modify_entrances($in,$modify=true) {
	global $DEBUG, $config, $q, $opdata, $tables, $epattern;
	$id = $in['id']; $new = $in['n'];
	if($DEBUG>0) log_txt(__FUNCTION__.": id=$id new=$new");
	if($new > $config['map']['max_entrances'])
		stop(array('result'=>'ERROR','desc'=>"Кол-во подъездов не должно превышать {$config['map']['max_entrances']}"));
 	if($id <= 0 || $new <= 0 || $new > $config['map']['max_entrances'] || !$in['table']){
 		log_txt(__FUNCTION__.": id:$id new:$new table:{$in['table']} ");
 		return false;
 	}
	if(!$q) $q = new sql_query($config['db']);
	if($in['table']=='map') $home = $q->get('homes',$id,'object');
	elseif($in['table']=='homes') $home = $q->get('homes',$id);
	else return false;

	setentrances($home['id'],$new);
	setapartments($home['id'],$home['apartments']);

	$q->query("UPDATE homes SET entrances = '$new' WHERE id='{$home['id']}'");
	$t=array_merge($tables['entrances'],array('filter'=>"AND home='{$home['id']}'"));
	$table = new Table($t);
	$r = array('append'=>$table->data);
	if($DEBUG>0) log_txt(__FUNCTION__.": data = ".arrstr($r));
	return $r;
}

function modify_apartments($in,$modify=true) {
	global $DEBUG, $config, $q, $opdata, $tables, $epattern;
	$id = $in['id']; $new = $in['n'];
	if($DEBUG>0) log_txt(__FUNCTION__.": id=$id new=$new");
	if($new > $config['map']['max_apartments'])
		stop(array('result'=>'ERROR','desc'=>"Кол-во квартир не должно превышать {$config['map']['max_apartments']}"));
 	if($id <= 0 || $new <= 0 || $new > $config['map']['max_apartments'] || !$in['table']) {
 		log_txt(__FUNCTION__.": id:$id new:$new table:{$in['table']} ");
 		return false;
 	}
	if(!$q) $q = new sql_query($config['db']);
	if($in['table']=='map') $home = $q->get('homes',$id,'object');
	elseif($in['table']=='homes') $home = $q->get('homes',$id);
	else return false;
	$mod = 0;
	if($home['apartments'] != $new){
		setapartments($home['id'], $new);
		if($DEBUG>0) log_txt(__FUNCTION__.": SQL: ".$q->sql);
		$mod = $q->modified();
	}
	if($mod == 0){
		if($DEBUG>0) log_txt(__FUNCTION__.": изменения отсутствуют");
		return true;
	}
	$q->query("UPDATE homes SET apartments = '$new' WHERE id='{$home['id']}'");
	$t = array_merge($tables['entrances'],array('filter'=>"AND home='{$home['id']}'"));
	$table = new Table($t);
	$r = array('append'=>$table->data);
	if($DEBUG>0) log_txt(__FUNCTION__.": data = ".arrstr($r));
	return $r;
}

function build_filter_for_map() {
	return filter2db('map');
}

?>
