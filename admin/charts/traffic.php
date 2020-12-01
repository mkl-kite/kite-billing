<?php
include_once("defines.php");
include_once("classes.php");
include_once("jpgraph.php");
include_once("jpgraph_line.php");
include_once("jpgraph_utils.inc.php");
if(!isset($q)) $q = new sql_query($config['db']);

$intday=(isset($_REQUEST['intday']))? numeric($_REQUEST['intday']) : 0;
$inttime=(isset($_REQUEST['inttime']))? numeric($_REQUEST['inttime']) : 0;
$period=(isset($_REQUEST['period']))? numeric($_REQUEST['period']) : 3600*24;
$user=(isset($_REQUEST['user']) && is_string($_REQUEST['user']))? str($_REQUEST['user']) : '';
$uid=(isset($_REQUEST['user']) && !preg_match('/^\d/',$_REQUEST['user']))? numeric($_REQUEST['user']) : '';
$uid=(isset($_REQUEST['uid']))? numeric($_REQUEST['uid']) : 0;
$id=(isset($_REQUEST['id']))? numeric($_REQUEST['id']) : 0;
$table=(isset($_REQUEST['table']))? str($_REQUEST['table']) : '';
if(!$user && $uid) $user = $q->select("SELECT user FROM users WHERE uid='{$uid}'",4);
if(!$user && $id && $table) {
	$r = $q->get($table,$id);
	if(isset($r['user'])) $user = $r['user'];
	elseif(isset($r['username'])) $user = $r['username'];
	elseif(isset($r['uid'])) {
		$user = $q->select("SELECT user FROM users WHERE uid='{$r['uid']}'",4);
	}
}

$interval=300;
$speed_limit=100*1024*1024;

function SpeedCallback($aVal) {
	if ($aVal>=1000000) {
		$out=round($aVal/1000000,1);
		return $out."M";
		}
	elseif ($aVal>=1000) {
		$out=$aVal/1000;
		return $out."K";
		}
	else {
		return $aVal;
		}
}
$inttime=$intday*86400;
$unixtime=floor((time()-$inttime)/$interval)*$interval;
$begintime=$unixtime-$period;
$btime=date('Y-m-d H:i:s',$begintime-300);
$etime=date('Y-m-d H:i:s',$begintime+86400);

$q->query("
	SELECT unix_timestamp(`when`) as w, in_bytes as i, out_bytes as o, time_on as t 
	FROM traffic 
	WHERE user='$user' AND `when` BETWEEN '$btime' AND '$etime'
	ORDER BY w
");
$num=$q->rows();
if ($num>0) { 
	$res=$q->result->fetch_assoc(); 
	$ut_prev=$res['w']; $data_prev=$res['i']; $data1_prev=$res['o']; $to_prev=$res['t'];
	}
for ($i=0; $i<($period/$interval); $i++) {
	$curtime=$begintime+$i*$interval;
	# пропустить все записи до нужного времени
	while (@$res && @$res['w']<$curtime) {
		$res=$q->result->fetch_assoc(); 
		};
	if ($num=0 || @$res['w']!=$curtime) {
		$data[$i] = 0; $data1[$i] = 0;	
		$ut_prev=$curtime; $data_prev=0; $data1_prev=0; $to_prev=0;
		}
	else { 
		if ($res['t']==0) { $divizor=$period; } else { $divizor=$res['t']; };
		if ($res['i']>=$data_prev && $res['t']-$to_prev<310) { $data[$i] = ($res['i']-$data_prev)/37.5; } else { $data[$i] = $res['i']*(8/$divizor); };
		if ($res['o']>=$data1_prev && $res['t']-$to_prev<310) { $data1[$i] = ($res['o']-$data1_prev)/37.5; } else { $data1[$i] = $res['o']*(8/$divizor); };
		if ($data[$i]>$speed_limit) { $data[$i]=$speed_limit; };
		if ($data1[$i]>$speed_limit) { $data1[$i]=$speed_limit; };
		$ut_prev=$res['w']; $data_prev=$res['i']; $data1_prev=$res['o']; $to_prev=$res['t'];
		$res=$q->result->fetch_assoc(); 
		};
	$xdata[$i] = $begintime+$i*$interval;
	};
		
$dateUtils = new DateScaleUtils();
list($tickPositions,$minTickPositions) = $dateUtils->GetTicks($xdata);
$begin = floor($begintime/3600)*3600;
for ($t=0; $t<=(($unixtime-$begintime)/3600); $t++) {
	$tickPositions[$t] = $begin+$t*3600;
	$minTickPositions[$t] = $tickPositions[$t]+1800;
	}

$xmin = $xdata[0];
$xmax = $xdata[$i-1];
// Create the graph. These two calls are always required
$graph = new Graph(700,200);	
$graph->SetScale('intlin',0,0,$xmin,$xmax);
$graph->title->Set("Traffic for ".$user." period ".date("Y-m-d",$begintime)." ".date("Y-m-d",$begintime+86400));
$graph->title->SetFont(FF_FONT1,FS_BOLD);
// $graph->SetShadow();
$graph->img->SetMargin(60,30,20,60);

$graph->xaxis->SetPos('min');
$graph->xaxis->SetTickPositions($tickPositions,$minTickPositions);
$graph->xaxis->SetLabelFormatString('H',true);
$graph->xaxis->SetLabelAngle(0);
$graph->xgrid->Show();
$graph->xaxis->title->Set("hour",'low');
$graph->yaxis->title->Set("bit/sec",'low');
$graph->xaxis->title->SetMargin(12);
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
