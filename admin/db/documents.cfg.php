<?php
include_once("classes.php");
include_once("table.php");
$need_form = false;

$tables['documents']=array(
	'title'=>'Документы пользователя',
	'target'=>"doc",
	'name'=>"documents",
	'module'=>"stdform",
	'limit'=>'yes',
	'key'=>'id',
	'class'=>'normal',
	'delete'=>'yes',
	'table_query'=>"
		SELECT 
			d.id,
			d.created,
			d.type,
			if(d.uid=0,c.address,u.address) as address,
			if(d.uid=0,c.fio,u.fio) as fio,
			d.operator
		FROM documents d LEFT OUTER JOIN users u ON d.uid = u.uid
		LEFT OUTER JOIN claims c ON c.type=1 AND d.val = c.unique_id, docdata v
		WHERE d.id=v.document :FILTER: :PERIOD:
		GROUP BY d.id ORDER BY :SORT:
	",
	'table_triggers'=>array(
		'type' => 'doc_type',
		'fio'=>'shortfio',
		'created'=>'cell_date',
		'operator' => 'doc_operator'
	),
	'form_query'=>"
		SELECT 
			id, 
			uid,
			val,
			'' as user,
			'' as address,
			type,
			created,
			operator
		FROM documents
		",
	'field_alias'=>array('type'=>'d','pid'=>'u','rid'=>'u','uid'=>'u','value'=>'v'),
	'form_triggers'=>array(
	),
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
		'type'=>array(
			'type'=>'select',
			'label'=>'Тип',
			'style'=>"width:110px",
			'list'=>all2array(document_types()),
			'title'=>'фильтр по типам документов',
			'value'=>''
		),
		'value'=>array(
			'type'=>'text',
			'label'=>'данные',
			'title'=>'фильтр по данным',
			'style'=>"width:200px",
			'value'=>''
		),
	),
	'table_menu'=>array(
		'edit'=>array('label'=>"<img src=\"pic/gtk-edit16.png\"> изменить",'to'=>'form'),
		'usrcard'=>array('label'=>"<img src=\"pic/usr.png\"> клиент",'to'=>'users.php','query'=>"go=usrstat&table=documents"),
		'delete'=>array('label'=>"<img src=\"pic/del.png\"> удалить",'to'=>'form','query'=>"go=stdform&do=delete&table=documents"),
		'print'=>array('label'=>"<img src=\"pic/gtk-print.png\"> печатать",'to'=>'window','target'=>"docpdf.php",'query'=>"table=documents"),
	),
 	'table_footer'=>array(
		'created'=>'Всего:',
		'operator'=>'fcount',
 	),
	'before_table_load'=>'before_load_docs',
	'before_check'=>'before_check_doc',
	'before_new'=>'before_new_doc',
	'before_edit'=>'before_edit_doc',
	'before_save'=>'before_save_doc',
	'form_onsave'=>'doc_onsave',
	'before_delete'=>'before_delete_doc',
	'group'=>'',
	'defaults'=>array(
		'operator'=>$opdata['id'],
		'created'=>date2db(),
		'filter'=>'build_filter_for_docs',
		'period'=>'build_period_for_docs',
		'sort'=>'created DESC',
	),
	'form_autocomplete'=>array(
		'address'=>'doc_auto_address',
		'user'=>'doc_auto_user'
	),

	// поля
	'fields'=>array(
		'id'=>array(
			'label'=>'id',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'created'=>array(
			'label'=>'Дата создания',
			'type'=>'date',
			'class'=>'date',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'uid'=>array(
			'label'=>'Клиент',
			'type'=>'hidden',
			'style'=>'width:100px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'val'=>array(
			'label'=>'table id',
			'type'=>'hidden',
			'style'=>'width:100px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'user'=>array(
			'label'=>'Клиент',
			'type'=>'nofield',
			'style'=>'width:100px',
			'native'=>false,
			'access'=>array('r'=>2,'w'=>3)
		),
		'address'=>array(
			'label'=>'адрес',
			'class'=>'nowr',
			'type'=>'autocomplete',
			'native'=>false,
			'access'=>array('r'=>2,'w'=>3)
		),
		'type'=>array(
			'label'=>'Тип документа',
			'type'=>'select',
			'class'=>'nowr',
			'list'=>'document_types',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'operator'=>array(
			'label'=>'Оператор',
			'type'=>'text',
			'class'=>'nowr',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'note'=>array(
			'label'=>'Примечание',
			'type'=>'textarea',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'fio'=>array(
			'label'=>'Ф.И.О.',
			'type'=>'text',
			'class'=>'nowr',
			'native'=>false,
			'access'=>array('r'=>1,'w'=>4)
		),
	)
);

function before_load_docs($t) {
	global $opdata;
	$uid = isset($_REQUEST['uid'])? numeric($_REQUEST['uid']) : 0;
	if($uid) {
		unset($t['filters']['value']);
	}
	unset($t['fields']['uid']);
	$t['header'] = 'Документы';
	$t['limit'] = 'yes';
	return $t;
}

function before_check_doc($f) {
	global $config, $need_form, $tables, $q;
	if(isset($_REQUEST['table'])){
		$f['table_query'] = preg_replace('/:[A-Z][A-Z]*:/','',sqltrim($f['table_query']));
	}
	return $f;
}

function before_new_doc($f,$my) {
	global $opdata, $tables, $config;
	if(isset($_REQUEST['tname']) && $_REQUEST['tname']=='claims' && isset($_REQUEST['id'])){
		$id=numeric($_REQUEST['id']);
		$cl = $my->q->get("claims",$id);
		if(!$cl) stop("Заявление $id не найдено!");
		if($cl['type']!=1) stop("Заявление должно быть на установку");
		$doc = $my->q->select("SELECT * FROM documents WHERE type='notice' AND val='$id'",1);
		if($doc) stop("Уведомление &#8470; {$doc['id']} уже выписано!");
		$doc = array('id'=>'new','val'=>$id,'type'=>"notice",'created'=>now(),'operator'=>$opdata['id']);
		$form = new Form($config);
		$t = $tables['documents'];
		$out = $form->save($t,$doc);
		stop($out);
	}
	$uid = isset($_REQUEST['uid'])? numeric($_REQUEST['uid']) : false;
	$val = isset($_REQUEST['id'])? numeric($_REQUEST['id']) : $uid;
	$f['name'] = 'documents';
	$f['header'] = 'Документ от '.cyrdate()."<br><br>";
	$f['fields']['created']['type'] = 'hidden';
	$f['fields']['operator']['type'] = 'hidden';
	$f['fields']['type']['style'] = 'width:320px';
	$f['fields']['address']['style'] = 'width:260px';
	$f['defaults']['uid']=$uid;
	$f['defaults']['val']=$val;
	if($uid){
		unset($f['fields']['user']);
		unset($f['fields']['address']);
	}
	return $f;
}

function before_edit_doc($f) {
	global $opdata, $q, $tables, $config;
	$id = (key_exists('id',$_REQUEST))? numeric($_REQUEST['id']) : 0;
	if(!($p = $q->get('documents',$id))) stop(array('result'=>'ERROR','desc'=>"Документ отсутствует!"));
	if($_REQUEST['go'] == 'stdform'){
		if(!isset($tables['docdata'])) include_once("docdata.cfg.php");
		if(isset($tables['docdata'])){
			$form = new form($config);
			$t = $tables['docdata'];
			$t['id'] = $id;
			$out = $form->get($t);
			stop($out);
		}
	}
	return $f;
}

function before_save_doc($c,$o,$my) {
	global $config, $doctypes;
	$r = array_merge($o,$c);
	$table = reset(array_keys($doctypes[$r['type']]["keys"]));
	if($table == 'users' && $r['uid']) {
		$c['val'] = $r['val'] = $r['uid'];
	}elseif($table == 'claims' && !$r['val'] && $r['uid']){
		$cl = $my->q->select("SELECT * FROM claims WHERE type=1 AND uid='{$r['uid']}' ORDER BY claimtime DESC",1);
		if($cl){
			$doc = $my->q->select("SELECT * FROM documents WHERE type='{$r['type']}' AND uid='{$r['uid']}'",1);
			if($doc) stop("{$doctypes[$r['type']]['label']} &#8470; {$doc['id']} уже выписано!");
			$c['val'] = $r['val'] = $cl['unique_id'];
			if($cl['uid']) $c['uid'] = $cl['uid'];
		}
	}
	if(!$r['val']) {
		log_txt(__function__.": Не найден данные для {$r['type']} в `$table`");
		stop("Не найдены данные для {$r['address']}!");
	}
	return $c;
}

function doc_onsave($id,$s,$my) {
	global $tables, $doctypes, $config;
	if(!is_numeric($id)) $id = $s['id'];
	$need_form = false;
	$q = new sql_query($config['db']);
	$db = array();
	$db['doc'] = $r = $q->get('documents',$id);
	if($r){
		foreach($doctypes[$r['type']]['keys'] as $next => $prev){
			$nx = preg_split('/\./',$next,2); if(count($nx)==1) array_push($nx, $q->table_key($nx[0]));
			$pr = preg_split('/\./',$prev,2); if(count($pr)==1) array_unshift($pr, "doc");
			$where = "`$nx[1]`='".@$db[$pr[0]][$pr[1]]."'";
			if($cnd = @$doctypes[$r['type']]['conditions'][$nx[0]]) $where .= " AND $cnd";
			$db[$nx[0]] = $q->select("SELECT * FROM `{$nx[0]}` WHERE $where",1);
		}
		foreach($doctypes[$r['type']]['fields'] as $k=>$v){
			$a = preg_split('/\./',$v,2);
			if(count($a)==1 && isset($db[$v][$k])) $row[$k] = $db[$v][$k];
			if(count($a)==2 && isset($db[$a[0]])) $row[$k] = @$db[$a[0]][$a[1]];
			if(count($a)==2 && $a[0]=='') $row[$k] = @$r[$a[1]];
			if(!isset($row[$k])){
				if(is_string($v) && function_exists($v)) $row[$k] = $v($r);
				elseif($v != '' && count($a)==1) $row[$k] = $v;
				elseif($v == ''){ $row[$k]=''; $need_form = true; }
			}
		}
		if(count($row)>0){
			foreach($row as $k=>$v) $ins[] = array('document'=>$id,'field'=>$k,'value'=>$v);
			if(!$q->insert('docdata',$ins)){
				log_txt(__FUNCTION__.": ERROR docdata not inserted!");
			}
			if($need_form) $need_form = $id;
		}else{
			log_txt(__FUNCTION__.": нет профиля для документа типа: {$r['type']}");
		}
	}
	$out = true;
	if($need_form){
		if(!isset($tables['docdata'])) include_once "docdata.cfg.php";
		$form = new form($config);
		$t = $tables['docdata'];
		$t['id'] = $need_form;
		$f = $form->get($t);
		$out = array('result' => 'OK');
		$out['form'] = $f['form'];
	}
	return $out;
}


function before_delete_doc($r) {
	global $doctypes, $config;
	$q = new sql_query($config['db']);
	$q->query("DELETE FROM docdata WHERE document='{$r['id']}'");
	return true;
}

function get_operators($id=false,$r=null,$fn=null) {
	global $cache, $config;
	if(!isset($cache['operators'])) {
		$q = new sql_query($config['db']);
		$cache['operators'] = $q->fetch_all("SELECT unique_id, fio FROM operators",'unique_id');
	}
	if($id !== false) return @$cache['operators'][$id];
	return $cache['operators'];
}

function doc_operator($op,$r=null,$fn=null) {
	return shortfio(get_operators($op));
}

function doc_type($id,$r=null,$fn=null) {
	global $doctypes;
	return $doctypes[$id]['label'];
}

function document_types(){
	global $doctypes;
	$r = array();
	foreach($doctypes as $k => $v){
		$r[$k] = $v['label'];
	}
	return $r;
}

function warranty_6month() {
	return date2db('6 month',false);
}

function warranty_14days() {
	return date2db('14 day',false);
}

function warranty_1month() {
	return date2db('1 month',false);
}

function build_period_for_docs() {
	return period2db('documents','created');
}

function build_filter_for_docs() {
	return filter2db('documents');
}
	
function doc_auto_address(){
	global $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	$req = (key_exists('req',$_REQUEST))? str($_REQUEST['req']) : '';
	$out['result'] = 'OK';
	$out['complete'] = $q->select("SELECT DISTINCT address as label, uid, user FROM users WHERE address like '%$req%' HAVING label!='' ORDER BY address");
	return $out;
}	

function doc_auto_user(){
	global $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	$req = (key_exists('req',$_REQUEST))? str($_REQUEST['req']) : '';
	$out['result'] = 'OK';
	$out['complete'] = $q->select("SELECT DISTINCT user as label, uid, address FROM users WHERE user like '%$req%' HAVING label!='' ORDER BY address");
	return $out;
}
?>
