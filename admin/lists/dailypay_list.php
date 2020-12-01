<?php 
include_once("utils.php"); 
include_once("defines.php");
include_once("classes.php");
include_once("log.php");
include_once("period_menu.php");
if(!isset($_REQUEST['date_begin'])) $period_fields['date_begin']['value'] = cyrdate(date("Y-m-01"));
if(!isset($_REQUEST['date_end'])) $period_fields['date_end']['value'] = cyrdate(date("Y-m-d"));

if ($opdata['status']<=3) { exit(0); }

$date_begin = isset($_REQUEST['date_begin'])? date2db(strtotime($_REQUEST['date_begin']),false) : date("Y-m-01");
$date_end = isset($_REQUEST['date_end'])? date2db(strtotime($_REQUEST['date_end']),false) : date("Y-m-d");
$owner = isset($_REQUEST['owner'])? strict($_REQUEST['owner']) : "";

$q = new sql_query($config['db']);
$terminal = $q->select("
	select distinct `from` 
	from pay p, povod pv 
	where 
		p.povod_id=pv.povod_id and 
		pv.typeofpay=1 and
		acttime between '$date_begin' and '$date_end 23:59:59'
",3);
$cspan = ''; $rspan='';
if($terminal){ $cspan = 'colspan="'.count($terminal).'"'; $rspan='rowspan="2"'; }
?>
<h3>Сводная по дням</h3><?php
echo period_menu(array_intersect_key($period_fields,array('owner'=>0,'date_begin'=>0,'date_end'=>0))); ?>
<TABLE class="normal" WIDTH="900px" align="center">
	<THEAD style="text-align:center">
	<TR><?php
	echo "<TR><TD $rspan>Дата</TD>";
	echo "<TD $rspan>{$typeofpay[0]}</TD>";
	echo "<TD $cspan>{$typeofpay[1]}</TD>";
	echo "<TD $rspan>{$typeofpay[2]}</TD>";
	echo "<TD $rspan>{$typeofpay[3]}</TD>";
	echo "<TD $rspan>кол-во<br>плативших</TD>";
	echo "<TD $rspan>Общая сумма</TD></TR>";
	if($terminal){
		echo "<TR>";
		foreach($terminal as $k=>$v) echo "<TD>{$v}</TD>";
		echo "</TR>";
	}
	?>		
	</THEAD>
	<TBODY>
<?php
foreach($terminal as $k=>$v) {
	$str.="sum(if(pv.typeofpay=1 and `from`='$v',summ,0)) as st$k, \n";
}

if($owner != ''){
	$owner_filter = "u.source = '$owner' AND";
}

$sqlstr="
	SELECT DATE(acttime) as d, pv.typeofpay as t, 
		sum(if(pv.typeofpay=0,summ,0)) as s0, 
		$str 
		sum(if(pv.typeofpay=2,summ,0)) as s2, 
		sum(if(pv.typeofpay=3,summ,0)) as s3, 
		sum(if(pv.typeofpay!=0,summ,0)) as s4,
		count(distinct p.user) as usr
	FROM pay as p LEFT OUTER JOIN povod as pv ON p.povod_id=pv.povod_id
		LEFT OUTER JOIN users as u ON p.uid = u.uid
	WHERE $owner_filter p.acttime between '$date_begin' and '$date_end 23:59:59' 
	GROUP by d
";
$s0 = $s1 = $s2 = $s3 = $s4 = $us = 0;
$result=$q->select($sqlstr);
$s1=array(); foreach($terminal as $k=>$v) $s1[$k]=0;
foreach($result as $k=>$row) {
?>
	<TR>
	<TD ALIGN=center><?php echo cell_bdate($row['d']); ?></TD>
	<TD ALIGN=right><?php printf("%.2f",$row['s0']); ?></TD>
	<?php
	if($terminal) foreach($terminal as $k=>$v) echo "<TD ALIGN=right>".sprintf("%.2f",$row['st'.$k])."</TD>\n";
	else echo "<TD ALIGN=right>0</TD>";
	?>
	<TD ALIGN=right><?php printf("%.2f",$row['s2']); ?></TD>
	<TD ALIGN=right><?php printf("%.2f",$row['s3']); ?></TD>
	<TD ALIGN=right><?php printf("%d",$row['usr']); ?></TD>
	<TD ALIGN=right><?php printf("%.2f",$row['s4']); ?></TD>
	</TR>
<?php
	$s0+=$row['s0'];
	foreach($terminal as $k=>$v) $s1[$k]+=$row['st'.$k];
	$s2+=$row['s2'];
	$s3+=$row['s3'];
	$s4+=$row['s4'];
}
$us = $q->select("SELECT count(distinct user) FROM pay WHERE $owner_filter acttime between '$date_begin' and '$date_end 23:59:59'",4);
?>
	</TBODY>
	<TFOOT>
	<TD></TD>
	<TD ALIGN=right><?php printf("%.2f",$s0); ?></TD>
	<?php
	if($terminal) foreach($terminal as $k=>$v) echo "<TD ALIGN=right>".sprintf("%.2f",$s1[$k])."</TD>";
	else echo "<TD ALIGN=right>0.00</TD>";
	?>
	<TD ALIGN=right><?php printf("%.2f",$s2); ?></TD>
	<TD ALIGN=right><?php printf("%.2f",$s3); ?></TD>
	<TD ALIGN=right><?php printf("%d",$us); ?></TD>
	<TD ALIGN=right><?php printf("%.2f",$s4); ?></TD>
	</TFOOT>
</TABLE>

<FORM NAME="dailypay" ACTION="<?php echo $menu; ?>" METHOD=POST>
	<INPUT TYPE=hidden NAME="go" VALUE="<?php echo $go; ?>">
	<INPUT TYPE=hidden NAME="do" VALUE="0">
	<INPUT TYPE=hidden NAME="date_begin" VALUE="<?php echo $date_begin; ?>">
	<INPUT TYPE=hidden NAME="date_end" VALUE="<?php echo $date_end; ?>">
	<INPUT TYPE=hidden NAME="call_to" VALUE="<?php echo $call_to; ?>">
	<INPUT TYPE=hidden NAME="owner" VALUE="<?php echo $owner; ?>">
</FORM>
