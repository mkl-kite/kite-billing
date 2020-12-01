<?php
include_once("classes.php");
include_once("defines.php");
include_once("jpgraph.php");
include_once("jpgraph_line.php");
include_once("jpgraph_utils.inc.php");
$interval=300;
$period=3600*24;
$speed_limit=1310720;

$q = new sql_query($config['db']);

function sql_log($what) {
	if($DEBUG>0) {
		$pfile=fopen ("/tmp/php-sql.log","a");
		fputs($pfile,"$what\n");
		fclose($pfile);
	}
	return 0;
}

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

$intday=(key_exists('intday',$_REQUEST))? $_REQUEST['intday'] : 0;
$inthour=(key_exists('inthour',$_REQUEST))? $_REQUEST['inthour'] : 0;
$inttime=$intday*86400+$inthour*3600;
$ct=time()-$inttime;
$unixtime=floor($ct/$interval)*$interval;
$begintime=$unixtime-$period;
$adjstart = floor($unixtime/$interval);

$q->query("SELECT unix_timestamp(`when`) as w, counter as u FROM usrontime
                      WHERE `when`>='".date("Y-m-d H:i:s",$begintime)."' LIMIT 288");

$num=$q->rows();
$min=1000000; $max=0;
if($num>0){
	$res=$q->result->fetch_assoc(); 
	$ut_prev=$res['w']; $data_prev=$res['u'];
	if($data_prev<$min) $min=$data_prev;
	if($data_prev>$max) $max=$data_prev;
}

for($i=0; $i<($period/$interval); $i++){
	$curtime=$begintime+$i*$interval;
	while(@$res && @$res['w']<$curtime) {
		$res = $q->result->fetch_assoc();
	}
	if($num=0 || @$res['w'] != $curtime) {
		$data[$i] = 0;
		$ut_prev = $curtime; $data_prev=0;
	}else{ 
		$data[$i] = @$res['u'];
		if($data[$i]<$min) $min=$data[$i];
		if($data[$i]>$max) $max=$data[$i];
		$ut_prev = @$res['w']; $data_prev=$res['u'];
		$res = $q->result->fetch_assoc();
	}
	$xdata[$i] = $begintime+$i*$interval;
}
$last = $data[$i-1];
		
$dateUtils = new DateScaleUtils();
list($tickPositions,$minTickPositions) = $dateUtils->GetTicks($xdata);
$begin = floor($begintime/3600)*3600;
for($t=0; $t<=(($unixtime-$begintime)/3600); $t++){
	$tickPositions[$t] = $begin+$t*3600;
	$minTickPositions[$t] = $tickPositions[$t]+1800;
}

$xmin = $xdata[0];
$xmax = $xdata[$i-1];
// Create the graph. These two calls are always required
$graph = new Graph(700,200);
$graph->SetScale('intlin',0,0,$xmin,$xmax);
$graph->title->Set("Users online");
$graph->title->SetFont(FF_FONT1,FS_BOLD);
// $graph->SetShadow();
$graph->img->SetMargin(60,30,20,60);

$graph->xaxis->SetPos('min');
$graph->xaxis->SetTickPositions($tickPositions,$minTickPositions);
$graph->xaxis->SetLabelFormatString('H',true);
$graph->xaxis->SetLabelAngle(0);
#$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD,8);
#$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD,8);
$graph->xgrid->Show();
#$graph->xaxis->scale->ticks->Set(7200,300);
$graph->SetAlphaBlending();
$graph->xaxis->title->Set("hour",'low');
$graph->yaxis->title->Set("users",'low');
$graph->xaxis->title->SetMargin(6);
$graph->yaxis->title->SetMargin(12);
$graph->yaxis->SetLabelFormatCallback('SpeedCallback');
$graph->footer->left->Set("min ".$min);
$graph->footer->left->SetFont(FF_ARIAL,FS_NORMAL,9);
$graph->footer->center->Set("max ".$max);
$graph->footer->center->SetFont(FF_ARIAL,FS_NORMAL,9);
$graph->footer->right->Set("last ".$last);
$graph->footer->right->SetFont(FF_ARIAL,FS_NORMAL,9);
$graph->footer->SetMargin(30,30,6,20);

// Create the linear plots foreach category
$dplot[] = new LinePLot($data,$xdata);

$dplot[0]->SetFillColor("green@0.4");

// Create the accumulated graph
$accplot = new AccLinePlot($dplot);

// Add the plot to the graph
$graph->Add($accplot);

#$graph->xaxis->SetTextTickInterval(2);

// Display the graph
$graph->Stroke();
?>
