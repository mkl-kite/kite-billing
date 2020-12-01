<?php
include_once("geodata.php");
include_once("map.cfg.php");
include_once("devices.cfg.php");
include_once("nagios.cfg.php");
include_once("classes.php");
include_once("form.php");
include_once("table.php");
$ng_switch = array_flip(array('host_name','alias','address','parents','services'));

$in['go'] = (key_exists('go',$_REQUEST))? strict($_REQUEST['go']) : 'devices';
$in['do'] = (key_exists('do',$_REQUEST))? strict($_REQUEST['do']) : '';
$in['id'] = (key_exists('id',$_REQUEST))? numeric($_REQUEST['id']) : 0;
$in['table'] = (key_exists('table',$_REQUEST))? strict($_REQUEST['table']) : false;

$q = new sql_query($config['db']);
$t = $tables['devices'];
$t['name']='devices';

switch($in['do']){

	case 'get_devices':
		if(!($d = $q->select("
			SELECT d.*, m1.address as a1, m2.address as a2
			FROM devices d LEFT OUTER JOIN map m1 ON d.node1=m1.id LEFT OUTER JOIN map m2 ON d.node2=m2.id
			WHERE d.node1='{$in['id']}' OR d.node2='{$in['id']}'
			ORDER BY type, name, INET_ATON(ip), id
		"))){
			stop(array('result'=>'OK','desc'=>"усройства не найдены"));
		}
		foreach($d as $k=>$dev){
			$d[$k]['name'] = get_devname($dev,$in['id'],false);
			unset($d[$k]['a1']); unset($d[$k]['a2']);
		}
		stop(array('result'=>'OK','devices'=>$d));
		break;

	case 'add':
	case 'new':
		if(array_key_exists('GeoJSON',$_REQUEST)) {
			if(!$m=save_new_geodata($GeoJSON)) {
				log_txt("nodes: new: GeoJSON=".sprint_r($GeoJSON));
				stop(array('result'=>'ERROR','desc'=>'ГеоДанные не были сохранены!'));
			}
			$dev=$q->select("select * from devices where object='$m'",1);
			$t['id']=$dev['id'];
			$t['fields']['type']['type']='nofield';
			$all=array();
			foreach($dev_fields_filter as $k=>$v) $all=array_unique(array_merge($all,$v));
			if(@$dev_fields_filter[$dev['type']]) foreach(array_diff($all,$dev_fields_filter[$dev['type']]) as $fname) unset($t['fields'][$fname]);
		}else{
			$t['defaults']['node1']=$in['id'];
			$t['defaults']['n1address']=$q->get('map',$in['id'],'id','address');
			$t['id']='new';
		}
		$form = new form($config);
		$out = (isset($m))? $form->get($t) : $form->getnew($t);
		if(isset($_REQUEST['selectednode'])) $out['form']['fields']['selectednode'] = array('type'=>'hidden','value'=>numeric($_REQUEST['selectednode']));
		stop($out);
		break;

	case 'edit':
		$form = new form($config);
		$t['fields']['type']['type']='nofield';
		if(!$in['id']){
			if(isset($_REQUEST['uid'])){
				$uid = numeric($_REQUEST['uid']);
				$client = $q->get('users',$uid);
				if($client) $obj = $q->select("SELECT * from map WHERE type='client' AND name='{$client['user']}'",1);
				if($obj) $dev = $q->select("SELECT * from devices WHERE node1='{$obj['id']}' AND type!='cable'",1);
				if($dev && $dev['type']=='onu'){
					$in['id'] = $dev['id'];
					$_REQUEST['selectednode'] = $dev['node1'];
				}
			}
		}
		$t['id'] = $in['id'];
		$out = $form->get($t);
		if(isset($_REQUEST['selectednode']) && is_numeric($_REQUEST['selectednode']) && $_REQUEST['selectednode']>0)
			$out['form']['fields']['selectednode'] = array('type'=>'hidden','value'=>$_REQUEST['selectednode']);
		stop($out);
		break;

	case 'save':
		$form = new form($config);
		$t['name']=$in['go'];
		stop($form->save($t));
		break;

	case 'delete':
		$ids=preg_split('/,/',$_REQUEST['ids']);
		foreach($ids as $k=>$v) $ids[$k]=numeric($v);
		$id=implode(',',$ids);
		$all=$q->select("SELECT * FROM devices WHERE object in ($id)");
		if(count($all)>0){
			$d = array(); $o = array();
			foreach($all as $k=>$v) {
				$d[]=$v['id'];
				if($v['object']>0) $o[]=$v['object'];
			}
			$delmap=implode(',',$o);
			$deldevices=implode(',',$d);
			foreach($q->select("SELECT id, type, name, address FROM map WHERE id in ($delmap)") as $k=>$v)
				$logmap[] = "{$objecttype[$v['type']]}[{$v['id']}]";
			foreach($q->select("SELECT id, type, name, numports FROM devices WHERE id in ($deldevices)") as $k=>$v)
				$logdev[] = "{$objecttype[$v['type']]}[{$v['id']}] {$v['name']}";
			if($q->query("DELETE FROM map WHERE id in ($delmap)")){
				log_db('удалил устройства',implode(', ',$logdev));
				log_db('удалил объекты из карт',implode(', ',$logmap));
			}
			$out = array('result'=>'OK','remove'=>array('device'=>$d),'desc'=>'Данные удалены!');
			if(count($o)>0) $out['delete'] = array('objects'=>$o);
			stop($out);
		}else{
			stop(array('result'=>'ERROR','desc'=>'Указанные бъекты не найдены в базе!'));
		}
		break;

	case 'remove':
		if($device=$q->select("SELECT * FROM devices WHERE id={$in['id']}",1)){
			$form = new form($config);
			$links = $q->select("SELECT count(*) FROM devports WHERE link is NOT NULL AND device='{$in['id']}'",4);
			$devname = get_devname($device,numeric($_REQUEST['selectednode']));
			$msg = ($links>0)? "<BR><FONT color=\"red\">устройство имеет $links неразорванных связей!</FONT><BR>" : "";
			stop($form->confirmForm($in['id'],'realremove',"$devname<BR>$msg<BR>Вы действительно хотите удалить это устройство?"));
		}else{
			stop(array('result'=>'ERROR','desc'=>'Указанное устройство не найдено в базе!'));
		}
		break;

	case 'realremove':
		stop(delete_devices($in['id']));
		break;

	case 'listservers':
		if(!isset($tables['switches'])) include_once("switches.cfg.php");
		$s = $tables['switches'];
		$s['target'] = 'map';
		$s['module'] = 'devices';
		$s['table_query'] = $s['table_query2'];
		$s['table_name'] = 'serverposition';
		unset($s['fields']['note']);
		$s['fields']['node']['label'] = 'адрес';
		$s['fields']['node']['style'] = 'width:160px';
		$s['fields']['name']['style'] = 'width:160px';
		$table = new Table($s);
		$html = $table->getHTML();
		$form = new form($config);
		stop($form->infoForm("<div style=\"max-height:500px;overflow-y:auto\">".$html."</div>"));
		break;

	case 'listswitches':
		if(!isset($tables['switches'])) include_once("switches.cfg.php");
		$sw = $tables['switches'];
		$sw['target'] = 'map';
		$sw['module'] = 'devices';
		$sw['limit'] = 'no';
		$sw['table_name'] = 'swposition';
		unset($sw['fields']['note']);
		$sw['fields']['node']['label'] = 'адрес';
		$sw['fields']['node']['style'] = 'width:160px';
		$sw['fields']['name']['style'] = 'width:160px';
		$table = new Table($sw);
		$html = $table->getHTML();
		$form = new form($config);
		stop($form->infoForm("<div style=\"max-height:500px;overflow-y:auto\">".$html."</div>"));
		break;

	case 'listwifi':
		if(!isset($tables['switches'])) include_once("switches.cfg.php");
		$st = $tables['switches'];
		$st['target'] = 'map';
		$st['module'] = 'devices';
		$st['limit'] = 'no';
		$st['table_query'] = $st['table_query1'];
		$st['table_name'] = 'wifiposition';
		unset($st['fields']['note']);
		$st['fields']['node']['label'] = 'адрес';
		$st['fields']['node']['style'] = 'width:160px';
		$st['fields']['name']['style'] = 'width:160px';
		$table = new Table($st);
		$html = $table->getHTML();
		$form = new form($config);
		stop($form->infoForm("<div style=\"max-height:500px;overflow-y:auto\">".$html."</div>"));
		break;

	case 'position':
		$pos = $q->select("SELECT d.node1 as id, xy.x as lng, xy.y as lat, 15 as zoom FROM devices d, map_xy xy WHERE d.node1 = xy.object AND d.id={$in['id']} LIMIT 1",1);
		if(!$pos) stop(array('result'=>'ERROR','desc'=>"Устройство не найдено!"));
		$node = $pos['id']; unset($pos['id']);
		stop(array('result'=>'OK','position'=>$pos, 'select'=>$node, 'device'=>$in['id']));
		break;

	case 'recount': // пересчёт длин кабелей
		if($opdata['level']<5) stop(array('result'=>'ERROR','desc'=>"Пересчет длин кабелей может делать только администиратор!"));
		$q1 = new sql_query($config['db']);
		if(!$q->query("
			SELECT d.id, d.object, d.numports, m1.address as a1, m2.address as a2, xy.slice, xy.num, xy.x, xy.y
			FROM devices d LEFT OUTER JOIN map m1 ON m1.id=d.node1 LEFT OUTER JOIN map m2 ON m2.id=d.node2, map_xy xy
			WHERE d.object=xy.object AND d.type='cable'
			ORDER BY numports, d.object, slice, num;
			")) {
			stop(array('result'=>'ERROR','desc'=>"Кабеля в базе не найдены!"));
		}
		$prev = $q->result->fetch_assoc();
		$prev_xy = array($prev['x'],$prev['y']);
		$t = array();
		$cab = 0; $seg = 0; $S = 0; $s = 0;
		while($row = $q->result->fetch_assoc()) {
			if($prev['object']!=$row['object']) {
				$S += $s;
				if(($up = $q1->update_record('map',array('id'=>$prev['object'],'length'=>$s))) === false) 
					$err[]="кабель id={$prev['object']} не обновлён!";
				$cab+=$up; $seg =0; $s = 0;
				$prev_xy = array($row['x'],$row['y']);
				if($prev['numports']!=$row['numports']) {
					$S = 0;
				}
			}else{
				$seg++;
				$xy = array($row['x'],$row['y']);
				$s += Distance($prev_xy,$xy);
				$prev_xy = $xy;
			}
			$prev = $row;
		}
		if(($up = $q1->update_record('map',array('id'=>$prev['object'],'length'=>$s))) === false)
			$err[]="кабель id={$prev['object']} не обновлён!";
		$cab+=$up;
		$form = new form($config);
		stop($form->infoForm("<p>пересчитано $cab кабелей!</p>".((count($err)>0)? "возникли следующие ошибки:<br>".implode("\n<br>",$err):"")));
		break;

	case 'auto_n1address':
		$req = (key_exists('req',$_REQUEST))? str($_REQUEST['req']) : '';
		$out['result'] = 'OK';
		$out['complete'] = $q->select("
			SELECT DISTINCT 
				address as label,
				id as node1
			FROM map
			WHERE type in ('node','client') AND address like '%$req%'
			ORDER BY address
			LIMIT 20
		");
		stop($out);
		break;

	case 'auto_n2address':
		$req = (key_exists('req',$_REQUEST))? str($_REQUEST['req']) : '';
		$out['result'] = 'OK';
		$out['complete'] = $q->select("
			SELECT DISTINCT 
				address as label,
				id as node2
			FROM map
			WHERE type in ('node','client') AND address like '%$req%'
			ORDER BY address
			LIMIT 20
		");
		stop($out);
		break;

	case 'reloadsubtype':
		$val=(key_exists('value',$_REQUEST))?$_REQUEST['value']:'';
		$r['']='';
		if($val == 'splitter') foreach($config['fading']['data'] as $k=>$v) $r[$k] = $k;
		if($val == 'divisor') foreach($config['fading']['data'] as $k=>$v) if($v['div']) $r[$v['div']] = $v['div'];
		if($val == 'cable') foreach($config['map']['cabletypes'] as $k=>$v) $r[$k] = $v;
		if($val == 'wifi') foreach($config['map']['typewifi'] as $k=>$v) $r[$k] = $v;
		$out['result']='OK';
		$out['select']['list']=$r;
		$out['target']='subtype';
		stop($out);
		break;

	case 'nagios_switch': // создание или обновление свича в конфиге nagios3
		if($opdata['status']<5) stop(array('result'=>'ERROR','desc'=>'Недостаточно прав!'));
		$switch = $q->get('devices',$in['id']);
		if($switch['ip']=='' || !preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/',$switch['ip']) || $switch['community']=='' || $switch['name']==''){
			stop(array('result'=>'ERROR', 'desc'=>"Свич неуправляемый!"));
		}
//		log_txt("devices::nagios_switch: work switch {$switch['ip']}");
		if(!($h = get_nagios("do=get_objects&objects=address:{$switch['ip']}",'hosts'))){
			if(isset($NAGIOS_ERROR)) stop($NAGIOS_ERROR);
		}
		$form = new form($config);
		if($h && count($h)>0){
			$host = reset($h);
			$t = $tables['nagios'];
			unset($host['services']);
			$t['defaults']=array_merge(array('type'=>'host','use'=>'generic-host','community'=>$switch['community']),$host);
			$t['defaults']['restart'] = 'yes';
			$out = $form->getnew($t);
			$out['form']['header']='Изменение свича';
			$out['form']['fields']['do']['value']='mod_ng_switch';
		}else{
			$sw = n3switch_new($switch);
			$t = $tables['nagios'];
			$t['defaults']=array_merge(array('type'=>'host','community'=>$switch['community']),$sw);
			$t['defaults']['restart'] = 'yes';
			$out = $form->getnew($t);
			$out['form']['header']='Добавление свича';
			$out['form']['fields']['do']['value']='new_ng_switch';
		}
//		log_txt("devices::nagios_switch: get_nagios: ".arrstr($host));
		stop($out);
		break;

	case 'new_ng_switch':
		if($opdata['status']<5) stop(array('result'=>'ERROR','desc'=>'Недостаточно прав!'));
		$new_sw = array_flip(array('host_name','alias','address','community','parents'));
		$sw = array_intersect_key($_REQUEST,$new_sw);
		if(CONFIGURE_NAGIOS>0)
		if(!($ng = get_nagios("do=switch_add&switch=".urlencode(json_encode($sw)).
			"&realsave={$_REQUEST['realsave']}&restart={$_REQUEST['restart']}","data")))
			stop($NAGIOS_ERROR);
		stop(array('result'=>'OK', 'desc'=>(CONFIGURE_NAGIOS>0)?"конфигурация NAGIOS обновлена!":""));
		break;

	case 'mod_ng_switch':
		if($opdata['status']<5) stop(array('result'=>'ERROR','desc'=>'Недостаточно прав!'));
		$switch = $q->get('devices',$in['id']);
		$sw = array_intersect_key($_REQUEST,$ng_switch);
		$oldsw = array();
		foreach($ng_switch as $nf=>$v) if(key_exists("old_".$nf,$_REQUEST)) $oldsw[$nf] = $_REQUEST["old_".$nf];
		$modsw = $q->compare($oldsw,$sw);
		if(count($modsw)==0){
			stop(array('result'=>'OK', 'desc'=>"Изменения не требуются!"));
			break;
		}
		// выбираем неизменившийся параметр для однозначного определения свича
		if(!isset($modsw['address'])) $obj = $ip = "address:".urlencode($_REQUEST['address']);
		elseif(!isset($modsw['host_name'])) $obj = $hn = "host_name:".urlencode($_REQUEST['host_name']);
		else stop(array('result'=>'ERROR', 'desc'=>'изменены сразу 2 ключевых параметра!'));
		// проверяем если ли уже такой свич
		if(isset($modsw['address']) && $q->get('devices',$modsw['address'],'ip'))
			stop(array('result'=>'ERROR', 'desc'=>"Свич с IP={$modsw['address']} уже есть!"));
		if(isset($modsw['host_name']) && $q->get('map',$sw['host_name'],'hostname'))
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
		break;

	case 'cutform':
		if(!write_access($t['fields']['object']['access'])) stop(array('result'=>'ERROR','desc'=>'Недостаточно прав!'));
		$lat = flt(@$_REQUEST['lat']); $lng = flt(@$_REQUEST['lng']);
		if(!$lat || !$lng) stop(array('result'=>'ERROR','desc'=>'Отсутствуют координаты!'));
		$t = array(
			'name'=>'devices', 'title'=>'Новый узел', 'key'=>'id', 'header'=>'<center>Адрес узла</center><br>',
			'defaults'=>array('cable'=>$in['id'],'lat'=>$lat,'lng'=>$lng,'address'=>'','rayon'=>$q->select("SELECT rid, Distance(longitude, latitude, '{$lng}', '{$lat}') as len FROM rayon WHERE latitude>0 and longitude>0 ORDER BY len LIMIT 1",4)),
			'query'=>"SELECT 'new' as id, 0 as rayon, 0 as cable, '' as lat, '' as lng, '' as address FROM devices",
			'fields'=>array(
				'cable'=>array('type'=>'hidden','native'=>false,'access'=>array('r'=>3,'w'=>5,'g'=>'map')),
				'lat'=>array('type'=>'hidden','native'=>false,'access'=>array('r'=>3,'w'=>5,'g'=>'map')),
				'lng'=>array('type'=>'hidden','native'=>false,'access'=>array('r'=>3,'w'=>5,'g'=>'map')),
				'address'=>array(
					'label'=>'адрес',
					'type'=>'autocomplete',
					'style'=>'width:190px',
					'native'=>false,
					'access'=>array('r'=>3,'w'=>5,'g'=>'map')
				),
				'rayon'=>array(
					'label'=>'район',
					'type'=>'select',
					'style'=>'max-width:220px',
					'list'=>'list_of_rayons',
					'native'=>true,
					'access'=>array('r'=>2,'w'=>3,'g'=>'map')
				),
			)
		);
		$form = new form($config);
		$out = $form->getnew($t);
		$out['form']['fields']['do']['value'] = 'cutcable';
		stop($out);
		break;

	case 'cutcable':
		if(!write_access($t['fields']['object']['access'])) stop(array('result'=>'ERROR','desc'=>'Недостаточно прав!'));
		$lat = flt(@$_REQUEST['lat']); $lng = flt(@$_REQUEST['lng']);
		if(!$lat || !$lng) stop(array('result'=>'ERROR','desc'=>'Отсутствуют координаты!'));
		$addr = str(@$_REQUEST['address']);
		if($addr == '') stop(array('result'=>'ERROR','desc'=>'Отсутствует адрес!'));
		$cable = numeric(@$_REQUEST['cable']);
		if($cable == '' || $cable <= 0) stop(array('result'=>'ERROR','desc'=>'Отсутствует идентификатор кабеля!'));
		$rayon = str(@$_REQUEST['rayon']);
		if($rayon == '') stop(array('result'=>'ERROR','desc'=>'Отсутствует район!'));
		$tmp = cut_cable($cable,array('x'=>$lng,'y'=>$lat),array('address'=>$addr,'rayon'=>$rayon));
		if(!$tmp) stop(array('result'=>'ERROR','desc'=>'Разрезать кабель не удалось!'));
		$out['result'] = 'OK';
		$out['modify']['GeoJSON'][] = getFeatureCollection($tmp['cable1']);
		$out['append']['GeoJSON'][] = getFeatureCollection($tmp['node']);
		$out['append']['GeoJSON'][] = getFeatureCollection($tmp['cable2']);
		stop($out);
		break;

	case 'get_xy':
		$table = 'devices';
		$id = isset($_REQUEST['id'])? numeric($_REQUEST['id']) : false;
		if(!$id || !($device = $q->get($table,$id))) stop(array('result'=>'ERROR','desc'=>"Запись о узле не найдена!"));
		if(!($o = $q->get('map',$device['node1']))) stop(array('result'=>'ERROR','desc'=>"Узел не найден!"));
		stop(array('result'=>'OK','object'=>$o['id']));
		break;

	case 'show_fading':
		$tr = new Trace();
		stop($tr->fd);
		break;

	case 'auto_address':
		stop(auto_address());
		break;

	default:
		stop(array(
		'result'=>'ERROR',
		'desc'=>"</pre><center>неверные данные<br>
			go={$in['go']}<br>
			do={$in['do']}<br>
			id={$in['id']}<br>
			</center><pre>"
		));
}
?>
