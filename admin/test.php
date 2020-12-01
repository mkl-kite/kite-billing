<?php
$DEBUG=1;
include_once("authorize.php");
include_once("utils.php");
include_once("devices.cfg.php");
include_once("form.php");
include_once("classes.php");
include_once("table.php");
include_once("snmpclass.php");
// $req = "<pre>\n\$_SERVER=".sprint_r($_SERVER)."</pre>";
$req = "<pre>\n\$_REQUEST=".sprint_r(array_diff_key($_REQUEST,$_COOKIE))."</pre>";
$req .= "<pre>\n\$_COOKIE=".sprint_r($_COOKIE)."</pre>";

$in['go'] = (isset($_REQUEST['go']))? strict($_REQUEST['go']) : '';
$in['do'] = (isset($_REQUEST['do']))? strict($_REQUEST['do']) : '';
$in['id'] = (isset($_REQUEST['id']))? numeric($_REQUEST['id']) : '';
$in['ip'] = (isset($_REQUEST['ip']))? str($_REQUEST['ip']) : '';
$in['uid'] = (isset($_REQUEST['uid']))? numeric($_REQUEST['uid']) : '';
$in['table'] = (isset($_REQUEST['table']))? str($_REQUEST['table']) : '';
$GeoJSON=(key_exists('GeoJSON',$_REQUEST))? $_REQUEST['GeoJSON'] : false;
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>тест php</title>
		<script type="text/javascript" language="javascript" src="js/jquery-1.10.2.min.js"></script>
		<script type="text/javascript" language="javascript" src="js/jquery-ui/jquery-ui.min.js"></script>
		<script type="text/javascript" language="javascript" src="js/jquery.json-2.3.min.js"></script>
		<script type="text/javascript" language="javascript" src="js/jquery.cookie.min.js"></script>
		<script type="text/javascript" language="javascript" src="js/jquery.loader.js"></script>
		<script type="text/javascript" language="javascript" src="js/jquery.popupForm.js"></script>
		<link rel="stylesheet" type="text/css" href="js/jquery-ui/jquery-ui.css">
		<link rel="stylesheet" type="text/css" href="js/jquery-ui/autocomplete.css">
		<link rel="stylesheet" type="text/css" href="base-admin.css">
		<link rel="stylesheet" type="text/css" href="switches.css">
	<style>
		body {
			font: 10pt mono;
			margin: 0;
		}
		#data {
			position:absolute;
			background: none;
			right:0;
			overflow-y: scroll;
			padding: 8px;
		}
		#opt {
			position:relative;
			float:left;
			width:350px;
			height:97%;
			margin-right:10px;
			overflow-x:hidden;
			overflow-y:auto;
		}
		.ui-resizable-handle {
			background-color: #d0d0f0;
			box-sizing:border-box;
			border-left:1px solid #fff;
			border-right:1px solid #777;
			cursor:ew-resize;
			z-index:1000 !important;
		}
		SPAN.label {
			display:inline-block;
			font: normal 8pt sans-serif;
			min-width:60px;
			text-align:right;
			margin-right:7px;
		}
		[name=test] .field {
			margin-top:10px;
			white-space: nowrap;
		}
		[name=test] .field img {
			position:relative;
			cursor:pointer;
			top:4px;
			left:7px;
		}
		[name=test] input[type=text], #newfn {
			border: 1px solid #bbb;
		}
		.fieldplus {
			margin-top:20px;
			padding-bottom:20px;
			white-space: nowrap;
			border-bottom: 3px dashed #999;
		}
		[name=test] .footer {
/*			position: absolute; 
			width:100%;
			bottom:0; */
			padding: 20px 0;
		}
		[name=test] .footer .button {
			float:right;
			margin-right:10px;
		}
	</style>
	<script language="JavaScript">
	<!--//
	$(document).ready(function() {
		var ldr=$.loader();
		$('#testbutton').bind('click', function(){
			var f=$('form[name=test]'), req=[];
			f.find('[name][type=text]').each(function(i){
				req.push($(this).attr('name')+'='+$(this).val());
			})
			$.popupForm({
				data: req.join('&'),
				onsubmit: function(){},
				loader:ldr
			})
		})
		$('#addbutton').bind('click', function(){
			var f=$('form[name=test]'), el=f.find('.footer'), fld=$('#newfn').val(), req=[];
			if(fld != '' && f.find('[name][name='+fld+']').length == 0) {
				el.before('<div class="field"><span class="label">'+fld+'</span><input type="text" name="'+fld+'" value=""/><img src="pic/delete.png"></div>');
				$('#newfn').val('')
			}
		})
		$('#newfn').bind('keydown',function(e){
			if(e.keyCode==13) { // return
				e.preventDefault()
				$('#addbutton').click()
				return false
			}
		})
		$('form[name=test]').on('click', 'img', function(){
			$(this).parent().remove();
		})
		$('.tablecontainer').prepareTable();
		$(window).resize(function(){
			$('#data').outerHeight($(window).height() - $('#frm').outerHeight());
			$('#opt').outerHeight($(window).height() - $('#frm').outerHeight());
			$('#data').outerWidth($(window).width() - $('#opt').outerWidth());
		})
		$('#opt').resizable({
			handles: 'e',
			minWidth: '0',
			maxWidth: $(window).width()-300,
			resize: function(e,ui){
//				$('#data').outerWidth($(window).width() - ui.size.width - 10).css('left',ui.size.width+10);
			}
		})
		$(window).trigger('resize')
	})
	//-->
	</script>
