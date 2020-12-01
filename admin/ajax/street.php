<?php
$call="online";
include_once("defines.php");
include_once("utils.php");
include_once("classes.php");

$term=$_REQUEST['term'];
if(is_array($term)) $term = (isset($term['term']))? $term['term'] : '';
else $term=str($term);
$q = new sql_query($config['db']);

if($term!='') {
	$streets=array();
	if($streets=$q->fetch_all("
		SELECT distinct left(trim(address),char_length(trim(address))-locate(' ',reverse(trim(address)))) as street
		FROM users
		WHERE address like '%$term%'
		HAVING street!=''
		ORDER BY address
		LIMIT 200
	")) stop(array('result'=>'OK','streets'=>$streets));
	else stop(array('result'=>'OK','streets'=>array()));
}else{
    stop(array('result'=>"ERROR",'desc'=>"пустой запрос"));
}
?>