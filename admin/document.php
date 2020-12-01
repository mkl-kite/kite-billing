<?php
include_once("authorize.php");
include_once("utils.php");
include_once "documents.cfg.php";
include_once "docdata.cfg.php";

if(!isset($_REQUEST['id'])) stop(array('result'=>'ERROR','desc'=>'Документ не найден!'));
$id = numeric($_REQUEST['id']);

if(!isset($_REQUEST['type'])){
	if(!($doc = get_docdata($id))) stop(array('result'=>'ERROR','desc'=>'Документ не существует!'));
	$_REQUEST['type'] = $doc['type'];
}
$type = strict($_REQUEST['type']);
$dir = "docs/";
$file = "print_{$type}.php";
$title = (isset($_REQUEST['title']))? $_REQUEST['title'] : "Документ '{$type}[$id]' для печати"; 

if(!file_exists($dir.$file)) stop(array('result'=>'ERROR','desc'=>"Документ $type не существует!"));

$TOP = "$title";
$showmenu="no";
include("top.php");
include($file);
include("bottom.php");
?>