</head>
<body>

<div id="opt">
	<div class="fieldplus">
	<span class="label">new</span><input id="newfn" type="text" name="new" value=""/>
	<input id="addbutton" type="button" value="add"/>
	</div>
	<form action="test.php" name="test">
<?php
		if(count($_GET)>0) {
		foreach($_GET as $k=>$v) {
		if(!key_exists($k,$in)) $in[$k] = str($v);
		print('
		<div class="field">
			<span class="label">'.$k.'</span><input type="text" name="'.$k.'" value=\''.$v.'\'/><img src="pic/delete.png">
		</div>
		');
		}
		} else {
?>
		<div class="field">
			<span class="label">go</span><input type="text" name="go" value="<?php echo $in['go']; ?>"/><img src="pic/delete.png">
		</div>
		<div class="field">
			<span class="label">do</span><input type="text" name="do" value="<?php echo $in['do']; ?>"/><img src="pic/delete.png">
		</div>
<?php
		}
?>
		<div class="footer">
			<input type="submit" class="button" value="Send"/>
			<input id="testbutton" type="button" class="button" value="form"/>
		</div>
	</form>
</div>
<div id="data">
<?php
echo $req;

if($in['go'] == 'stdform' || $in['table']=='') {
	$form = new form($config);
	if(key_exists('go',$in) && $in['go']!='') {
		$file="ajax/".$in['go'].".php";
		if(@filetype($file)=='file') {
			if($DEBUG>0) echo "<pre>\ntest.php\ninclude file=".sprint_r($file)."\n</pre>";
			include_once($file);
		}else{
			echo "file $file not found";
			echo "<pre>".sprint_r($_COOKIE)."</pre>";
		}
	}
}else{
	$in['go'] = (key_exists('go',$in))? $in['go'] : 'stdtable';
	$q = new sql_query($config['db']);
	$q->sql_fields($in['table']);
	$stdfld = array('go'=>0,'do'=>0,'tname'=>0,'table'=>0);
	foreach($_REQUEST as $k=>$v) if(!isset($stdfld[$k])) $add[] = "$k=$v";
	$add = isset($add)? '&'.implode('&',$add) : "";
	echo "<div class=\"tablecontainer\" query=\"go={$in['go']}&do={$in['table']}&page=1&pagerows=10&tname={$in['table']}".$add."\"></div>";
}
?>

</div>
</body>
</html>
