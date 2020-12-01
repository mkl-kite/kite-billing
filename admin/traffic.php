<?php
include_once("defines.php");
include_once("classes.php");
define("MRTG_CONF_DIR","/usr/local/etc/mrtg");
define("MRTG_HTTP","mrtg/");

$CSSfile="base-admin.css";
$jQuery="js/jquery-1.10.2.min.js";
if(!isset($q)) $q = new sql_query($config['db']);

$mydev = isset($_GET['device'])? str($_GET['device']) : "";

$dev=array();
$vip=array();

function readInfo($target) {
	global $dev;
	$port=preg_replace('/.*_/','',$target);
	$mydev=preg_replace('/_.*/','',$target);
	$file=$dev[$mydev]['workdir']."/".$target.'.info';
	$info='порт '.$port;
	if(is_array($dev[$mydev]['target'])) {
		$index=array_search($target,$dev[$mydev]['target']);
		if($index>=0) $info.=" : ".@$dev[$mydev]['info'][$index];
	}
	$content='';
	if ($pfile = @fopen($file,"r")) {
		while (($buffer = fgets($pfile, 4096)) !== false) {
			$content.=$buffer;
		}
		fclose($pfile);
		if($content!='') $info.=" : ".$content;
		
	}
	return $info;
}

function readMrtgConfig($dir) {
	global $mydev, $vip, $devorder, $q;
	$dev=array();
	$dir=preg_replace('/\/$/','',$dir);
	if (is_dir($dir)) {
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if(filetype($dir."/".$file)=='file' && (preg_match('/.cfg$/',$file) || preg_match('/.vip$/',$file))) {
					if ($pfile=@fopen($dir."/".$file,"r")) {
						$index=preg_replace('/.cfg.*/','',$file);
						$devorder[$index]=ip2long($index);
						while (($buffer = fgets($pfile, 4096)) !== false) {
							if(preg_match('/^#\s+System:/',$buffer)) {
								$dev[$index]['system']=preg_replace('/^#\s+System:\s*/','',$buffer);
							}
							if(preg_match('/^[^#]*WorkDir:/',$buffer)) { 
								$dev[$index]['workdir']=preg_replace(array('/^[^#]*WorkDir:\s*/','/\n$/'),array('',''),$buffer);
							}
							if(preg_match('/^[^#]*Target/',$buffer)) { 
								$dev[$index]['target'][]=preg_replace(array('/[^\[]*.([^\]]*).*/','/\n$/'),array('\1',''),$buffer);
								if(preg_match('/.vip$/',$file)) $dev[$index]['vip'][]=preg_replace(array('/.*:/','/\n$/'),array('',''),$buffer);
							}
						}
						fclose($pfile);
					}else{
						log_txt("Невозможно открыть файл ".$dir.$file);
					}
				}
			}
			if(count($dev)==0) {
				log_txt("В директории $dir не найдены файлы конфигурации");
				return false;
			}else{
				asort($devorder);
				if($mydev=='') $mydev=key($devorder);
				$q->query("SELECT address, mrtg FROM map WHERE mrtg!=''") or log_txt("traffic.php: Нет данных по mrtg в таблице map!");
				if($q->result) while($r = $q->result->fetch_assoc()) {
					$target=preg_replace(array('/.*\//','/-.*/'),array('',''),$r['mrtg']);
					$tmpdev=preg_replace('/_.*/','',$target);
					$index=@array_search($target,@$dev[$tmpdev]['target']);
					if($index>=0) $dev[$tmpdev]['info'][$index]=$r['address'];
				}
			}
		}else{
			log_txt("Нет доступа к директории '$dir'");
			return false;
		}
	}else{
		log_txt("Директория '$dir' не найдена!");
		return false;
	}
	return $dev;
}

function createDevList() {
	global $dev, $devorder, $mydev, $vip;
	$res = '';
	foreach($devorder as $k=>$v) {
		$select = ($k==$mydev)? " select" : "";
		if(@$dev[$k]['vip']) $class='vipdev'; else $class='device';
		$res .= "<a href=\"traffic.php?device=".$k."\">".
		"<dev class=\"$class".$select."\"><span class=\"devname\">".
		$dev[$k]['system']."</span><span class=\"devip\">$k</span></dev></a>";
	}
	return $res;
}

