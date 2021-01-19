<?php
include ("defines.php");
include ("classes.php");
include ("jpgraph.php");
include ("jpgraph_line.php");
include ("jpgraph_utils.inc.php");
if(!isset($q)) $q = new sql_query($config['db']);

$class=(isset($_REQUEST['class']))? preg_replace('/[^a-z\-_]/','',mb_substr($_REQUEST['class'],0,31)) : '';
$intday=(isset($_REQUEST['intday']))? preg_replace('/[^0-9]/','',$_REQUEST['intday']) : 0;
$inttime=(isset($_REQUEST['inttime']))? preg_replace('/[^0-9]/','',$_REQUEST['inttime']) : 0;
$period=(isset($_REQUEST['period']))? preg_replace('/[^0-9\.]/','',$_REQUEST['period']) : 1;
$src=(isset($_REQUEST['source']))? preg_replace('/[^a-z\-_]/','',$_REQUEST['source']) : '';
$source = ($src)? "source = '$src' AND" : '';

$label = $legend = array('counter'=>'всего','iptv'=>'iptv');
$colors = array('counter'=>'green@0.4','iptv'=>'blue@0.4');
$month = array('1'=>"Янв",'2'=>"Фев",'3'=>"Мар",'4'=>"Апр",'5'=>"Май",'6'=>"Июн",'7'=>"Июл",'8'=>"Авг",'9'=>"Сен",'10'=>"Окт",'11'=>"Ноя",'12'=>"Дек");


$interval=$period * 300;

# Возвращает вместо 1000 1К и вместо 1000000 1М для градуирования оси Y
function SpeedCallback($aVal) {
	if($aVal>=1000000){
		$out=round($aVal/1000000,1);
		return $out."M";
	}elseif($aVal>=1000){
		$out=$aVal/1000;
		return $out."K";
	}else{
		return $aVal;
	}
}

if($intday>0) $inttime = $inttime + $intday*86400; // сдвиг на * дней
$now = floor((time()-$inttime)/$interval)*$interval; // время начала текущего интервала
$begintime = $now - (3600 * 24 * $period); // ровно [period] суток назад от начала текущего интервала
$endtime = $now;
$btime = date('Y-m-d H:i:s',$begintime - 300);
$etime = date('Y-m-d H:i:s',$begintime + 3600 * 24 * $period);
$fields = $q->table_fields('usrontime');
if($fields){
	unset($fields['id']); unset($fields['when']);
	if($class && isset($fields[$class])) $f[$class] = "max(`{$class}`) as `$class`";
	else foreach($fields as $n=>$v) $f[$n] = "max(`{$n}`) as `$n`";
} 
// log_txt("users_online: fields: ".arrstr($f));

