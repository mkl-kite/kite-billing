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
$tags = array(
	0 => 'lan',
	1 => 'сектор',
	2 => 'Wi-Fi'
);
$header = "Кол-во подключённых помесячно";

$colors = array(
	0 => "orange",
	1 => "burlywood",
	2 => "darkgoldenrod",
	3 => "brown",
	4 => "seagreen",
	5 => "gold",
	6 => "indianred",
	7 => "olivedrab",
	8 => "#808000",
	9 => "midnightblue",
	10 => "cadetblue",
	11 => "cornflowerblue",
	12 => "darkcyan",
	13 => "darkolivegreen",
	14 => "dimgray",
	15 => "indianred",
	16 => "#4B0082",
	17 => "maroon",
	18 => "darkorange",
	19 => "goldenrod",
	20 => "yellow",
	21 => "lightskyblue",
	22 => "lightslategray",
	23 => "lightsteelblue",
	24 => "lightyellow",
	25 => "lime",
	26 => "limegreen",
	27 => "linen",
	28 => "magenta",
	29 => "maroon",
	30 => "mediumaquamarine",
	31 => "mediumblue"
);

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
    '12'=>"Дек" 
);

$tech = $tags;
$res = $q->select("
	SELECT year(prescribe) as y,
		   month(prescribe) as m,
		   IF(LOCATE('{$tags[1]}',p.name)>0,1,IF(LOCATE('{$tags[2]}',p.name)>0,2,0)) as grp,
		   count(u.user) as n
	FROM users u, claims c, claimperform j, workorders w, packets p
	WHERE 
		u.uid = c.uid AND c.type=1 AND
		w.woid = j.woid AND
		j.cid = c.unique_id AND j.status = 3 AND
		u.pid = p.pid AND
		w.prescribe > DATE_FORMAT(DATE_ADD($now,INTERVAL -11 MONTH),'%Y-%m-01') AND
		w.prescribe < DATE($now)
	GROUP BY y, m, grp
");
$old_m=-1; $i=-1;
foreach($res as $k=>$v) {
	if($old_m!=$v['m']) {
		$i++;
		$nm[$i]=$v['m'];
		$m[$i]=$month[$v['m']];
		$old_m=$v['m'];
	}
	$r[$v['grp']][$i]=$v['n'];
	@$rsumm[$v['grp']]+=@$v['n'];
}
asort($rsumm);
$rsumm = array_reverse($rsumm,true);
$rs=array_slice($rsumm,0,10,true);

foreach($nm as $i=>$month) {
	foreach($r as $grp=>$r_name) {
		$data[$grp][$i] = (@$r[$grp][$i])? $r[$grp][$i] : 0;
	}
}

// Create the graph. These two calls are always required
$graph = new Graph(550,200,"auto");	
$graph->SetScale('textlin');
$graph->title->Set($header);
$graph->title->SetFont(FF_ARIAL,FS_BOLD);
$graph->img->SetMargin(30,120,30,30);

$graph->xaxis->SetTickLabels($m);
$graph->xaxis->SetFont(FF_ARIAL,FS_NORMAL,8);
$graph->yscale->SetGrace(10);
$graph->legend->Pos(0.02,0.2,"right","top");
$graph->legend->SetFont(FF_ARIAL,FS_NORMAL);

// Create the bar plot
$i=0;
foreach($data as $grp=>$d) {
	$bp = new BarPlot($d);
	$bp->SetFillColor($colors[$i]); $i++;
	if(@$rs[$grp]) {
		$bp->SetLegend($tech[$grp]);
		$bp->SetValuePos('center');
		$bp->value->Show();
		$bp->value->SetFormat('%d');
	}
	$bplot[] = $bp;
}
$abplot = new AccBarPlot($bplot);
$abplot->SetShadow('darkgray');
$abplot->SetWidth(0.6);
$abplot->value->Show();
$abplot->value->SetFormat('%d');

// Add the plot to the graph
$graph->Add($abplot);

// Display the graph
$graph->Stroke();
?>
