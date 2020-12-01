<?php
include_once("utils.php");
include_once("classes.php");
if (!$user) return;
if(!isset($q)) $q = new sql_query($config['db']);
if(!isset($q1)) $q1 = new sql_query($config['db']);

?>
<CENTER>
<?php
include("ym_menu.php");

$t_start_time = date("Y-m-d G:i:s", mktime(0,0,0,$nm,1,$ny));
$t_stop_time = date("Y-m-d G:i:s", mktime(23,59,59,$nmt,date("t",mktime(12,0,0,$nmt,15,$nyt)),$nyt));

$res = $q->select("
	SELECT sum(acctsessiontime) as time_on_t, 
		sum(inputgigawords << 32 | acctinputoctets) as inbytes_t, 
		sum(outputgigawords << 32 | acctoutputoctets) as outbytes_t, 
		count(username) as count, 
		sum(billing_minus) as money_t 
	FROM radacct 
	WHERE username='".$user."' AND 
		(acctstarttime between '$t_start_time' and '$t_stop_time' OR acctstoptime is NULL)
",1);
?>
<TABLE class="usrinfo" BORDER=0 CELLSPACING=0 CELLPADDING=0>
<TR><TD>
	<A href="users.php?go=usrchart&user=<?php echo $user; ?>">
	<IMG class="normal" SRC="charts/traffic.php?user=<?php echo $user; ?>" border="0" width="700" height="200"></A>
</TD><TD class="common">
<?php
print("
всего соединений: <b>{$res['count']}</b><BR>
времени: <b>".sectime('h:m:s', $res['time_on_t'])."</b><BR>
получено Mb: <b>".sprintf("%.1f",$res['outbytes_t']/MBYTE)."</b><BR>
отправленно Mb: <b>".sprintf("%.1f",$res['inbytes_t']/MBYTE)."</b><BR>
всего Mb: <b>".sprintf("%.1f",($res['outbytes_t']+$res['inbytes_t'])/MBYTE)."</b><BR>
потраченно денег: <b>".sprintf("%.2f",$res['money_t'])."</b><BR>
добавленно денег: <b>".sprintf("%.2f",@$res_pop['money_t'])."</b><BR>\n");
?>
</TD></TR></TABLE><BR>
  <HR>
  <TABLE class="normal">
   <THEAD>
   <TR>
    <TD>Подключился</TD>
    <TD>Отключился</TD>
    <TD>Время</TD>
    <TD>Принял Мб</TD>
    <TD>Послал Мб</TD>
    <TD>Устройство</TD>
    <TD>НАС:Порт</TD>
    <TD>ip</TD>
    <TD>Пакет</TD>
    <TD>Пр-л</TD>
    <TD>До снятия</TD>
    <TD>Снято</TD>
   </TR>
   </THEAD>
   <TBODY>
<?php
	if($q->query("
		SELECT before_billing, billing_minus, username, nasportid, 
			date_format(acctstarttime, '%d %b %H:%i:%s') as fstart_time, 
			date_format(acctstoptime,'%d %b %H:%i:%s') as fstop_time, 
			acctsessiontime, connectinfo_start, framedipaddress, IF(p.name is not null, p.name, a.groupname) as pname,
			inputgigawords << 32 | acctinputoctets as inbytes, 
			outputgigawords << 32 | acctoutputoctets as outbytes,
			callingstationid, concat(nasipaddress,':',nasportid) as nas, 
			framedprotocol as proto, calledstationid, connectinfo_start
		FROM radacct a LEFT OUTER JOIN packets p ON a.pid=p.pid
		WHERE username='".$user."' and (acctstarttime between '".$t_start_time."' and '".$t_stop_time."')
		ORDER BY acctstarttime
	")){
		while($row = $q->result->fetch_assoc()) {
			if($row['connectinfo_start'] && ($ui = parse_opt82($row['connectinfo_start']))){
				$dev = $q1->select("SELECT name, type, ip, node1 FROM devices WHERE macaddress='{$ui['device']}'",1);
				$addr = ($dev)? $q1->select("SELECT address FROM map WHERE id='{$dev['node1']}'",4) : "";
				if(!$dev) $info = "Устройство {$ui['device']} отсутствует в базе";
				else $info = "{$addr}\r{$devtype[$dev['type']]} {$dev['name']}\r".(($dev['type']=='onu')? $ui['device'] : $dev['ip'])."\rпорт ".$ui['port'];
			}elseif($row['proto'] == 'PPP') $info = "подключение PPPoE";
			else $info = "нет данных";

			print("
			<TR>
			<TD class=\"start\">{$row['fstart_time']}</TD>
			<TD class=\"start\">{$row['fstop_time']}</TD>
			<TD class=\"stime\">".($ttime = sectime('h:m:s', $row['acctsessiontime']))."</TD>
			<TD class=\"traf\">".sprintf("%.1f",$row['outbytes']/MBYTE)."</TD>
			<TD class=\"traf\">".sprintf("%.1f",$row['inbytes']/MBYTE)."</TD>
			<TD class=\"csid\" title=\"$info\">{$row['callingstationid']}</TD>
			<TD class=\"ip\">{$row['nas']}</TD>
			<TD class=\"ip\">{$row['framedipaddress']}</TD>
			<TD class=\"pname\">".get_explaine_packet($row['pname'])."</TD>
			<TD class=\"proto\">{$row['proto']}</TD>
			<TD align=\"right\">".sprintf("%.2f",$row['before_billing'])."</TD>
			<TD align=\"right\">".sprintf("%.2f",$row['billing_minus'])."</TD>
			</TR>
			");
		}
	}
?>
</TBODY>
<TFOOT>
	<TR><TD colspan="12" style="height:10px"></TD></TR>
</TFOOT>
</TABLE>
</CENTER>
