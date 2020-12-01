<style type="text/css">
BODY  {	background-color: #FFFFFF; }

TABLE  {font-family: tahoma; font-size: 8pt; text-decoration : none;}
TABLE.t1  { width: 90%; background-color : #FFFFFF; border-style: none; border-width: 0px;}
TABLE.t2  { width: 100%; border-style: solid; border-color: #000000; border-width: 1px 1px 1px 1px; }
TR.rez0  { border-top: solid; border-color: #000000 border-width:1px;}
TR.rez1  { border-top: solid; border-color: #000000 border-width:1px;}
TD.cap1  {
    white-space: nowrap;
    background-color: #FFFFFF;
    font-family: tahoma; 
    font-weight: bold; 
    text-align: left; 
    color: #000000;
    border-bottom: #000000 solid 1px;
    border-right: none;
    border-top: none;
    border-left: none;
    }
TD.itog  {
    white-space: nowrap; 
    background-color: #FFFFFF;
    font-family: tahoma; 
	font-style: bold; 
    text-align: right; 
    border-bottom: #000000 solid 2px;
    border-right: #000000 solid 2px;
    border-top: #000000 solid 2px;
    border-left: #000000 solid 2px;
    }
TD.data  {
    white-space: nowrap;
    font-family: tahoma; 
    text-align: right; 
	margin-right:10%;
    }

P.radio {text-indent: -25; margin-left: 22; margin-right: 0}
P.line {border-bottom: #AFAFFF solid 3px;}

#f0 {color: #000000; font-family: verdana; font-style: italic; font-size:small;}
#f1 {color: #6020FF;}


@media screen {
.printbtn {
	position: absolute;
	top: 0.5cm;
	left: 0.5cm;
	height: 0.5cm;
	width: 4.5cm;
	}
.printbtn A:hover {
	text-decoration: blink;
	}
}

@media print {
.printbtn {
	visibility: hidden;
	}
}
</style>
<?php
$now=date("Y-m-d H:m:s",time());
$my_date=date("Y-m-d",time());
include_once("classes.php");
if(!$q) $q = new sql_query($config['db']);
$oid = (key_exists('oid',$_REQUEST))? numeric($_REQUEST['oid']) : 0;
if(!$oid) $oid = (key_exists('id',$_REQUEST))? numeric($_REQUEST['id']) : 0;

if (!$oid) {
	#Получение данных по платежной ведомости
	if(@$opdata['document']>0) $order = $q->get("orders",$opdata['document']);
}

# Формирование дописок к sql выражению для выполнения запроса к базе
$call_to=(key_exists('call_to',$_REQUEST))? strict($_REQUEST['call_to']) : '';
if ($call_to != "" && $call_to != "-1" && $opdata['status']>3) { 
	$sql_add=" and p.from='".$call_to."' ";
}else{
	$call_to=$opdata['login'];
	$sql_add="p.oid={$opdata['document']} and p.from='".$call_to."' ";
}

# если п.в. указана явно - то это просмотр, иначе это сдача текущей п.в.
if(!$oid) $oid = $opdata['document'];

$base_surrency=0;
$cr = $q->fetch_all("SELECT * FROM currency");
foreach($cr as $r) if($r['rate']==1.00) $base_surrency=$r['id'];

$o = $q->select("
	SELECT o.*, DATE_FORMAT(`open`, '%d-%m-%Y') as mydate, fio 
	FROM `orders` as o LEFT OUTER JOIN `operators` as op ON o.operator=op.login
	WHERE oid='$oid'
",1) or stop(array('result'=>'ERROR','desc'=>"Ведомость не найдена!"));
$o['shortname'] = shortfio($o['fio']);
$opname=$o['shortname'];
$call_to=$o['operator'];
$sql_add="p.oid=".$oid." and p.from='".$call_to."' ";

echo "<div align=center>Платежная ведомость <B>&#8470;$oid</B><BR>от <B>".$o['mydate']."</B> по оператору <B><I>".$opname."</I></B></BR>";
// ---------------------- new ---------------------
$q->query("
	SELECT
		date_format(p.acttime, '%d-%m-%Y') as payday, 
		date_format(p.acttime, '%H:%i') as paytime, 
		p.user as usr, 
		p.uid as uid, 
		u.fio as fio,
		p.note as note, 
		p.from as adm, 
		pvd.povod as povod,
		pvd.kassa as del,
		p.money as money, 
		p.money>=0 as znak,
		p.currency as currency,
		p.summ as summ,
		p.oid as oid,
		p.unique_id as id
	FROM `pay` as p, `povod` as pvd, `users` as u
	WHERE p.povod_id=pvd.povod_id and 
		u.uid=p.uid and ".$sql_add."
	ORDER BY povod, currency, znak, p.acttime 
") or stop(array('result'=>'ERROR','desc'=>"Платежи не найдены!"));

$numrow = $q->rows();
$row = $q->result->fetch_assoc();
$sum_znak=0; $sum_all=0;
$old_znak=$row['znak'];
$old_povod=$row['povod'];
$sum_currency=0; 
$old_currency=$row['currency'];
$old_del=$row['del'];
$endstr="";
print("
<TABLE class=\"t1\">
<TR><TD colspan=\"3\"><BR><span id=f0>{$row['povod']}</span></TD></TR>
<TR class=\"cap1\">
<TD class=\"cap1\"><B>Дата</B></TD>".(($call_to == "-1")? "<TD class=\"cap1\"><B>Оператор</B></TD>":"")."
<TD class=\"cap1\"><B>Пользователь</B></TD>
<TD class=\"cap1\"><B>Комментарий</B></TD>
<TD class=\"cap1\"><B>Сумма, {$cr[$old_currency]['short']}</B></TD>
</TR>
");

for ($i = 0; $i < $numrow; $i++) {
#------------- здесь новый раздел znak -------------
	if ($row['znak']!=$old_znak || $row['povod']!=$old_povod || $row['currency']!=$old_currency) {
		$cs=($call_to == "-1")? "colspan=\"2\"" : "";
		printf ("<TR>\n<TD colspan=\"3\"></TD>\n<TD class=\"itog\" {$cs}> %.2f %s</TD>\n</TR><BR>\n", $sum_znak,$cr[$old_currency]['short']);
		$del = ($old_del==0)? "<DEL>%.2f %s</DEL>" : "%.2f %s";
		$endstr = sprintf("%s<TR><TD>%s</TD><TD class=\"data\">$del</TD></TR>\n",$endstr,$old_povod,$sum_znak,$cr[$old_currency]['short']);
		if ($row['povod']!=$old_povod) {
			printf("<TR><TD colspan=\"3\"><BR><span id=f0>%s</span></TD></TR>\n", $row['povod']);
			$old_povod=$row['povod']; $old_del=$row['del'];
		}
		print("
		<TR class=\"cap1\">
		<TD class=\"cap1\"><B>Дата</B></TD>".(($call_to == "-1")? "<TD class=\"cap1\"><B>Оператор</B></TD>":"")."
		<TD class=\"cap1\"><B>Пользователь</B></TD>
		<TD class=\"cap1\"><B>Комментарий</B></TD>
		<TD class=\"cap1\"><B>Сумма, {$cr[$row['currency']]['short']}</B></TD>
		</TR>
		");
		$sum_znak=0;
		$old_znak=$row['znak'];
		$sum_currency=0;
		$old_currency=$row['currency'];
		}
	print("
	<TR>
	<TD nowrap> {$row['payday']} {$row['paytime']} </TD>".(($call_to == "-1")? "<TD> {$row['adm']} </TD>":"")."
	<TD nowrap> {$row['fio']} (<B>{$row['usr']}</B>)</TD>
	<TD> {$row['note']} </TD>
	<TD class=\"data\">".sprintf('%1.2f',$row['money'])." </TD>
	</TR>
	");
	$sum_znak = $sum_znak+$row['money'];
	$sum_currency = $sum_currency+$row['summ'];
	if($row['del']!=0) $sum_all = $sum_all + $row['summ'];
	$row = $q->result->fetch_assoc();
}
printf ("<TR>\n<TD colspan=\"3\">&nbsp;</TD>\n<TD class=\"itog\"".$cs."> %.2f %s</TD>\n</TR>\n", $sum_znak,$cr[$old_currency]['short']);
$del = ($old_del==0)? "<DEL>%.2f %s</DEL>" : "%.2f %s";
$endstr=sprintf("%s<TR><TD>%s</TD><TD class=\"data\">$del</TD></TR>\n",$endstr,$old_povod,$sum_znak,$cr[$old_currency]['short']);
$endstr=sprintf("%s<TR><TD><B>Итого:</B></TD><TD class=\"itog\">&nbsp;%.2f %s&nbsp;</TD></TR>\n",$endstr,$sum_all,$cr[$base_surrency]['short']);
echo "</TABLE>\n";
printf ("<BR><BR><TABLE>\n %s </TABLE><BR><BR>", $endstr);
?>
</div>
