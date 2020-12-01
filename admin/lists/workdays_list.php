<script src="js/workdays.js"></script>
<style type="text/css">
	TABLE.workdays {
		border-spacing: 0;
		border-top: 1px solid black;
		border-left: 1px solid black;
/*		border-collapse: collapse; */
	}
	TABLE.workdays TD {
		border-top: none;
		border-left: none;
		border-right: 1px solid black;
		border-bottom: 1px solid black;
/*		border: 1px solid #777; */
	}
	TABLE.workdays TD.day {
		width: 18px;
	}
	TABLE.workdays TD.sunday {
		background-color: #90cc90;
		width: 16px;
		color:#fff;
		text-shadow: 1px 1px 1px #030;
	}
	TABLE.workdays TD.planned {
		background-color: rgba(128, 180, 230, 0.4);
	}
	TABLE.workdays TD.worked {
		background-image: url(pic/ok.png);
		background-repeat: no-repeat;
		background-position: center center;
	}
	TABLE.workdays TD.vacation {
		background-image: url(pic/reload.png);
		background-repeat: no-repeat;
		background-position: center center;
	}
	TABLE.workdays TD.sickleave {
		background-image: url(pic/plus.png);
		background-repeat: no-repeat;
		background-position: center center;
	}
	TABLE.workdays TD.note {
		background-image: url(pic/daynote.png);
		background-repeat: no-repeat;
		background-position: top right;
	}
	TABLE.workdays TD.overtime {
		background-image: url(pic/overtime.png);
		background-repeat: no-repeat;
		background-position: bottom right;
	}
	TABLE.workdays THEAD TD.today {
		background-color: #ffee00;
	}
	TABLE.workdays TD.result {
		text-align:center;
	}
	TABLE.workdays THEAD TD, TABLE.workdays THEAD TH {
		background-color: #99dd99;
		padding: 4px;
	/*	white-space: nowrap; */
		text-align:center;
		font-weight: bold;
		text-shadow: 1px 1px 1px #ded;
	}
	TABLE.workdays THEAD TD SPAN.weekday {
		font-weight: normal;
		font: normal 8pt sans-serif;
	}
	TABLE.workdays TFOOT TD {
		padding: 4px;
		background-color: #99dd99;
		font-weight: bold;
	}
	TABLE.workdays TBODY TD {
		padding: 2px 4px;
	}
	TABLE.workdays TBODY TD.wday {
		padding: 0;
		cursor: pointer;
		position:relative;
		text-align:center;
		min-width:26px;
	}
	TABLE.workdays TBODY TD DIV {
		cursor: pointer;
		position:absolute;
		height:6px;
		width:6px;
	}
	TABLE.workdays TBODY TD DIV.daynote {
		background-color: #7eff00;
		top:0;
		right:0;
	}
	TABLE.workdays TBODY TD DIV.overtime {
		background-color: #ffd200;
		bottom:0;
		right:0;
	}
	TABLE.workdays TBODY TR {
		height:18px;
	}
	TABLE.workdays TBODY TR:hover {
		background-color: #d0d0ee;
	}
	TABLE.workdays TBODY TR TD:first-child {
		width: 30px;
		text-align:center;
	}
	TABLE.workdays TBODY TR.selected {
		background-color: #f0f0aa;
	}
</style>
<?php
include_once("classes.php");
$q = new sql_query($config['db']);
$menu="";
$link="";
$wd=array('0'=>'Вс','1'=>'Пн','2'=>'Вт','3'=>'Ср','4'=>'Чт','5'=>'Пт','6'=>'Сб');

$month=numeric($_REQUEST['month']);
$year=numeric($_REQUEST['year']);
$today=date("d");
if($month==""||$year=="") {
	$days=date("t");
	$year=date("Y");
	$month=date("m");
}else{
	$days=date("t",strtotime("$year-$month-$today"));
}
$prev_year=date('Y',strtotime("-1 month",strtotime("$year-$month-01")));
$prev_month=date('m',strtotime("-1 month",strtotime("$year-$month-01")));
$next_year=date('Y',strtotime("+1 month",strtotime("$year-$month-01")));
$next_month=date('m',strtotime("+1 month",strtotime("$year-$month-01")));

$employers=$q->select("SELECT * FROM employers ORDER BY fio",2,'eid');
foreach($employers as $k=>$v) $employers[$k]['fio'] = shortfio($v['fio']);

