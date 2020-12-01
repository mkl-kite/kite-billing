<?php
include_once("period_menu.php");
if(!isset($q)) $q = new sql_query($config['db']);
if(!isset($_REQUEST['date_begin'])) $period_fields['date_begin']['value'] = cyrdate(date("Y-m-d"));
if(!isset($_REQUEST['date_end'])) $period_fields['date_end']['value'] = cyrdate(date("Y-m-d",strtotime('2 week')));

$go=(isset($_REQUEST['go']))? strict($_REQUEST['go']) : '';
$do=(isset($_REQUEST['do']))? strict($_REQUEST['do']) : '';

$date_begin = isset($_REQUEST['date_begin'])? date2db(strtotime($_REQUEST['date_begin'])) : date("Y-m-d");
$date_end = isset($_REQUEST['date_end'])? date2db(strtotime($_REQUEST['date_end'])) : date("Y-m-d",strtotime('2 week'));

$mytitle="Выполняемые работы";

echo "<h3>$mytitle</h3>";
echo period_menu(array_intersect_key($period_fields,array('date_begin'=>0,'date_end'=>0))).'<br>';

// ---------------------- new ---------------------
$claims = $q->select("
	SELECT c.unique_id, c.type, c.uid, c.user, c.phone, c.rid, c.address, c.content, 
		cp.woid as nn, cp.begintime, cp.endtime, cp.status,
		cp.note, cp.unique_id as cpid,
		DATE_FORMAT(wo.prescribe,'%Y-%m-%d') as pt, 
		DATE_FORMAT(wo.prescribe, '%d') as d, 
		DATE_FORMAT(wo.prescribe, '%m') as m,
		DATE_FORMAT(wo.prescribe, '%w') as w
	FROM `claims` as c, 
		`claimperform` as cp,
		`workorders` as wo
	WHERE c.unique_id=cp.cid and
		wo.woid=cp.woid and
		wo.prescribe>='$date_begin' and
		wo.prescribe<='$date_end' and
		wo.performed is null
	ORDER BY pt, nn, cp.begintime
");
$online = $q->fetch_all("
	SELECT c.unique_id as cid, a.username, max(a.acctstarttime) as last, a.acctsessiontime, c.claimtime
	FROM radacct as a force index (username), claims as c
	WHERE c.user=a.username AND a.acctstarttime>c.claimtime AND acctsessiontime>300 AND a.pid>0 AND c.type=2 AND c.status<3
	GROUP BY a.username ORDER BY cid
",'cid');

$old_pt="";
$old_woid="";
foreach($claims as $i=>$row) {
	# Закрыть таблицу с заданиями для напечатанного наряда
	if($old_woid!=$row['nn'] && $old_pt==$row['pt']) echo "\n</TABLE>";
	# Закончились запланированные заявки на один день и начались на другой
	if ($old_pt!=$row['pt']) {
		# закрыть старую таблицу нарисовать число и начать новую 
		if ($i!=0) { ?>
			</TABLE></TD><TD align="center" valign="top" width="130px"><?php
			echo ($old_w==0 || $old_w==6) ? "<b class=\"freeday\">" : ""; ?>
			<h1><?php echo $old_d; ?></h1><H3><?php echo $mon[$old_m]; ?></H3><H5><?php echo $week_days[$old_w]; ?></H5><?php
			echo ($old_w==0 || $old_w==6) ? "</b>" : "";?>
			</TD></TR></TABLE><BR><BR><?php
			} ?>
		<TABLE BORDER=0 CELLSPACING=2 CELLPADDING=4 BGCOLOR=black WIDTH=95%>
		<TR BGCOLOR="#99dd99">
		<TD ALIGN=left><?php
		$old_pt=$row['pt'];$old_d=$row['d'];$old_m=$row['m'];$old_w=$row['w'];
		} 
	if ($old_woid!=$row['nn']) {  ?><div class="ordertitle"><font color="#040">
		<SPAN class="linkform" add="go=stdform&do=edit&table=workorders&id=<?php echo $row['nn'];?>">Наряд &#8470; <?php echo $row['nn'];?></SPAN>
		&nbsp;&nbsp;&nbsp;Исполнители: <I><?php
		$employers = $q->fetch_all("SELECT e.eid,e.fio FROM employers e, workpeople wp WHERE eid=employer AND worder=".$row['nn']." ORDER BY e.fio",'eid');
		if(!$employers) $employers = array();
		foreach($employers as $id=>$fio) $employers[$id] = shortfio($fio);
		echo implode(' ',$employers); ?></I></font>
		<span class="linkform" style="padding:5px;float:right" add="docpdf.php?type=workorder&id=<?php echo $row['nn']; ?>"><img src="pic/prn.png"></span>
		</div>
		<TABLE BORDER=0 CELLSPACING=1 CELLPADDING=4 BGCOLOR=black WIDTH=100%>
		<TR ALIGN=center BGCOLOR="#bdb">
			<TD width="50px"><B>&#8470;</B></TD>
			<TD width="350px"><B>Адрес</B></TD>
			<TD width="15px"> </TD>
			<TD width="120px"><B>Телефон</B></TD>
			<TD width="70px"><B>Время</B></TD>
			<TD><B>Описание</B></TD>
			<TD><B>Примечание</B></TD>
		</TR> <?php
		$old_woid=$row['nn'];
		} ?>
	<TR BGCOLOR="#dddd99">
	<TD style="white-space:nowrap;text-align:left;width:70px"><SPAN class="linkform" add="go=claims&do=edit&id=<?php echo $row['unique_id'];?>"><?php echo $row['unique_id'];?></SPAN><?php
	if($row['uid']>0) {
		printf("<img src=\"pic/go.png\" title=\"перейти к карточке\" add=\"users.php?go=usrstat&uid=%u\"/>\n",$row['uid']);
	} ?></TD>
	<TD ALIGN=left class="nowr" style="cursor:default;text-align:left" title="<?php echo $claim_types[$row['type']]; ?>"><?php
		$a = parse_address($row['address']);
		echo "<span class=\"rayon\">".((!$a['rayon'] && $row['rid']>1)? user_rayon($row['rid'])." &ensp;</span>":'');
		?><b style="color:<?php echo $claim_colors[$row['type']];?>"><?php
		echo $row['address'].
		((isset($online[$row['unique_id']]))? ' <span class="warn" title="последнее подключение '.
		cyrdate($online[$row['unique_id']]['last'],'%d %B %H:%M').'">?</span>': ''); ?></b>
	</TD>
	<TD><?php echo $status_cp[$row['status']]; ?></TD>
	<TD ALIGN=center><?php echo $row['phone'];?></TD>
	<TD ALIGN=center><?php echo cyrdate($row['begintime'],'%H:%M');?></TD>
	<TD ALIGN=left><?php echo $row['content'];?></TD>
	<TD ALIGN=left><?php echo $row['note'];?></TD>
	</TR> <?php
	}
# Закрыть последнюю строку и таблицу ?>
</TABLE></TD><TD align="center" width="130px"><?php
echo ($old_w==0 || $old_w==6) ? "<b class=\"freeday\">" : ""; ?>
<h1><?php echo $old_d; ?></h1><H3><?php echo $mon[$old_m]; ?></H3><H5><?php echo $week_days[$old_w]; ?></H5><?php
echo ($old_w==0 || $old_w==6) ? "</b>" : "";?>
</TD></TR></TABLE><BR><BR>
