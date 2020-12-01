<style type="text/css">
.banner {
    font-family: sans-serif;
    text-align: left; 
	float: left;
	background: #fff;
	display: inline;
}
.banner p {
	border-bottom:1px solid #000;
	font-size: 9pt;
	margin:0 0 3mm 0;
}
.header {
    font-family: serif;
    font-weight: bold;
    font-size: 14pt;
    text-align: center;
   	position:relative;
	margin-bottom:8mm;
}
.header i {
    font-size: 11pt;
}
.header h1, .header h2 {
	margin:3mm 0 0 0;
	padding:0;
}
.header h1:first-child, .header h2:first-child {
	margin:0;
	padding:0;
}
.header h3 {
	margin:0;
	padding:0;
}
.header hr {
	margin:1mm 0 1mm 0;
	padding:0;
}
.header .banner {
	margin-top:2mm;
	font-size: 10pt;
	font-weight:normal;
}
u {
	display:inline-block;
	text-align: center;
	border-bottom:1px solid #000;
	text-decoration: none;
}
.header u {
    font: 11pt sans-serif; 
}
ul {
	list-style-type: decimal;
	list-style-position: outside;
	margin-top:3mm;
	padding: 0 0 0 5mm;
}
li {
	font: 12pt serif;
	margin-top: 2mm;
}
.numeric {
	font:11pt sans-serif;
}
.numeric p {
	margin-left:1.5cm !important;
	font:11pt sans-serif;
	margin:1mm 0 1mm 0;
}
.numeric div { position:relative; }
.numeric div > div {
	position:absolute;
	top:1mm;
	left:1.6cm;
	text-indent:55mm;
	line-height:1.5;
}

p.underline {
	border-bottom:1px solid #000;
	font:11pt sans-serif;
	margin:-1mm 0 2mm 0;
}
p.substr {
	border:none;
	text-align:center;
	font: 7pt serif;
	margin:-2mm 0 2mm 0 !important;
	position:relative;
	z-index:100;
}

h1 {
	font-size: 22pt;
    font-weight: bold; 
}

h2 {
	font-size: 16pt;
    font-weight: bold; 
}

h3 {
	font-size: 10pt;
    font-weight: bold; 
}
.boxname {
	margin: 2mm 0 0 0.5cm;
	float: left;
	padding: 5px 10px 5px 10px;
}
.box {
	padding: 0;
	float:left;
}
.box p:first-child {
	border-bottom:1px solid #000;
	font-size: 9pt;
	margin:3mm;
}

TABLE  {
	font-family: sans-serif;
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
	margin-bottom: 3mm;
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
	text-align: center;
	padding: 5px 10px 5px 10px; 
}
.t2 TD {
    white-space: normal;
	vertical-align: top;
    font-family: sans-serif;
    color: #000;
    border-bottom: #000 solid 1px;
    border-left: #000 solid 1px;
}
.t2 TD:first-child  {
    text-align: center; 
}
.t2 TD:last-child  {
    border-right: none;
}
.t2 THEAD TR:first-child TD {
    border-top: #000 solid 1px;
}
.t2 THEAD TD {
    font-weight: bold;
    vertical-align: middle;
    text-align: center; 
}
.t2 td b {
    white-space: nowrap;
}

/* это для линии от текста до правого края */
h1, h2 {
	padding:10px 20px 0
}