$workdays=$q->select("
	SELECT eid, day(`date`) as `day`, work, worktime, overtime, note FROM workdays 
	WHERE `date` between '$year-$month-01' AND '$year-$month-31'");

foreach($workdays as $k=>$v) {
	$employers[$v['eid']]['days'][$v['day']]=array_intersect_key($v,array('work'=>0,'worktime'=>1,'overtime'=>2,'note'=>3));
	$employers[$v['eid']]['blocked']=0;
}
?>
<BR>
<H3>Табель выходов</H3>
<BR>
<TABLE class="workdays" WIDTH=80% align="center">
<THEAD>
<TR class="header">
	<TD colspan="<?php echo $days+6;?>">
		<A class="prev" href="claims.php?go=workdays&year=<?php echo $prev_year;?>&month=<?php echo $prev_month;?>">
			<IMG src="pic/prev.png" style="float:left;"></A>
		<?php echo $mon[$month]." ".$year;?>
		<A class="next" href="claims.php?go=workdays&year=<?php echo $next_year;?>&month=<?php echo $next_month;?>">
			<IMG src="pic/next.png" style="float:right;"></A>
	</TD>
</TR>
<TR>
	<TD class="num">&#8470;<BR>п/п</TD>
	<TD>Ф.И.О.</TD>
<?php 
	for($i=1;$i<=$days;$i++) {
		$class='day';
		$w[$i]=date('w',strtotime("$year-$month-$i"));
		if($w[$i]==0||$w[$i]==6) $class='sunday';
		if(date('Y-m-d',strtotime("$year-$month-$i"))==date('Y-m-d')) $class.=' today';
		echo "<TD class=\"$class\">$i<BR><span class=\"weekday\">".$wd[$w[$i]]."</span></TD>\n";
	} 
?>
	<TD class="result"><SPAN class="weekday">раб<br>дн</SPAN></TD>
	<TD class="result"><SPAN class="weekday">вых<br>дн</SPAN></TD>
	<TD class="result"><SPAN class="weekday">св<br>ур</SPAN></TD>
	<TD class="result"><SPAN class="weekday">отп<br>дн</SPAN></TD>
</TR>
</THEAD>
<TBODY>
<?php
$c=1;
foreach($employers as $k=>$v) {
	$wd = 0; $ot = 0; $sd = 0; $vd = 0; $sl = 0;
	if($v['blocked']==0) {
		echo "<TR id=\"e$k\">\n";
		echo "<TD class=\"num\">$c</TD>\n";
		echo "<TD class=\"fio\">{$v['fio']}</TD>\n";
		for($i=1;$i<=$days;$i++) {
			$class = array("wday"); $add='';
			if(@$v['days'][$i]['work']==1)
				$class[] = "planned";
			if(@$v['days'][$i]['work']==2) {
				$class[] = "planned";
				if(@$v['days'][$i]['worktime']==8) $class[] = "worked"; else $add .= $v['days'][$i]['worktime'];
				if($w[$i]==0||$w[$i]==6) $sd++; else $wd++;
			}
			if(@$v['days'][$i]['note']!='')
				$add .= "<div class=\"daynote\" title=\"{$v['days'][$i]['note']}\"></div>";
			if(@$v['days'][$i]['overtime']>0 && @$v['days'][$i]['work']==2) {
				$add .= "<div class=\"overtime\"></div>";
				$ot += $v['days'][$i]['overtime'];
			}
			if(@$v['days'][$i]['work']==3){
				$class[] = "vacation";
				$vd++;
			}
			if(@$v['days'][$i]['work']==4){
				$class[] = "sickleave";
				$sl++;
			}
			if(date('Y-m-d',strtotime("$year-$month-$i")) == date('Y-m-d')) $class[] = 'today';
			$title = array();
			echo "<TD id=\"".$k."d".$i."\" class=\"".implode(' ',$class)."\" "
				.((@$v['days'][$i]['work'] == 2)? "title=\"рабочее время: {$v['days'][$i]['worktime']}ч.\r"
				."сверхурочные: {$v['days'][$i]['overtime']}ч.\"":"").">$add</TD>\n";
		}
		echo "<TD class=\"result wd\">$wd</TD>\n<TD class=\"result fd\">$sd</TD>\n<TD class=\"result ot\">$ot</TD>\n<TD class=\"result vd\">$vd</TD>\n";
		echo "</TR>\n";
		$c++;
	}
}
?>
</TBODY>
</TABLE>
