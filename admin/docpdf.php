<?php
include_once "authorize.php";
include_once "utils.php";
include_once "documents.cfg.php";
include_once "docdata.cfg.php";

if(!isset($_REQUEST['id'])) 
	stop(array('result'=>'ERROR','desc'=>"type={$_REQUEST['type']} id={$_REQUEST['id']}"));

$id = numeric($_REQUEST['id']);
if(!isset($_REQUEST['type']) && isset($_REQUEST['id'])){
	if(!($doc = get_docdata($id))) $doc = array('id'=>0,'type'=>'unknown');
	$type = $doc['type'];
	$_REQUEST['type'] = $type;
}
$type = strict($_REQUEST['type']);
$pdf = "{$type}-{$id}.pdf";

// log_txt("docpdf.php: doc=".arrstr($doc));
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
