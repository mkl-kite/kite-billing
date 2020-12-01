<?php
include_once("authorize.php");
include_once("utils.php");
include_once "documents.cfg.php";
include_once "docdata.cfg.php";

if(!isset($client)) 
	stop(array('result'=>'ERROR','desc'=>"Client not logged in!"));

if(!isset($_REQUEST['id'])) stop(array('result'=>'ERROR','desc'=>'Документ не найден!'));
$id = numeric($_REQUEST['id']);

if(!isset($doc) && !($doc = get_docdata($id)))
	stop(array('result'=>'ERROR','desc'=>"Документ не найден!"));

if($doc['uid']!=$client['uid'])
	stop(array('result'=>'ERROR','desc'=>"Доступ запрещён!"));

if(!isset($type)) $type = $doc['type'];
$dir = "../admin/docs/";
$file = "print_{$type}.php";
$title = (isset($_REQUEST['title']))? $_REQUEST['title'] : "Документ '{$type}[$id]' для печати"; 

if(!file_exists($dir.$file)){
	log_txt("document.php:  filename = '".$dir.$file."'");
	stop(array('result'=>'ERROR','desc'=>'Отсутствует шаблон документа!'));
}

$TOP = "$title";
$showmenu="no";
include("top.php");
include($file);
include("bottom.php");
?>
