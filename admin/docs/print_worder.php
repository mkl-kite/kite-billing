<style type="text/css">
.header {
    font-family: serif;
    font-weight: bold;
    font-size: 14pt;
    text-align: center;
   	position:relative;
}
.header i {
    font-size: 11pt;
}
.header h3 {
	margin-bottom:0;
}
.header B {
	font-size: 18pt;
}
.banner {
    font-family: tahoma;
	float: left;
}
.banner ul {
	list-style-type: decimal;
	list-style-position: inside;
	padding: 0;
	margin-top:3mm;
}
.banner li {
	font: 9pt serif;
	margin-top: 8px;
	border-bottom: 1px solid #000;
}
.banner > h3 {
	font-size: 10pt;
    font-weight: bold; 
}
.boxname {
	margin: 2mm 0 0 0.5cm;
	float: left;
	padding: 5px 10px 5px 10px;
}
.box {
	width: 7cm;
	padding: 0;
	float:left;
}
.box p {
	margin-top:3mm;
}
TABLE  {
	font-family: tahoma;
	font-size: 8pt;
    text-align: left; 
	text-decoration : none;
	border-style: solid;
	border-color: #000;
	border-width: 1px;
	border-spacing: 0;
	width: 100%; 
}
TABLE.t2  { 
	margin-top: 5mm;
	width: 100%; 
	border: 0; 
}
TR.rez0  { 
	border-top: solid; 
	border-color: #000;
	border-width:1px;
}
TR.rez1  { 
	border-top: solid; 
	border-color: #000; 
	border-width:1px;
}
TD {
	text-align: left;
	padding: 5px 10px 5px 10px; 
}
.t2 TD {
    white-space: normal;
	vertical-align: top;
    font-family: tahoma;
    color: #000;
    border-bottom: #000 solid 1px;
    border-right: none;
    border-left: #000 solid 1px;
}
.t2 TD:first-child  {
    text-align: center; 
    border-left: none;
}
.t2 THEAD TR:first-child TD {
    border-top: #000 solid 1px;
}
.t2 THEAD TD {
    border-top: #000 solid 1px;
    font-weight: bold;
    vertical-align: middle;
    text-align: center; 
}
.t2 td b {
    white-space: nowrap;
}
.t2 td.type {
    text-align: center;
}

h1, h2 {
	padding:10px 20px 0
}
.banner {
    text-align: left; 
	float: left;
	background: #fff;
	display: inline;
}
.banner em {
	display:inline-block;
	position:relative;
    font-family: tahoma; 
	font-size: 8pt;
	padding:0 3px 0 0;
	top:2mm;
	padding: 1mm;
	background:#fff;
}
.banner s {
	margin-left:3mm;
    font: 11pt tahoma; 
    text-decoration:none;
}
.banner p {
	border-bottom:1px solid #000;
	font-size: 9pt;
	margin:0 0 3mm 0;
}
.signbox ul {
	list-style-type: none;
	list-style-position: none;
}
.signbox em {
	top:2mm;
}
.sign {
	padding: 0 5mm;
	width: 7cm;
}
.sign p:first-child {
	text-align: center;
	border-bottom:none;
	margin-bottom: 4mm;
}
#chef { 
	float: right;
	margin-top:1cm;
	width:7cm;
}
IMG.logo {
	position: absolute;
	top: 2mm;
	left: 2mm;
	height: 21mm;
	width: 35mm;
	z-index:1;
}
HTML  {	
	margin: 0;
	padding: 0;
}
BODY  {	
	background-color: #	fff; 
	margin: 0;
	padding: 0;
	width:100%;
	height:100%;
	font: 12pt sans-serif;
}
</style>

<?php 
$woid=(isset($_REQUEST['id']))? numeric($_REQUEST['id']) : '';

$my_date=date("Y-m-d",time());
$my_time=date("H:i",time());
include_once("classes.php");
$q = new sql_query($config['db']);
$worder=$q->get("workorders",$woid);
if(isset($worder['manager']) && $worder['manager']>0) $manager = $q->get("employers",$worder['manager']);
elseif (isset($config['manager']) && $config['manager']>0) $manager = $q->get("employers",$config['manager']);
else $manager = '';
$employers = $q->select("SELECT e.eid,e.fio FROM employers e, workpeople wp WHERE eid=employer AND worder=".$woid." ORDER BY e.fio");

