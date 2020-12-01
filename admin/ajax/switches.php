<?php
include_once("snmpclass.php");
include_once("switch.cfg.php");

$in['go'] = (array_key_exists('go',$_REQUEST))? strict($_REQUEST['go']) : 'sw';
$in['do'] = (array_key_exists('do',$_REQUEST))? strict($_REQUEST['do']) : '';
$in['id'] = (array_key_exists('id',$_REQUEST))? numeric($_REQUEST['id']) : 0;
$in['uid'] = (array_key_exists('uid',$_REQUEST))? numeric($_REQUEST['uid']) : 0;
$in['ip'] = (array_key_exists('ip',$_REQUEST))? str($_REQUEST['ip']) : '';

$q = new sql_query($config['db']);

if($in['id']>0 && $in['uid']==0) {
	if($in['user'] = $q->select("SELECT * FROM users WHERE uid='{$in['id']}'",1)) {
		$in['uid'] = $in['id'];
	}
}
$javascript = "
<SCRIPT language=\"JavaScript\">
$(document).ready(function() {
	if(typeof(ldr) !== 'object') ldr = $.loader()
	$('div.switch[switch]').on('click','.port',function(e){
		var p = $(this).attr('port'), sw = $(this).parents('.switch[switch]').attr('switch')
		window.open('references.php?go=switches&do=show&id='+sw+'&port='+p,'_self')
	})
})
</SCRIPT>
";

