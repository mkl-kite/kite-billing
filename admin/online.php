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
	var src = $('img#online').attr('src');
	function addParam(url,name,value){
		if(typeof url !== 'string') return false;
		var a = url.replace(/\?.*/,''), d=$.parseQuery(url);
		d[name] = value;
		return a+'?'+$.mkQuery(d);
	}
	setInterval(function(){
		var t = $("table.normal").get(0), p = $('.pager input').val();
		$('img#online').attr('src',addParam(src,'tmp',(new Date()).getUTC()));
		if(t && t['_reload']) t._reload(p)
	},300000)
});
</script>
<?php
$intday=(isset($_REQUEST['intday']))? numeric($_REQUEST['intday']) : 0;
$inthour=(isset($_REQUEST['inthour']))? numeric($_REQUEST['inthour']) : 0;
?>
<CENTER>
<?php
if($go=="chartlist") {
$id='intday='.$intday;
$ih='inthour='.$inthour;
$add='&'.$id.'&'.$ih;

$next1='&'.$id.'&inthour='.($inthour+6);
$next2='&intday='.($intday+1).'&'.$ih;
$next3='&intday='.($intday+60).'&'.$ih;
$prev1='&'.$id.'&inthour='.($inthour-6);
$prev2='&intday='.($intday-1).'&'.$ih;
$prev3='&intday='.($intday-60).'&'.$ih;
?>
<p>
<a class="button" href='online.php?go="chartlist"<?php echo $next3; ?>' title="назад 60 дней">&lt;&lt;&lt;</a>&nbsp;
<a class="button" href='online.php?go="chartlist"<?php echo $next2; ?>' title="назад 1 день">&lt;&lt;</a>&nbsp;
<a class="button" href='online.php?go="chartlist"<?php echo $next1; ?>' title="назад 6 часов">&lt;</a>&nbsp;
<a class="button" href='online.php?go="chartlist"<?php echo $prev1; ?>' title="вперед 6 часов">&gt;</a>&nbsp;
<a class="button" href='online.php?go="chartlist"<?php echo $prev2; ?>' title="вперед 1 день">&gt;&gt;</a>&nbsp;
<a class="button" href='online.php?go="chartlist"<?php echo $prev3; ?>' title="вперед 60 дней">&gt;&gt;&gt;</a>
</p>
<div class="chartlist" style="text-align:center">
<p><IMG class="diagram" SRC="charts/users_online.php?class=counter&period=1<?php echo $add; ?>"></p>
<p><IMG class="diagram" SRC="charts/users_online.php?class=counter&period=7<?php echo $add; ?>"></p>
<p><IMG class="diagram" SRC="charts/users_online.php?class=counter&period=365<?php echo $add; ?>"></p>
<p><IMG class="diagram" SRC="charts/users_online.php?class=iptv&period=1<?php echo $add; ?>"></p>
<p><IMG class="diagram" SRC="charts/users_online.php?class=iptv&period=7<?php echo $add; ?>"></p>
<p><IMG class="diagram" SRC="charts/users_online.php?class=iptv&period=365<?php echo $add; ?>"></p>
</div>
<?php
} else {
	if ($opdata['status']>3) { ?> 
	<p><A HREF="online.php?go=chartlist"><IMG id="online" class="diagram" SRC="charts/users.php?class=counter" border="0" width="700" height="200"></A></p><HR><?php
	}
	?><br>
	<div class="tablecontainer" query="go=stdtable&do=radacct&tname=radacct" style="max-width:90%"></div><?php
} ?>

</CENTER>
<?php
include("bottom.php");
?>
