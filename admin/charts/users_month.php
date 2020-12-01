<?php
include_once("defines.php");
include_once("classes.php");
include_once("jpgraph.php");
include_once("jpgraph_line.php");
include_once("jpgraph_utils.inc.php");
$q = new sql_query($config['db']);

$intday=(array_key_exists('intday',$_REQUEST))? $_REQUEST['intday'] : 0;
$interval=86400;
$period=3600*24*365;
$speed_limit=1310720;
// $pfile=fopen ("/tmp/php-sql.log","w");fclose($pfile);

function txt_log($what)
{
  if ($DEBUG>0) {
      $pfile=fopen ("/tmp/php-sql.log","a");
      fputs($pfile,"$what\n");
      fclose($pfile);
	}
	return 0;
  }

$inttime = $intday * 86400;
$ct = time() - $inttime;
# unixtime - это текущее время выровненное по интервалу
$unixtime = floor($ct/$interval) * $interval;
# begintime - это время выровненное по интервалу минус период (видимая область диаграммы)
$begintime = $unixtime - $period;

#Запрос к базе о кол-ве пользователей по времени
$res = $q->fetch_all("
	SELECT date(`when`) as w, max(counter) as name
	FROM usrontime
	WHERE `when` >=from_unixtime($begintime-300) and `when` < from_unixtime($begintime-300+$period)
	GROUP BY w LIMIT 365
",'w');

# перебор по циклу времени и соответствующих ему данных
$min=100000000; $max=0;
$old_month=0; $n=0;
$ymax=0;
$ymin=2000000000;
$start_i=0;
$st=floor(time()/$interval)*$interval-$period;
for ($i=365; $i>0; $i--) {
	$t=strtotime("-$i day",$unixtime);
	$k=date('Y-m-d',$t);
	$n=365-$i;
	if(isset($res[$k])) { 
		$data[$n] = $res[$k];
		if($data[$n]<$min) $min=$data[$n];
		if($data[$n]>$max) $max=$data[$n];
	}else{ 
		$data[$n] = 0.00;
	}
	$xdata[$n] = $t;
	if($ymin>$data[$n]) $ymin=$data[$n];
	if($data[$n]>$ymax) $ymax=$data[$n];
// 	txt_log("t=$t; k=$k; n=$n; d[k]=".$data[$n]."; ");
};
$last = $data[364];

$dateUtils = new DateScaleUtils();
list($tickPositions,$minTickPositions) = $dateUtils->GetTicks($xdata);

$xmin = $xdata[0];
$xmax = $xdata[$n];
$yshift = floor($ymin/100)*100;
$ymax = floor($ymax*1.05);
// Create the graph. These two calls are always required
$graph = new Graph(700,200);	
// txt_log("yshift=$yshift; ymax=$ymax; xmin=$xmin; xmax=$xmax");
$graph->SetScale('intlin',$yshift,$ymax,$xmin,$xmax);
$graph->title->Set("Users online");
$graph->title->SetFont(FF_FONT1,FS_BOLD);
// $graph->SetShadow();
$graph->img->SetMargin(60,30,20,60);

$graph->xaxis->SetPos('days');
$graph->xaxis->SetTickPositions($tickPositions,$minTickPositions);
$graph->xaxis->SetLabelFormatString('M',true);
$graph->xaxis->SetLabelAngle(0);
$graph->xgrid->Show();
$graph->xaxis->SetPos($yshift);
$graph->SetAlphaBlending();
$graph->xaxis->title->Set("month",'low');
$graph->yaxis->title->Set("users",'low');
$graph->xaxis->title->SetMargin(6);
$graph->yaxis->title->SetMargin(12);
$graph->footer->left->Set("min ".$min);
$graph->footer->left->SetFont(FF_ARIAL,FS_NORMAL,9);
$graph->footer->center->Set("max ".$max);
$graph->footer->center->SetFont(FF_ARIAL,FS_NORMAL,9);
$graph->footer->right->Set("last ".$last);
$graph->footer->right->SetFont(FF_ARIAL,FS_NORMAL,9);
$graph->footer->SetMargin(30,30,6,20);

// Create the linear plots for each category
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
