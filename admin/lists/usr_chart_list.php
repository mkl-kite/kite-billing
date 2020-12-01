<?php
if (!$user) return;

include_once("utils.php"); ?>
<CENTER>
<?php
echo "<h3>Трафик за последние 7 дней по <b>{$client['address']}</b></h3>";
for($i=0;$i<7;$i++) {
	echo "
	<div style=\"margin:15px 0 0 0\">
	<IMG SRC=\"charts/traffic.php?user={$user}&intday={$i}\" border=\"0\" width=\"700\" height=\"200\">
	</div>
	";
}
?>
</CENTER>
