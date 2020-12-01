<?php
$now = preg_match('/^\d\d\d\d-\d\d-\d\d$/',$_REQUEST['date'])? "'{$_REQUEST['date']}'" : 'now()';
$locale_char_set='utf-8';
include_once("defines.php");
include_once("classes.php");
include_once("jpgraph.php");
include_once("jpgraph_line.php");
include_once("jpgraph_bar.php");
include_once("jpgraph_utils.inc.php");
$q = new sql_query($config['db']);

$month = array ( 
    '1'=>"Янв", 
    '2'=>"Фев", 
    '3'=>"Мар", 
    '4'=>"Апр", 
    '5'=>"Май", 
    '6'=>"Июн", 
    '7'=>"Июл", 
    '8'=>"Авг", 
    '9'=>"Сен", 
    '10'=>"Окт", 
    '11'=>"Ноя", 
    '12'=>"Дек" );

#Запрос к базе о кол-ве пользователей по времени
$result=$q->select("
	SELECT 
		year(p.acttime) as y, 
		month(p.acttime) as m, 
		sum(p.summ) as s
	FROM
		pay as p, povod as pv
	WHERE
		p.povod_id=pv.povod_id and pv.diagram=1 and
		p.acttime>DATE_FORMAT(DATE_ADD($now,INTERVAL -11 MONTH),'%Y-%m-01')
	GROUP BY y, m
");

foreach($result as $k=>$v) {
	$m[]=sprintf("%s",$month[$v['m']]);
	$data[]=$v['s'];
};

// Create the graph. These two calls are always required
$graph = new Graph(500,200,"auto");	
$graph->SetScale('textlin');
$graph->title->Set("Сумма оплат за месяц");
$graph->title->SetFont(FF_ARIAL,FS_BOLD);
// $graph->SetShadow();
$graph->img->SetMargin(55,30,30,40);
$graph->yscale->SetGrace(10);

$graph->xaxis->SetTickLabels($m);
$graph->xaxis->SetFont(FF_ARIAL,FS_NORMAL,8);
#$graph->xgrid->Show();

// Create the bar plot
$bplot = new BarPlot($data);
$bplot->SetFillColor("orange");
$bplot->SetShadow('darkgray');
$bplot->SetWidth(0.5);
$bplot->value->Show();
$bplot->value->SetFont(FF_ARIAL,FS_NORMAL);
$bplot->value->SetAngle(45);
#$bplot->SetValuePos('center');
$bplot->value->SetFormat('%d');

// Add the plot to the graph
$graph->Add($bplot);

// Display the graph
$graph->Stroke();
?>