switch($in['do']){

	case 'list':
		$t = $tables['devices'];
		$t['table_header'] = $header = "Список свичей";
		$t['target'] = 'html';
		$t['module'] = "switches";
		$t['table_query'] = "
			SELECT d.id, d.name, d.ip, d.community, m.address as node, d.numports, d.note 
			FROM devices d, map m 
			WHERE m.id = d.node1 AND d.type='switch' AND ip!='' 
			ORDER BY INET_ATON(ip)
		";

		$table = new Table($t);
		if(@$_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest') {
			stop(array('result'=>'OK','table'=>$table->get()));
		}else{
			echo $table->getHTML();
		}
		break;

	case 'show':
		$in['ip'] = $q->select("SELECT ip FROM devices WHERE id='{$in['id']}'",4);

	case 'get_fdb':
		if(!preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/',$in['ip'])) {
			stop(array('result'=>'ERROR','desc'=>"Нужен ip ({$in['ip']})!"));
		}
		if(!($d = $q->select("SELECT * FROM devices WHERE type='switch' AND ip='{$in['ip']}'",1))) {
			stop(array('result'=>'ERROR','desc'=>"Свич ip={$in['ip']} не найден!"));
		}
		if($d['community']=='') {
			stop(array('result'=>'ERROR','desc'=>"Для свича {$d['name']} ({$d['ip']}) не определён comminity"));
		}
		$switch = $d;
		$sw = new switch_snmp($switch);
		if($sw->model=='BDCOM' || $sw->model=='C-DATA') {
			if(!set_time_limit(45)) log_txt(__file__.": ERROR set_time_limit is not set!");
			$t = $tables['switch'];
			$t['table_header'] = $header = "{$d['name']}<br><p style=\"display:inline-block;max-width:800px;color:red;font-size:12pt\">обратите внимание, данные обновляются только с интервалом 5 минут!</p>";
			$onu = $sw->onufdb($switch); // [macONU]=>portname
			$aonu = array_flip($onu); // [portname]=>macONU
			$signal = $sw->onusignal($switch); // [macONU]=>signal
			$ports = $sw->ports(null,0); // [portindex]=>array(unit,number,name,type)
			$aports = array_flip($sw->ports()); // [portname]=>portId
			$fdb = $sw->fdb(null); // array(unit,port,vlan,portname,portindex,mac,uid,username,address,online,device)
			foreach($fdb as $i => $row) { // вносим в items индекс fdb по mac
				unset($fdb[$i]['port']);
				if(key_exists($row['device'],$onu)) $ports[$aports[$onu[$row['device']]]]['items'][] = $i;
				else $ports[$row['portindex']]['items'][] = $i;
			}
			foreach($ports as $i => $port){ // проходим по всем портам счича
				$rec = array('unit'=>'','port'=>$port['name'],'onu'=>'','signal'=>'', 'vlan'=>'','mac'=>'','uid'=>'','address'=>'','username'=>'','online'=>'');
				if(isset($port['items'])){
					foreach($port['items'] as $index){
						$rec = array_merge($rec,array_intersect_key($fdb[$index],$rec));
						if($fdb[$index]['device']){
							if(isset($aonu[$port['name']]) && $aonu[$port['name']] != $fdb[$index]['device'])
								$rec['onu'] = $aonu[$port['name']]." <img src=\"pic/warn16.png\" title=\"не совпадает мак ONU\r{$fdb[$index]['device']}\rportname: {$port['name']}\r portonu: {$onu[$fdb[$index]['device']]}\">";
							else $rec['onu'] = $fdb[$index]['device'];
							$rec['signal'] = @$signal[$fdb[$index]['device']];
						}elseif(preg_match('/:/',$port['name'])){
							$devinfo = search_device($aonu[$port['name']]);
							foreach($rec as $k=>$v) if(!$v && isset($devinfo[$k])) $rec[$k] = $devinfo[$k];
							$rec['signal'] = @$signal[$aonu[$port['name']]];
						}
						$data[] = $rec;
					}
				}else{
					if(isset($aonu[$port['name']])){
						$rec = array_merge($rec,search_device($aonu[$port['name']]));
						$rec['signal'] = @$signal[$rec['onu']];
						$data[] = $rec;
					}
				}
			}
			$t['data'] = $data;
		}else{
			$t = $tables['switch'];
			$t['table_header'] = $header = "Таблица МАК-адресов для {$d['name']} ip:{$d['ip']}";
			$sw = new switch_snmp(array('ip'=>$in['ip'],'community'=>$d['community']));
			$t['data'] = $sw->online? $sw->fdb() : array();
		}
		foreach($t['data'] as $i=>$v) {
			if(($u = $t['data'][$i]['username'])!='')
				$t['data'][$i]['username'] = "<a class=\"usrview\" href=\"users.php?go=usrstat&user=$u\" target=\"blank\">$u</a>";
		}
		
		$table = new Table($t);
		if(@$_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest') {
			stop(array('result'=>'OK','table'=>$table->get()));
		}else{
			echo show_switch($sw,(($sw->model=='BDCOM' && $sw->numports<=10)?6:8));
			echo "<DIV style=\"clear:both; height: 1px;\"></DIV>";
			echo $table->getHTML();
		}
		break;

	case 'userport':	// отображение тех данных по пользователю в зависимости от типа подключения
		$sw = new switch_search();
		echo '<CENTER><DIV class="container" style="float:none;width:640px;display:table">';
		$user = $q->get('users',$in['uid']);
		$client = $q->select("SELECT * FROM map WHERE type='client' AND name='{$user['user']}'",1);
		$dtype = ($client)? $config['map']['clientdevtypes'][$client['subtype']] : false;
		$dev = ($client)? $q->select("SELECT * FROM devices WHERE type='$dtype' AND node1='{$client['id']}'",1) : false;
		$port = ($dev)? $q->select("SELECT * FROM devports WHERE device='{$dev['id']}' AND porttype='fiber'",1) : false;
		if(@$client['subtype'] == 'pon' && ($bdcom_data = pon_user_data($user['uid']))){
			echo '<DIV id="bdcominfo"><BR>Данные подключения пользователя<BR>';
			$t = array(
				'name'=>'pon_user_data',
				'class'=>'normal',
				'style'=>"width:100%;white-space:nowrap",
				'fields'=>array(
					'param'=>array(
						'label'=>"параметр",
						'access'=>3
					),
					'value'=>array(
						'label'=>"Значение",
						'access'=>3
					)
				),
				'data'=>$bdcom_data['table']
			);
			$table = new Table($t);
			echo $table->getHTML();
			
			echo '<BR>График уровня сигнала<BR>';
			if(isset($config['zabbix_url'])) { 
				echo "<img src=\"{$config['zabbix_url']}chart2own.php?devip={$bdcom_data['info']['ip']}".
				"&devport={$bdcom_data['info']['port']}&width=510&height=60&legend=1&period=604800\">";
			}elseif(isset($config['rra_url'])) {
				echo "<a href=\"users.php?go=onu_signal&uid=$uid&host={$bdcom_data['info']['ip']}&mac={$bdcom_data['info']['mac']}\">
				<img src=\"{$config['rra_url']}?do=client_graph&host={$bdcom_data['info']['ip']}&mac={$bdcom_data['info']['mac']}\"></a>";
			}
			echo "</DIV>";
			echo '<DIV id="usertech"><BR>Линия подключения пользователя<BR>'.
			'<TABLE class="normal" id="tracetable" WIDTH=100% style="white-space:nowrap">
			<THEAD><TR ALIGN=left><TD>&#8470;</TD><TD>цвет</TD><TD>туба</TD><TD>уст-во</TD><TD>адрес</TD></TR></THEAD>
			<TBODY>';
			$tr = new Trace();
			$line = $tr->clLine($port['id']);
			
			foreach($line as $k=>$v) {
				printf("<TR><TD>%s</TD>",$v['number']);
				printf("<TD style=\"background-color:%s\"></TD>",$v['color']);
				printf("<TD style=\"background-color:%s\"></TD>",$v['bandle']);
				printf("<TD>%s</TD>", $v['device']);
				printf("<TD>%s</TD></TR>\n",$v['address']);
			}
			echo '</TBODY></TABLE></DIV><DIV style="clear:both; height: 10px;"></DIV>';
		}elseif(@$client['subtype'] != 'wifi'){
			if(!($switch = $sw->userport($in['uid'])) && !$client) {
				stop(array('result'=>'ERROR','desc'=>implode('<br>',$sw->errors)));
			}elseif($sw->errors && $client){
				if(!$client)
					show_error(array('result'=>'ERROR','desc'=>"В картах клиент не найден!"));
				elseif(!$dev)
					show_error(array('result'=>'ERROR','desc'=>"Не найдено клиентское устройство!"));
				elseif(!$port)
					show_error(array('result'=>'ERROR','desc'=>"Клиентское устройство не подсоединено!"));
				$tr = new Trace();
				$caps = $tr->capdevices($port['id']);
				foreach(array('begin','end') as $n) {
					if($caps[$n]['type']=='switch' && $caps[$n]['community']=='') $caps[$n]['type'] = 'sw';
					$cp[$caps[$n]['type']] = $n;
				}
				if(isset($cp['switch'])){
					$tmp = array('id','ip','name','numports','macaddress','address','port');
					$switch = $caps[$cp['switch']];
					$swtrace = array(array_intersect_key($switch,array_flip($tmp)));
					$swtrace[0]['port'] = $caps['ports'][$cp['switch']]['number'];
					$switch['port'] = $caps['ports'][$cp['switch']]['number'];
				}else{ $swtrace = array(); }
				$swtraceline = $tr->traceFormat();
			}
			// $swtraceline - линия к пользователю
			if(!$swtrace || !$swtraceline){
				$swtrace = $sw->trace;
				$swtraceline = $sw->traceline;
			}
			$table = new Table(array(
				'name'=>'switch_sequence',
				'header'=>'Последовательность свичей от сервера доступа',
				'table_key'=>'id',
				'style'=>"width:800px;white-space:nowrap",
				'fields'=>array(
					'id'=>(array('type'=>'hidden','access'=>2)),
					'ip'=>(array('label'=>'ip','access'=>2)),
					'name'=>(array('label'=>'Тип','access'=>2)),
					'numports'=>(array('label'=>'кол-во<br>портов','access'=>2)),
					'macaddress'=>(array('label'=>'Мак','access'=>2)),
					'address'=>(array('label'=>'Адрес','access'=>2)),
					'port'=>(array('label'=>'Порт','access'=>2))
				),
				'data'=>$swtrace
			));

			if($swtraceline) $tline = new Table(array(
				'header'=>"Линия к пользователю:",
				'name'=>'dev_sequence',
				'table_key'=>'id',
				'style'=>"width:800px;white-space:nowrap",
				'fields'=>array(
					'id'=>array('type'=>'hidden','access'=>0),
					'port'=>array('label'=>'Порт','style'=>'text-align:center','access'=>0),
					'name'=>array('label'=>'Название','access'=>0),
					'address'=>array('label'=>'Адрес','access'=>0)
				),
				'data'=>$swtraceline
			));

			if(@$_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest') {
				stop(array('result'=>'OK','table'=>$table->get()));
			}else{
				if(isset($javascript)) echo $javascript;
				echo "<DIV style=\"clear:both; height: 1px;\"></DIV>";
				echo "<br><br>".show_switch($switch);
				echo "<br><br><center>".$table->getHTML()."</center>";
				if($swtraceline) echo "<br><br><center>".$tline->getHTML()."</center>";
			}
		}elseif(@$client['subtype'] == 'wifi'){
			if($dev['macaddress']) $mac = $dev['macaddress'];
			else $mac = $q->select("SELECT callingstationid FROM radacct WHERE nasipaddress!='' AND username='{$user['user']}' ORDER BY acctstarttime DESC LIMIT 1",4);
			if($client && $client['connect'])
				$baseStation = $q->select("SELECT d.* FROM devices d, devports p1, devports p2 WHERE p2.device='{$dev['id']}' AND p2.porttype='wifi' AND p2.link=p1.id AND d.type='wifi' AND d.subtype='ap' AND p1.device=d.id",1);
			if($baseStation) {
				echo "<h3>Базовая станция: <a style=\"color:#66f;text-decoration:none\" href=\"http://{$baseStation['ip']}/\" target=\"blank\">{$baseStation['ip']}</a></h3>";
				echo '<BR>График уровня сигнала<BR>';
				if(isset($config['zabbix_url'])) { 
					echo "<img src=\"{$config['zabbix_url']}chart2own.php?devip={$baseStation['ip']}".
					"&devport={$mac}&width=510&height=60&legend=1&period=604800\">";
				}elseif(isset($config['rra_url'])) {
					echo "<a href=\"users.php?go=wifi_signal&uid=$uid&host={$baseStation['ip']}&mac={$mac}\">
					<img src=\"{$config['rra_url']}?do=client_graph&host={$baseStation['ip']}&mac={$mac}\"></a>";
				}
			}else{
				echo "<h3 style=\"color:red\">Отсутствует запись о базовой станции</h3>";
				log_txt("client: ".arrstr($client));
				log_txt("map: ".arrstr($map));
				log_txt("baseStation: ".arrstr($baseStation));
			}
		}
		echo '</DIV></CENTER>';
		break;

	case 'userdata':
		$t = array(
			'table_name'=>'user_tech_data',
			'data'=>pon_user_data($in['uid'])
		);
		$table = new Table($t);
		if(@$_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest') {
			stop(array('result'=>'OK','table'=>$table->get()));
		}else{
			echo $table->getHTML();
		}
		break;

	case 'show_descr':
		$r['ip'] = preg_replace('/[^0-9.]/','',$_REQUEST['ip']);
		$r['community'] = preg_replace('/[^0-9A-Z]/i','',$_REQUEST['community']);
		$r['oid'] = 'descr';
		$sw = new switch_snmp($r);
		$data = $sw->walk($r);
		if(!$data) $data = implode('<br>',$sw->errors);
		stop(array('result'=>'OK','data'=>$data));
		break;

	case 'show_name':
		$r['ip'] = preg_replace('/[^0-9.]/','',$_REQUEST['ip']);
		$r['community'] = preg_replace('/[^0-9A-Z]/i','',$_REQUEST['community']);
		$r['oid'] = 'name';
		$sw = new switch_snmp($r);
		$data = $sw->walk($r);
		if(!$data) $data = implode('<br>',$sw->errors);
		stop(array('result'=>'OK','data'=>$data));
		break;

	case 'show_type':
		$r['ip'] = preg_replace('/[^0-9.]/','',$_REQUEST['ip']);
		$r['community'] = preg_replace('/[^0-9A-Z]/i','',$_REQUEST['community']);
		$r['oid'] = 'type';
		$sw = new switch_snmp($r);
		$data = $sw->walk($r);
		if(!$data) $data = implode('<br>',$sw->errors);
		stop(array('result'=>'OK','data'=>$data));
		break;

	case 'show_ports':
		$r['ip'] = preg_replace('/[^0-9.]/','',$_REQUEST['ip']);
		$r['community'] = preg_replace('/[^0-9A-Z]/i','',$_REQUEST['community']);
		$sw = new switch_snmp($r);
		$data = $sw->ports();
		if(!$data) $data = implode('<br>',$sw->errors);
		stop(array('result'=>'OK','data'=>$data));
		break;

	case 'show_fdb':
		$r['ip'] = preg_replace('/[^0-9.]/','',$_REQUEST['ip']);
		$r['community'] = preg_replace('/[^0-9A-Z]/i','',$_REQUEST['community']);
		$sw = new switch_snmp($r);
		$data = $sw->fdb();
		if(!$data) $data = implode('<br>',$sw->errors);
		stop(array('result'=>'OK','data'=>$data));
		break;

	case 'show_mac':
		$r['ip'] = preg_replace('/[^0-9.]/','',$_REQUEST['ip']);
		$r['community'] = preg_replace('/[^0-9A-Z]/i','',$_REQUEST['community']);
		$r['OID'] = $snmp_conf['oids']['ALL']['mac_on_vlan']['OID'];
		$sw = new switch_snmp($r);
		$data = $sw->walk($r);
		if(!$data) $data = implode('<br>',$sw->errors);
		stop(array('result'=>'OK','data'=>$data));
		break;

	case 'show_onu':
		$r['ip'] = preg_replace('/[^0-9.]/','',$_REQUEST['ip']);
		$r['community'] = preg_replace('/[^0-9A-Z]/i','',$_REQUEST['community']);
		$sw = new switch_snmp($r);
		$data = $sw->onufdb($r);
		if(!$data) $data = implode('<br>',$sw->errors);
		stop(array('result'=>'OK','data'=>$data));
		break;

	case 'show_signal':
		$r['ip'] = preg_replace('/[^0-9.]/','',$_REQUEST['ip']);
		$r['community'] = preg_replace('/[^0-9A-Z]/i','',$_REQUEST['community']);
		$sw = new switch_snmp($r);
		$data = $sw->onusignal($r);
		if(!$data) $data = implode('<br>',$sw->errors);
		stop(array('result'=>'OK','data'=>$data));
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

function search_device($mac) {
	global $DEBUG, $config, $q, $objecttype;
	if(!is_object($q)) $q = new sql_query($config['db']);
	$fld=array('uid'=>0,'username'=>1,'address'=>2);
	$dev = $q->select("SELECT * FROM devices WHERE macaddress='{$mac}'",1);
	$object = $dev? $q->select("SELECT * FROM map WHERE id='{$dev['node1']}'",1) : false;
	$user = ($object['type']=='client')? $q->select("SELECT *,user as username FROM users WHERE user='{$object['name']}'",1) : false;
	if($user) return array_merge(array('onu'=>$mac),array_intersect_key($user,$fld));
	elseif($object) return array('onu'=>$mac,'address'=>$object['address']);
	elseif(!$dev) return array('onu'=>"<font color=\"red\" title=\"ONU не найден в базе\">$mac</font>");
	return array('onu'=>"<font color=\"#a0a\" title=\"непонятный ONU\r".sprint_r($dev)."\">$mac</font>");
}
?>
