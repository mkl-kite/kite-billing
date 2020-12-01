<style type="text/css">
h1, h2 {
	padding:10px 20px 0
}

.header {
    font-family: tahoma; 
    font-weight: bold; 
    font-size: 14pt; 
    text-align: center; 
}
DIV.boxname {
    font-family: tahoma; 
	font-size: 10pt;
    font-weight: bold; 
    padding-top: 3.5mm;
}
TABLE.report  {
	font-family: tahoma;
	font-size: 8pt;
	text-decoration : none;
	width:100%;
}
TABLE.report THEAD TR:first-child TD {
	border-top: black solid 1px;
}
TABLE.report TR TD:last-child {
	border-right: none;
}
TABLE.report THEAD TD {
	overflow: hidden;
}
TABLE.report TD {
/*    white-space: nowrap; */
	padding: 3px 5px 3px 5px;
	border-right: black solid 1px;
	border-bottom: black solid 1px;
}
.reportheader DIV {
	display:inline;
	float: right;
	margin-right: 5mm;
}
.reportheader DIV:first-child {
	margin: 0;
}
#auto {
	width: 8cm;
	overflow: hidden;
}
#auto em {
    font-family: tahoma; 
	font-size: 8pt;
	padding:0 3px 0 0;
}
#auto p {
	line-height: 0.5;
	border-bottom:1px solid #000;
	white-space:nowrap;
	text-align:left;
}
#auto p span {
	position:relative;
	bottom: 1mm;
	margin-left: 3mm;
	margin-bottom: 3mm;
}

@media screen {
.printbtn {
	position: absolute;
	top: 0.5cm;
	left: 0.5cm;
	height: 0.5cm;
	width: 1.3cm;
	}
.printbtn A {
	text-decoration: blink;
	}
}
</style>
<script language="JavaScript">
$(document).ready(function() {
	var bg = $('body').css('background-color');
	$('#auto em').css('background-color',bg);
})
</script>
<?php
include_once("classes.php");
include_once("devices.cfg.php");
include_once("ports.cfg.php");

$query = new sql_query($config['db']);
$dt = array(
	'cable' => 'жила',
	'switch' => 'порт',
	'onu' => 'порт',
	'server' => 'порт',
	'patchpanel' => 'порт',
	'divisor' => 'жила',
	'splitter' => 'жила',
	'wifi' => 'порт'
);


$devid = (key_exists('id',$_REQUEST))? numeric($_REQUEST['id']) : 0;
$nodeid = (key_exists('nodeid',$_REQUEST))? numeric($_REQUEST['nodeid']) : 0;
if(!$nodeid && $devid) {
	$device = $query->get('devices',$devid);
	if($device && $device['type']!='cable') $nodeid = $device['node1'];
}

$now=now();
$my_date=date('Y-m-d');

if(!$devid>0 || !$nodeid>0) {
	$out=array('result'=>'ERROR','desc'=>"Ошибка запроса:\ndevid={$devid}\nnodeid={$nodeid}");
	stop($out);
}

$node = $query->select("SELECT * FROM map WHERE id={$nodeid}",1);

if($go=='nodes') $filter = "d.node1={$nodeid} OR d.node2={$nodeid}";
else $filter = "d.id={$devid}";

$colors = $q->fetch_all("SELECT distinct color, rucolor FROM devprofiles ORDER BY color",'color');
foreach($colors as $k=>$c) $pcolor[$k] = preg_replace(array('/ый|ой/','/ий/'),array('ая','яя'),$c);
foreach($colors as $k=>$c) $bcolor[$k] = preg_replace(array('/ый/','/ий/'),array('ой','ей'),$c);

