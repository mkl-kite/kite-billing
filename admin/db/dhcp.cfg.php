<?php
include_once("classes.php");
include_once("table.php");
$need_form = false;

$tables['dhcp']=array(
	'title'=>'Текущие выданные ip',
	'target'=>"html",
	'name'=>"dhcp",
	'module'=>"dhcp2html",
	'limit'=>'no',
	'key'=>'ip',
	'class'=>'normal',
	'delete'=>'no',
	'add'=>'no',
	'table_query'=>"",
	'table_triggers'=>array(
		'start' => 'cell_atime',
		'end'=>'cell_atime',
		'cltt'=>'cell_atime',
	),
	'form_query'=>"",
	'field_alias'=>array('pid'=>'u','rid'=>'u','uid'=>'u'),
	'form_triggers'=>array(
	),
/*
	'filters'=>array(
		'uid'=>array(
			'label'=>'uid',
			'type'=>'hidden',
			'value'=>isset($_REQUEST['uid'])? numeric($_REQUEST['uid']) : ""
		),
		'end'=>array(
			'type'=>'date',
			'label'=>'конец',
			'style'=>'width:80px',
			'title'=>'дата конца',
			'value'=>cyrdate(strtotime('now'))
		),
		'begin'=>array(
			'type'=>'date',
			'label'=>'начало',
			'style'=>'width:80px',
			'title'=>'дата начала',
			'value'=>cyrdate("2000-01-01"),
		),
	),
*/
	'table_menu'=>array(
		'edit'=>array('label'=>"<img src=\"pic/gtk-edit16.png\"> изменить",'to'=>'form'),
		'reload'=>array('label'=>"<img src=\"pic/refresh.png\"> обновить",'to'=>'_reload'),
		'print'=>array('label'=>"<img src=\"pic/gtk-print.png\"> печатать",'to'=>'window','target'=>"docpdf.php",'query'=>"table=documents"),
	),
	'before_table_load'=>'before_load_dhcp',
	'before_edit'=>'before_edit_dhcp',
	'before_save'=>'before_save_dhcp',
	'form_onsave'=>'dhcp_onsave',
	'before_delete'=>'before_delete_dhcp',
	'group'=>'',
	'defaults'=>array(
		'operator'=>$opdata['id'],
		'created'=>date2db(),
		'filter'=>'build_filter_for_docs',
		'period'=>'build_period_for_docs',
		'sort'=>'created DESC',
	),

	// поля
	'fields'=>array(
		'id'=>array(
			'label'=>'id',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'ip'=>array(
			'label'=>'ip адрес',
			'class'=>'nowr',
			'type'=>'text',
			'table_style'=>'width:110px',
			'native'=>false,
			'access'=>array('r'=>2,'w'=>3)
		),
		'start'=>array(
			'label'=>'Начало аренды',
			'type'=>'date',
			'class'=>'date',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'end'=>array(
			'label'=>'Конец аренды',
			'type'=>'date',
			'class'=>'date',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'cltt'=>array(
			'label'=>'cltt',
			'type'=>'date',
			'class'=>'date',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'state'=>array(
			'label'=>'Состояние',
			'type'=>'text',
			'class'=>'nowr ctxt',
			'native'=>false,
			'access'=>array('r'=>2,'w'=>3)
		),
		'next state'=>array(
			'label'=>'Сл.состояние',
			'type'=>'text',
			'class'=>'nowr ctxt',
			'native'=>false,
			'access'=>array('r'=>2,'w'=>3)
		),
		'mac'=>array(
			'label'=>'mac адрес',
			'class'=>'csid',
			'type'=>'autocomplete',
			'native'=>false,
			'access'=>array('r'=>2,'w'=>3)
		),
		'host'=>array(
			'label'=>'название',
			'type'=>'text',
			'class'=>'nowr',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
	)
);

function mytime($s){
	$d = new DateTime(preg_replace('/\//','-',$s[2])." ".$s[3], new DateTimeZone('UTC'));
	$d->setTimezone(new DateTimeZone(date_default_timezone_get()));
	return $d->format('Y-m-d H:i:s');
}

function get_dhcp() {
	$clients = array();
	$file = "/var/lib/dhcp/dhcpd.leases";
	$filds = array('start','end','cltt','state','next state','mac','host');
	if(filetype($file)=='file') {
		if ($pfile=@fopen($file,"r")) {
			$ip = false;
			while(($buffer = fgets($pfile, 4096)) !== false) {
				$buffer = trim($buffer);
				if(preg_match('/\s*([0-9\.][0-9\.]*)\s*{/',$buffer,$m)) {
					$ip = $m[1];
					$client = array('id'=>ip2long($ip), 'ip'=>$ip);
					foreach($filds as $k) $client[$k] = '';
				}elseif(preg_match('/}/',$buffer)) {
					if($ip && count($client)>0 && $client['state']!='free') {
						$clients[$client['id']]=$client;
					}
					$ip = false;
				}else{
					if($ip) {
						$s = preg_replace('/;/','',trim($buffer));
						$s = preg_split('/\s+/',$s);
						$n = false; $v = false;
						if($s[0] == 'starts') { $n = "start"; $v = mytime($s); }
						elseif($s[0] == 'ends') { $n = "end"; $v = mytime($s); }
						elseif($s[0] == 'cltt') { $n = "cltt"; $v = mytime($s); } 
						elseif($s[0] == 'binding') { $n = "state"; $v = $s[2]; }
						elseif($s[0] == 'next') { $n = "next state"; $v = $s[3]; }
						elseif($s[0] == 'hardware') { $n = "mac"; $v = $s[2]; }
						elseif($s[0] == 'client-hostname') { $n = "host"; $v = preg_replace('/"/','',$s[1]); }
						if($n && $v) $client[$n] = $v;
					}
				}
			}
			fclose($pfile);
		}else
			stop(array('result'=>'ERROR','desc'=>"Невозможно открыть файл $file"));
	}else
		stop(array('result'=>'ERROR','desc'=>"Файл $file не найден!"));
	return $clients;
}

function before_load_dhcp($f) {
	global $opdata;
	$f['data'] = get_dhcp();
	return $f;
}

function before_edit_dhcp($f) {
	global $opdata, $q, $tables, $config;
	return $f;
}

function before_save_dhcp($c,$o) {
	global $config;
	return $c;
}

function doc_onsave($id,$s) {
	global $doctypes, $config, $need_form;
}


function before_delete_dhcp($r) {
	global $doctypes, $config;
	return true;
}

function build_period_for_dhcp() {
	return period2db('documents','created');
}

function build_filter_for_dhcp() {
	return filter2db('documents');
}

?>