if ($woid=="") {
	exit(0);
	}
?>
<?php 

# Получение данных о наряде
?>
<div class="header">
	<IMG class="logo" src="https://<?php echo "localhost".preg_replace('/\/[^\/]+$/','/',$_SERVER['REQUEST_URI']); ?>pic/logo.png">
	<h3><?php echo COMPANY_NAME; ?>&trade; &nbsp;&nbsp;&nbsp; <?php echo FIRMNAME; ?></h3>
	<B>Наряд &#8470;&nbsp;<?php echo $woid; ?></B><br>
	от &nbsp;<I><?php echo cyrdate($worder['prescribe'],'%d %B %Y'); ?></I>
</div>
<BR>

<div class="banner">
<div class="box" style="margin-top:2mm">
	<p style="white-space:nowrap"><em>Руководитель работ:</em> <s><?php echo shortfio($manager['fio']); ?></s></p>
</div>
<br>
<div id="auto" class="box">
	<p><em>Автомобиль:</em></p>
</div>
</div>

<div class="banner" style="float:right">
<div class="boxname">Исполнители: </div>
<div class="box">
<ul><?php 
foreach($employers as $i=>$row) echo "<li>".shortfio($row['fio'])."</li>";
?>
</ul>
</div>
</div>
<BR style="clear:both">
<?php 

# Получение данных о заданиях
$jobs = $q->select("
	SELECT cp.cid, c.type, c.uid, c.user, r.r_name as rayon, c.address, c.phone, c.content, c.fio, cp.note, cp.begintime
	FROM `claimperform` as cp, `claims` as c LEFT OUTER JOIN rayon as r ON r.rid=c.rid
	WHERE cp.cid=c.unique_id and cp.woid='$woid'
	ORDER BY cp.begintime
");
?>
<TABLE class="t2">
<THEAD>
<TR>
	<TD>&#8470;<BR>п/п</TD>
	<TD>ID</TD>
	<TD width="35%">Адрес, тел. ФИО</TD>
	<TD>Тип работ</TD>
	<TD width="45%">Описание</TD>
	<TD>Подпись<br>клиента</TD>
</TR>
</THEAD>
<TBODY>
<?php 
foreach($jobs as $i=>$row) { ?>
<TR>
	<TD class="data_first" align="center"><?php echo $i+1; ?></TD>
	<TD><?php echo $row['cid']; ?></TD>
	<TD><?php echo "<B>".$row['rayon']." &ensp;".$row['address']."</B><BR>".$row['phone']."<BR><I>".$row['fio']."</I>"; ?></TD>
	<TD class="type"><?php echo $claim_types[$row['type']]; ?><BR>
	<?php echo cyrdate($row['begintime'],'%H:%M'); ?></TD>
	<TD><?php 
		$txt = array();
		if($row['type']!=4) {
			if($row['user']!=''){
				$u = $q->select("SELECT * FROM users WHERE user='{$row['user']}'",1);
				$txt[] = "login: {$row['user']}".($u? " &emsp; password: {$u['password']}" : "");
			}elseif($row['uid']>0){
				$u = $q->get('users',$row['uid']);
				$txt[] = "login: {$u['user']} &emsp; password: {$u['password']}";
			}elseif($row['fio']!='') $txt[] = "login: ".fiotologin($row['fio']);
		}
		if($row['content']!='') $txt[] = $row['content']; 
		if($row['note']!='') $txt[] = $row['note']; 
		echo implode('<BR>',$txt); ?>
	</TD>
	<TD></TD>
	</TR><?php 
}
?>
</TBODY>
</TABLE>
<BR style="clear:both">

<div class="banner" style="width:7cm">
	<p><em>Выдал <?php echo shortfio($manager['fio']); ?></em></p>
</div>
<BR style="clear:both"><BR><BR><BR>

<div class="banner signbox">
<h3>Подписи исполнителей о выполнении наряда: </h3>
<div class="box">
<ul><?php 
foreach($employers as $i=>$row) echo "<li><em>".shortfio($row['fio'])."</em></li>";
?>
</ul>
</div>
</div>

<div class="banner" style="width:10cm;float:right">
	<p><em>Наряд выполнен в:</em></p>
</div>

<div class="banner sign" id="chef">
<p>Подпись</p>
<p><em>Руководитель работ:</em></p>
</div>
