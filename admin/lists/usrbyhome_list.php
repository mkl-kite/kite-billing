<?php
include_once("utils.php");
$faction="stat.php";
if(!isset($home)) $home = isset($_REQUEST['home'])? str($_REQUEST['home']) : "";
if(!isset($rayon)) $rayon = isset($_REQUEST['rayon'])? numeric($_REQUEST['rayon']) : "";
if(!$q) $q = new sql_query($config['db']);

function print_head_table($h) { 
	echo "<h4>$h</h4>" ?>
	<TABLE class="normal" WIDTH=100% align="center">
	<THEAD>
	<TR>
	<TD>Login</TD><TD>ФИО</TD><TD>Телефон</TD><TD>Адресс</TD><TD>Район</TD><TD>Счет/Кредит, Грн</TD><TD>Последнее<br>подключение</TD>
	</TR>
	</THEAD><?php 
}

function print_body_table($r) {
	echo "<TBODY>";
	foreach($r as $k=>$v) { ?>
		<TR>
		<TD><A ALIGN=center HREF=users.php?go=usrstat&uid=<?php echo $v['uid']; ?>><?php echo $v['user']; ?></TD>
		<TD nowrap><?php echo $v['fio']; ?></TD>
		<TD nowrap><?php echo $v['phone']; ?></TD>
		<TD nowrap><?php echo $v['address']; ?></TD>
		<TD nowrap><?php echo $v['r_name']; ?></TD>
		<TD nowrap ALIGN=center><?php printf("%0.2f / %0.2f",$v['deposit'],$v['credit']); ?></TD>
		<TD nowrap ALIGN=center><?php echo $v['last_connection']; ?></TD>
		</TR><?php
	}
	echo "</TBODY>";
}

function print_footer_table($c) { ?>
	<TFOOT><TR><TD colspan=6>Всего:</TD><TD ALIGN=right><?php echo $c; ?></TD></TR></TFOOT>
	</TABLE><?php
} ?>

<h3> Список клиентов по &ensp;<b><?php echo $home; ?></b></h3><div style="max-width:1100px">
<?php
$usr = $q->select("
	SELECT u.*, if(locate('/',address)>0,replace(address,'$home/',''),0) as kv, r.r_name 
	FROM users as u, rayon as r 
	WHERE u.rid='$rayon' AND u.rid=r.rid and trim(substr(address,1,if(locate('/',address)>0,locate('/',address)-1,char_length(address))))='$home'
	ORDER BY convert(kv,unsigned)
");
foreach($usr as $k=>$u) {
    if(strtotime($u['last_connection']) < strtotime("-3 month",time())) $row[0][]=$u;
    if(strtotime($u['last_connection']) < strtotime("-1 month",time())&&
		strtotime($u['last_connection']) > strtotime("-3 month",time())) $row[1][]=$u;
    if(strtotime($u['last_connection']) > strtotime("-1 month",time())) $row[2][]=$u;
}

if(count($row[2])>0) {
	print_head_table("Активные");
	ksort($row[2],SORT_NUMERIC);
	print_body_table($row[2]);
	print_footer_table(count($row[2]));
}
if(count($row[1])>0) {
	print_head_table("Думающие");
	ksort($row[1],SORT_NUMERIC);
	print_body_table($row[1]);
	print_footer_table(count($row[1]));
}
if(count($row[0])>0) {
	print_head_table("Ушедшие");
	ksort($row[0],SORT_NUMERIC);
	print_body_table($row[0]);
	print_footer_table(count($row[0]));
}
?></div>
