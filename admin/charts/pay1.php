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
		sum(IF(pk.name not like '%Wi-Fi%',p.summ,0)) as lan,
		sum(IF(pk.name like '%Wi-Fi%',p.summ,0)) as wifi
	FROM
		pay as p
		LEFT OUTER JOIN povod as pv ON p.povod_id=pv.povod_id
		LEFT OUTER JOIN users as u ON p.uid=u.uid
		LEFT OUTER JOIN packets as pk ON pk.pid=u.pid
	WHERE
		pv.diagram=1 and
		p.acttime>DATE_FORMAT(DATE_ADD($now,INTERVAL -11 MONTH),'%Y-%m-01')
	GROUP BY y, m
");

foreach($result as $k=>$v) {
	$m[]=sprintf("%s",$month[$v['m']]);
	$data['lan'][] = $v['lan'];
	$data['wifi'][] = $v['wifi'];
};

#$datax=$gDateLocale->GetShortMonth($m);

// Create the graph. These two calls are always required
$graph = new Graph(600,200,"auto");	
$graph->SetScale('textlin');
$graph->title->Set("Сумма оплат за месяц");
$graph->title->SetFont(FF_ARIAL,FS_BOLD);
$graph->img->SetMargin(55,80,30,40);
$graph->yscale->SetGrace(10);

$graph->xaxis->SetTickLabels($m);
$graph->xaxis->SetFont(FF_ARIAL,FS_NORMAL,8);
$graph->legend->Pos(0.02,0.1,"right","top");


// Create the bar plot
$colors=array('orange','skyblue');
foreach(array('lan','wifi') as $k=>$v) {
	$acc[$k] = new BarPlot($data[$v]);
	$acc[$k]->SetFillColor($colors[$k]);
	$acc[$k]->SetShadow('darkgray');
	$acc[$k]->SetWidth(0.5);
	$acc[$k]->value->Show();
	$acc[$k]->value->SetFont(FF_ARIAL,FS_NORMAL);
	$acc[$k]->value->SetAngle(45);
	$acc[$k]->SetValuePos('top');
	$acc[$k]->value->SetFormat('%d');
	$acc[$k]->SetLegend($v);
}

//$accbplot = new AccBarPlot(array($bplot2,$bplot));
$gbplot = new GroupBarPlot($acc);
$graph->Add($gbplot);
// Display the graph
$graph->Stroke();
?>
