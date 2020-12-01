<?php 
include_once("classes.php");

$woid = (isset($_REQUEST['id']))? numeric($_REQUEST['id']) : '';
if(!$q) $q = new sql_query($config['db']);
$wo = $q->get('workorders',$woid);
if($wo['type'] == 'decree' || $wo['type'] == '') include_once('print_worder.php');
elseif($wo['type'] == 'permit') {
	include_once('print_wopermit.php');
	echo '<div style="page-break-after:always;"></div>';
	include_once('print_worder.php');
}
?>
