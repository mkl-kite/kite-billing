<?php

$kassamenu = array(
	'Офисы'		=>array('HREF'=>"kassa.php?go=places"),
	'Ведомости'	=>array('HREF'=>"kassa.php?go=orders"),
	'Платежи'	=>array('HREF'=>"kassa.php?go=pay_log"),
	'По дням'	=>array('HREF'=>"kassa.php?go=dailypay"),
	'Моя ведомость'=>array('HREF'=>"#",'onclick'=>"window.open('docpdf.php?type=pay_list&id=0','_blank');"),
);
echo make_menu($kassamenu,'kassamenu');
?>
