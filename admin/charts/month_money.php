<?php
$now = preg_match('/^\d\d\d\d-\d\d-\d\d$/',$_REQUEST['date'])? "'{$_REQUEST['date']}'" : 'now()';
$locale_char_set='utf-8';
include_once("defines.php");
include_once("classes.php");
include_once("jpgraph.php");
include_once("jpgraph_pie.php");
include_once("jpgraph_pie3d.php");

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
	SELECT povod as p, abs(sum(summ)) as s FROM povod, pay
	WHERE povod.povod_id = pay.povod_id AND acttime>DATE_FORMAT($now,'%Y-%m-01') AND povod.kassa=1
	GROUP BY povod
");

for ($i=0; $i < $q->rows(); $i++) {
	$res = $q->result->fetch_assoc(); 
	$l[]=$res['p'];
	$data[$i] = $res['s'];
}
#$datax=$gDateLocale->GetShortMonth($m);

// Create the graph. These two calls are always required
$graph = new PieGraph(700,250,"auto");	
#$graph->SetScale('textlin');
$graph->title->Set("Распределение денег за месяц");
$graph->title->SetFont(FF_ARIAL,FS_BOLD);
// $graph->SetShadow();
$graph->legend->SetFont(FF_ARIAL,FS_NORMAL);
#$graph->img->SetMargin(40,30,30,40);

// Create the plot
$p1 = new PiePlot3d($data);
$p1->SetAngle(45);
$p1->SetSize(0.5);
$p1->SetCenter(0.25,0.45);
$p1->SetLegends($l);

// Add the plot to the graph
$graph->Add($p1);

// Display the graph
$graph->Stroke();
?>