$result = $q->select("
	SELECT floor(unix_timestamp(`when`)/$interval)*$interval as wn, ".implode(', ',$f)."
	FROM usrontime
	WHERE `when` BETWEEN '$btime' AND '$etime'
	GROUP BY wn
");
// log_txt("users_online: SQL: ".sqltrim($q->sql));

foreach($f as $n=>$v){ $min[$n]=1000000; $max[$n]=0; }
$num = count($result);
if ($num>0) {
	$res = reset($result);
	$ut_prev = $res['wn'];
}

for ($i = 0; $i < (86400*$period/$interval); $i++) {
	$curtime = $begintime + $i * $interval;
	# пропустить все записи до нужного времени
	while (@$res && @$res['wn'] < $curtime) $res = next($result);
	if ($num=0 || @$res['wn'] != $curtime) {
		foreach($f as $n=>$v) $data[$n][$i] = 0;
	}else{
		foreach($f as $n=>$v){
			$data[$n][$i] = $res[$n];
			if($data[$n][$i]<$min[$n]) $min[$n]=$data[$n][$i];
			if($data[$n][$i]>$max[$n]) $max[$n]=$data[$n][$i];
		}
		$res = next($result); 
	};
	$xdata[$i] = $begintime + $i * $interval;
}

foreach($f as $n=>$v){
	$last[$n] = $data[$n][$i-1];
	$legend[$n] = sprintf("%-10s  min: %-5s   max: %-5s   last: %-5s",$legend[$n],$min[$n],$max[$n],$last[$n]);
}

$dateUtils = new DateScaleUtils();
list($tickPositions,$minTickPositions) = $dateUtils->GetTicks($xdata);
if($period>734) { $tick = '1 year'; $XLabelFormat = 'Y'; $half = '1 month'; }
elseif($period>=48) { $tick = '1 month'; $XLabelFormat = 'M'; $half = 86400; }
elseif($period>14) { $tick = 86400 * 7; $XLabelFormat = 'd'; $half = 86400; }
elseif($period>=3) { $tick = 86400; $XLabelFormat = 'd'; $half = 6 * 3600; }
elseif($period>=2) { $tick = 2*3600; $XLabelFormat = 'H'; $half = 3600; }
else { $tick = 3600; $XLabelFormat = 'H'; $half = floor($tick / 2); }
$begin = floor($begintime/$tick)*$tick;

// log_txt(preg_replace('/.*\//','',__file__).": tick = $tick half = $half   interval = $interval  begin = ".date('Y-m-d H:i:s',$begin)." ($begin)");
if($period>734){
	$old_p = 0; $minTickPositions=$tickPositions; $tickPositions = array();
	foreach($xdata as $k => $t) {
		$p = strtotime(date("Y-01-01",$t));
		if($t == $p || ($p > $old_p)) $tickPositions[] = $p;
		$old_p = $p;
	}
}elseif($period<48 && is_numeric($tick)){
	if($tick == 604800) $begin = floor(($begintime - (4*86400))/$tick)*$tick + 4*86400;
	$old_p = 0; $old_hp = 0;
	foreach($xdata as $k => $t) {
		$p = floor(($t - $begin)/$tick) * $tick;
		$hp = floor(($t - $begin)/$half) * $half;
		if($t == $p || ($p > $old_p)) $tickPositions[] = $t;
		elseif($t == $hp || $hp > $old_hp) $minTickPositions[] = $t;
		$old_p = $p; $old_hp = $hp;
	}
}
$xmin = $xdata[0];
$xmax = $xdata[$i-1];

// Create the graph. These two calls are always required
$graph = new Graph(700,180+count($f)*20);
$graph->SetScale('intlin',0,0,$xmin,$xmax);
$graph->title->Set("Кол-во пользователей ".($class?mb_strtoupper($label[$class]):"")." за период с ".date("d-m-Y",$begintime)." до ".date("d-m-Y",$endtime));
$graph->title->SetFont(FF_ARIAL,FS_BOLD);
// $graph->title->SetMargin(8,0,0,0);
$graph->legend->Pos(0.08,0.97,"left","bottom");
$graph->legend->SetFont(FF_ARIAL,FS_NORMAL,9);
//$graph->legend->SetTitleMargin(3);
$graph->legend->SetShadow(false);

// $graph->SetShadow();
$graph->img->SetMargin(60,30,10,40+count($f)*14);

$graph->xaxis->SetPos('min');
$graph->xaxis->SetTickPositions($tickPositions,$minTickPositions);
$graph->xaxis->SetLabelFormatString($XLabelFormat,true);
$graph->xaxis->SetLabelAngle(0);
$graph->xgrid->Show();
$graph->yaxis->title->Set("users",'low');
$graph->yaxis->title->SetMargin(12);
$graph->SetAlphaBlending();
$graph->yaxis->SetLabelFormatCallback('SpeedCallback');

// Create the linear plots for each category
foreach($data as $n=>$d) {
	$lp = new LinePlot($d,$xdata);
	$lp->SetFillColor($colors[$n]);
	$lp->SetLegend($legend[$n]);
	$graph->Add($lp);
//	log_txt("users_online: $n => color: {$colors[$n]} legend: {$legend[$n]}");
}

// Display the graph
$graph->Stroke();
?>
