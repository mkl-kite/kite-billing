<?php
$now = preg_match('/^(\d\d\d\d)-(\d\d)-\d\d$/',$_REQUEST['date'],$d)? "'{$_REQUEST['date']}'" : 'now()';
$locale_char_set='utf-8';
include_once("defines.php");
include_once("classes.php");
include_once("jpgraph.php");
include_once("jpgraph_pie.php");
include_once("jpgraph_pie3d.php");

$q = new sql_query($config['db']);

if(key_exists('current_year',$_REQUEST)) $current_year = $q->escape_string($_REQUEST['current_year']);
elseif(isset($d[1])) $current_year = $d[1];
else  $current_year = strftime("%Y");
if(key_exists('current_month',$_REQUEST)) $current_month = $q->escape_string($_REQUEST['current_month']);
elseif(isset($d[2])) $current_month = $d[2];
else $current_month = date('m');

$date_begin="$current_year-$current_month-01"; 
$date_end="$current_year-$current_month-31 23:59:59";

$q->query("
	SELECT r.rid as n, 
		r.r_name as p, 
		sum(p.summ) as s, 
		count(u.user) as u 
	FROM rayon as r, 
		users as u, 
		pay as p,
		povod as pv 
	WHERE r.rid=u.rid AND
		u.user=p.user AND
		p.povod_id=pv.povod_id AND
		pv.diagram=1 AND
		p.acttime BETWEEN '$date_begin' and '$date_end'
	GROUP BY r.rid
");

for ($i=0; $i<$q->rows(); $i++) {
	$res = $q->result->fetch_assoc(); 
	$l[]=sprintf("%s",$res['p']);
	$data[$i] = $res['s'];
}

// Create the graph. These two calls are always required
$graph = new PieGraph(730,390,"auto");	
$graph->title->Set("Распределение денег\nза {$mon[$current_month]} $current_year");
$graph->title->SetFont(FF_ARIAL,FS_BOLD);
$graph->legend->Pos(0.02,0.02,"right","top");
$graph->legend->SetFont(FF_ARIAL,FS_NORMAL);

// Create the plot
$p1 = new PiePlot3d($data);
$p1->SetAngle(45);
$p1->SetSize(0.5);
$p1->SetCenter(0.32,0.45);
$p1->SetLegends($l);
$p1->SetTheme("sand");

// Add the plot to the graph
$graph->Add($p1);

// Display the graph
$graph->Stroke();
?>
