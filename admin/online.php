<?php
$TOP = "Подключенные пользователи";
$META = "
	<META HTTP-EQUIV=\"Cache-Control\" CONTENT=\"no-cache\">\n
	<META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">
";
$CSSfile=array("base-admin.css");
$myscript=array("js/popuptraf.js");
include("top.php");
?>
<script>
$(document).ready(function(){
	var dia = $('img.diagram').attr('src');
	setInterval(function(){
		var t = $("table.normal").get(0), p = $('.pager input').val();
		$('img.diagram').attr('src',dia+'?tmp='+Math.random());
		if(t && t['_reload']) t._reload(p)
	},300000)
});
</script>
<?php
$intday=(isset($_REQUEST['intday']))? $_REQUEST['intday'] : 0;
$inthour=(isset($_REQUEST['inthour']))? $_REQUEST['inthour'] : 0;
$order=(isset($_REQUEST['order']))? numeric($_REQUEST['order']) : 0;
?>
<CENTER>
<?php
if($go=="chartlist") {
$id='intday='.$intday;
$ih='inthour='.$inthour;
$add='?'.$id.'&'.$ih;

$next1='&'.$id.'&inthour='.($inthour+6);
$next2='&intday='.($intday+1).'&'.$ih;
$next3='&intday='.($intday+60).'&'.$ih;
$prev1='&'.$id.'&inthour='.($inthour-6);
$prev2='&intday='.($intday-1).'&'.$ih;
$prev3='&intday='.($intday-60).'&'.$ih;
?>
<p>
<center>
<a class="button" href='online.php?go="chartlist"<?php echo $next3; ?>' title="назад 60 дней">&lt;&lt;&lt;</a>&nbsp;
<a class="button" href='online.php?go="chartlist"<?php echo $next2; ?>' title="назад 1 день">&lt;&lt;</a>&nbsp;
<a class="button" href='online.php?go="chartlist"<?php echo $next1; ?>' title="назад 6 часов">&lt;</a>&nbsp;
<a class="button" href='online.php?go="chartlist"<?php echo $prev1; ?>' title="вперед 6 часов">&gt;</a>&nbsp;
<a class="button" href='online.php?go="chartlist"<?php echo $prev2; ?>' title="вперед 1 день">&gt;&gt;</a>&nbsp;
<a class="button" href='online.php?go="chartlist"<?php echo $prev3; ?>' title="вперед 60 дней">&gt;&gt;&gt;</a>
</center>
</p>
<p><IMG class="diagram" SRC="charts/users.php<?php echo $add; ?>" border="0" width="700" height="200"></p>
<p><IMG class="diagram" SRC="charts/users_week.php<?php echo $add; ?>" border="0" width="700" height="200"></p>
<p><IMG class="diagram" SRC="charts/users_month.php<?php echo $add; ?>" border="0" width="700" height="200"></p>
<?php
} else {
	if ($opdata['status']>3) { ?> 
	<p><A HREF="online.php?go=chartlist"><IMG class="diagram" SRC="charts/users.php" border="0" width="700" height="200"></A></p><HR><?php
	}
	?><br>
	<div class="tablecontainer" query="go=stdtable&do=radacct&tname=radacct" style="max-width:90%"></div><?php
} ?>

</CENTER>
<?php
include("bottom.php");
?>
