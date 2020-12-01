<?php
include_once("classes.php");
include_once("geodata.php");

$q = new sql_query($config['db']);

if(!$q->query("
	SELECT d.id, d.object, d.numports, m1.address as a1, m2.address as a2, xy.slice, xy.num, xy.x, xy.y
	FROM devices d, map_xy xy, map m1, map m2
	WHERE d.object=xy.object AND d.type='cable' AND m1.id=d.node1 AND m2.id=d.node2
	ORDER BY numports, d.object, slice, num;
	")) {
	show_error("Кабеля в базе не найдены!");
	exit(0);
}
$prev = $q->result->fetch_assoc();
$prev_xy = array($prev['x'],$prev['y']);
$t = array();
$cab = 0; $seg = 0; $S = 0; $s = 0;
while($row = $q->result->fetch_assoc()) {
	if($prev['object']!=$row['object']) {
		$S += $s;
		$cab++; $seg = 0; $s = 0;
		$prev_xy = array($row['x'],$row['y']);
		if($prev['numports']!=$row['numports']) {
			$t[] = array('fiber'=>$prev['numports'],'num'=>$cab+1,'length'=>sprintf("%.2f",$S/1000));
			$S = 0; $cab = 0;
		}
	}else{
		$seg++;
		$xy = array($row['x'],$row['y']);
		$s += Distance($prev_xy,$xy);
		$prev_xy = array($row['x'],$row['y']);
	}
    $prev = $row;
}
$S += $s;
$t[] = array('fiber'=>$prev['numports'],'num'=>$cab+1,'length'=>sprintf("%.2f",$S/1000));

$cables = array(
	'name'=>'cable_length',
	'class'=>'normal',
	'style'=>"width:300px;white-space:nowrap",
	'fields'=>array(
		'fiber'=>array(
			'label'=>"жильность",
			'style'=>'text-align:center',
			'access'=>3
		),
		'num'=>array(
			'label'=>"Кол-во<br>сегментов",
			'style'=>'text-align:right',
			'access'=>3
		),
		'address'=>array(
			'label'=>"адрес",
			'style'=>'text-align:right',
			'access'=>3
		),
		'length'=>array(
			'label'=>"Общая<br>длина (км)",
			'style'=>'text-align:right',
			'access'=>3
		),
		'sum'=>array(
			'label'=>"Длина<br>нарастающим итогом (км)",
			'style'=>'text-align:right',
			'access'=>3
		)
	),
	'data'=>$t
);
$table = new Table($cables);
echo "<h3>Статистика по кабелям</h3><div><a href=\"stat.php?go=cables_ext\">подробно</a></div>";
echo $table->getHTML();
echo "</center>";
