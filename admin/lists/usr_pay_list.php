<?php
include_once("log.php");
include_once("users.cfg.php");
include_once("period_menu.php");
if(!$q) $q = new sql_query($config['db']);
if(!isset($_REQUEST['date_begin'])) $period_fields['date_begin']['value'] = cyrdate(date("Y-m-d"));
if(!isset($_REQUEST['date_end'])) $period_fields['date_end']['value'] = cyrdate(date("Y-m-d"));

$date_begin = isset($_REQUEST['date_begin'])? date2db(strtotime($_REQUEST['date_begin']),false) : date("Y-m-d");
$date_end = isset($_REQUEST['date_end'])? date2db(strtotime($_REQUEST['date_end']),false) : date("Y-m-d");
$owner = isset($_REQUEST['owner'])? strict($_REQUEST['owner']) : "";

if (isset($_REQUEST['del_payid']) && ($del_payid = numeric($_REQUEST['del_payid']))>0) {
	# получить данные по платежу
	if(!($pay = $q->get('pay',$del_payid)))
		stop(array('result'=>'ERROR','desc'=>"Не найден платеж в базе!"));
	$p = new payment($config);
	if(!$p->remove_pay($pay))
		stop(array('result'=>'ERROR','desc'=>implode('<br>',$p->errors)));
}

# Формирование дописок к sql выражению для выполнения запроса к базе
$uid = key_exists('uid',$_REQUEST)? numeric($_REQUEST['uid']) : '';
$call_to = key_exists('call_to',$_REQUEST)? strict($_REQUEST['call_to']) : '';

if($uid) {
    $sql_add=" AND p.uid=".$uid." ";
	if(!($user = $q->get('users',$uid))) stop(array('result'=>'ERROR','desc'=>"Пользователь не найден!"));
	# если не было определения даты то показать оплаты за год по клиенту
	if(strtotime($date_begin)>strtotime('-1 year')) $date_begin=date("Y-m-d",strtotime('-1 year'));
}else{ 
	$sql_add=""; 
}

# если статус оператора >3 и $call_to == "-1" то выборка будет по всем операторам ($from="")
if(!$call_to && ($uid || $opdata['status']>3)) $from="";
if($opdata['status']<=3) $call_to = $opdata['login'];
if($call_to) $from=" AND p.from='".$call_to."' ";

$base_currency = get_valute();

$owner_filter = ($config['owns'] && $owner != '')? "u.source = '$owner' AND" : "";

echo "<h3>Платежи</h3>";

echo period_menu(array_intersect_key($period_fields,array('owner'=>0,'call_to'=>0,'date_begin'=>0,'date_end'=>0)));

$sqlstr = "
	SELECT p.user as user,
		p.unique_id as id,
		p.acttime, 
		u.contract,
		p.user as usr,
		u.fio as fio,
		u.address as address,
		p.uid as uid,
		p.card,
		concat(p.note,' ',p.card) as note, 
		p.from as adm, 
		IF(op.fio is null,p.from,op.fio) as admname, 
		pvd.povod as povod,
		pvd.kassa as del,
		p.money as money, 
		p.money>=0 as znak,
		p.currency as currency,
		p.summ as summ,
		p.oid as oid,
		p.unique_id as id,
		o.close as close,
		o.accept as accept
	FROM `pay` as p LEFT OUTER JOIN `orders` as o ON p.oid=o.oid
			LEFT OUTER JOIN `povod` as pvd ON p.povod_id=pvd.povod_id
			LEFT OUTER JOIN `operators` as op ON p.from=binary(op.login),
			`users` as u
	WHERE $owner_filter
		u.uid=p.uid and
		p.acttime between '$date_begin' and 
		'$date_end 23:59:59' ".$sql_add.$from."
	ORDER BY povod, currency, znak, p.acttime 
";

	if(!$q->query($sqlstr)) stop(array('result'=>'ERROR','desc'=>"Ошибка получения данных!"));
	$numrow = $q->rows();
	$row = $q->result->fetch_assoc();
	$sum_znak=0; $sum_all=0;
	$old_znak=$row['znak'];
	$old_povod=$row['povod'];
	$sum_currency=0; 
	$old_currency=$row['currency'];
	$old_del=$row['del'];
	$endstr="";
?>
	<DIV align="center" style="min-width:600px;max-width:1024px;text-align:left;">
	<font size="+1" color="#005500" face="Arial"> <?php echo $row['povod']; ?> </font>
	<TABLE class="normal" WIDTH=100%>
	<THEAD>
	<TR><TD>Дата</TD>
	<TD>Оператор</TD>
	<TD>Лиц.счет</TD>
	<?php if(!$uid){?><TD>Пользователь</TD><?php }?>
	<TD>Описание</TD>
	<TD>Сумма</TD>
	<TD style="width:20px"></TD></TR>
	</THEAD>
	<TBODY>
