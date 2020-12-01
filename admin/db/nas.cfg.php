<?php
include_once("classes.php");
include_once("table.php");

$tables['nas']=array(
	'title'=>'Список серверов доступа',
	'target'=>"form",
	'name'=>"nas",
	'module'=>"stdform",
	'key'=>'id',
	'class'=>'normal',
	'delete'=>'yes',
// 	'footer'=>array(),
	'table_query'=>"
		SELECT id, shortname, nastype, n.nasipaddress, description, ippool, a.c 
		FROM nas n LEFT OUTER JOIN (
			SELECT nasipaddress, count(username) as c 
			FROM radacct WHERE acctstoptime is null 
			GROUP BY nasipaddress
		) as a ON n.nasipaddress = a.nasipaddress
		WHERE 1
		ORDER BY :SORT:
		",
	'form_query'=>"SELECT * FROM nas",
	'table_triggers'=>array(
		'nastype' => 'nas_type'
	),
 	'table_footer'=>array(
		'shortname'=>'Всего:',
		'description'=>'fcount',
		'c'=>'fsum',
 	),
	'form_triggers'=>array(
	),
	'group'=>'',
	'defaults'=>array(
		'operator'=>$opdata['id'],
		'sort'=>'get_sort_field',
	),

	// поля
	'fields'=>array(
		'id'=>array(
			'label'=>'id',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>5)
		),
		'nasname'=>array(
			'label'=>'FQDN или IP',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'shortname'=>array(
			'label'=>'Краткое имя',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'type'=>array(
			'label'=>'Тип',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'nasipaddress'=>array(
			'label'=>'IP для доступа',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'ports'=>array(
			'label'=>'Кол-во портов',
			'type'=>'text',
			'style'=>'width:60px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'secret'=>array(
			'label'=>'Ключ',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'community'=>array(
			'label'=>'Комьюнити',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'description'=>array(
			'label'=>'Описание',
			'type'=>'text',
			'table_class'=>'nowr',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'ippool'=>array(
			'label'=>'Пул',
			'type'=>'select',
			'list'=>'get_pools',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'nastype'=>array(
			'label'=>'Внутренний тип',
			'type'=>'select',
			'list'=>'nas_types',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'c'=>array(
			'label'=>'online',
			'type'=>'text',
			'class'=>'number',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>5)
		),
	)
);

function nas_types() {
	global $nas_types, $cache;
	$r = array();
	$dir = USERKILLDIR;
	if(isset($cache['nas_types'])) return $cache['nas_types'];
	if(is_dir($dir)){
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if(filetype($dir . $file)=='file' && preg_match('/\.userkill$/',$file)) {
					$key = preg_replace('/\.userkill$/','',$file);
					$cache['nas_types'][$key] = $r[$key] = $key;
				}
			}
			closedir($dh);
		}else{
			log_txt("невозможно открыть каталог: $dir");
		}
	}else{ return $nas_types; }
	return $r;
}

function nas_type($t,$r=null,$fn=null) {
	$nt = nas_types();
	return $nt[$t];
}

function get_pools() {
	global $config, $cache;
	$q = new sql_query($config['db']);
	$r = $q->fetch_all("SELECT distinct pool_name as id, pool_name FROM  radippool ORDER BY pool_name");
	return array_merge(array(''=>''),$r);
}

function get_sort_field($t) {
	global $tables, $_REQUEST;
	if(!isset($_REQUEST['sort'])) return 'n.nasipaddress';
	$sort = strict($_REQUEST['sort']);
	if($sort != 'c' && $sort != 'c desc') $sort = 'n.'.$sort;
	log_txt(__function__.": sort:$sort");
	return $sort;
}

?>
