<?php
include("defines.php");
include("classes.php");
$user = (key_exists('user',$_REQUEST))? $_REQUEST['user']:'';
$uid = (key_exists('uid',$_REQUEST))? $_REQUEST['uid']:0;
if($uid>0) {
	$q = new sql_query($config['db']);
	$user = $q->select("SELECT user FROM users WHERE uid = {$uid}",4);
}
if ($user!="") {
	echo "user=".$user."<BR>";
	exec("/usr/bin/sudo -u root /root/bin/radgrep ".escapeshellcmd($user)." > /tmp/radlog.tmp 2>&1");
}else{
	exec("/usr/bin/sudo -u root /root/bin/radcat > /tmp/radlog.tmp 2>&1");
}
if($pfile=fopen("/tmp/radlog.tmp","r")){
?><PRE><FONT FACE="monospace"><?php
while(!feof($pfile)) {
	$buff = fgets($pfile,255);
	echo $buff;
}
fclose($pfile);
}
?>
</FONT>
</PRE>
