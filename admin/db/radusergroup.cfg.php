<?php
include_once("classes.php");
include_once("rayon.cfg.php");
if(!$q) $q = sql_query($config['db']);
$selector = array('ALL'=>'ALL','USER'=>'USER');

$tables['radusergroup']=array(
	'name'=>'radusergroup',
	'title'=>'Объект',
	'target'=>"html",
	'limit'=>'yes',
	'module'=>"stdform",
	'key'=>'groupname',
	'class'=>'normal',
	'delete'=>'yes',
	'header'=>"",
	'table_query'=>"
		SELECT
			groupname,
			username,
			groupname as gname,
			priority
		FROM
			radusergroup
		WHERE 1 :FILTER:
		ORDER BY :SORT:
		",
	'form_query'=>"
		SELECT
			groupname,
			username,
			groupname as gname,
			priority
		FROM
			radusergroup
		",
	'filters'=>array(
	),
	'defaults'=>array(
		'sort'=>'priority, groupname',
		'filter'=>'build_filter_for_profiles',
	),
	'table_menu'=>array(
		'open'=>array('label'=>"<img src=\"pic/open.png\"> открыть",'to'=>'self','target'=>"references.php",'query'=>"go=stdform&do=show&table=radusergroup"),
		'edit'=>array('label'=>"<img src=\"pic/gtk-edit16.png\"> изменить",'to'=>'form','query'=>'go=stdform&do=edit&table=radusergroup'),
		'clone'=>array('label'=>"<img src=\"pic/copy.png\"> клонировать",'to'=>'form','query'=>'go=stdform&do=clone&table=radusergroup'),
	),
// 	'footer'=>array(),
	'table_triggers'=>array(
		'rayon'=>'get_rayon'
	),
	'form_triggers'=>array(
	),
	'form_autocomplete'=>array(
		'username'=>'profiles_auto_username',
	),
	'before_new'=>'before_new_profile',
	'before_edit'=>'before_edit_profile',
	'before_save'=>'before_save_profile',
	'form_save'=>'save_profile',
	'checks'=>'checks_profile',
	'group'=>'',

	// поля
	'fields'=>array(
		'groupname'=>array(
			'label'=>'id',
			'type'=>'hidden',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>3)
		),
		'clone'=>array(
			'label'=>'шаблон',
			'type'=>'select',
			'list'=>$q->fetch_all("SELECT distinct groupname as id, groupname FROM radusergroup ORDER BY priority, groupname"),
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'username'=>array(
			'label'=>'селектор',
			'type'=>'select',
			'list'=>$selector,
			'class'=>'nowr',
			'style'=>'width:80px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'gname'=>array(
			'label'=>'имя группы',
			'type'=>'text',
			'class'=>'nowr',
			'style'=>'width:160px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'priority'=>array(
			'label'=>'приоритет',
			'type'=>'text',
			'class'=>'nowr',
			'style'=>'width:50px;text-align:right',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
	),
);

function before_new_profile($f) {
	global $config, $q, $DEBUG;
	if(isset($f['clone'])){
		$new = $q->get('radusergroup',$f['clone'],'groupname');
		if($new){
			$f['defaults']['username'] = $new['username'];
			$i=1;
			while($q->get('radusergroup',$new['groupname']."-$i","groupname")){ $i++; }
			$f['defaults']['username'] = $new['username'];
			$f['defaults']['groupname'] = $new['groupname']."-$i";
			$f['defaults']['gname'] = $new['groupname']."-$i";
			$f['defaults']['priority'] = $new['priority'];
			$f['defaults']['clone'] = $new['groupname'];
			$f['id'] = 'new';
			foreach($f['defaults'] as $k=>$v) $f['record'][$k] = '';
			log_txt(__function__.": defaults=".arrstr($f['defaults']));
		}else 
			stop(array('result'=>'ERROR','desc'=>"Не указан шаблон!"));
	}
	return $f;
}

function before_edit_profile($f) {
	unset($f['fields']['clone']);
	return $f;
}

function checks_profile($r,$my) {
	global $DEBUG, $config, $q;
	if($DEBUG>0) log_txt(__function__.": start");
	if($my->id=='new'){
		if($q->select("SELECT count(*) FROM radusergroup WHERE groupname='{$r['groupname']}'")>0)
			stop(array('result'=>'ERROR','desc'=>"Профиль уже существует!"));
	}else{
		if($q->select("SELECT count(*) FROM radusergroup WHERE groupname='{$r['gname']}'")>0)
			stop(array('result'=>'ERROR','desc'=>"Профиль уже существует!"));
	}
}

function save_profile($s,$my) {
	global $DEBUG, $config, $opdata, $tables, $q;
	log_txt(__function__.": s=".arrstr($s));
	if($my->id == 'new'){
		if(isset($s['clone']) && isset($s['gname']) && $s['gname']!=''){ // создание клона профиля
			$p = array_intersect_key($s,$q->table_fields('radusergroup'));
			$p['groupname'] = $s['gname'];
			log_txt(__function__.": clone: ".arrstr($p));
			if(!$q->insert('radusergroup',$p)){
				stop(array('result'=>'ERROR','desc'=>"Создание профиля завершилось ошибкой!<br>profile=".arrstr($profile)));
			}
			$rgc = $q->select("SELECT '{$p['groupname']}' as groupname, attribute, op, value FROM radgroupcheck WHERE groupname='{$s['clone']}'");
			if(!($r = $q->insert('radgroupcheck',$rgc))){
				$q->del('radusergroup',$p);
				stop(array('result'=>'ERROR','desc'=>"Создание профиля `{$p['groupname']}` check завершилось ошибкой!<br>".arrstr($r)));
			}
			$rgr = $q->select("SELECT '{$p['groupname']}' as groupname, attribute, op, value FROM radgroupreply WHERE groupname='{$s['clone']}'");
			if(!($r = $q->insert('radgroupreply',$rgr))){
				$q->del('radusergroup',$p);
				stop(array('result'=>'ERROR','desc'=>"Создание профиля `{$p['groupname']}` reply завершилось ошибкой!<br>".arrstr($r)));
			}
			if(!$q->select("SELECT * FROM radgroupreply WHERE groupname='{$p['groupname']}' AND attribute='Class'"))
				$q->insert('radgroupreply',array('attribute'=>'Class','op'=>':=','groupname'=>$p['groupname']));
			elseif(!$q->query("UPDATE radgroupreply SET value='{$p['groupname']}' WHERE groupname='{$p['groupname']}' AND attribute='Class'")){
				$q->del('radusergroup',$p);
				stop(array('result'=>'ERROR','desc'=>"Обновление профиля `{$p['groupname']}` reply завершилось ошибкой!"));
			}
			$s['groupname'] = $p['groupname'];
		}
	}else{
		$p = array();
		if(isset($s['gname']) && isset($my->row['gname']) && $s['gname']!=$my->row['gname']){
			$q->query("UPDATE radgroupcheck SET groupname='{$s['gname']}' WHERE groupname='{$my->row['gname']}'");
			$q->query("UPDATE radgroupreply SET groupname='{$s['gname']}' WHERE groupname='{$my->row['gname']}'");
			$q->query("UPDATE radgroupreply SET value='{$s['gname']}' WHERE groupname='{$s['gname']}' AND attribute='Class'");
			$p['groupname'] = $s['gname'];
		}
		if(isset($s['username'])) $p['username'] = $s['username'];
		if(isset($s['priority'])) $p['priority'] = $s['priority'];
		foreach($p as $k=>$v) $a[] = "`$k`='$v'";
		if(isset($a) && $my->id != ''){
			if(!$q->query("UPDATE radusergroup SET ".implode(', ',$a)." WHERE groupname='{$my->id}'"))
				stop(array('result'=>'ERROR','desc'=>"Обновление профиля `{$my->id}` завершилось ошибкой!"));
		}elseif(isset($a)){
			stop(array('result'=>'ERROR','desc'=>"Обновление профиля `{$my->id}` reply завершилось ошибкой!<br> set=".arrstr($a)."<br> row=".arrstr($my->row)));
		}else return false;
	}
	if($DEBUG>0) log_txt(__function__.": s=".arrstr($s));
	return $s['groupname'];
}

function profiles_auto_object() {
	global $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	$req = (isset($_REQUEST['req']))? str($_REQUEST['req']) : '';
	$out['result'] = 'OK';
	$out['complete'] = $q->select("
		SELECT distinct object as label FROM profiles
		WHERE owner like '%$req%'
		HAVING label!=''
		ORDER BY owner
	");
	return $out;
}

function profiles_auto_owner() {
	global $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	$req = (isset($_REQUEST['req']))? str($_REQUEST['req']) : '';
	$out['result'] = 'OK';
	$out['complete'] = $q->select("
		SELECT distinct owner as label FROM profiles
		WHERE owner like '%$req%'
		HAVING label!=''
		ORDER BY owner
	");
	return $out;
}

function profiles_auto_address() {
	global $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	$req = (isset($_REQUEST['req']))? str($_REQUEST['req']) : '';
	$out['result'] = 'OK';
	$out['complete'] = $q->select("
		SELECT distinct address as label
		FROM ((SELECT distinct address FROM profiles) UNION
			(SELECT distinct left(trim(address),char_length(trim(address))-locate(' ',reverse(trim(address)))) as address FROM users)) as a
		WHERE address like '%$req%'
		HAVING label!=''
		ORDER BY address
	");
	return $out;
}

function profiles_get_rayons() {
	global $opdata, $config, $q;
	if(!$q) $q=new sql_query($config['db']);
	$sql="SELECT rid as id, r_name as name FROM rayon ORDER BY name";
	return $q->fetch_all($sql);
}

function build_filter_for_profiles($t) {
	global $tables, $_REQUEST;
	$r = array(); $s = '';
	$a = $tables[$t]['filters'];
	if(is_array($a)){
		foreach($a as $k=>$v){
			if ($k == 'rayon') {
				if(isset($_REQUEST[$k]) && $v!='') $r[] = "AND `$k` in (".preg_replace('/[^0-9,]/','',$_REQUEST[$k]).")";
			}else{
				if(isset($_REQUEST[$k]) && $v!='') $r[] = "AND `$k`='".str($_REQUEST[$k])."'";
			}
		}
		$s .= implode(' ',$r);
	}
 	log_txt(__function__.": return: $s");
	return $s;
}
?>