$devs = $query->select("
	SELECT  d.id, d.type, d.subtype, d.ip, d.community, d.object, d.name, d.colorscheme,
		n1.address as node1, n2.address as node2, d.numports, d.bandleports, d.note
	FROM devices d
		LEFT OUTER JOIN map n1 ON d.node1 = n1.id
		LEFT OUTER JOIN map n2 ON d.node2 = n2.id
	WHERE $filter
	ORDER BY d.type
");

foreach($devs as $i=>$dev) {
	$ff = $tables['devices']['fields_filter']($dev);
	$dev = array_diff_key($dev,$ff);
	foreach($dev as $k=>$v) $labels[$k] = $tables['devices']['fields'][$k]['label'];
	if($dev['type']=='cable') {
		$labels['node1']='начало'; 
		$labels['node2']='конец'; 
		$dev['subtype']=$config['map']['cabletypes'][$dev['subtype']];
	}else unset($dev['node1']);
	if(array_search($dev['type'],array('cable','divisor','splitter'))!==false) {
		$labels['numports'] = 'кол-во жил';
		if($dev['bandleports']==0 || $dev['bandleports']>=$dev['numports']) unset($dev['bandleports']);
	}
	$dev['type'] = $devtype[$dev['type']];
	foreach($dev as $k=>$v) {
		if($v && $k!='id' && $k!='object') $devices[$i][$labels[$k]] = $v;
	}
}
?>

<div class="header">
	<B>узел: <FONT size="+2"><?php echo $node['address']; ?></FONT></B><br>
</div>
<?php
$tr = new Trace();

foreach($devices as $i=>$device) {
	$out=array();
	$res = $query->query("
		SELECT 
			p1.id, p1.number, p1.porttype, p1.color, p1.coloropt, p1.bandle, p1.note,
			p2.id as p2id, p2.number as linkport, p2.color as linkcolor, p2.coloropt as linkcoloropt, p2.bandle as linkbandle,
			d2.id as devid, d2.type, d2.subtype, d2.name, d2.numports, d2.ip, d2.node1, d2.node2, m1.address as a1, m2.address as a2
		FROM devports p1 
			LEFT OUTER JOIN devports p2 ON p1.link=p2.id 
			LEFT OUTER JOIN devices d1 ON p1.device=d1.id 
			LEFT OUTER JOIN devices d2 ON p2.device=d2.id 
			LEFT OUTER JOIN map m1 ON d2.node1=m1.id
			LEFT OUTER JOIN map m2 ON d2.node2=m2.id
		WHERE p1.node={$nodeid} AND p1.device={$devs[$i]['id']}
		ORDER BY p1.number, p1.node, p1.porttype
	");
	
	while($p = $res->fetch_assoc()) {
		foreach($p as $k=>$v) $out[$p['porttype']][$p['number']][$k]="$v";
		$out[$p['porttype']][$p['number']]['color'] = @$pcolor[$p['color']];
		$out[$p['porttype']][$p['number']]['bandle'] = @$pcolor[$p['bandle']];
		$out[$p['porttype']][$p['number']]['linkcolor'] = @$pcolor[$p['linkcolor']];
		$out[$p['porttype']][$p['number']]['linkbandle'] = @$pcolor[$p['linkbandle']];
		// добавляем конечную точку
		$d=false; $a = array();
		if($p['linkport']>0 && ($cd = $tr->capdevices($p['id'],1))) $d = $cd['end'];
		if($d && ($ep = $cd['ports']['end']) && $ep['device']!=$p['devid']) {
			if(isset($dt[$d['type']]) && $dt[$d['type']] == 'порт') $a[] = "порт {$ep['number']}";
			elseif(isset($dt[$d['type']])) $a[] = $pcolor[$ep['color']].(($ep['coloropt']=='dashed')? "+":"")." жила ".($ep['bandle']? " в ".$bcolor[$ep['bandle']]:"");
			if($ep['node'] != $nodeid) $a[] = $cd['nodes']['end']['address']; 
			$out[$p['porttype']][$p['number']]['endpoint'] = get_devname($d,$cd['nodes']['end']['id'],true,64)." ".implode(' ',$a);
 		}else{
			$out[$p['porttype']][$p['number']]['endpoint'] = "";
 		}
		$out[$p['porttype']][$p['number']]['name'] = ($p['type'])? get_devname($p,$nodeid,true,64) : "";
	}
?>

<BR>
<div class="reportheader">
<div id="auto">
<?php foreach($device as $k=>$v) echo "<p><em>{$k}:</em><span>{$v}</span></p>"; ?>
</div>
<div class="boxname">Устройство: </div>
</div>
<div style="clear:both; height: 25px;"></div>

<TABLE class="report" CELLSPACING=0 class="t2">
<THEAD>
<TR>
	<TD colspan="4">устройство</TD>
	<TD colspan="4">соединение</TD>
	<TD>Конечная точка</TD> 
</TR>
<TR>
	<TD>&#8470;<BR>порта</TD>
	<TD>цвет</TD>
	<TD>Связка</TD> 
	<TD>Прим.</TD>
	<TD>&#8470;<BR>порта</TD>
	<TD>цвет</TD>
	<TD>Связка</TD>
	<TD>Устройство</TD> 
	<TD></TD> 
</TR>
</THEAD>
<TBODY>
<?php
foreach($out as $k=>$ptype) {
?>
<TR>
	<TD colspan="9"><?php echo $porttype[$k]; ?></TD>
</TR> 
<?php
	foreach($ptype as $k=>$port) {
?>
<TR>
	<TD><?php echo $port['number']; ?></TD>
	<TD><?php echo $port['color'].(($port['coloropt']=='dashed')? " +":""); ?></TD>
	<TD><?php echo $port['bandle']; ?></TD>
	<TD><?php echo $port['note']; ?></TD>
	<TD><?php echo $port['linkport']; ?></TD>
	<TD><?php echo $port['linkcolor'].(($port['linkcoloropt']=='dashed')?" +":""); ?></TD>
	<TD><?php echo $port['linkbandle']; ?></TD>
	<TD><?php echo $port['name']; ?></TD>
	<TD><?php echo $port['endpoint']; ?></TD>
</TR>
<?php
	}
}
?>
</TBODY>
</TABLE>
<BR><BR><BR>
<?php
}
?>
