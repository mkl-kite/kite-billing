<CENTER>
<?php
if ($opdata['status']>=4) { ?><A HREF=bugh.php?go=tearing>Разрывы</A><?php }
if ($opdata['status']>=4) { ?>|<A HREF=bugh.php?go=cables>Кабели</A><?php }
if ($opdata['status']>=3) { ?>|<A HREF=bugh.php?go=homes>По домам</A><?php }
if ($opdata['status']>=3) { ?>|<A HREF=bugh.php?go=usrmac>MAC адреса</A><?php }
if ($opdata['status']>=3) { ?>|<A HREF=bugh.php?go=dolg>Должники</A><?php }
if ($opdata['status']>=4) { ?>|<A HREF=bugh.php?go=dohod>Бюджет</A><?php }
if ($opdata['status']>=3) { ?>|<A HREF=bugh.php?go=credit>Кредиты</A><?php }
if ($opdata['status']>=4) { ?>|<A HREF=bugh.php?go=create>Подключения</A><?php }
if ($opdata['status']>=4) { ?>|<A HREF=bugh.php?go=worders>Наряды</A><?php }
if ($opdata['status']>=4) { ?>|<A HREF=bugh.php?go=users>Движения</A><?php }
if ($opdata['status']>=3) { ?>|<A HREF=bugh.php?go=lostusers>Убывшие</A><?php }
if ($opdata['status']>=4) { ?>|<A HREF=bugh.php?go=diagramms>Диаграммы</A><?php }
?>
</CENTER>
