<?php
$now = preg_match('/^\d\d\d\d-\d\d-\d\d$/',@$_REQUEST['date'])? "'{$_REQUEST['date']}'" : 'now()';
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
		p.acttime>DATE_FORMAT(DATE_ADD($now,INTERVAL -23 MONTH),'%Y-%m-01')
	GROUP BY y, m
");

foreach($result as $k=>$v) $pay[$v['y']][$v['m']] = $v['s'];
$tm = strtotime("-23 month"); $y = date("Y",$tm)*1; $m = date("m",$tm)*1;
for($i=23;$i>=0;$i--){
	$id = ($i>11)? 'prev' : 'last';
	if($i>11) $tick[] = $month[$m];
	$data[$id][] = isset($pay[$y][$m])? $pay[$y][$m] : 0;
	if($m+1>12){ $m=1; $y++; }else $m++;
}

// Create the graph. These two calls are always required
$graph = new Graph(700,260,"auto");	
$graph->SetScale('textlin');
$graph->title->Set("Сумма оплат помксячно последние 2 года");
$graph->title->SetFont(FF_ARIAL,FS_BOLD);
// $graph->SetShadow();
$graph->img->SetMargin(65,30,30,40);
$graph->yscale->SetGrace(10);

$graph->xaxis->SetTickLabels($tick);
$graph->xaxis->SetFont(FF_ARIAL,FS_NORMAL,8);
// $graph->xgrid->Show();

$colors=array('prev'=>'skyblue','last'=>'orange');
foreach(array('prev'=>'прошлый','last'=>'текущий') as $k=>$v) {
	$a = new BarPlot($data[$k]);
	$a->SetFillColor($colors[$k]);
	$a->SetShadow('darkgray');
	$a->value->Show();
	$a->value->SetFont(FF_ARIAL,FS_NORMAL);
	$a->SetValuePos('top');
	$a->value->SetAngle(75);
	$a->value->SetFormat('%d');
//	$a->SetLegend($v);
	$acc[] = $a;
}

// Create the bar plot
$bplot = new GroupBarPlot($acc);

// Add the plot to the graph
$graph->Add($bplot);

// Display the graph
$graph->Stroke();
?>
