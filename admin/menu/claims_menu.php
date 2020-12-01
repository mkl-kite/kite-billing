<?php
include_once("defines.php");
?>
<SCRIPT language="JavaScript">
$(document).ready(function() {
	$('img[add]').click(function(){
		if(!ldr) ldr = $.loader();
		var val = $(this).attr('add');
		window.open(val,'_blank');
		return false;
	})
})
</SCRIPT>
<?php

$claimsmenu = array(
	'График выходов'=>array('HREF'=>"claims.php?go=workdays"),
	'Заявления'		=>array('HREF'=>"claims.php?go=claimopen"),
	'Наряды'		=>array('HREF'=>"claims.php?go=worderlist"),
	'План'			=>array('HREF'=>"claims.php?go=claimplane"),
	'Новое задание'	=>array('HREF'=>"#",'class'=>"linkform",'add'=>'go=claims&do=new&type=1'),
);
echo make_menu($claimsmenu,'claimsmenu');
?>
