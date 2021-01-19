<?php
include_once("geodata.php");
include_once("map.cfg.php");
include_once("classes.php");
include_once("form.php");

$in['go'] = (key_exists('go',$_REQUEST))? strict($_REQUEST['go']) : 'clients';
$in['do'] = (key_exists('do',$_REQUEST))? strict($_REQUEST['do']) : "";
$in['id'] = (key_exists('id',$_REQUEST))? numeric($_REQUEST['id']) : '0';
$in['value'] = (key_exists('value',$_REQUEST))? numeric($_REQUEST['value']) : 0;

$fld_client_filter=array(
	'pon'=>array('macaddress'),
);
$fld_client_label = array(
	'wifi'=>array('connect','баз.станция'),
	'pon'=>array('connect','узел подкл-я'),
	'ftth'=>array('connect','узел подкл-я'),
);

$q = new sql_query($config['db']);
$t = $tables['map'];
$t['fields']['name']['label']='логин';
$t['fields']['name']['type']='autocomplete';
foreach(array('mrtg') as $k=>$v) unset($t['fields'][$v]);
$form = new form($config);
$m = false;

switch($in['do']){

	case 'add':
	case 'new':
		if($GeoJSON && $m=save_new_geodata($GeoJSON)) {
			$t['form_query']="SELECT id,type,gtype,'pon' as subtype,name,address,connect, '' as macaddress, hostname,service,note FROM map";
			$t['id']=$m;
			$t['name']='map';
			$t['do']='firstsave';
			$t['fields']['type']['type']='nofield';
			$t['fields']['subtype']['onchange'] = js_type_onchange($fld_client_filter,$fld_client_label);
			stop($form->get($t));
		}else{
			log_txt("clients: new: GeoJSON=".sprint_r($GeoJSON));
			stop(array('result'=>'ERROR','desc'=>"Гео Данные не были сохранены!"));
		}
		break;

	case 'edit':
		if($GeoJSON && !($m=save_geodata($GeoJSON))) {
			log_txt("clients: save: GeoJSON=".sprint_r($GeoJSON));
			stop(array('result'=>'ERROR','desc'=>'ГеоДанные не были сохранены!'));
		}
		$t['id']=($m)? $m : $in['id'];
		$t['name']='map';
		$t['fields']['type']['type']='nofield';
		$t['fields']['subtype']['onchange'] = js_type_onchange($fld_client_filter,$fld_client_label);
		stop($form->get($t));
		break;

	case 'firstsave':
	case 'save':
		$t['name']='map';
		$t['key']='id';
		$t['id']=$in['id'];
		stop($form->save($t));
		break;

	case 'delete':
		$ids=preg_split('/,/',$_REQUEST['ids']);
		foreach($ids as $k=>$v) $ids[$k]=numeric($v);
		$id=implode(',',$ids);
		$all=$q->fetch_all("SELECT id FROM map WHERE id in ($id)");
		if(count($all)>0){
			$str_all=implode(',',$all);
			$q->query("DELETE FROM map WHERE id in ($str_all)");
			stop(array('result'=>'OK','delete'=>array('objects'=>$all),'desc'=>'Данные удалены!'));
		}else{
			stop(array('result'=>'ERROR','desc'=>'Указанные бъекты не найдены в базе!'));
		}
		break;

	case 'remove':
		$form = new form($config);
		$client = $q->select("SELECT * FROM map WHERE id={$in['id']}",1);
		stop($form->confirmForm($in['id'],'realremove',"{$client['address']}<BR>Вы действительно хотите удалить клиента?"));
		break;

	case 'realremove':
		$q->query("DELETE FROM map WHERE id={$in['id']}");
		stop(array('result'=>'OK','delete'=>array('objects'=>array($in['id']))));
		break;

	case 'reloadservices':
		$val=(key_exists('value',$_REQUEST))?$_REQUEST['value']:'';
		$r['']='';
		foreach(get_nagios('do=get_services&host='.$val,'services') as $k=>$v) $r[$v]=$v;
		$out['result']='OK';
		$out['select']['list']=$r;
		$out['target']='service';
		stop($out);
		break;

	case 'reload_connect':
		$val=(key_exists('value',$_REQUEST))? strict($_REQUEST['value']):'';
		$client = $q->get('map',$in['id']);
		if(!$client) stop("Клиент не найден!");
		if($val == 'wifi') $r = list_of_near_wifi($client);
		else $r = list_of_near_nodes($client);
		foreach($r as $k=>$v) $l['_'.$k] = $v;
		$out['result']='OK';
		$out['select']['list']=$l;
		$out['target']='connect';
		if($val=='pon' && $client['subtype']!=$val){
			$conn = $q->select("SELECT opt82 FROM users WHERE user='{$client['name']}' AND opt82!=''",4);
			if($conn && ($ui = parse_opt82($conn))){
				$out['modify']['macaddress'] = $ui['device'];
			}
		}

		stop($out);
		break;

	case 'auto_address':
		$req = (key_exists('req',$_REQUEST))? str($_REQUEST['req']) : '';
		$d = $q->select("
			SELECT uid, address as label, user as name, opt82 FROM users
			WHERE address like '%$req%' ORDER BY address LIMIT 30
		");
		foreach($d as $k=>$v){
			if($v['opt82'] && ($m = arrfld(parse_opt82($v['opt82']),'device'))) $v['macaddress'] = $m;
			if(!$v['macaddress']) $v['macaddress'] = $q->select("SELECT value FROM documents d, docdata f WHERE d.uid='{$v['uid']}' AND
				type='warranty' AND d.id=f.document AND f.field='code' ORDER BY d.created DESC LIMIT 1",4);
			if(!$v['macaddress']) unset($v['macaddress']);
			unset($v['opt82']); unset($v['uid']);
			$out['complete'][] = $v;
		}
		stop($out);
		break;

	case 'auto_name':
		$req = (key_exists('req',$_REQUEST))? str($_REQUEST['req']) : '';
		$out['result'] = 'OK';
		$d = $q->select("
			SELECT uid, user as label, address, opt82 FROM users
			WHERE user like '%$req%' ORDER BY user LIMIT 30
		");
		foreach($d as $k=>$v){
			if($v['opt82'] && ($m = arrfld(parse_opt82($v['opt82']),'device'))) $v['macaddress'] = $m;
			if(!$v['macaddress']) $v['macaddress'] = $q->select("SELECT value FROM documents d, docdata f WHERE d.uid='{$v['uid']}' AND
				type='warranty' AND d.id=f.document AND f.field='code' ORDER BY d.created DESC LIMIT 1",4);
			if(!$v['macaddress']) unset($v['macaddress']);
			unset($v['opt82']); unset($v['uid']);
			$out['complete'][] = $v;
		}
		stop($out);
		break;

	case 'convert': // единственно когда клиентов из devices нужно добавить в map
		if($opdata['status']<5) stop(array('result'=>'ERROR','desc'=>'Недостаточно прав!'));
		$node = (isset($_REQUEST['node']))? numeric($_REQUEST['node']):0;
		stop(convert_clients($node));
		break;

	case 'sendmail': // отправка почты клиенту
		if($opdata['status']<3) stop(array('result'=>'ERROR','desc'=>'Недостаточно прав!'));
		$to = (isset($_REQUEST['to']))? $_REQUEST['to']:false;
		if(is_numeric($to)){
			if($to<10000) $addr = $q->select("SELECT email FROM users WHERE uid='{$to}' AND email like '%@%'",4);
			else $addr = $q->select("SELECT email FROM users WHERE contract='{$to}' AND email like '%@%'",4);
		}elseif(is_string($to)){
			$to = str($to);
			if(preg_match('/[a-z_\-.]*@[a-z_\-.]*/',$to)) $addr = $q->select("SELECT email FROM users WHERE email='{$to}'",4);
			else $addr = $q->select("SELECT email FROM users WHERE user='{$to}' AND email like '%@%'",4);
		}
		if(!$addr) stop(array('result'=>'ERROR','desc'=>'Не определён адрес!'));
		$msg['to'] = $addr;
		$msg['subject'] = (isset($_REQUEST['subject']))? str($_REQUEST['subject']):'';
		$msg['body'] = (isset($_REQUEST['body']))? $_REQUEST['body']:'';
		if(!($sm = send_mail($msg))) stop($MAIL_ERROR);
		stop(array('result'=>'OK','mail'=>$sm));
		break;

	case 'testDbLog':
		if($opdata['status']<5) stop(array('result'=>'ERROR','desc'=>'Недостаточно прав!'));
		$id = ($in['id']>0)? $in['id']:false;
		if($client = $q->get('devices',$id)){
			stop(array('result'=>'OK','table'=>$table->getHTML()));
		}else{
			stop(array('result'=>'ERROR','desc'=>"ничего не обработано!"));
		}
		break;

	case 'mkNgService':
		if($opdata['status']<5) stop(array('result'=>'ERROR','desc'=>'Недостаточно прав!'));
		$id = ($in['id']>0)? $in['id']:false;
		if($d = makeNagiosClients($id)){
			$table = new Table(array('data'=>$d));
			stop(array('result'=>'OK','table'=>$table->getHTML()));
		}else{
			stop(array('result'=>'ERROR','desc'=>"ничего не обработано!"));
		}
		break;

	case 'document':
		$id = ($in['id']>0)? $in['id'] : false;
		$doctype = (isset($_REQUEST['type']))? $_REQUEST['type']:"unknown";
		if($doc = $q->get('documents',$id)){
			$fields = $q->fetch_all("SELECT field, value FROM decdata WHERE document='{$id}'",'field');
			$doc = array_merge($doc,$fields);
			stop(array('result'=>'OK','document'=>$doc));
		}else{
			stop(array('result'=>'ERROR','desc'=>"ничего не найдено!"));
		}
		break;

	case 'trace':
		$uid = ($in['id']>0)? $in['id'] : false;
		if(!($client = $q->select("SELECT m.* FROM map m, devices d, users u WHERE m.type='client' AND m.id=d.node1 AND d.type='client' AND d.name = u.user AND u.uid='$uid'",1)))
			stop(array('result'=>'ERROR','desc'=>"В картах клиент не найден!"));
		if(!($cable = $q->select("SELECT * FROM devices WHERE type='cable' AND ( node1='{$client['id']}' OR node2='{$client['id']}' )",1)))
			stop(array('result'=>'ERROR','desc'=>"Не найден клиентский кабель!"));
		if(!($ports = $q->select("SELECT * FROM devports WHERE device='{$cable['id']}' AND node!='{$client['id']}' AND link is NOT NULL")))
			stop(array('result'=>'ERROR','desc'=>"У клиентский кабеля отсутстует сварка!"));
		if(count($ports)>1) stop(array('result'=>'ERROR','desc'=>"У клиентского кабеля больше одной сварки!"));
		$port = array_shift($ports);
		$tr = new Trace();
		$caps = $tr->capdevices($port['id']);
		foreach(array('begin','end') as $k=>$n) {
			if($caps[$n]['type']=='switch' && $caps[$n]['community']=='') $caps[$n]['type'] = 'sw';
			$cp[$caps[$n]['type']] = $n;
		}
		stop(array('result'=>'OK','data'=>$tr->traceFormat()));
		break;

	case 'userkill':
		$table = isset($_REQUEST['table'])? strict($_REQUEST['table']) : false;
		if($table){ // если указана таблица - берём из неё запись и ищем в ней поле идентифицирующее клиента
			if(!isset($tables[$table])) include_once("{$table}.cfg.php");
			if(!isset($tables[$table])) stop(array('result'=>'ERROR','desc'=>"Ошибка запроса!"));
			$id = isset($_REQUEST['id'])? numeric($_REQUEST['id']) : false;
			if(!($r = $q->get($table,$id))) stop(array('result'=>'ERROR','desc'=>"Запись о соединении не найдена!"));
			if(isset($r['uid'])) $id=$r['uid'];
			elseif(isset($r['user'])) $id=$r['user'];
			elseif(isset($r['username'])) $id=$r['username'];
		}
		if(!isset($id)){ // если id не задан и не найден в таблицах проверяем если ли информация по клиенту в самом запросе
			$id = isset($_REQUEST['id'])? numeric($_REQUEST['id']) : false;
			if(!$id) $id = isset($_REQUEST['uid'])? numeric($_REQUEST['uid']) : false;
			if(!$id) $id = isset($_REQUEST['user'])? numeric($_REQUEST['user']) : false;
		}
		if(!isset($tables['users'])) include_once("users.cfg.php");
		// создаём объект польозвателя
		$user = new user($id);
 		if(!$user->data && isset($r)) $data = $r['radacctid']; else $data = '';
		if(!($ract = $user->disconnect($data))) stop(array('result'=>'ERROR','desc'=>implode('<br>',$user->errors)));
		stop(array('result'=>'OK','delete'=>$ract));
		break;

	case 'mapobject':
		$id = isset($_REQUEST['id'])? numeric($_REQUEST['id']) : false;
		$table = isset($_REQUEST['table'])? strict($_REQUEST['table']) : false;
		$dev = ($table == 'devices')? $q->get($table,$id) : false;
		$object = ($dev && $dev['type']!='cable')? $dev['node1'] : false;
		if(!$object){
			stop(array('result'=>'ERROR','desc'=>"Объект на карте не найден!"));
		}
		stop(array('result'=>'OK','object'=>$object));
		break;

	case 'clientobject':
		$table = isset($_REQUEST['table'])? strict($_REQUEST['table']) : false;
		$u = false;
		if($table){ // если указана таблица - берём из неё запись и ищем в ней поле идентифицирующее клиента
			if(!isset($tables[$table])) include_once("{$table}.cfg.php");
			if(!isset($tables[$table])) stop(array('result'=>'ERROR','desc'=>"Ошибка запроса!"));
			$id = isset($_REQUEST['id'])? numeric($_REQUEST['id']) : false;
			if($id && !($r = $q->get($table,$id))) stop(array('result'=>'ERROR','desc'=>"Запись о пользователе не найдена!"));
			if($r){
				$u = array_intersect_key($r,array('uid'=>0,'user'=>1,'username'=>2));
				if(isset($u['username'])){ $u['user'] = $u['username']; unset($u['username']); }
			}
		}
		if(!$u){ // если id не задан и не найден в таблицах проверяем если ли информация по клиенту в самом запросе
			$u['uid'] = isset($_REQUEST['id'])? numeric($_REQUEST['id']) : false;
			if(!$id) $u['uid'] = isset($_REQUEST['uid'])? numeric($_REQUEST['uid']) : false;
			if(!$id) $u['user'] = isset($_REQUEST['user'])? strict($_REQUEST['user']) : false;
			if(!$id) $u['user'] = isset($_REQUEST['login'])? strict($_REQUEST['login']) : false;
		}
		if(!isset($tables['users'])) include_once("users.cfg.php");
		if(!($client = $q->get('users',$u)))  stop(array('result'=>'ERROR','desc'=>"Клиент не найден!"));
		if(isset($client[0])) $client = $client[0];
		// ищем объект на карте
		$object = $q->select("SELECT id FROM map WHERE type='client' AND name='{$client['user']}'",4);
		if(!$object) {
			$a = parse_address($client['address']);
			if($tmp = $q->select("SELECT * FROM map WHERE type='home' AND address like '%{$a['addr']}%'")){
				foreach($tmp as $k=>$v){
					if(!($s = parse_address($v['address']))) continue;
					if($a['home'].$a['litera'] == $s['home'].$s['litera']){ $object = $v['id']; break; }
				}
			}
		}
		if(!$object){
			stop(array('result'=>'ERROR','desc'=>"Объект на карте не найден!"));
		}
		stop(array('result'=>'OK','object'=>$object));
		break;

	case 'block':
		$id = isset($_REQUEST['id'])? numeric($_REQUEST['id']) : false;
		if($DEBUG>0) log_txt("clients: id='$id'");
		$table = isset($_REQUEST['table'])? strict($_REQUEST['table']) : false;
		if($DEBUG>0) log_txt("clients: table='$table'");
		if($table && $table!='users'){
			if(!isset($tables[$table])) include_once("{$table}.cfg.php");
			if(!isset($tables[$table])) stop(array('result'=>'ERROR','desc'=>"Ошибка запроса!"));
			if(!($r = $q->get($table,$id))) stop(array('result'=>'ERROR','desc'=>"Запись о соединении не найдена!"));
			if(isset($r['uid'])) $id=$r['uid'];
			elseif(isset($r['user'])) $id=$r['user'];
			elseif(isset($r['username'])) $id=$r['username'];
			if($DEBUG>0) log_txt("clients: table='$table' \$id='$id'");
		}
		if(!isset($id)){
			if($DEBUG>0) log_txt("clients: id is not recive");
			$id = isset($_REQUEST['id'])? numeric($_REQUEST['id']) : false;
			if(!$id) $id = isset($_REQUEST['uid'])? numeric($_REQUEST['uid']) : false;
			if(!$id) $id = isset($_REQUEST['user'])? numeric($_REQUEST['user']) : false;
		}
		if(!isset($tables['users'])){
			include_once("users.cfg.php");
			if($DEBUG>0) log_txt("clients: included users table");
		}
		$form = new Form($config);
		$form->save($tables['users'],array('id'=>$user->data['uid'],'blocked'=>1));
		stop(array('result'=>'OK','reload'=>1));
		break;

	case 'sqltest':
		if(!isset($_REQUEST['device'])) stop(array('result'=>'ERROR','desc'=>'device unknown!'));
		if(!isset($_REQUEST['bandle'])) stop(array('result'=>'ERROR','desc'=>'bandle unknown!'));
		if(!isset($_REQUEST['colorscheme'])) stop(array('result'=>'ERROR','desc'=>'colorscheme unknown!'));
		$dev_id = numeric($_REQUEST['device']);
		$bandle = numeric($_REQUEST['bandle']);
		$colorscheme = strict($_REQUEST['colorscheme']);
		$out = $q->query("
			SELECT
				if(d.type!='splitter',dp.color,if(p.number=1,'white',dps.color)) as p.color, 
				if(d.numports<={$bandle},'',dp1.color) as p.bandle, 
				if(d.type!='splitter',dp.option,if(p.number=1,'solid',dps.option)) as coloropt
			FROM
				devports p 
				LEFT JOIN devices d ON p.device=d.id 
				LEFT JOIN map o ON d.object=o.id 
				LEFT OUTER JOIN devprofiles as dp ON dp.name='{$colorscheme}' AND dp.port=mod(p.number-1,{$bandle})+1
				LEFT OUTER JOIN devprofiles as dps ON dps.name='{$colorscheme}' AND dps.port=mod(p.number-1,{$bandle})
				LEFT OUTER JOIN devprofiles as dp1 ON dp1.name='{$colorscheme}' AND dp1.port=floor((p.number-1)/{$bandle})+1
			WHERE
				p.device='{$dev_id}'
		");
		stop(array('result'=>($out)? 'OK':'ERROR', 'data'=>(!$out)? $q->errors : $out));
		break;

	case 'reloadsubtypeclient':
		if(!isset($config['map']['clienttypes'][$in['value']])) stop("Неправильный тип клиента");
		$l = array();
		if($in['value'] == 'ftth' || $in['value'] == 'pon') $l = list_of_near_nodes($in['id']);
		elseif($in['value'] == 'wifi') $l = list_of_near_wifi($in['id']);
		if(!isset($l)) log_txt("clients.php: WARNING reloadsubtypeclient Данные не найдены!");
		$out['target']='connect';
		$out['select']['list'] = $l;
		$out['result']='OK';
		stop($out);
		break;

	default:
		stop(array(
		'result'=>'ERROR',
		'desc'=>"неверные данные
			go=$_REQUEST[go]
			do=$_REQUEST[do]
			id=$_REQUEST[id]"
		));
}

function makeNagiosClients($id=false){
	global $config, $DEBUG, $opdata, $NAGIOS_ERROR;
	$ok = false;
	$q = new sql_query($config['db']);
	$t = new Trace();
	$filter = ($id)? "AND c.id=$id" : "";
	$clients = $q->select("
		SELECT c.*, d.id as device, p.id as port 
		FROM map c, devices d, devports p 
		WHERE c.type='client' $filter AND (c.hostname='' OR c.service='') AND
		d.node1=c.id AND d.type='client' AND p.device=d.id AND p.number=1
		ORDER BY address
	");
	if($clients) foreach($clients as $i=>$c) {
		if($c['address']=='' || $c['name']==''){
			if($DEBUG>0) log_txt(__FUNCTION__.": client[{$c['id']}] name:{$c['name']} address:{$c['address']}");
			continue;
		}
		$ch = $t->traceChain($c['port']);
		$begin = reset($ch); $end = end($ch);
		if($begin['type']!='client') { $begin = end($ch); $begin = end($ch); }
		foreach(array('device','port','modified','gtype','length','mrtg') as $k=>$v) unset($c[$v]);
		if($begin['type']!='client') {
			$c['note'] = "client[{$c['id']}] ошибка данных";
			$ok[] = $c;
			continue;
		}
		if($begin['id'] == $end['id']){
			$c['note'] = "ОШИБКА - пустая цепочка!";
			$ok[] = $c;
			continue;
		}
		if($end['type']!='switch'){
			$c['note'] = "ОШИБКА - цепочка не заканчивается на свиче!";
			$ok[] = $c;
			continue;
		}
		$switch = $q->get('devices',$end['device']);
		if($switch['ip']=='' || !preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/',$switch['ip']) || $switch['community']==''){
			$c['note'] = "Свич тупой!";
			$ok[] = $c;
			continue;
		}
		$addr = preg_replace('/^(ул\.|пер\.|пл\.|пр\.|мн\.|м-н\.|пс\.|п\.)/','client ',$c['address']);
		$ng_url="do=service_add&objects=address:{$switch['ip']}&service=port:{$end['number']},community:{$switch['community']},address:".urlencode($addr)."&realsave=yes";
		$ng = get_nagios($ng_url,'data');
		if(count($ng)==0){
			$c['note'] = "ОШИБКА - {$NAGIOS_ERROR['desc']}";
			$ok[] = $c;
			continue;
		}else{
			$c['note'] = "OK ответ: ".arrstr($ng);
			if(isset($ng['host']) && isset($ng['service'])) $q->update_record('map',array('id'=>$c['id'],'hostname'=>$ng['host'],'service'=>$ng['service']));
			$c['hostname'] = $ng['host'];
			$c['service'] = $ng['service'];
			$ok[] = $c;
		}
	}
	return $ok;
}
?>
