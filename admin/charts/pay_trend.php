<?php
$now = preg_match('/^(\d\d\d\d)-(\d\d)-\d\d$/',$_REQUEST['date'],$d)? "'{$_REQUEST['date']}'" : 'now()';
$locale_char_set='utf-8';
include_once("defines.php");
include_once("classes.php");
include_once("log.php");
require_once("jpgraph.php");
require_once("jpgraph_line.php");
setlocale ("LC_TIME", "ru_RU");

$q = new sql_query($config['db']);

$m1 = date('m',strtotime('-1 month'));
$m2 = date('m');

// Some data
$ydata = $q->fetch_all("
	SELECT day(acttime) as d, count(*) as c
	FROM pay
	WHERE 
		acttime between date_format(date(date_add($now,INTERVAL -1 MONTH)),'%Y-%m-01') 
		AND date_format(date($now),'%Y-%m-01')
	GROUP BY d
",'d');
$ydata1 = $q->fetch_all("
	SELECT day(acttime) as d, count(*) as c
	FROM pay
	WHERE acttime between date_format(date($now),'%Y-%m-01') AND date($now)
	GROUP BY d
",'d');

end($ydata1);
$last_key = key($ydata1);
$summ=0;$summ1=0;
for($i=1;$i<=$last_key;$i++) {
	$summ += (@$ydata[$i])?$ydata[$i]:0;
	$d1[$i-1]=$summ;
	$summ1 += (@$ydata1[$i])?$ydata1[$i]:0;
	$d2[$i-1]=$summ1;
}

// echo "<pre>d1=".sprint_r($ydata)."d2=".sprint_r($ydata1)."</pre>";

// Create the graph. These two calls are always required
$graph = new Graph(550,300);
$graph->SetScale('textlin');

$graph->title->Set("Кол-во плативших нарстающим игогом\n в сравнении с предыдущим месяцем");
$graph->title->SetFont(FF_ARIAL,FS_BOLD);
$graph->legend->Pos(0.02,0.8,"right","bottom");
$graph->legend->SetFont(FF_ARIAL,FS_NORMAL);


// Create the linear plot
$lineplot = new LinePlot($d1);
$lineplot->SetColor('blue');
$lineplot->SetLegend($mon[$m1]);

$lineplot1 = new LinePlot($d2);
$lineplot1->SetColor('red');
$lineplot1->SetLegend($mon[$m2]);

// Add the plot to the graph
$graph->Add($lineplot);
$graph->Add($lineplot1);

// Display the graph
$graph->Stroke();
?>
