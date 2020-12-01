<?php

$statmenu = array(
	'Разрывы'		=>array('level'=>4,'HREF'=>"stat.php?go=tearing"),
	'Кабели'		=>array('level'=>4,'HREF'=>"stat.php?go=cables"),
	'По домам'		=>array('level'=>3,'HREF'=>"stat.php?go=homes"),
	'DHCP'			=>array('level'=>4,'HREF'=>"stat.php?go=dhcp"),
	'MAC адреса'	=>array('level'=>3,'HREF'=>"stat.php?go=usrmac"),
	'Должники'		=>array('level'=>3,'HREF'=>"stat.php?go=dolg"),
	'Бюджет'		=>array('level'=>4,'HREF'=>"stat.php?go=dohod"),
	'Кредиты'		=>array('level'=>3,'HREF'=>"stat.php?go=credit"),
	'Подключения'	=>array('level'=>4,'HREF'=>"stat.php?go=create"),
	'Наряды'		=>array('level'=>4,'HREF'=>"stat.php?go=worders"),
	'Движения'		=>array('level'=>4,'HREF'=>"stat.php?go=users"),
	'Убывшие'		=>array('level'=>3,'HREF'=>"stat.php?go=lostusers"),
	'Диаграммы'		=>array('level'=>4,'HREF'=>"stat.php?go=diagramms"),
);

echo make_menu($statmenu,'statmenu');
?>
