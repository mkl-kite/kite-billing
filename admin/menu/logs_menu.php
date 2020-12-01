<?php

$logmenu = array(
	'Лог радиуса'		=>array('level'=>3,'HREF'=>"logs.php?go=radius"),
	'Журнал SMS'		=>array('level'=>3,'HREF'=>"logs.php?go=smslist"),
	'Журнал операций'	=>array('level'=>2,'HREF'=>"logs.php?go=logs"),
);
echo make_menu($logmenu,'logmenu');
?>
