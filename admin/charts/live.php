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

function sql_log($what)
{
  if ($DEBUG>0) {
      $pfile=fopen ("/tmp/php-sql.log","a");
      fputs($pfile,"$what\n");
      fclose($pfile);
	}
	return 0;
  }

#Запрос к базе о кол-ве пользователей по времени
$q->query("
	SELECT year(p.acttime) as y, month(p.acttime) as m, count(distinct p.user) as n
	FROM pay as p, povod as pv
	WHERE p.povod_id=pv.povod_id AND pv.diagram=1 AND
		p.acttime>DATE_FORMAT(DATE_ADD($now,INTERVAL -11 MONTH),'%Y-%m-01')
	GROUP BY y, m
");

for ($i=0; $i<$q->rows(); $i++) {
	$res = $q->result->fetch_assoc(); 
	$m[] = sprintf("%s",$month[$res['m']]);
	$data[$i] = $res['n'];
}

#$datax=$gDateLocale->GetShortMonth($m);

// Create the graph. These two calls are always required
$graph = new Graph(450,200,"auto");	
$graph->SetScale('textlin');
$graph->title->Set("Кол-во плативших за месяц");
$graph->title->SetFont(FF_ARIAL,FS_BOLD);
$graph->img->SetMargin(40,30,30,40);

$graph->xaxis->SetTickLabels($m);
$graph->xaxis->SetFont(FF_ARIAL,FS_NORMAL,8);
$graph->yscale->SetGrace(10);

// Create the bar plot
$bplot = new BarPlot($data);
$bplot->SetFillColor("orange");
$bplot->SetShadow('darkgray');
$bplot->SetWidth(0.5);
$bplot->SetFillGradient('darkred','yellow',GRAD_HOR);
$bplot->value->Show();
$bplot->value->SetFormat('%d');

// Add the plot to the graph
$graph->Add($bplot);

// Display the graph
$graph->Stroke();
?>
