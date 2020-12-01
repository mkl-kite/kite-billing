<?php
include_once("utils.php");
include_once("defines.php");
if(!$q) $q = new sql_query($config['db']);

if(!$q->query("
	SELECT 	u.user, 
			u.fio,
			u.address,
			p.name, 
			p.tos,
			p.fixed,
			u.blocked, 
			u.deposit, 
			u.credit, 
			u.uid, 
			rr.value as uip
	FROM	users as u, 
			packets as p,
			radreply as rr
	WHERE u.pid=p.pid and u.user=rr.username and rr.attribute='Framed-IP-Address' 
	ORDER BY inet_aton(rr.value)
")) stop(array('result'=>'ERROR','desc'=>"Статические IP адреса не найдены!"));
?>
<H3>Список клиентов со статическими IP</H3>
<BR>
<TABLE class="normal" WIDTH=80%>
	<THEAD>
    <TR>
      <TD>Логин</TD>
      <TD>Пакет</TD>      
      <TD>Ф.И.О.</TD>      
      <TD>Адрес</TD>      
      <TD>Депозит</TD>      
      <TD>IP</TD>
    </TR>
	</THEAD>
	<TBODY>
<?php

while($r = $q->result->fetch_assoc()) { ?>
    <TR>
    <TD <?php echo ($r['blocked'] == 1)? "class=\"debtor\"" : ""; ?>>
		<A HREF=users.php?go=usrstat&uid=<?php echo $r['uid']; ?>><B><?php echo $r['user']; ?></B></A>
	</TD>
    <TD><?php echo $r['name']; ?></TD>
    <TD><?php echo $r['fio']; ?></TD>
    <TD><?php echo $r['address']; ?></TD>
	<TD ALIGN=center <?php 	echo (($r['tos']>0 || $r['fixed']>0) && ($r['deposit'] + $r['credit']) <= 0) ? "class=\"debtor\"" : ""; ?>>
		<?php printf("%1.2f/%.2f",$r['deposit'],$r['credit']); ?>
	</TD>
	<TD class="ip"><?php echo $r['uip']; ?></TD>
	</TR><?php
}
?>
	</TBODY>
	<TFOOT><TR><TD colspan="5"></TD></TR></TFOOT>
  </TABLE>
