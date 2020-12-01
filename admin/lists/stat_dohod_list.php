<?php
$faction="stat.php";
if(!$q) $q = new sql_query($config['db']);
?>
<CENTER><BR>
<p><H3> </H3></p>
</CENTER>

<TABLE class="normal" WIDTH=50% align="center">
	<THEAD>
    <TR>
	<TD>Пакет</TD>
	<TD>Пользователей</TD>
	<TD>Долги</TD>
	<TD>Кредиты</TD>
    </TR>
	</THEAD>
	<TBODY>
<?php
$usr = $q->select("
	SELECT p.name, count(u.user) as cu, sum(u.deposit) as sm, sum(u.credit) as cr
	FROM users as u, packets as p
	WHERE  p.pid=u.pid and (p.tos>0 or p.fixed>0) and u.deposit<0
	GROUP BY p.name ORDER BY p.num
");
foreach($usr as $k=>$u){ ?>
    <TR>
    <TD ALIGN=left><?php echo $u['name']; ?></TD>
	<TD ALIGN=right><?php echo $u['cu']; ?></TD>
	<TD ALIGN=right><?php printf("%0.2f",abs($u['sm'])); ?></TD>
	<TD ALIGN=right><?php printf("%0.2f",abs($u['cr'])); ?></TD> <?php
    $summ+=abs($u['sm']);
    $summcr+=abs($u['cr']);
} ?>
	</TBODY>
	<TFOOT>
		<TR>
		<TD colspan=2>Итого все должники должны , у них кредитов :</TD>
		<TD ALIGN=right><?php printf("%0.2f",$summ); ?></TD>
		<TD ALIGN=right><?php printf("%0.2f",$summcr); ?></TD>
		</TR>
	</TFOOT>
</TABLE>
