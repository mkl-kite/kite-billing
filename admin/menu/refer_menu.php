<?php
include_once("defines.php");
?>
<SCRIPT>
$(document).ready(function(){
	var f = $("form#attr").get(0), el;
	if(f){
		$(f).css('position','relative')
		el = $('<div>').css({top:'10px',right:'10px',width:'25px',height:'25px',position:'absolute'});
		$(f).append(el);
		el.text($('textarea#val',f).val().length);
		$('textarea#val',f).bind('keyup',function(){
			var c = $(this).val()
			$(el).text(c.length);
		})
	}
});
</SCRIPT>
<?php
$refermenu = array(
	'Тарифы'	=>array('level'=>3,'HREF'=>"references.php?go=packetlist"),
	'Сервера'	=>array('level'=>4,'HREF'=>"references.php?go=naslist"),
	'Профили'	=>array('level'=>4,'HREF'=>"references.php?go=profiles"),
	'Пулы'		=>array('level'=>4,'HREF'=>"references.php?go=ippools"),
	'Свичи'		=>array('level'=>3,'HREF'=>"references.php?go=switches&do=list"),
	'Wi-Fi'		=>array('level'=>3,'HREF'=>"references.php?go=wifi&do=list"),
	'Узлы'		=>array('level'=>4,'HREF'=>"references.php?go=nodelist"),
	'Районы'	=>array('level'=>2,'HREF'=>"references.php?go=rayon"),
	'Дома'		=>array('level'=>3,'HREF'=>"references.php?go=homes"),
	'Поводы'	=>array('level'=>4,'HREF'=>"references.php?go=povod"),
	'Валюты'	=>array('level'=>4,'HREF'=>"references.php?go=currency"),
	'Аренда'	=>array('level'=>4,'HREF'=>"references.php?go=leases"),
	'Документы'	=>array('level'=>4,'HREF'=>"references.php?go=usrdocs"),
	'Карточки'	=>array('level'=>4,'HREF'=>"references.php?go=cards"),
	'Операторы'	=>array('level'=>4,'HREF'=>"references.php?go=operators"),
	'Служащие'	=>array('level'=>3,'HREF'=>"references.php?go=employers"),
	'Новости'	=>array('level'=>4,'HREF'=>"references.php?go=news"),
	'Выд.IP'	=>array('level'=>4,'HREF'=>"references.php?go=framedip"),
);
echo make_menu($refermenu,'refermenu');
?>
