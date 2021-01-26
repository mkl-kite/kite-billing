<?php
global $opdata;
include_once("classes.php");
$q = new sql_query($config['db']);

if (isset($_POST['auth_name'])) {
	$login=$q->escape_string($_POST['auth_name']);
	$pass=$q->escape_string($_POST['auth_pass']);
	$op = $q->select("SELECT *, unique_id as id, status as level FROM operators WHERE login='$login' and pass='$pass' and blocked=0",1);
	if ($op) {
		$ip_ok="no";
		if($oprows = $q->select("SELECT * FROM kassa WHERE computers like '%".$op['ip']."%'",1)) {
			foreach(explode(",",$oprows['computers']) as $i => $m) if ($m==$op['ip']) $ip_ok="yes";
			$op['kid']=$oprows['kid'];
		}else{
			$op['kid']=0;
		}
		session_start();
		$_SESSION['sess_user_id'] = $op['id'];
		$_SESSION['sess_ip'] = $_SERVER['REMOTE_ADDR'];
		$_SESSION['sess_opdata'] = $op;
		$opdata = $op;
		log_txt("подключился с IP=".$_SESSION['sess_ip']);
	}else{
		log_txt("попытка подключения log:$login pass:$pass с IP=".$_SERVER['REMOTE_ADDR']);
	}
	$h="Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	if($DEBUG>8) log_txt(@sprint_r($_SERVER));
	header($h);
	exit;
}
if (isset($_GET['go']) AND preg_match('/.*logout$/',$_GET['go'])) {
	session_start();
	$opdata=$_SESSION['sess_opdata'];
	log_txt("отключился с IP=".$_SERVER['REMOTE_ADDR']);
	session_destroy();
	header("Location: https://".$_SERVER['HTTP_HOST'].preg_replace('/\?.*/','',$_SERVER['REQUEST_URI']));
	exit;
}
if (isset($_REQUEST[session_name()]) || isset($_COOKIE[session_name()])) session_start();
if (isset($_SESSION['sess_user_id']) AND $_SESSION['sess_ip'] == $_SERVER['REMOTE_ADDR']) {
	$opdata=$_SESSION['sess_opdata'];
	if(isset($opdata['login'])) define("ADMIN",$opdata['login']);
	foreach($_POST as $k=>$v) if(!preg_match('/^old_/',$k)) $post[] = "$k=".sqltrim($v);
	log_txt("IP: {$_SERVER['REMOTE_ADDR']} URL: {$_SERVER['REQUEST_URI']}"
		.((isset($post))? " POST: ".implode(',',$post) : ""));
	return;
}elseif(@$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
	log_txt("sess_ip=".$_SESSION['sess_ip']."REMOTE_ADDR=".$_SERVER['REMOTE_ADDR']);
	$out['result']="close";
	$out['action']="reload";
	echo json_encode($out,JSON_UNESCAPED_UNICODE);
}else{
?>
<!DOCTYPE html PUBLIC
 "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML>
<HEAD>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8 ;no-cashe">
<link rel="stylesheet" type="text/css" href="base-admin.css"> 
<link rel="icon" type="image/png" href="logo.png" sizes="16x16">
<TITLE>Панель управления биллингом</TITLE>
<SCRIPT type="text/javascript" src="js/jquery-1.10.2.min.js"></SCRIPT>
<style type="text/css">
	.box1 {
		top: 0;
		left: 0;
		overflow: auto;
		position: fixed;
		text-align: center;
		height: 100%;
		width: 100%;
	}
	.box2 {
		display: inline-table;
		height: 100%;
	}
	.box3 {
		display: table-cell;
		height: 400px;
		vertical-align: middle;
	}

	#sh_authform {
		background-color:#FFF;
	}
	#city {
		width:400px;
		position:relative;
		top:-20px;
		left:-20px;
		z-index:10;
		font:normal bold 32pt sans-serif;
		color:#00b;
		text-shadow:#888 2px 2px 3px;
	}
	#guard {
		width:250px;
		border:1px solid #686;
		position:absolute;
		top:1%;
		right:3%
	}
</style>
</HEAD>
<BODY>
<DIV class="box1">
<DIV class="box2">
<DIV class="box3">
	<img src="pic/logo.png">
<SPAN id="city"><?php echo COMPANY_UNIT; ?></SPAN>
</DIV>
</DIV>
</DIV>
<form id="guard" class="normal" method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
<DIV class="form-item">
    <SPAN class="form-title label" style="width:80px">Логин:</SPAN>
	<SPAN class="field">
		<input type="text" class="form-field form-text" name="auth_name" style="width:130px"><br>
	</SPAN>
</DIV>
<DIV class="form-item">
    <SPAN class="form-title label" style="width:80px">Пароль:</SPAN>
	<SPAN class="field">
		<input type="password" class="form-field form-text" name="auth_pass" style="width:130px"><br>
	</SPAN>
</DIV>
<DIV class="submit-container">
	<SPAN class="footer-right">
		<input type="submit" class="submit-button" value="Войти" style="margin-right:25px"><br>
	</SPAN>
</DIV>
</form>
</DIV>
</CENTER>
</BODY>
</HTML>
<?php 
}
exit;
?>