<?php
	for ($i = 0; $i < $numrow; $i++) {
#------------- здесь новый раздел znak -------------
		if ($row['znak']!=$old_znak || $row['povod']!=$old_povod || $row['currency']!=$old_currency) { ?>
			</TBODY>
			<TFOOT>
			<TR>
			<TD colspan="<?php echo ($uid)?"3":"4";?>"></TD>
			<TD align="right"><B><?php printf("%.2f %s",$sum_znak,arrfld(get_valute($old_currency),'short'));?></B></TD>
			<TD align="right"><B><?php printf("%.2f %s",$sum_currency,$base_currency['short']);?></B></TD>
			<TD></TD>
			</TR>
			</TFOOT>
			</TABLE>
			<BR>
<?php
			if ($old_del==0) $del="<FONT color=red>%.2f (%s)</FONT>"; else $del="%.2f (%s)";
			$endstr=sprintf("%s<TR><TD>%s</TD><TD ALIGN=right>$del</TD></TR>\n",$endstr,$old_povod,$sum_znak,arrfld(get_valute($old_currency),'short'));
			if ($row['povod']!=$old_povod) { 
?>
			<BR><font size="+1" color="#005500" face="Arial"> <?php echo $row['povod']; ?> </FONT><BR>
<?php
				$old_povod=$row['povod']; $old_del=$row['del'];
				}
?>
			</TABLE>
			<TABLE class="normal" WIDTH=100%>
			<THEAD>
			<TR>
			<TD>Дата</TD>
			<TD>Оператор</TD>
			<TD>Лиц.счет</TD>
			<?php if(!$uid){?><TD>Пользователь</TD><?php }?>
			<TD>Описание</TD>
			<TD>Сумма</TD>
			<TD style="width:20px"></TD></TR>
			</THEAD>
			<TBODY>
<?php
			$sum_znak=0;
			$old_znak=$row['znak'];
			$sum_currency=0;
			$old_currency=$row['currency'];
			}
?>
		<TR>
		<TD class="date"> <?php echo cell_atime($row['acttime']); ?> </TD>
		<TD class="ctxt nowr"> <?php echo shortfio($row['admname']); ?> </TD>
		<TD> <?php echo $row['contract']; ?> </TD>
		<?php if(!$uid){?><TD> <A HREF=users.php?go=usrstat&uid=<?php printf("%d",$row['uid']);?>><B> <?php echo $row['address'];?></B></A></TD><?php }?>
		<TD> <?php echo $row['note'];?> </TD>
		<TD align="right"> <?php printf("%.2f %s",$row['money'],$cr[$row['currency']]['short']);?> </TD>
		<TD align="center"> <?php if($row['close']==null && $row['accept']==null && $row['card']=='' && ($opdata['status']>3 || $opdata['login']==$row['adm'])) { ?> 
			<A class="linkform" add="go=stdform&do=remove&id=<?php echo $row['id']; ?>&table=pay" TITLE="Удалить"><img src="pic/delete.png"></A></TD> <?php 
		}else{
			echo '<img src="pic/lock.png">';
		} ?>
		</TR>
<?php
		$sum_znak=$sum_znak+$row['money'];
		$sum_currency=$sum_currency+$row['summ'];
		if ($row['del']!=0) $sum_all=$sum_all+$row['summ'];
		$row = $q->result->fetch_assoc();
		}
?>
	</TBODY>
	<TFOOT>
	<TR>
	<TD colspan="<?php echo ($uid)?"3":"4";?>">&nbsp;</TD>
	<TD align="right"><B><?php printf("%.2f %s",$sum_znak,arrfld(get_valute($old_currency),'short'));?></B></TD>
	<TD align="right"><B><?php printf("%.2f %s",$sum_currency,$base_currency['short']);?></B></TD>
	<TD></TD>
	</TR>
	</TFOOT>
<?php
	if ($old_del==0) $del="<FONT color=red>%.2f (%s)</FONT>"; else $del="%.2f (%s)";
	$endstr=sprintf("%s<TR><TD>%s</TD><TD ALIGN=right>$del</TD></TR>\n",$endstr,$old_povod,$sum_znak,arrfld(get_valute($old_currency),'short'));
	$endstr=sprintf("%s<TR><TD><B>Итого:</B></TD><TD ALIGN=right><B>%.2f (%s)</B></TD></TR>\n",$endstr,$sum_all,$base_currency['short']);
?>
	</TABLE>

	<BR><BR>
	<TABLE WIDTH=400px> <?php echo $endstr; ?> </TABLE>
	<BR><BR>
<?php
	if (!$uid && $opdata['status']>3) {
		$q->query("
			SELECT  sum(money) as s, 
					count(distinct user) as n,
					sum(money)/count(distinct user) as rez
			FROM 	pay p, povod v
			WHERE 	p.povod_id=v.povod_id AND typeofpay>0 AND 
					acttime between '$date_begin' AND 
					'$date_end 23:59:59'
		") or stop();
		$numrow = $q->rows();
		$row = $q->result->fetch_assoc();
		printf (" ИТОГО:  %s плативших <BR>\n", $row['n']);
		printf (" Доходность в расчете на 1-го пользователя составляет %1.2f/%s = %1.2f \n", $row['s'], $row['n'], $row['rez']);
	}
?>
</DIV>
