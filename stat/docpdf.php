<?php
include_once "authorize.php";
include_once "utils.php";
include_once "documents.cfg.php";
include_once "docdata.cfg.php";

if(!isset($client)) 
	stop(array('result'=>'ERROR','desc'=>"Client not logged in!"));

if(!isset($_REQUEST['id'])) 
	stop(array('result'=>'ERROR','desc'=>"type={$_REQUEST['type']} id={$_REQUEST['id']}"));

$id = numeric($_REQUEST['id']);
if(!($doc = get_docdata($id)))
	stop(array('result'=>'ERROR','desc'=>"Документ не найден!"));

if($doc['uid']!=$client['uid'])
	stop(array('result'=>'ERROR','desc'=>"Доступ запрещён!"));

$type = $_REQUEST['type'] = $doc['type'];
$pdf = "{$type}-{$id}.pdf";

ob_start();
include "document.php";
$html = ob_get_clean();

$dscr = array( 0 => array("pipe","r"), 1 => array("pipe","w"));
$proc = proc_open("/usr/local/bin/wkhtmltopdf -B 10mm -T 10mm -L 10mm -R 10mm -s A4  - -",$dscr,$pipes);
if(is_resource($proc)){
	ob_start();
	header("Content-type: application/pdf");
	fwrite($pipes[0], $html);
	fclose($pipes[0]);
	echo stream_get_contents($pipes[1]);
	fclose($pipes[1]);
	$result = proc_close($proc);
	ob_end_flush();
}
?>
