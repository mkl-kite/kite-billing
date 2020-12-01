<?php
	include_once 'classes.php';

	$uid = (isset($_REQUEST['uid']))? numeric($_REQUEST['uid']):false;
	if(!$uid || $uid==0) stop(array('result'=>'ERROR','desc'=>'Не указан пользователь!'));
	if(!isset($q)) $q = new sql_query($config['db']);
	$user = $q->get('users',$uid);
?>
	<h3>Документы для <?php echo shortfio($user['fio']); ?></h3><br><center>
	<div class="tablecontainer" query="go=stdtable&do=documents&tname=documents&uid=<?php echo $uid; ?>" style="max-width:900px"></div>
	</center>

