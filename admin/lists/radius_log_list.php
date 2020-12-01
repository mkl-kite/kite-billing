<?php
include_once("classes.php");
if($user==''&&$uid>0){
	$q = new sql_query($config['db']);
	$user = $q->select("SELECT user FROM users WHERE uid=$uid",4);
}
?>
<p>
<center><h2><b>Выборка лога радиуса</b></h2>
<p>
<iframe align="center" frameborder="no" width="90%" height="400" scrolling="auto" src="radius_log_file.php<?php echo ($user!="")? "?user=".$user:''; ?>">
    Ваш браузер не поддерживает фреймы
</iframe>
</center>
