<?php
include_once("geodata.php");
include_once("ports.cfg.php");
include_once("classes.php");
include_once("form.php");

$in['go'] = (array_key_exists('go',$_REQUEST))? strict($_REQUEST['go']) : 'ports';
$in['do'] = (array_key_exists('do',$_REQUEST))? strict($_REQUEST['do']) : '';
$in['id'] = (array_key_exists('id',$_REQUEST))? numeric($_REQUEST['id']) : 0;
$in['devid'] = (array_key_exists('devid',$_REQUEST))? numeric($_REQUEST['devid']) : 0;
$in['nodeid'] = (array_key_exists('nodeid',$_REQUEST))? numeric($_REQUEST['nodeid']) : 0;
$in['value'] = (array_key_exists('value',$_REQUEST))? numeric($_REQUEST['value']) : 0;

$q = new sql_query($config['db']);
$t = $tables['ports'];
$t['name']='ports';
$form = new form($config);

switch($in['do']){
	case 'get':
		if($in['devid']>0&&$in['nodeid']>0){
			$portcolor=$q->fetch_all("SELECT distinct color, htmlcolor FROM devprofiles ORDER BY color",'color');
			$fld=array('devid'=>'id', 'devtype'=>'type', 'subtype'=>'subtype', 'devname'=>'name', 'numports'=>'numports', 'ip'=>'ip', 'node1'=>'node1', 'node2'=>'node2', 'a1'=>'a1', 'a2'=>'a2');
			$fld1 = array('a1','a2','node1','node2');
			if(!$portcolor) $portcolor=array();
			$bcolor=bandlecolor($portcolor);
			$out=array();
			$first_pass = true;
			$res = $q->query("
				SELECT 
					p1.id, p1.device, p1.number, p1.node, p1.porttype, p1.color, p1.coloropt, p1.bandle, p1.divide, p1.note,
					p2.number as linkport, p2.color as linkcolor, p2.coloropt as linkcoloropt, p2.bandle as linkbandle,
					d.id as devid, d.type as devtype, d.subtype, d.name as devname, d.numports, d.ip, n.id as nodeid, 
					d.node1, d.node2, a1.address as a1, a2.address as a2
				FROM devports p1 
					LEFT OUTER JOIN devports p2 ON p1.link=p2.id 
					LEFT OUTER JOIN devices d ON p2.device=d.id 
					LEFT OUTER JOIN map n ON p2.node=n.id
					LEFT OUTER JOIN map a1 ON d.node1=a1.id 
					LEFT OUTER JOIN map a2 ON d.node2=a2.id 
				WHERE p1.node={$in['nodeid']} AND p1.device={$in['devid']} 
				ORDER BY p1.number, p1.node, p1.porttype
			");
			$i = 0;
			$delport = array(); $dp = array();
			while($r = $res->fetch_assoc()){
				$i++;
				if($first_pass) {
					$out['node']=$r['node'];
					$out['device']=$r['device'];
					$out['fields']=array_keys(array_diff_key($r,array_flip($fld1)));
					$first_pass=false;
				}
				if($r['linkport']){
					foreach(array_intersect_key($r,$fld) as $k=>$v) $d[$fld[$k]] = $v;
					$r['devname'] = get_devname($d,$in['nodeid']);
				}
				$r = array_diff_key($r,array_flip($fld1));
				$port = array();
				foreach($r as $k=>$v){
					if(($k=='color'||$k=='linkcolor'||$k=='linkbandle')&&key_exists($v,$portcolor)) $v=$portcolor[$v];
					if($k=='bandle'&&key_exists($v,$bcolor)) $v=$bcolor[$v];
					$port[]="$v";
				}
				if(isset($out['ports'][$r['porttype']][$r['number']])){ $dp[]="{$r['number']}:{$r['id']}"; $delport[] = $port[0]; }
				$out['ports'][$r['porttype']][$r['number']] = $port;
			}
			if($delport){
				log_txt("ERROR: задвоенные порты: ".implode(", ",$dp));
			}
			$out['result']='OK';
		}else{
			$out=array('result'=>'ERROR','desc'=>"Ошибка запроса:\ndevid={$in['devid']}\nnodeid={$in['nodeid']}");
		}
		stop($out);
		break;

	case 'connect':
		if($in['id']>0){
			$res=$q->get("devports",$in['id']);
			if($res && ($res['link'] === null || $res['link']==0)){
				$device = $q->get("devices",$res['device']);
				if($res['porttype'] == 'wifi' && $device['subtype']=='ap') {
					stop(array('result'=>'ERROR','desc'=>"Этот порт не может быть присоединён!"));
				}else{
					$in['nodeid']=$res['node'];
					$in['devid']=$res['device'];
					$t['name']=$in['go'];
					if($res['porttype']=='fiber') $t['fields']['allports']['type']='select';
					$t['header']=get_devname($device,numeric($_REQUEST['selectednode']));
					foreach(array('color','coloropt','bandle','note','divide','module') as $fn) unset($t['fields'][$fn]);
					stop($form->get($t));
				}
			}else{
				stop(array('result'=>'ERROR','desc'=>"Порт уже имеет соединение!"));
			}
		}
		break;

	case 'disconnect':
		if($in['id']>0){
			$res=$q->get("devports",$in['id']);
			if(count($res)>0 && $res['link'] !== null){
				stop($form->confirmForm($in['id'],'realdisconnect','Разорвать соединение?'));
			}else{
				stop(array('result'=>'ERROR','desc'=>"Порт не имеет соединения!"));
			}
		}
		break;

	case 'realdisconnect':
		if($in['id']>0){
			$t['name']='devports';
			$t['form_onsave']='out_save_link_result';
			$p = $q->get("devports",$in['id'],'',array('id','device','node','link'));
			stop($form->save($t,array('link'=>""),$p));
		}
		break;

	case 'reloadlink': // перегрузка поля select в форме для соединения портов
		if($in['id']>0){
			$port=$q->select("SELECT p.*, d.type FROM devices d, devports p WHERE d.id=p.device AND p.id={$in['id']};",1);
			$device=$q->select("SELECT * FROM devices WHERE id={$in['value']};",1);
			$colors=$q->fetch_all("SELECT distinct color, rucolor FROM devprofiles ORDER BY color",'color');
			foreach($colors as $k=>$c) $pcolor[$k] = preg_replace(array('/ый|ой/','/ий/'),array('ая','яя'),$c);
			foreach($colors as $k=>$c) $bcolor[$k] = preg_replace(array('/ый/','/ий/'),array('ой','ей'),$c);
			if($port && $device){
				$condition="";
				$in['nodeid']=$port['node'];
				if($port['porttype']!='wifi') $condition=" AND node={$in['nodeid']}";
				if($port['porttype']=='fiber') {
					if($device['type']=='switch') $ptype='cuper';
					else $ptype='fiber';
				}
				if($port['porttype']=='coupler'){
					if($device['type']=='switch') $ptype='cuper';
					else {
						$ptype='coupler';
						if($port['device']==$device['id']) {
							$condition.=" AND id!={$in['id']}";
						}
					}
				}
				if($port['porttype']=='cuper'){
					if($device['type']=='patchpanel') $ptype='coupler';
					elseif($device['type']=='switch' || $device['type']=='server' || $device['type']=='wifi') $ptype='cuper';
					else $ptype='fiber';
				}
				if($port['porttype']=='wifi'){
					$condition='';
					$ptype='wifi';
				}
				$in['devid']=$port['device'];
				$out['target']='link';
				$r=$q->fetch_all("
					SELECT id, number, color, coloropt, bandle, porttype
					FROM devports 
					WHERE device={$in['value']} 
						AND porttype='$ptype' $condition
						AND link is NULL
					ORDER BY number
				");
				$p=array();
				foreach($r as $k=>$v) {
					if($device['type'] == 'cable'){
						$name = $v['number'].". ".$pcolor[$v['color']].
						(($v['coloropt']!='solid')? " с метками " : "")." жила".
						(($v['bandle'])? " в ".$bcolor[$v['bandle']]." тубе" : "");
					}elseif($device['type'] == 'divisor' || $device['type'] == 'splitter'){
						$name = $v['number'].". ".$pcolor[$v['color']]." жила";
					}elseif($device['type'] == 'switch'){
						$name = $v['number'].". порт";
					}else{
						$name = $v['number'].' '.$porttype[$v['porttype']];
					}
					$p[$k] = $name.(($v['color']=='')?"":"<span class=\"color\" style=\"background-color:{$v['color']};border-style:{$v['coloropt']};border-color:{$v['bandle']}\">&nbsp;</span>");
				}
				$out['select']['list']=$p;
				$out['result']='OK';
				stop($out);
			}else{
				stop(array('result'=>'ERROR','desc'=>"Отсутствуют данные в базе!"));
			}
		}
		break;

	case 'edit':
		if($in['id']>0){
			$fdev = array('type', 'subtype', 'name', 'numports', 'colorscheme', 'bandleports', 'ip', 'node1', 'node2');
			$selectednode = (isset($_REQUEST['selectednode']))? numeric($_REQUEST['selectednode']) : 0;
			$res=$q->select("
				SELECT p.*, d.type, d.subtype, d.name, d.numports, d.colorscheme, d.bandleports, d.ip, d.node1, d.node2
				FROM devports p LEFT OUTER JOIN devices d ON d.id=p.device
				WHERE p.id={$in['id']};
			",1);
			foreach($fdev as $v) {
				$dev[$v]=$res[$v];
				unset($res[$v]); // удаляем поля устройства из записи
			}
			if(count($res)>0){
				$in['nodeid']=$res['node'];
				$in['devid']=$res['device'];
				$t['name']='devports';
				$t['header']=get_devname($dev,$selectednode);
				if(($res['porttype']!='fiber') || $dev['type']=='patchpanel') {
					if($dev['type']!='splitter') foreach(array('color','coloropt','bandle') as $n) unset($t['fields'][$n]);
					else foreach(array('coloropt','bandle') as $n) unset($t['fields'][$n]);
				}elseif($dev['bandleports']==0 || $dev['numports']<=$dev['bandleports'])
					unset($t['fields']['bandle']);
				if($dev['type']=='divisor' || $dev['type'] == 'splitter'){
					$t['fields']['porttype']['type'] = 'select';
					$t['fields']['porttype']['list'] = $porttype;
				}
				if($dev['type']!='divisor' && $dev['type']!='splitter') unset($t['fields']['divide']);
				if($dev['type']!='switch') unset($t['fields']['module']);
				unset($t['fields']['linkdevice']);
				unset($t['fields']['link']);
				stop($form->get($t));
			}else{
				stop(array('result'=>'ERROR','desc'=>"Запись не найдена в базе!"));
			}
		}
		break;

	case 'save':
		$form = new form($config);
		$t['name']='devports';
		$dev = $q->select("SELECT d.* FROM devports p, devices d WHERE d.id=p.device AND p.id='{$in['id']}'",1);
		if($dev['type']=='divisor' || $dev['type'] == 'splitter'){
			$t['fields']['porttype']['type'] = 'select';
			$t['fields']['porttype']['list'] = $porttype;
			unset($t['form_triggers']['porttype']);
		}
		// если "allports" - выполнить соединение по всем портам
		if(isset($_REQUEST['id']) && isset($_REQUEST['link']) && isset($_REQUEST['allports']) && $_REQUEST['allports']==1) {
			$new=$form->separate($t['fields']);
			$old=$form->separate($t['fields'],'old_');
			$port = $q->select("SELECT * FROM devports WHERE id={$new['id']}",1);
			$lport = $q->select("SELECT * FROM devports WHERE id={$new['link']}",1);
			$delta = $lport['number'] - $port['number'];
			$sql = "
				SELECT p1.id as id, p2.id as link, p1.link as old
				FROM devports p1, devports p2 
				WHERE p1.node=p2.node AND p1.node={$new['node']} AND p1.porttype=p2.porttype AND
				p1.device = {$port['device']} AND p2.device = {$lport['device']} AND
				p1.number>={$port['number']} AND p2.number=p1.number+{$delta} AND
				(p1.link is NULL OR p1.link=0) AND (p2.link is NULL OR p2.link=0)
				ORDER BY p1.number
			";
			$ports = $q->select($sql);
			if($ports) {
				$out['result'] = 'OK';
				$old=array();
				foreach($ports as $k=>$v) {
					$old[$k] = array('id'=>$v['id'],'link'=>$v['old']);
					unset($ports[$k]['old']);
				}
				foreach($ports as $k=>$v) {
					$res = $form->save($t,$v,$old[$k]);
					if(isset($res['modify']['ports'][0])) 
						$out['modify']['ports'][] = $res['modify']['ports'][0];
					else
						log_txt("ports: allports=1 id={$v['id']} не изменен!");
				}
			}else{
				$out = array('result'=>'ERROR', 'desc'=>"Не найдены свободные порты!");
			}
			stop($out);
		}else{
			stop($form->save($t));
		}
		break;

	case 'delete':
		break;

	case 'getTrace':
		if($in['id']>0){
			$p = $q->get('devports',$in['id']);
			if($p && $p['porttype']=='wifi' && $d = $q->get('devices',$p['device'])){
				$users=$q->select("
					SELECT 
						user,
						uid, 
						u.address,
						fio as 'Ф.И.О.',
						phone as 'телефон',
						DATE_FORMAT(last_connection,'%d-%m-%Y') as 'последнее подключение',
						if(last_connection<date_add(now(),interval -1 month),3,2) as state
					FROM users u, map m, devports p1, devports p2
					WHERE
						u.user = m.name AND m.type='client' AND m.subtype='wifi' AND m.id=p1.node AND
						p1.porttype='wifi' AND p1.link=p2.id AND p2.device = '{$d['id']}' AND 
						last_connection>date_add(now(),interval -6 month)
					ORDER BY u.address
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
					WHERE acctstoptime IS NULL AND username in ('".implode("','",array_keys($users))."')
				",2);
				foreach(array_replace_recursive($users,(count($users)>0 && $result)? $result:array()) as $k=>$v) {
					$out['users'][] = $v; // для сохранения сортировки по kv
				}
				stop($out);
			}else{
				$trace = new Trace();
				stop($trace->get($in['id']));
			}
		}
		break;

	default:
 		foreach($in as $k => $v) $text.=$k."=".$v."<br>";
		stop(array(
		'result'=>'ERROR',
		'desc'=>"</pre><center>неверные данные<br>$text</center><pre>"
		));
}

?>
