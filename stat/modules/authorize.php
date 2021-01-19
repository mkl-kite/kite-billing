<?php
include_once("classes.php");
include_once("users.cfg.php");
$q = new sql_query($config['db']);
$guest = $q->select("SELECT * FROM radacct WHERE acctstoptime is NULL AND framedipaddress='{$_SERVER['REMOTE_ADDR']}' AND framedprotocol='IPoE'",1);
if($guest && $guest['groupname'] == 'unknown'){
	$effort = floor($guest['credit']);
	$efforts = 5 - $effort;
}else $efforts = false;

if(isset($_POST['auth_name'])) {
	$login=$q->escape_string(preg_replace('/[^a-z0-9_.-]/i','',$_POST['auth_name']));
	$pass=$q->escape_string(preg_replace('/[\\"\'!@#%\^&*(){};\|<>\[\]]/','',$_POST['auth_pass']));
	if($user = $q->select("SELECT * FROM users WHERE user='$login' and password='$pass'",1)) {
		if(key_exists('disabled',$user) && $user['disabled']) stop("Пользователь отключен!");
		$user['level'] = $user['status'] = 2;
		session_start();
		$_SESSION['sess_uid'] = $user['uid'];
		$_SESSION['sess_ip'] = $_SERVER['REMOTE_ADDR'];
		$_SESSION['sess_user'] = $user;
		log_txt("клиент {$user['fio']} ({$user['user']}) {$user['address']} подключился с IP=".$_SESSION['sess_ip']);
		if($efforts) {
			$effort++;
			$double = $q->select("SELECT * FROM radacct WHERE acctstoptime is NULL and username='{$user['user']}'",1);
			if($double){
				log_txt("попытка изменения mac:{$guest['callingstationid']} ip:{$guest['framedipaddress']} login: $login  pass: $pass");
				$msg = "Пользователь {$user['user']} уже подключен!";
			}else{
				$client=$_SESSION['sess_user'];
				$u = new user($user['user']);
				if($new = $u->localization($guest['callingstationid'],$guest['connectinfo_start'])){
					$_SESSION['sess_user'] = $u->data;
					$client=$_SESSION['sess_user'];
					$q->insert('news',array('uid'=>$user['uid'],'expired'=>date2db('1 month'),'name'=>"Регистрация ноового оборудования",'content'=>"Вы успешно переключились на новое оборудование (<b>{$new['csid']}</b>)"));
					send_coa($user,$guest);
				}else{
					log_txt("ошибки при локализации: ".implode(', ',$u->errors));
					$q->insert('news',array('uid'=>$user['uid'],'expired'=>date2db('2 day'),'name'=>"ОШИБКА регистрации оборудования", 'content'=>"Переключение отменено, произошли следующие ошибки: ".implode(', ',$u->errors)));
				}
				$q->query("UPDATE radacct SET credit=5 WHERE radacctid={$guest['radacctid']}");
				header("Location: ".(isset($_SERVER['HTTPS'])?"https://":"http://").$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
				exit;
			}
		}
	}
	if($efforts){
		$effort++;
		$efforts = 5 - $effort;
		$q->query("UPDATE radacct SET credit=$effort WHERE radacctid={$guest['radacctid']}");
		log_txt("{$effort}-я попытка подключения {$_SESSION['sess_ip']} ip: {$_SERVER['REMOTE_ADDR']} log:$login pass:$pass");
	}else
		log_txt("попытка подключения ip: {$_SERVER['REMOTE_ADDR']}  login: $login  pass: $pass");
}else{
	if($DEBUG>0) log_txt("ip: {$_SERVER['REMOTE_ADDR']} sess_name='".$_COOKIE[session_name()]."'");
}
if(isset($_GET['go']) && preg_match('/.*logout$/',$_GET['go'])) {
	session_start();
	$client=$_SESSION['sess_user'];
	log_txt("клиент {$client['fio']} ({$client['user']}) {$client['address']} отключился с IP=".(isset($_SESSION['sess_ip'])?$_SESSION['sess_ip']:$_SERVER['REMOTE_ADDR']));
	session_destroy();
	header("Location: ".(isset($_SERVER['HTTPS'])?"https://":"http://").$_SERVER['HTTP_HOST'].preg_replace('/\?.*/','',$_SERVER['REQUEST_URI']));
	exit;
}
if(isset($_REQUEST[session_name()]) || isset($_COOKIE[session_name()])) session_start();
if(isset($_SESSION['sess_uid']) && $_SESSION['sess_ip'] == $_SERVER['REMOTE_ADDR']) {
	$client=$_SESSION['sess_user'];
	$opdata = array('login' => 'CLIENT','status' => 2);
	if($user = $q->select("SELECT * FROM users WHERE uid={$client['uid']}",1)) {
		$new = $q->compare($client,$user);
		if(count($new)>0) foreach($new as $k=>$v) $client[$k] = $v;
	}
	foreach($_POST as $k=>$v) $post[] = "$k=".((@$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')? sqltrim($v):sqltrim($v));
	log_txt("{$client['user']} IP: {$_SESSION['sess_ip']} URL: {$_SERVER['REQUEST_URI']}".((isset($post))? " POST: ".implode(',',$post) : ""));
	return;
}elseif(@$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
	log_txt("неавторизованное соединение ip: ".$_SERVER['REMOTE_ADDR']);
	$out['result']="close";
	echo json_encode($out);
	exit;
}else{
?>
<!DOCTYPE html PUBLIC
 "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML>
<HEAD>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=koi8-r ;no-cashe">
<meta name="viewport" content="width=device-width, user-scalable=no">
<TITLE>Личный кабинет</TITLE>
<LINK rel="stylesheet" type="text/css" href="authorize.css">
<SCRIPT src="js/jquery-1.10.2.min.js"></SCRIPT>
<SCRIPT src="js/jquery.popupForm.js"></SCRIPT>
<SCRIPT language="javascript">
	var mobile = <?php echo $mobile? "true":"false"; ?>;
	$(document).ready(function() {
		$(window).resize(function(e){
			var h = $(window).height(), w = $(window).width(), banner = $('#banner').get(0);
			$('.box1').outerHeight(h); $('.box1').outerWidth(w);
			if( h < 400 ) $('.social, .contacts').hide(); else $('.social, .contacts').show();

			if(banner){
				if( w > 700 ) $(banner).css('font-size','16pt');
				else $(banner).css('font-size','14pt');

				if(h < 600){
					$('.social').hide();
					$(banner).css({top:0});
				}else{
					$('.social').show();
					$(banner).css({top:'100px'});
				}
			}

			if(h > w && h < 700 || h < 500){
				$('form > .form-logo').hide()
				$('form fieldset').css({margin:0});
			}else if(h > w && h >= 700){
				$('form > .form-logo').show().css({float:'none',margin:0});
				$('form fieldset').css({marginLeft:0});
			}else{
				$('form > .form-logo').show().css({float:'left',marginLeft:'10px'});
				$('form fieldset').css({marginLeft:'-40px'});
			}
		})
		$(window).trigger('resize');
		$('#login').focus();
	})
</SCRIPT>
</HEAD>
<BODY>
<DIV class="box1"><DIV class="box2"><DIV class="box3"><?php
	if($efforts !== false){
		if($efforts>4 || $efforts<1) $suff = 'ок'; elseif($efforts<5 && $efforts>1) $suff = 'ки'; else $suff = 'ка';
		if($efforts>0) $msg = "Регистрация нового оборудования: &ensp; У Вас осталось {$efforts} попыт{$suff}";
		else $msg = "Зарегистрировать новое оборудование не получилось!";
	}
	if(isset($msg)) echo "<DIV id=\"banner\" class=\"efforts".(($efforts==0)?'_red':'')."\">$msg</DIV>"; ?>
<form id="guard" method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
	<div class="form-header"><?php echo $config['authorize']['company']; ?></div>
	<div class="form-logo"><?php echo $config['authorize']['picture']; ?></div>
	<fieldset>
	<div class="form-item"><span class="label">логин</span><input id="login" type="text" name="auth_name"></div>
	<div class="form-item"><span class="label">пароль</span><input id="pass" type="password" name="auth_pass"></div>
	<div class="form-footer"><input type="submit" class="button" value="Войти"></div>
	</fieldset>
</form>
</DIV></DIV>
<?php
	if(COMPANY_SOCIAL != 'COMPANY_SOCIAL'){
		$links = array('vk'=>COMPANY_SOCIAL);
		echo "<div class=\"social\">";
		foreach($links as $k=>$v) echo "<span><img src=\"pic/$k.png\"> <a href=\"$v\" target=\"blank\">$v</a></span>";
		echo "</div>";
	}
	if(COMPANY_PHONE != 'COMPANY_PHONE'){
		echo "<div class=\"contacts\">";
		foreach(preg_split('/,/',COMPANY_PHONE) as $k=>$v) echo "<span class=\"phone\">&#9742; ".trim($v)." </span>";
		echo "</div>";
	}
?>
</div>
</DIV>
</BODY>
</HTML>
<?php
}
exit;
?>
