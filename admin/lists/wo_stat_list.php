<?php
include_once("period_menu.php");
if(!isset($_REQUEST['date_begin'])) $period_fields['date_begin']['value'] = cyrdate(date("Y-m-01"));
if(!isset($_REQUEST['date_end'])) $period_fields['date_end']['value'] = cyrdate(date("Y-m-d"));

$date_begin = isset($_REQUEST['date_begin'])? date2db(strtotime($_REQUEST['date_begin'])) : date("Y-m-01");
$date_end = isset($_REQUEST['date_end'])? date2db(strtotime($_REQUEST['date_end'])) : date("Y-m-d");
$employer = numeric($_REQUEST['employer']);
?>
<br><h3>Статистика по нарядам</h3><?php
echo period_menu(array_intersect_key($period_fields,array('employer'=>0,'date_begin'=>0,'date_end'=>0)));
?>
<br>
<TABLE class="normal" WIDTH=700px align="center">
<THEAD>
<TR>
	<TD><B>Вид работ</B></TD>
	<TD><B>Запланировано</B></TD>
	<TD><B>Выполняется</B></TD>
	<TD><B>Не выполнено</B></TD>
	<TD><B>Выполнено</B></TD>
</TR>
</THEAD>
<TBODY>
<?php
$wotypes = $q->fetch_all("
	SELECT 
		c.type, 
		sum(IF(cp.status=1 and wo.prescribe>date(now()),1,0)) as planned, 
		sum(IF(cp.status=1 and wo.prescribe<=date(now()),1,0)) as perf,
		sum(IF(cp.status=2,1,0)) as fail,
		sum(IF(cp.status=3,1,0)) as ended
	 FROM 
		workorders as wo, 
		claimperform as cp, 
		claims as c".(($employer>0)?", workpeople as p":"")."
	 WHERE
		c.type>0 AND
		wo.woid = cp.woid AND
		cp.cid = c.unique_id AND
		".(($employer>0)?"wo.woid = p.worder AND employer=$employer AND":"")."
		wo.prescribe >= '$date_begin' AND 
		(cp.status<2 OR cp.status>1 AND wo.prescribe <= '$date_end 23:59:59')
	 GROUP BY c.type
",'type');

$planned=0;
$perf=0;
$fail=0;
$ended=0;
foreach($claim_types as $k=>$v) {
	if($k==0) continue; ?>
    <TR>
    <TD ALIGN=left><?php echo $claim_types[$k]; ?></TD>
    <TD ALIGN=right><?php echo $wotypes[$k]['planned']; ?></TD>
    <TD ALIGN=right><?php echo $wotypes[$k]['perf']; ?></TD>
    <TD ALIGN=right><?php echo $wotypes[$k]['fail']; ?></TD>
    <TD ALIGN=right><?php echo $wotypes[$k]['ended']; ?></TD>
    </TR><?php
    $planned+=$wotypes[$k]['planned'];
    $perf+=$wotypes[$k]['perf'];
    $fail+=$wotypes[$k]['fail'];
    $ended+=$wotypes[$k]['ended'];
}
?>

	</TBODY>
	<TFOOT>
	<TR>
	<TD><B>Итого:</B></TD>
	<TD ALIGN=right><B><?php printf("%.0f",$planned); ?></B></TD>
	<TD ALIGN=right><B><?php printf("%.0f",$perf); ?></B></TD>
	<TD ALIGN=right><B><?php printf("%.0f",$fail); ?></B></TD>
	<TD ALIGN=right><B><?php printf("%.0f",$ended); ?></B></TD>
	</TR>
	</TFOOT>
  </TABLE>
<?php
