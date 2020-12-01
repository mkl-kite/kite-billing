<?php
include ("defines.php");
include ("classes.php");
include ("jpgraph.php");
include ("jpgraph_line.php");
include ("jpgraph_utils.inc.php");
if(!isset($q)) $q = new sql_query($config['db']);

$intday=(isset($_REQUEST['intday']))? preg_replace('/[^0-9]/','',$_REQUEST['intday']) : 0;
$inttime=(isset($_REQUEST['inttime']))? preg_replace('/[^0-9]/','',$_REQUEST['inttime']) : 0;
$period=(isset($_REQUEST['period']))? preg_replace('/[^0-9\.]/','',$_REQUEST['period']) : 1;
$src=(isset($_REQUEST['source']))? preg_replace('/[^a-z\-_]/','',$_REQUEST['source']) : '';
$source = ($src)? "source = '$src' AND" : '';

$interval=$period * 300;
$speed_limit=10*1024*1024*1024;

function SpeedCallback($v) {
	if ($v >= 1000000000) $out = round($v/1000000000,1)."G";
	elseif ($v >= 1000000) $out = round($v/1000000,1)."M";
	elseif ($v >= 1000) $out = ($v/1000)."K";
	else $out = $aVal;
	return $out;
}

if($intday>0) $inttime = $inttime + $intday*86400; // сдвиг на * дней
$now = floor((time()-$inttime)/$interval)*$interval; // время начала текущего интервала
$begintime = $now - (3600 * 24 * $period); // ровно [period] суток назад от начала текущего интервала
$endtime = $now;
$btime=date('Y-m-d H:i:s',$begintime - 300);
$etime=date('Y-m-d H:i:s',$begintime + 3600 * 24 * $period);

$result = $q->select("
	SELECT floor(unix_timestamp(`w`)/$interval)*$interval as wn, max(`in`) as i, max(`out`) as o
	FROM srctraf 
	WHERE $source `w` BETWEEN '$btime' AND '$etime'
	GROUP BY wn
");
// log_txt(preg_replace('/.*\//','',__file__).": SQL: ".sqltrim($q->sql));
$num = count($result);
if ($num>0) {
	$res = reset($result);
	$ut_prev = $res['wn'];
}

for ($i = 0; $i < (3600*24*$period/$interval); $i++) {
	$curtime = $begintime + $i * $interval;
	# пропустить все записи до нужного времени
	while (@$res && @$res['wn'] < $curtime) $res = next($result);
	if ($num=0 || @$res['wn'] != $curtime) {
		$data[$i] = 0; $data1[$i] = 0;	
	}else{
		$data[$i] = ($res['i']) * 8 / 300;
		$data1[$i] = ($res['o']) * 8 / 300;
		if($data[$i]>$speed_limit) $data[$i]=$speed_limit; elseif($data[$i]<0) $data[$i]=0;
		if($data1[$i]>$speed_limit) $data1[$i]=$speed_limit; elseif($data1[$i]<0) $data1[$i]=0;
		$res = next($result); 
	};
	$xdata[$i] = $begintime + $i * $interval;
}
		
$dateUtils = new DateScaleUtils();
list($tickPositions,$minTickPositions) = $dateUtils->GetTicks($xdata);
if($period>735) { $tick = 86400 * 365; $XLabelFormat = 'Y'; $half = floor($tick / 2); }
elseif($period>=48) { $tick = '1 month'; $XLabelFormat = 'M'; $half = 86400; }
elseif($period>14) { $tick = 86400 * 7; $XLabelFormat = 'd'; $half = 86400; }
elseif($period>=3) { $tick = 86400; $XLabelFormat = 'd'; $half = 6 * 3600; }
elseif($period>=2) { $tick = 2*3600; $XLabelFormat = 'H'; $half = 3600; }
else { $tick = 3600; $XLabelFormat = 'H'; $half = floor($tick / 2); }
$begin = floor($begintime/$tick)*$tick;

// log_txt(preg_replace('/.*\//','',__file__).": tick = $tick half = $half   interval = $interval  begin = ".date('Y-m-d H:i:s',$begin)." ($begin)");
if($period<48 && is_numeric($tick)){
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
$graph = new Graph(650,150);	
$graph->SetScale('intlin',0,0,$xmin,$xmax);
$graph->title->Set("Трафик по ".$src." за период с ".date("d-m-Y",$begintime)." до ".date("d-m-Y",$endtime));
$graph->title->SetFont(FF_ARIAL,FS_BOLD);
// $graph->SetShadow();
$graph->img->SetMargin(70,20,10,30);

$graph->xaxis->SetPos('min');
$graph->xaxis->SetTickPositions($tickPositions,$minTickPositions);
$graph->xaxis->SetLabelFormatString($XLabelFormat,true);
$graph->xaxis->SetLabelAngle(0);
$graph->xgrid->Show();
#$graph->xaxis->title->Set("hour",'low');
$graph->yaxis->title->Set("bit/sec",'low');
#$graph->xaxis->title->SetMargin(12);
$graph->yaxis->title->SetMargin(12);
$graph->SetAlphaBlending();
$graph->yaxis->SetLabelFormatCallback('SpeedCallback');

// Create the linear plots for each category
$dplot[] = new LinePLot($data,$xdata);
$dplot[] = new LinePLot($data1,$xdata);

$dplot[0]->SetFillColor("blue@0.4");
$dplot[1]->SetFillColor("green@0.4");

// Add the plot to the graph
$graph->Add($dplot[1]);
$graph->Add($dplot[0]);

#$graph->xaxis->SetTextTickInterval(2);

// Display the graph
$graph->Stroke();
?>
