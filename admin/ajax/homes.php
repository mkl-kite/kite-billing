<?php
include_once("map.cfg.php");
include_once("rayon.cfg.php");
include_once("geodata.php");
include_once("classes.php");
include_once("form.php");

$in['go'] = (isset($_REQUEST['go']))? strict($_REQUEST['go']) : 'map';
$in['do'] = (isset($_REQUEST['do']))? strict($_REQUEST['do']) : '';
$in['id'] = (isset($_REQUEST['id']))? numeric($_REQUEST['id']) : 0;
if(isset($_REQUEST['id']) && $_REQUEST['id']=='new') $in['id'] == 'new';
$q = new sql_query($config['db']);
$form = new Form($config);

switch($in['do']){

	case 'get':
		$fc = getFeatureCollection();
		if(!is_array($fc)) $fc = array();
		$out = array(
			'onuSignalURL'=>ONU_SIGNAL_URL,
			'devtypes'=>array_slice($devtype,1),
			'cablecolors'=>$config['map']['cablecolors'],
			'rayons'=>get_rayons(),
			'tfade'=>$config['fading']['numstyle'],
			'GeoJSON'=>$fc
		);
		$out['nagiosURL'] = (ICINGA_URL)? '' : NAGIOS_URL;
		stop($out);
		break;

	case 'modify':
		if(!($m = save_geodata($GeoJSON))) {
			log_txt("ошибка при записи ГеоДанных \$GeoJSON=".sprint_r($GeoJSON));
			stop(array('result'=>'ERROR','desc'=>'Ошибка при записи ГеоДанных!'));
		}
		$c = getFeatureCollection($m);
		stop(array('result'=>'OK','desc'=>'Данные записаны!','feature'=>$c['features'][0]));
		break;

	case 'add':
	case 'new':
		if($m=save_new_geodata($GeoJSON)) {
			$tables['map']['name']='map';
			$tables['map']['id']=$m;
			$tables['map']['do']='firstsave';
			stop($form->get($tables['map']));
		}else{
			stop(array('result'=>'ERROR','desc'=>'Отсутствуют Гео Данные!'));
		}
		break;

	case 'edit':
		$tables['map']['name']='map';
		$tables['map']['id']=$in['id'];
		$tables['map']['fields']['name']['type']='hidden';
		if(!($o = $q->get('map',$in['id']))) stop(array('result'=>'ERROR','desc'=>'Объект не найден в базе!'));
		if($o['type'] == 'home')
			$tables['map']['style'] .= 'max-width:735px';
		stop($form->get($tables['map']));
		break;

	case 'save':
	case 'firstsave':
		$tables['map']['name']='map';
		stop($form->save($tables['map']));
		break;

	case 'modifyfield':
		$in['field'] = (isset($_REQUEST['field']))? strict($_REQUEST['field']) : false;
		$in['table'] = (isset($_REQUEST['table']))? strict($_REQUEST['table']) : false;
		$in['n'] = (isset($_REQUEST['n']))? numeric($_REQUEST['n']) : false;
		$func = 'modify_'.$in['field'];
		if(!function_exists($func)) stop(array('result'=>'ERROR','desc'=>"Функция не найдена!"));
		$r = $func($in);
		if(!$r) stop(array('result'=>'ERROR','desc'=>"Ошибка при изменении {$in['field']}!"));
		$out = array('result'=>'OK');
		if(is_array($r)) $out = array_merge($out, $r);
		stop($out);
		break;

	case 'remove':
		if($o = $q->select("SELECT * FROM map WHERE id={$in['id']}",1)){
			$out = $form->confirmForm($in['id'],'realremove',"{$o['address']}<BR>Вы действительно хотите удалить этот объект?");
			$out['form']['fields']['table'] = array('type'=>'hidden','value'=>'map');
			$out['form']['fields']['go'] = array('type'=>'hidden','value'=>'stdform');
			stop($out);
		}else{
			stop(array('result'=>'ERROR','desc'=>'Указанный объект не найден в базе!'));
		}
		break;

	case 'delete':
		$ids=preg_split('/,/',$_REQUEST['ids']);
		foreach($ids as $k=>$v) $ids[$k]=numeric($v);
		if($d = deleteFeatures($ids)) {
			$out = array('result'=>'OK','delete'=>array('objects'=>$d),'desc'=>'Данные удалены!');
			if(isset($modified)) $outp['modify']['GeoJSON'][] = getFeatureCollection($modified);
			stop($out);
		}else{
			stop(array('result'=>'ERROR','desc'=>'Указанные бъекты не найдены в базе!'));
		}
		break;

	case 'get_users':
		$home=$q->select("SELECT * FROM map WHERE id={$in['id']}",1);
		$address = $home['address'];
		$rayon = $home['rayon'];
		$out['result']='OK';
		$users=$q->select("
			SELECT 
				user,
				uid,
				address,
				CAST(SUBSTRING(address,LOCATE('/',address)+1) as UNSIGNED) as kv,
				fio as 'Ф.И.О.',
				phone as 'телефон',
				DATE_FORMAT(last_connection,'%d-%m-%Y') as 'последнее подключение',
				if(last_connection<date_add(now(),interval -1 month),3,2) as state
			FROM users
			WHERE (address = '{$address}' OR address like '{$address}/%') AND rid = '{$rayon}' AND
			last_connection>date_add(now(),interval -6 month)
			ORDER BY address
		",'','user');
		$result=$q->select("
			SELECT 
				username as user,
				nasipaddress as 'сервер',
				nasportid as 'порт',
				DATE_FORMAT(acctstarttime,'%d-%m-%Y %H:%i') as 'последнее подключение',
				acctsessiontime as 'продолжительность',
				concat(ROUND((inputgigawords << 32 | acctinputoctets)/1048576,2),'/',
					ROUND((outputgigawords << 32 | acctoutputoctets)/1048576,2)) as 'трафик',
				framedipaddress as ip,
				callingstationid as mac,
				1 as state
			FROM radacct
			WHERE acctstoptime IS NULL AND username in ('".implode('\',\'',array_keys($users))."')
		",2);
		foreach(array_replace_recursive($users,(count($users)>0 && $result)? $result:array()) as $k=>$v) {
			$out['users'][] = $v; // для сохранения сортировки по kv
		}
		stop($out);
		break;

	case 'auto_address':
		stop(auto_address());
		break;

	case 'reloadservices':
		$val=(key_exists('value',$_REQUEST))?$_REQUEST['value']:'';
		$r = get_service_names(array("hostname"=>$val));
		$out['result']='OK';
		$out['select']['list']=$r;
		$out['target']='service';
		stop($out);
		break;

	case 'homeobject':
		$table = 'homes';
		if(!isset($tables[$table])) include_once("{$table}.cfg.php");
		if(!isset($tables[$table])) stop(array('result'=>'ERROR','desc'=>"Ошибка запроса!"));
		$id = isset($_REQUEST['id'])? numeric($_REQUEST['id']) : false;
		if(!$id || !($home = $q->get($table,$id))) stop(array('result'=>'ERROR','desc'=>"Запись о доме не найдена!"));
		if(!($object = $q->get('map',$home['object'],'','id'))) stop(array('result'=>'ERROR','desc'=>"Объект на карте не найден!"));
		stop(array('result'=>'OK','object'=>$object));
		break;

	default:
		log_txt("Неизвестная команда\n REQUEST = ".sprint_r($_REQUEST));
		stop(array('result'=>'ERROR','desc'=>'Неизвестная команда!'));
}

function get_houses() {
	global $opdata, $config;
	$q=new sql_query($config['db']);
	return $q->fetch_all("
		SELECT DISTINCT 
			trim(substr(address,1,IF(locate('/',address)-1>0,locate('/',address)-1,CHAR_LENGTH(address)))) as address,
			trim(substr(address,1,IF(locate('/',address)-1>0,locate('/',address)-1,CHAR_LENGTH(address)))) as adr
		FROM users 
		ORDER BY address
	",'address');
}
?>