function createChartList($mydev) {
	global $dev;
	$dir = MRTG_HTTP.preg_replace(array('/\/$/','/.*\//'),array('',''),@$dev[$mydev]['workdir']);
	$devname = $dev[$mydev]['system'].(preg_match('/.vip$/',$mydev)? "" : $mydev);
	if(isset($dev[$mydev]['workdir']) && !is_dir($dev[$mydev]['workdir']))
		log_txt(__function__.": ERROR каталог для '$mydev' ({$dev[$mydev]['workdir']}) не найден!");
	$res = "<h3>$devname</h3>";
	if(isset($_GET['host'])){ // показывает диаграммы по устройству за все периоды
		$host = str($_GET['host']);
		foreach($dev[$mydev]['target'] as $k=>$v) {
			if(preg_match("/host=$host/",$v)) {
				$m = array("4 часа"=>$v."&period=-14400","день"=>$v."&period=-86400","неделя"=>$v."&period=week","месяц"=>$v."&period=month","год"=>$v."&period=year");
				foreach($m as $h=>$img){
					$res .= "<div class=\"mrtgday\"><div class=\"nameport\">$h</div><div class=\"port\"><img src=\"$img\"></div></div>";
				}
			}
		}
	}else{
		foreach($dev[$mydev]['target'] as $k=>$v) {
			$res .= '<div class="mrtgday">';
			if(@$dev[$mydev]['vip']) {
				$menuimg = "";
				if(preg_match('/\-day.png/',$v)) $href = "<a href=\"".preg_replace('/\-day.png/','.html',$v)."\" target=\"blank\">";
				elseif(preg_match('/&host=([0-9a-z][0-9a-z.]*)/',$v,$m)) $href = "<a href=\"traffic.php?device={$mydev}&host={$m[1]}\">";
				$img = "<img src=\"$v\">";
				$info = $dev[$mydev]['vip'][$k]; 
			}else{
				$menuimg = "<img src=\"pic/gtk-edit.png\" class=\"menuimg\" port=\"$v\"/>";
				$href = "<a href=\"".$dir."/".$v.".html\" target=\"blank\">";
				$img = "<img src=\"".$dir."/".$v."-day.png\">";
				$info = readInfo($v);
			}
			$res .= "<div class=\"nameport\">$info</div><div class=\"port\">".
				$href.$img."</a>".$menuimg."</div>";
			$res .= '</div>';
		}
	}
	return $res;
}

$TOP = "Traffic";
$META = "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"300\">\n
		 <META HTTP-EQUIV=\"Cache-Control\" CONTENT=\"no-cache\">\n
		 <META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">";

include("top.php");

$dev = readMrtgConfig(MRTG_CONF_DIR);
?>
<style type="text/css">
	.device, .vipdev {
		width: 200px;
		height: 40px;
		display: table;
		margin-bottom: 15px;
		float: none;
		border-radius: 4px;
		background: #aaa;
		background: -moz-linear-gradient(top, #bbb, #888);
		box-shadow: rgba(000,000,000,0.9) 0 1px 1px, inset rgba(255,255,255,0.4) 0 0px 0;
		font: bold 10pt sans-serif;
		position: relative;
		overflow: hide;
	}
	.select .devip {
		color: #fc7;
	}
	.devname {
		color: black;
		font: normal 10pt sans-serif;
		text-shadow: white 1px 1px;
		float:left;
	}
	.devip {
		color: white;
		text-shadow: black 1px 1px;
		position: absolute;
		right: 3px;
		bottom: 3px;
	}
	.menuimg {
		display:none;
	}
	#mrtgcharts .port {
		margin:0 0 30px 0;
		position:relative;
	}
	#mrtgcharts .port:hover .menuimg {
		display:block;
		position:absolute;
		top:5px;
		right:5px;
		cursor:pointer;
	}
	.nameport {
		text-align:left;
		max-width: 650px;
	}
</style>
<SCRIPT language="JavaScript">
<!--//
$(document).ready(function() {
	ldr = $.loader()
	$('.menuimg').click(function(){
		var p = $(this).attr('port'),
			info = $(this).parents('.port').prev('.nameport')
		$.popupForm({
			data: "go=mrtginfo&do=edit&device="+p,
			onsubmit: function(d) {
				if(d.result) {
					if(d.result=='OK') {
						info.empty().append(d.nameport)
					}
				}else{
					alert('Не удалось записать данные')
				}
			},
			loader:ldr
		})
		return false
	})
})
//-->
</SCRIPT>

<center>
<table id="mrtgcontent">
	<tr>
	<td style="vertical-align:top">
	<div id="mrtgmenu" style="float:left;height:100%;padding-top:50px">
<?php
	$devlist = createDevList();
	if(!$devlist) echo "Отсутствует список устройств!";
	else echo $devlist;
?>
	</div>
	</td>
	<td valign="top" style="text-align:right;padding-left:30px;">
	<div id="mrtgcharts">
<?php
	$chartlist = createChartList($mydev);
	if(!$chartlist) echo "Отсутствует список портов!";
	else echo $chartlist;
?>
	</div>
	</td>
	</div>
	</tr>
</table>
<center>
<?php
include("bottom.php");
?>
