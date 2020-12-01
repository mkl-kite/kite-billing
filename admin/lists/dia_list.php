<?php
	$now = isset($_REQUEST['date'])? "?date={$_REQUEST['date']}" : "";
?>
<br>
<center>
<DIV style="display:cell;width:90%;align:center">
<IMG class="diagram" SRC="charts/pay1.php<?php echo $now; ?>">
<IMG class="diagram" SRC="charts/pay.php<?php echo $now; ?>">
<IMG class="diagram" SRC="charts/pay2.php<?php echo $now; ?>">
<IMG class="diagram" SRC="charts/new_usr.php<?php echo $now; ?>">
<IMG class="diagram" SRC="charts/live.php<?php echo $now; ?>">
<IMG class="diagram" SRC="charts/lost_usr.php<?php echo $now; ?>">
<IMG class="diagram" SRC="charts/packet_money.php<?php echo $now; ?>">
<IMG class="diagram" SRC="charts/pay_trend.php<?php echo $now; ?>">
<IMG class="diagram" SRC="charts/lost_usr_by_rid.php<?php echo $now; ?>">
</DIV>
</center>
<br>
