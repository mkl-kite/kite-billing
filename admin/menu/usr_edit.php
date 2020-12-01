<?php
include_once("classes.php");
include_once("snmpclass.php");
include_once("table.php");
include_once("log.php");
include_once("geodata.php");
if(!isset($q)) $q = new sql_query($config['db']);

if (!isset($client)) stop(array('result'=>'ERROR','desc'=>"Пользователь не найден!"));

?>
<CENTER>
<DIV id="userclaims" class="container"><?php
$claims = $q->select("SELECT *, DATE_FORMAT(claimtime,'%d-%m-%Y') as ct FROM `claims` WHERE uid='{$client['uid']}' ORDER BY claimtime DESC LIMIT 10");
if($claims) {
?>
<BR>Работа по заявлениям клиента:<BR>
<TABLE class="normal" id="claimtable" BORDER=0 CELLSPACING=5 CELLPADDING=0 WIDTH=100%>
<THEAD>
	<TR ALIGN=left>
	<TD>&#8470;</TD>
	<TD>Дата</TD>
	<TD>Тип</TD>
	<TD>Описание</TD>
	<TD>Статус</TD>
	</TR>
</THEAD>
<TBODY>
<?php
foreach($claims as $i => $row) { ?>
	<TR height="35px">
		<TD><SPAN class="linkform" add="go=claims&do=edit&id=<?php echo $row['unique_id'];?>"><?php echo $row['unique_id'];?></SPAN></TD>
		<TD><?php echo $row['ct'];?></TD>
		<TD style="color:#800;"><?php echo $claim_types[$row['type']];?></TD>
		<TD style="min-width:350px;"><?php echo $row['content'];?></TD>
		<TD><?php echo $claim_status[$row['status']];?></TD>
	</TR>
	<TR>
		<TD style="border-bottom: #99dd99 solid 4px;"></TD>
		<TD colspan="4" align="left" style="border-bottom: #99dd99 solid 4px;">
<?php
	$cp = $q->select("SELECT cp.cid, w.woid, DATE_FORMAT(w.prescribe,'%d-%m-%Y') as prescribe, cp.note 
			 FROM `claimperform` as cp, `workorders` as w 
			 WHERE cp.woid=w.woid and cp.cid='{$row['unique_id']}'
			 ORDER BY w.prescribe DESC LIMIT 8");
?>
	<TABLE class="order" width="100%"><TR>
<?php
	foreach($cp as $k => $cprow) {
		$emploers = $q->fetch_all("SELECT eid, fio FROM employers WHERE eid in (SELECT employer FROM workpeople WHERE worder='{$cprow['woid']}')","eid");
		foreach($emploers as $id=>$e) $emploers[$id] = shortfio($e); ?>
		<TD width="80px">Наряд: <B><?php echo "<span class=\"linkform\" add=\"go=stdform&table=workorders&do=edit&id={$cprow['woid']}\">{$cprow['woid']}</span>"; ?></B></TD>
		<TD width="80px"><?php echo $cprow['prescribe']; ?></TD>
		<TD style="font: italic 9pt sans;"><?php echo implode(' ',array_values($emploers)); ?></TD>
		<TD><?php echo $cprow['note']; ?></TD></TR><?php
		}
?>
	</TABLE>
	</TD>
	</TR><?php
	}
} ?>
</TBODY>
<TFOOT>
	</TD><TD></TD><TD></TD><TD></TD><TD></TD><TD></TD></TR>
</TFOOT>
</TABLE>
</DIV>

</TBODY>
</TABLE>
</DIV>
<DIV style="clear:both; height: 10px;"></DIV>
<HR>
</CENTER>

<?php include("bottom.php"); ?>
