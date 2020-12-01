<?php
include_once("classes.php");
include_once("table.php");
include_once("documents.cfg.php");

$tables['docdata']=array(
	'title'=>'Документы пользователя',
	'target'=>"form",
	'name'=>"docdata",
	'module'=>"documents",
	'key'=>'id',
	'class'=>'normal',
	'delete'=>'yes',
	'table_query'=>"
		SELECT 
			d.id,
			d.type,
			d.created,
			d.operator
		FROM documents d, users u
		WHERE d.uid = u.uid :UIDFILTER:
		ORDER BY :SORT:
	",
	'record'=>'get_docdata',
	'form_triggers'=>array(
	),
	'before_edit'=>'before_edit_docdata',
	'before_save'=>'before_save_docdata',
	'form_save'=>'save_docdata',
	'form_onsave'=>'docdata_onsave',
	'group'=>'',
	'defaults'=>array(
	),

	// поля
	'fields'=>array(
		'id'=>array(
			'label'=>'id',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'document'=>array(
			'label'=>'Документ',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'field'=>array(
			'label'=>'Название поля',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'value'=>array(
			'label'=>'Значение поля',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'uid'=>array(
			'label'=>'uid',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>3)
		),
		'login'=>array(
			'label'=>'Логин',
			'type'=>'text',
			'style'=>'width:100px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'claim'=>array(
			'label'=>'cid',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'pid'=>array(
			'label'=>'Тариф',
			'type'=>'select',
			'list'=>'list_of_packets',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'fio'=>array(
			'label'=>'Ф.И.О.',
			'type'=>'text',
			'style'=>'width:230px',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>3)
		),
		'psp'=>array(
			'label'=>'паспорт',
			'type'=>'text',
			'style'=>'width:100px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'pspissue'=>array(
			'label'=>'выдан',
			'type'=>'text',
			'style'=>'width:230px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'inn'=>array(
			'label'=>'ИНН',
			'type'=>'text',
			'style'=>'width:230px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'reply'=>array(
			'label'=>'Возможность',
			'type'=>'select',
			'list'=>array("есть"=>"есть","отсутствует"=>"отсутствует"),
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'password'=>array(
			'label'=>'пароль',
			'type'=>'text',
			'style'=>'width:100px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>2)
		),
		'expired'=>array(
			'label'=>'Заканчивается',
			'type'=>'date',
			'style'=>'width:100px',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>3)
		),
		'device'=>array(
			'label'=>'Устройство',
			'style'=>'width:160px',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'model'=>array(
			'label'=>'Модель',
			'style'=>'width:160px',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'macaddress'=>array(
			'label'=>'мак адрес',
			'style'=>'width:160px',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'code'=>array(
			'label'=>'s/n или mac',
			'style'=>'width:160px',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'phone'=>array(
			'label'=>'телефон',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>2)
		),
		'address'=>array(
			'label'=>'адрес',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'rid'=>array(
			'label'=>'район',
			'type'=>'select',
			'list'=>'list_of_rayons',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'contract'=>array(
			'label'=>'контракт',
			'type'=>'text',
			'style'=>'width:100px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'email'=>array(
			'label'=>'email',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>2)
		),
		'source'=>array(
			'label'=>'source',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'name'=>array(
			'label'=>'название',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'note'=>array(
			'label'=>'Примечания',
			'type'=>'textarea',
			'style'=>'width:230px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
	)
);
function before_edit_docdata($f,$my) {
	global $doctypes;
	$f['header'] = "Документ &#8470; {$f['id']}";
	$doc = $my->q->get('documents',$f['id']);
	if($doc && key_exists($doc['type'],$doctypes)) $f['header'] = $doctypes[$doc['type']]['label']." &#8470; {$f['id']}";
	return $f;
}

function before_save_docdata($c,$s,$my) {
	global $doctypes, $tables;
	log_txt(__FUNCTION__.": cmp = ".arrstr($c));
	if(isset($c['code'])){
		$m = preg_replace('/[^A-F0-9]/i','',$c['code']);
		if(mb_strlen($m)==12) $c['code'] = strtoupper(preg_replace('/(..)(..)(..)(..)(..)(..)/','$1:$2:$3:$4:$5:$6',$m));
	}
	log_txt(__FUNCTION__.": cmp = ".arrstr($c));
	$doc = $my->q->get('documents',$my->row['document']);
	if($doc && key_exists($doc['type'],$doctypes) && isset($doctypes[$doc['type']]['input'])){
		foreach($doctypes[$doc['type']]['input'] as $fname => $match)
			if(isset($c[$fname]) && !preg_match($match,$c[$fname]))
				stop("<p>{$tables['docdata']['fields'][$fname]['label']}</p> содержит ошибку!");
	}
	return $c;
}

function save_docdata($s,$my) {
	global $config, $doctypes;
	if(!isset($s['id'])){
		log_txt(__function__.": id is not defined! \$save = ".arrstr($s));
		return false;
	}
	if($s['id'])
	$doc = $my->q->get('documents',$s['id']);
	$key = $my->q->table_key('documents');
	$document = array_intersect_key($s,$doc);
	if(!isset($doctypes[$doc['type']]['fields'])){
		log_txt(__function__.": type is unknown! \$save = ".arrstr($s));
		return false;
	}
	$docdata = array_diff_key($s,$doc);
	// извлекаем данные по документу из базы
	$f = $doctypes[$doc['type']]['fields'];
	$docdata = array_intersect_key($docdata,$f);
	if(count($docdata)==0){
		log_txt(__function__.": WARNING, \$save is empty! \$s = ".arrstr($s));
	}
	$d = $my->q->fetch_all("SELECT field, id FROM docdata WHERE document='{$s['id']}'",'field');
	$i = 0;
	foreach($docdata as $k=>$v){
		if(isset($d[$k])){
			$c = array('id'=>$d[$k], 'value'=>$v);
			$res = $my->q->update_record('docdata',$c);
			$i++;
		}else{
			$c = array('document'=>$doc['id'],'field'=>$k,'value'=>$v);
			$c['id'] = $my->q->insert('docdata',$c);
			$i++;
		}
	}
	if(count($document)>1) $my->q->update_record('documents',$document);
	return $i;
}

function get_docdata($id) {
	global $config, $doctypes;
	$q = new sql_query($config['db']);
	$r = $q->get('documents',$id);
	$f = $doctypes[$r['type']]['fields'];
	foreach($f as $k=>$v) $f[$k] = '';
	$fld = $q->select("SELECT * FROM docdata WHERE document='{$id}'");
	if($fld && count($fld)>0){
		foreach($fld as $n=>$v) $d[$v['field']] = $v['value'];
		$r = array_merge($r,$f,array_intersect_key($d,$f));
	}
	return $r;
}

function docdata_onsave($id,$r) {
	if($_REQUEST['go']=='stdform') return array('result'=>'DOC','action'=>'reload','to'=>"docpdf.php?id=$id");
	return array('result'=>'OK','modify'=>array());
}
?>