em {
	display:inline-block;
	position:relative;
	top:1mm;
	padding: 1mm;
	background:#fff;
	margin-right:3mm;
	font-style:normal;
}
s {
	margin-left:3mm;
    font: 11pt sans-serif; 
    text-decoration:none;
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
	margin-bottom: 8mm;
}
#chef { 
	float: right;
	margin-top:1cm;
	width:7cm;
}
.note {
    font: 8pt sans-serif; 
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
$worder=$q->get("workorders", $woid);
if(isset($worder['manager']) && $worder['manager']>0) $manager = $q->get("employers",$worder['manager']);
elseif (isset($config['manager']) && $config['manager']>0) $manager = $q->get("employers",$config['manager']);
else $manager = '';
$employers = $q->select("SELECT e.* FROM employers e, workpeople wp WHERE eid=employer AND worder='{$woid}'");
$jobaddr = $q->fetch_all("SELECT c.address FROM claims c, claimperform p, workorders w WHERE w.woid = p.woid AND p.cid = c.unique_id AND w.woid = '{$woid}'");
if(!$worder['type']) $worder['type'] = 'access';
if(!$worder['worktype']) $worder['worktype'] = 'elevation';

if($woid=="") exit(0);
?>
<div class="header">
	<h1><?php echo COMPANY_NAME; ?>&trade; &nbsp;&nbsp;&nbsp; <?php echo FIRMNAME; ?><Br><hr/></h2>
	<h2 style="margin:0">НАРЯД-ДОПУСК &#8470;&nbsp;<?php echo $woid; ?></h2>
	<h3>на производство работ в местах действия опасных или вредных факторов</h3>

	<div class="banner">Выдан &laquo;<u style="width:8mm"><?php echo cyrdate($worder['prescribe'],'%d');
	?></u>&raquo; <u style="width:20mm">&nbsp; <?php echo cyrdate($worder['prescribe'],'%B');
	?>&nbsp;</u> &nbsp;20<u style="width:8mm"><?php echo cyrdate($worder['prescribe'],'%y'); ?></u>г.</div>

	<div class="banner" style="float:right">Действителен до &laquo;<u style="width:8mm"><?php echo cyrdate($worder['prescribe'],'%d');
	?></u>&raquo; <u style="width:20mm">&nbsp; <?php echo cyrdate($worder['prescribe'],'%B');
	?>&nbsp;</u> &nbsp;20<u style="width:8mm"><?php echo cyrdate($worder['prescribe'],'%y'); ?></u>г.</div>
	<br style="clear:both">
</div>

<div class="numeric">
		<p class="underline"><em>1. &nbsp;&nbsp;Руководителю работ </em> &nbsp;<?php echo shortfio($manager['fio']).", ".$manager['seat']; ?></p>
		<p class="substr">(Ф.И.О., должность)</p>

		<div>
			<p class="underline"><em>2. &nbsp;&nbsp;На выполнение работ </em></p>
			<div><?php echo "г.".COMPANY_UNIT." ".implode(', ',$jobaddr); ?></div>
		</div>
		<p class="underline">&nbsp;</p>
		<p class="substr">(наименование работ, место, условия их выполнения)</p>

		<p>3. &nbsp;&nbsp;Опасные производственные факторы, которые действуют или могут возникнуть независимо
		от выполняемой работы в местах ее производства:</p>
		<p class="underline" style="text-align:center"><?php echo $config['wo']['worktype'][$worder['worktype']]['name']; ?></p>

		<p>4. &nbsp;&nbsp;До начала производства работ необходимо выполнить следующие мероприятия:</p>
		<TABLE class="t2">
		<THEAD>
		<TR>
			<TD>&#8470;<BR>п/п</TD>
			<TD>Наименование мероприятия</TD>
			<TD width="35%">Срок выполнения</TD>
			<TD>Ответственный<br> исполнитель</TD>
		</TR>
		</THEAD>
		<TBODY>
		</TBODY>
		<?php
		$i=1;
		foreach($config['wo']['worktype'][$worder['worktype']]['before'] as $k=>$v) {
			print("<tr><td>$i</td><td>$v</td><td></td><td>".shortfio($employers[0]['fio'])."</td></tr>\n"); $i++;
		}
		if($i<=3) for($n=$i-1; $n<3; $n++) print("<tr><td>&nbsp;</td><td></td><td></td><td></td></tr>\n");
		?>
		</TABLE>
		<div class="banner">Начало работ в __ час. ___ мин.<u style="width:8mm"><?php 
		echo cyrdate($worder['prescribe'],'%d'); ?></u>.<u style="width:8mm"><?php 
		echo cyrdate($worder['prescribe'],'%m'); ?></u> 20<u style="width:8mm"><?php 
		echo cyrdate($worder['prescribe'],'%y'); ?></u>г.</div>
		<div class="banner" style="float:right">Окончание работ в__ час.___ мин.<u style="width:8mm"><?php 
		echo cyrdate($worder['prescribe'],'%d'); ?></u>.<u style="width:8mm"><?php 
		echo cyrdate($worder['prescribe'],'%m'); ?></u> 20<u style="width:8mm"><?php
		echo cyrdate($worder['prescribe'],'%y'); ?></u>г.</div>
		<br style="clear:both">

		<p>4.&nbsp;&nbsp;В процессе производства работ необходимо выполнять следующие мероприятия:</p>
		<TABLE class="t2">
		<THEAD>
		<TR>
			<TD>&#8470;<BR>п/п</TD>
			<TD>Наименование мероприятия</TD>
			<TD width="35%">Срок выполнения</TD>
			<TD>Ответственный<br> исполнитель</TD>
		</TR>
		</THEAD>
		<TBODY>
		<?php
		$i=1;
		foreach($config['wo']['worktype'][$worder['worktype']]['during'] as $k=>$v) {
			print("<tr><td>$i</td><td>$v</td><td></td><td>".shortfio($employers[0]['fio'])."</td></tr>\n"); $i++;
		}
		if($i<=3) for($n=$i-1;$n<3;$n++) print("<tr><td>&nbsp;</td><td></td><td></td><td></td></tr>\n");
		?>
		</TBODY>
		</TABLE>

		<p>5.&nbsp;&nbsp;Состав исполнителей работ:</p>
		<TABLE class="t2">
		<THEAD>
		<TR>
			<TD>Фамилия, имя, отчество</TD>
			<TD>Квалификация, группа по ТБ</TD>
			<TD>С условиями работ ознакомил, инструктаж провел</TD>
			<TD>С условиями работ ознакомлен</TD>
		</TR>
		</THEAD>
		<TBODY><?php 
		foreach($employers as $i=>$row) echo "<tr><td>".shortfio($row['fio'])."</td><td>".$row['category']."</td><td>".shortfio($manager['fio'])."</td><td></td></tr>";
		?>
		</TBODY>
		</TABLE>

		<p class="underline"><em>6. &nbsp;&nbsp;Наряд-допуск выдал</em> &nbsp;&nbsp;<?php echo shortfio($manager['fio']).", ".$manager['seat']; ?></p>
		<p class="substr">(уполномоченный приказом руководителя организации, Ф.И.О., должность, подпись)</p>

		<p class="underline"><em>7 .&nbsp;&nbsp;Наряд-допуск принял</em> &nbsp;&nbsp;<?php echo shortfio($employers[0]['fio']).", ".$employers[0]['seat']; ?></p>
		<p class="substr">(должность, Ф.И.О., подпись)</p>

		<p>8. &nbsp;&nbsp;Письменное разрешение действующего предприятия (эксплуатирующей организации) на производство работ имеется.</p>
		<p>Мероприятия по безопасности строительного производства согласованы</p>
		<p class="underline"> &nbsp; </p>
		<p class="substr">(должность, Ф.И.О., подпись уполномоченного представителя действующего предприятия)</p>

		<p>9.&nbsp;&nbsp;Рабочее место и условия труда проверены. Мероприятия по безопасности производства, указанные в наряде-допуске, выполнены.
		<p class="underline"><em>Разрешаю приступить к выполнению работ</em> &nbsp;&nbsp;<?php echo shortfio($manager['fio']).", ".$manager['seat']." &nbsp &nbsp".cyrdate($worder['prescribe']); ?></p>
		<p class="substr">(Ф.И.О., должность, подпись, дата)</p>

		<p class="underline"><em>10. &nbsp;&nbsp;Наряд-допуск продлен до </em></p>
		<p class="substr">(дата, подпись лица, выдавшего наряд-допуск)</p>

		<p>11.&nbsp;&nbsp;Работа выполнена в полном объеме. Материалы, инструмент, приспособления убраны. Люди выведены. Наряд-допуск закрыт.
		<br style="clear:both">
		<div class="box" style="width:9cm">
		<p><em>Руководитель работ</em></p>
		<p class="substr">(дата, подпись)</p>
		</div>
		<div class="box" style="float:right;width:13cm">
		<p><em>Лицо, выдавшее наряд-допуск</em></p>
		<p class="substr">(дата, подпись)</p>
		</div>
</div>
<br style="clear:both">
<div class="note" style="margin-top:5mm">
<b>Примечание:</b> Наряд-допуск оформляется в двух экземплярах (1-й находится у лица, выдавшего наряд, 2-й - у ответственного руководителя работ),
при работах на территории действующего предприятия наряд-допуск оформляется в трех экземплярах
(3-й экземпляр выдается ответственному лицу действующего предприятия).
</div>
