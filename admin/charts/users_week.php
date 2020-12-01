<?php
include_once("defines.php");
include_once("classes.php");
include_once("jpgraph.php");
include_once("jpgraph_line.php");
include_once("jpgraph_utils.inc.php");

$q = new sql_query($config['db']);

$intday=(array_key_exists('intday',$_REQUEST))? $_REQUEST['intday'] : 0;
$inthour=(array_key_exists('inthour',$_REQUEST))? $_REQUEST['inthour'] : 0;
$interval=1800;
$period=3600*24*7;
$speed_limit=1310720;

# Возвращает вместо 1000 1К и вместо 1000000 1М для градуирования оси Y
function SpeedCallback($aVal) {
	if ($aVal>=1000000) {
		$out=round($aVal/1000000,1);
		return $out."M";
	}elseif ($aVal>=1000) {
		$out=$aVal/1000;
		return $out."K";
	}else {
		return $aVal;
	}
}
$inttime=$intday*86400+$inthour*3600;
$ct=time()-$inttime;
# unixtime - это текущее время выровненное по интервалу
$unixtime=floor($ct/$interval)*$interval;
# begintime - это время выровненное по интервалу минус период (видимая область диаграммы)
$begintime=$unixtime-$period;


$ST=strtotime(date('Y-m-d',$ct));
$RT=floor($ct/86400)*86400;
# вычисляем разницу в секундах между локальным временем и гринвичем
$Raznica=abs($RT-$ST);
#sql_log("\nразница =  abs( $ST - $RT ) ".$Raznica);

#Запрос к базе о кол-ве пользователей по времени
$q->query("
	SELECT unix_timestamp(`when`) as w, counter as u
	FROM usrontime
	WHERE `when`>=from_unixtime($begintime-300) 
	ORDER BY `when`
");
#sql_log("SQL=".$sqlstr);
$num = $q->rows();
#sql_log("num=".$num);

# задание начальных значений
$min=1000000; $max=0;
if ($num>0) { 
	$res = $q->result->fetch_assoc(); 
	$ut_prev=$res['w']; $data_prev=$res['u'];
	if($data_prev<$min) $min=$data_prev;
	if($data_prev>$max) $max=$data_prev;
}
# перебор по циклу времени и соответствующих ему данных
for ($i=0; $i<($period/$interval); $i++) {
	# время, для которого должны быть данные в базе
	$curtime=$begintime+$i*$interval;
	# если время данных не совпадает с нужным - то счииывать следующую запись из базы пока время не сравняется...
	$tmpsum=0; $n=0;
	while ($res && $res['w']<$curtime) {
		$res = $q->result->fetch_assoc();
		$tmpsum=$tmpsum+$res['u']; $n++;
	}
	if($n>0) $endsum=round($tmpsum/$n); else $endsum=0;
	# если нет данных по текущему времени то данные будут нулевые
	if ($num=0 || $res['w']!=$curtime) {
		$data[$i] = 0;
		$ut_prev=$curtime; $data_prev=0;
	# если время совпало то получить данные и установить значение графиков
	} else { 
		$data[$i] = $endsum;
		if($data[$i]<$min) $min=$data[$i];
		if($data[$i]>$max) $max=$data[$i];
		$ut_prev=$res['w']; $data_prev=$endsum;
		$res = $q->result->fetch_assoc();
	}
	# задать значение графика времени 
	$xdata[$i] = $begintime+$i*$interval;
}
$last = $data[$i-1];

$dateUtils = new DateScaleUtils();
list($tickPositions,$minTickPositions) = $dateUtils->GetTicks($xdata);
$begin = floor($begintime/86400)*86400-$Raznica;
for ($t=0; $t<=($unixtime-$begintime)/86400; $t++) {
	# задание основной разметки
	$tickPositions[$t] = $begin+$t*86400;
	# задание вспомогательной (малой) разметки
	$minTickPositions[$t] = $tickPositions[$t]+43200;
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
$graph->xaxis->SetLabelFormatString('d',true);
$graph->xaxis->SetLabelAngle(0);
#$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD,8);
#$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD,8);
$graph->xgrid->Show();
#$graph->xaxis->scale->ticks->Set(7200,300);
$graph->SetAlphaBlending();
$graph->xaxis->title->Set("day",'low');
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
