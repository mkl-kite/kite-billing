<?php
include_once("classes.php");
include_once("rayon_packet.cfg.php");
include_once("rayon.cfg.php");
include_once("geodata.php");
$errors = array();

$tables['claims']=array(
	'name'=>'claims',
	'title'=>'Заявка',
	'target'=>'select',
	'module'=>"claims",
	'key'=>'unique_id',
	'limit'=>'yes',
	'class'=>'normal',
	'delete'=>'no',
	'header'=>"<div class=\"button\" id=\"ext\" style=\"position:relative;height:30px;width:30px;float:right;background:url(pic/next.png) no-repeat center\"></div>",
	'form_query'=>"
		SELECT 
			unique_id,
			type,
			status,
			claimtime,
			user,
			uid,
			address,
			rid,
			fio,
			phone,
			content,
			operator,
			woid,
			perform_note,
			location
		FROM
			claims
		",
	'table_query'=>"
		SELECT 
			unique_id,
			unique_id as claim,
			claimtime,
			status,
			concat(address,':',cast(type as CHAR CHARACTER SET utf8)) as address,
			phone,
			c.rid,
			content,
			operator
		FROM
			claims c JOIN rayon r ON c.rid = r.rid
		WHERE 1 :FILTER: :PERIOD:
		ORDER BY :SORT:
		",
	'field_alias'=>array('pid'=>'c','rid'=>'c'),
	'layout'=>array(
		'claim'=>array(
			'type'=>'fieldset',
			'legend'=>"Заявление",
			'style'=>'width:370px;height:430px;float:left;',
			'fields'=>array('operator','type','status','claimtime','rid','user', 'address','fio','phone','content','perform_note')
		),
		'rayonmap'=>array(
			'type'=>'fieldset',
			'legend'=>'Карта',
			'class'=>'closable',
			'style'=>'display:none;width:350px;height:430px;float:left;',
			'fields'=>array('location')
		),
	),
	'form_autocomplete'=>array(
		'address'=>'claim_auto_address',
		'fio'=>'claim_auto_fio',
		'user'=>'claim_auto_user',
	),
	'filters'=>array(
		'operator'=>array(
			'type'=>'select',
			'label'=>'принял',
			'title'=>'оператор',
			'style'=>'width:90px',
			'list'=>all2array(list_operators()),
			'value'=>'_'
		),
		'end'=>array(
			'type'=>'date',
			'label'=>'конец',
			'style'=>'width:80px',
			'title'=>'дата конца',
			'value'=>cyrdate(date('Y-m-d'))
		),
		'begin'=>array(
			'type'=>'date',
			'label'=>'начало',
			'style'=>'width:80px',
			'title'=>'дата начала',
			'value'=>cyrdate(strtotime('-1 year'))
		),
		'rid'=>array(
			'type'=>'checklist',
			'label'=>'районы',
			'title'=>'выбор районов',
			'keep'=>true,
			'list'=>$q->fetch_all("SELECT concat('_',rid) as id, r_name as name FROM rayon ORDER BY r_name"),
			'value'=>'_'
		),
		'status'=>array(
			'type'=>'select',
			'label'=>'статус',
			'style'=>'width:90px',
			'keep'=>true,
			'title'=>'состаяние заявленя',
			'list'=>all2array($claim_status),
			'value'=>isset($_COOKIE['usrconfig']['claims']['rid'])? $_COOKIE['usrconfig']['claims']['rid'] : '_',
		),
		'type'=>array(
			'type'=>'select',
			'label'=>'тип',
			'title'=>'тип заявленя',
			'style'=>'width:90px',
			'list'=>all2array($claim_types),
			'value'=>'_'
		),
	),
	'table_menu'=>array(
		'edit'=>array('label'=>"<img src=\"pic/gtk-edit16.png\"> изменить",'to'=>'form'),
		'notice'=>array('label'=>"<img src=\"pic/doc.png\"> уведомить",'to'=>'edit','query'=>"go=stdform&do=new&table=documents&tname=claims"),
		'usrcard'=>array('label'=>"<img src=\"pic/usr.png\"> клиент",'to'=>'edit','query'=>"go=claims&do=user"),
		'showmap'=>array('label'=>"<img src=\"pic/usr.png\"> показать на карте",'to'=>'map','query'=>"go=clients&do=clientobject&table=claims"),
		'hold'=>array('label'=>"<img src=\"pic/hold.png\"> отложить",'to'=>'edit','query'=>"go=claims&do=hold"),
		'select'=>array('label'=>"<img src=\"pic/ok.png\"> выбрать",'to'=>'select'),
	),
	'fixed_menu'=>array(
		'new'=>array('label'=>"новый наряд",'image'=>"pic/doc-new.png",'to'=>'claims','query'=>"go=stdform&do=new&table=workorders"),
		'exists'=>array('label'=>"сущ. наряд",'image'=>"pic/doc-in.png",'to'=>'claims', 'query'=>"go=worders&do=list&table=workorders"),
		'hold'=>array('label'=>"отолжить",'image'=>"pic/hold.png",'to'=>'claims','query'=>"go=claims&do=hold"),
		'close'=>array('label'=>"закрыть",'image'=>"pic/stop.png",'to'=>'claims','query'=>"go=claims&do=close"),
		'cancel'=>array('label'=>"отменить",'image'=>"pic/del.png",'to'=>'cancel'),
	),
	'table_footer'=>array(
		'claim'=>'Всего:',
		'operator'=>'fcount',
	),
	'defaults'=>array(
		'numports'=>1,
		'filter'=>'build_filter_for_claims',
		'period'=>'build_period_for_claims',
		'sort'=>'claimsort'
	),
// 	если проверка не пройдена функция должна прервать обработку данных
	'checks'=>array(
		'save'=>'check_claim_for_save'
	),
// 	преобразование данных формы к заданному формату base->server->client
	'form_triggers'=>array(
		'operator'=>'get_operator',
		'claimtime'=>'cell_atime'
	),
	'table_triggers'=>array(
		'claim'=>'get_claim',
		'type'=>'get_claimtype',
		'rid'=>'get_rayon',
		'address'=>'get_claddress',
		'status'=>'get_tclaimstatus',
		'claimtime'=>'cell_atime',
		'operator'=>'get_operator'
	),
	'before_new'=>'before_new_claim',
	'before_edit'=>'before_edit_claim',
	'before_save'=>'before_save_claim',
 	'form_onsave'=>'onsave_claim',
	'allow_delete'=>'allow_delete_claim',
	'before_table_send'=>'claims_send',
	'group'=>'',

	'fields'=>array(
		'unique_id'=>array(
			'label'=>'id',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'claim'=>array(
			'label'=>'N',
			'type'=>'text',
			'table_style'=>'width:30px',
			'native'=>false,
			'access'=>array('r'=>3,'w'=>3)
		),
		'woid'=>array(
			'label'=>'наряд',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'type'=>array(
			'label'=>'тип',
			'type'=>'select',
			'list'=>$claim_types,
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3),
			'onchange'=>"
				var s=$(this).val().replace(/[^0-9]/g,''),
					f=$(this).parents('form'),
 					fld=['user','fio','phone'],
 					uid=f.find('[name=uid]').val(), n;
				if(s==4 && !(uid>0)) for(n in fld) f.find('#field-'+fld[n]).hide();
				else for(n in fld) f.find('#field-'+fld[n]).show();
			"
		),
		'status'=>array(
			'label'=>'статус',
			'type'=>'select',
			'list'=>'claim_status_list',
			'class'=>'fio ctxt',
			'table_style'=>'width:20px',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>3),
			'onchange'=>"
				var s=$(this).val().replace(/[^0-9]/g,''),
					f=$(this).parents('form'),
 					fld=['type','rid','user','address','fio','phone'],
					p=f.find('#field-perform_note'), n;
				if(s>2) for(n in fld) f.find('[name='+fld[n]+']').attr('disabled',true);
				else for(n in fld) f.find('[name='+fld[n]+']').removeAttr('disabled');
				if(s==5) p.show(); else p.hide();
			"
		),
		'claimtime'=>array(
			'label'=>'дата заявки',
			'type'=>'nofield',
			'class'=>'date',
			'table_style'=>'width:110px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'rid'=>array(
			'label'=>'район',
			'type'=>'select',
			'list'=>'list_of_rayons',
			'class'=>'fio',
			'table_style'=>'width:200px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3),
			'onchange'=>"
				var rid=$(this).val().replace(/[^0-9]/g,''),
					f=$(this).parents('form'), m = f.find('[type=map]').get(0);
				if(m){
					if(typeof(ldr) !== 'object') ldr = $.loader();
					if(!(rid > 0)) return false;
					ldr.get({
						data:'go=claims&do=rayon_xy&id='+rid,
						onLoaded: function(d){
							var p = [], z = 12;
							if('rayon' in d) {
								p = d.rayon.split(/,/);
								if(p.length >= 2){
									if(p.length == 3) p[2] = p[2] - 2;
									if('_map' in m){
										m._map.setView({lat:p[0],lng:p[1]},p[2]||z);
									}else{
										default_position = p.join(',');
									}
								}
							}
						}
					})
				}
			"
		),
		'user'=>array(
			'label'=>'логин',
			'type'=>'autocomplete',
			'style'=>'width:150px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'address'=>array(
			'label'=>'адрес',
			'type'=>'autocomplete',
			'style'=>'width:250px',
			'table_style'=>'width:250px',
			'class'=>'fio',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'fio'=>array(
			'label'=>'Ф.И.О.',
			'type'=>'autocomplete',
			'style'=>'width:250px',
			'class'=>'fio',
			'native'=>true,
			'access'=>array('r'=>1,'w'=>3)
		),
		'uid'=>array(
			'label'=>'uid',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'phone'=>array(
			'label'=>'телефон',
			'type'=>'text',
			'style'=>'width:250px',
			'class'=>'phone',
			'table_style'=>'width:110px',
			'native'=>true,
			'access'=>array('r'=>2,'w'=>3)
		),
		'content'=>array(
			'label'=>'содержимое',
			'type'=>'textarea',
			'style'=>'width:250px;height:80px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'operator'=>array(
			'label'=>'принял',
			'type'=>'nofield',
			'class'=>'address',
			'table_style'=>'width:100px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'perform_note'=>array(
			'label'=>'причина',
			'style'=>'width:250px',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'perform_operator'=>array(
			'label'=>'закрыл',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
		'location'=>array(
			'label'=>'Карта',
			'type'=>'map',
			'style'=>'width:350px;height:420px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>3)
		),
	)
);
function claims_send($t){
	global $DEBUG, $q, $opdata;
	if($opdata['level']>4){
		$t['table_menu']['delete'] = array(
			'label'=>"<img src=\"pic/del.png\"> удалить",
			'to'=>'form',
			'query'=>"go=claims&do=delete&table=claims"
		);
	}
	return $t;
}

function before_new_claim($f) {
	global $DEBUG, $q, $config, $opdata;
	$point = false;
	if($DEBUG>0) log_txt(__function__.": {$t['name']} id={$t['id']}");
	if(!is_object($q)) $q = sql_query($config['db']);
	$type = (isset($_REQUEST['type']))? preg_replace('[^0-9]','',$_REQUEST['type']) : '1';
	$uid = (isset($_REQUEST['uid']))? numeric($_REQUEST['uid']) : false;
	if(!$uid && @$_REQUEST['key']=='uid' && isset($_REQUEST['id'])) $uid = numeric($_REQUEST['id']);
	$addr = (isset($_REQUEST['address']))? str($_REQUEST['address']) : false;
	$f['id'] = 'new';
	$f['header'] = "новая заявка".$f['header'];
	$f['defaults']['claimtime'] = strftime('%Y-%m-%d %H:%M');
	if($type!='') $f['defaults']['type']=$type;
	if($uid) {
		$f['defaults']['type']='2';
		if($tmp = $q->select("SELECT uid,user,rid,fio,address,phone FROM users WHERE uid='{$uid}'",1))
			$f['defaults'] = array_merge($f['defaults'],$tmp);
		$f['focus']='content';
	}
	// определяем координаты пользователя
	if(isset($f['defaults']['user']) && (!isset($f['defaults']['location']) || $f['defaults']['location'] == '')){
		$point = $q->select("SELECT x.x, x.y FROM map_xy x, map m WHERE x.object = m.id AND m.type='client' AND m.name='{$f['defaults']['user']}'",1);
	}
	if(!$point && isset($f['defaults']['address']) && (!isset($f['defaults']['location']) || $f['defaults']['location'] == '')){
		if($m = parse_address($f['defaults']['address'])){
			if($tmp = $q->select("SELECT id, type, address, gtype FROM map WHERE address like '%{$m['addr']}%' ORDER BY type"))
				foreach($tmp as $k=>$v){
					if(!($s = parse_address($v['address']))) continue;
					if($m['home'].$m['litera'] == $s['home'].$s['litera']){
						if($v['gtype'] == 'Point')
							$point = $q->select("SELECT x, y FROM map_xy WHERE object = '{$v['id']}'",1);
						else
							$point = $q->select("SELECT sum(x)/count(*) as x, sum(y)/count(*) as y FROM map_xy WHERE object='{$v['id']}' AND num!=0",1);
						break;
					}
				}
		}
	}
	if($point) $f['defaults']['location'] = "{$point['y']},{$point['x']},15";
	// Смотрим есть ли уже такая заявка
	$req = array_intersect_assoc($f['defaults'],array('address'=>0,'uid'=>0));
	if($_REQUEST['do'] != 'realnew'){
		if($uid>0 && ($cl = $q->select("SELECT * FROM claims WHERE uid='$uid' AND claimtime>date(now())"))){
			$form = new form($config);
			$out = $form->confirmForm('new','realnew',"Заявление сегодня уже принято!<BR>Вы хотите добавить ещё одно?",'claims');
			$out['form']['fields']['uid']=array('type'=>'hidden','value'=>$cl[0]['uid']);
			stop($out);
		}
		if($f['defaults']['address'] && ($cl = $q->select("SELECT * FROM claims WHERE address='{$f['defaults']['address']}' AND status=1 AND type='$type'"))){
			$form = new form($config);
			$out = $form->confirmForm('new','realnew',"Такое заявление уже есть в отложенных!<BR>Вы хотите добавить ещё одно?",'claims');
			$out['form']['fields']['uid']=array('type'=>'hidden','value'=>$uid);
			stop($out);
		}
	}else{
		$f['do'] = 'realsave';
	}
	if(!$f['defaults']['operator']) $f['defaults']['operator'] = $opdata['login'];
	return $f;
}

function get_operator($v,$r,$fn=null) {
	$op = list_operators();
	if(isset($op[$v])) return $op[$v];
	return $v;
}

function before_edit_claim($t) {
	global $DEBUG, $q, $config, $opdata;
	if($DEBUG>0) log_txt(__function__.": {$t['name']} id={$t['id']}");
	if(@$t['id']=='new' || @$_REQUEST['id']=='new' || @$_REQUEST[$t['key']]=='new'){
		$t['id'] = 'new';
		$func = @$t['before_new'];
		if(function_exists($func)) $t = $func($t);
	}else{
		$id = (isset($_REQUEST['id']))? numeric($_REQUEST['id']) : 0;
		if($id>0) $t['header'] = "заявка &#8470; ".$id.$t['header'];
		$form_triggers=array('type'=>'get_claimtype','status'=>'get_claimstatus','rid'=>'get_rayon_name');
		if(!($c = $t['record'] = $q->select("SELECT * FROM `claims` WHERE unique_id='{$id}'",1)))
			stop(array('result'=>'ERROR','desc'=>'Это заявление отсутствует в базе!'));
		if($c['status']==4)
			foreach(array('type','status','rid','address','user','fio') as $n) $t['fields'][$n]['type'] = 'nofield';
		if($opdata['level']<3)
			foreach(array('type','status','rid','address','user','fio', 'phone') as $n) $t['fields'][$n]['type'] = 'nofield';
		if($c['status']>=3){
			foreach(array('content','phone') as $n) $t['fields'][$n]['type'] = 'nofield';
			$t['fields']['content']['style'] .= ';overflow-y:auto';
		}
		foreach($form_triggers as $k=>$proc)
			if($t['fields'][$k]['type'] == 'nofield') $t['form_triggers'][$k] = $proc;
	}
	return $t;
}

function get_claim($v,$r,$fn=null) {
	global $claim_types;
	$out = "<span class=\"linkform\" add=\"go=claims&do=edit&id=$v\">$v</span>";
	return $out;
}

function get_claddress($v,$r,$fn=null) {
	global $claim_types, $claim_colors;
	$a = preg_split('/:/',$v);
	$out = "<span class=\"cltype\" style=\"color:{$claim_colors[$a[1]]}\" title=\"{$claim_types[$a[1]]}\">{$a[0]}</span>";
	return $out;
}

function get_claimtype($v,$r,$fn=null) {
	global $claim_types, $claim_colors;
	$out = "<b style=\"color:{$claim_colors[$v]}\">".$claim_types[$v].'</b>';
	return $out;
}

function get_tclaimstatus($v,$r,$fn=null) {
	global $status_cl;
	return $status_cl[$v];
}

function get_claimstatus($v,$r,$fn=null) {
	global $claim_status;
	return $claim_status[$v];
}

function before_save_claim($cmp,$old,$my) {
	global $config, $opdata, $q;
	$point = false;
	$r = array_merge($old,$cmp);
	if($my->id == 'new' && $r['type']==1 && isset($cmp['uid'])) unset($cmp['uid']);
	if($my->id == 'new'){
		if(isset($r['address']) && $r['address']!='' && $_REQUEST['do'] != 'realsave'){
			if($cl = $q->select("SELECT * FROM claims WHERE address='{$r['address']}' AND claimtime>date(now())")){
				$form = new form($config);
				$out = $form->confirmForm('new','realsave',"Заявление по адресу {$r['address']} уже принято!<BR>Вы хотите добавить ещё одно?",'claims');
				foreach($cmp as $k=>$v) $out['form']['fields'][$k] = array('type'=>'hidden','value'=>$v);
				$out['nosubmit'] = true;
				stop($out);
			}
			if($cl = $q->select("SELECT * FROM claims WHERE address='{$r['address']}' AND status=1")){
				$form = new form($config);
				$out = $form->confirmForm('new','realsave',"Заявление по адресу {$r['address']}<BR>уже есть в отложенных!<BR>Вы хотите добавить ещё одно?",'claims');
				foreach($cmp as $k=>$v) $out['form']['fields'][$k] = array('type'=>'hidden','value'=>$v);
				stop($out);
			}
		}
	}
	if($my->id == 'new' && strtotime($cmp['claimtime'])<strtotime(date('Y-m-d'))) $cmp['claimtime'] = date2db();
	if(isset($cmp['fio'])) $cmp['fio'] = trim($cmp['fio']);
	if(key_exists('phone',$cmp)) $cmp['phone'] = normalize_phone($cmp['phone']);
	if(key_exists('user',$cmp) && $cmp['user']!='') {
		if(!isset($q)) $q = new sql_query($config['db']);
		if(!($uid = $q->get("users",$cmp['user'],'user','uid'))) { // если пользователя нет в базе
			$cmp['user']=='';
			$cmp['uid']=='';
		}
	}
	if(isset($cmp['address']) && $r['type']!=4 && !($cmp['address'] = normalize_address($cmp['address']))) stop("Ошибка в адресе");
	if(isset($cmp['user']) && (!isset($cmp['location']) || $r['location'] == '')){
		$point = $q->select("SELECT x.x, x.y FROM map_xy x, map m WHERE x.object = m.id AND m.type='client' AND m.name='{$f['defaults']['user']}'",1);
	}
	if(isset($cmp['address']) && (!isset($cmp['location']) || $r['location'] == '')) {
		if(!is_object($q)) $q = sql_query($config['db']);
		if($m = parse_address($cmp['address'])){
			if($tmp = $q->select("SELECT id, type, address, gtype FROM map WHERE address like '%{$m['addr']}%' ORDER BY type"))
				foreach($tmp as $k=>$v){
					if(!($s = parse_address($v['address']))) continue;
					if($m['home'].$m['litera'] == $s['home'].$s['litera']){
						if($v['gtype'] == 'Point')
							$point = $q->select("SELECT x, y FROM map_xy WHERE object = '{$v['id']}'",1);
						else
							$point = $q->select("SELECT sum(x)/count(*) as x, sum(y)/count(*) as y FROM map_xy WHERE object='{$v['id']}' AND num!=0",1);
						break;
					}
				}
		}
	}
	if($point) $cmp['location'] = "{$point['y']},{$point['x']},15";
	if(isset($cmp['user']) && $cmp['user']=='' && $r['type']==1) {
			$cmp['user'] = ($r['fio']=='')? "usr".rand(100000000,999999999) : fiotologin($r['fio']);
	}
	if(!isset($cmp['operator']) && $r['operator']=='') $cmp['operator'] = $opdata['login'];
	if(isset($cmp['content'])) $cmp['content'] = trim(preg_replace('/[\r\n]{1,}/',' ',$cmp['content']));
	if(isset($cmp['status'])){
		if(!isset($q)) $q = new sql_query($config['db']);
		$claim = $q->get("claims",@$r['unique_id']);
		if($old['status']<2){
			if($cmp['status']==4 || $cmp['status']==2){
				stop(array('result'=>'ERROR','desc'=>"Статус не может быть изменен на {$claim_status[$cmp['status']]}"));
			}
		}elseif($old['status']==2) {
			if($cmp['status']==4) { // выполнено
				$q->query("UPDATE claimperform SET status=3 WHERE cid='{$claim['unique_id']}' AND woid='{$claim['woid']}'");
				if($q->modified()<=0) log_txt(__function__.": для заявки {$r['unique_id']} не был изменён статус задания!");
			}else{
				$cp = $q->get("claimperform",array('cid'=>$claim['unique_id'],'woid'=>$claim['woid']));
				if(!isset($cp[0])) stop(array('result'=>'ERROR','desc'=>"Не найдено задание для этого заявления!"));
				if($cp[0]['status']<2) {
					$q->del('claimperform',$cp[0]['unique_id']);
					$cmp['woid']=null;
				}
				if($cmp['status']<2 && $cp[0]['status']>2)
					$q->update_record("claimperform",array('unique_id'=>$r['unique_id'],'status'=>2));
			}
		}elseif($old['status']==4){
			$cp = $q->get("claimperform",array('cid'=>$claim['unique_id'],'woid'=>$claim['woid']));
			if(!isset($cp[0])) stop(array('result'=>'ERROR','desc'=>"Не найдено задание для этого заявления!"));
			$wo = $q->get('workorders',$claim['woid']);
			if(!$wo) stop(array('result'=>'ERROR','desc'=>"Не найден наряд для этого заявления!"));
			if($cmp['status']==2){
				if($wo['status']==3){
					stop(array('result'=>'ERROR','desc'=>"Статус не может быть изменен на {$claim_status[$cmp['status']]}"));
				}else{
					$q->update_record("claimperform",array('unique_id'=>$cp[0]['unique_id'],'status'=>0));
				}
			}elseif($cmp['status']<2){
				if($wo['status']==3){
					stop(array('result'=>'ERROR','desc'=>"Статус не может быть изменен на {$claim_status[$cmp['status']]}"));
				}else{
					$q->del("claimperform",$cp[0]['unique_id']);
					$cmp['woid'] = null;
				}
			}
		}else{
			if($cmp['status']==4 || $cmp['status']==2){
				stop(array('result'=>'ERROR','desc'=>"Статус не может быть изменен на {$claim_status[$cmp['status']]}"));
			}
		}
		if($cmp['status']>2) $cmp['perform_operator'] = $opdata['login'];
		else $cmp['perform_operator'] = '';
	}
// 	log_txt(__function__.": cmp: ".arrstr($cmp));
	return $cmp;
}

function check_phone($s) {
	global $errors;
	if(normalize_phone($s)) return true;
	$errors[] = 'Не указан номео телефона';
	return false;
}

function check_rid($s) {
	global $errors;
	if(is_numeric($s) and $s>0) return true;
	$errors[] = 'Не указан район';
	return false;
}

function check_type($s) {
	global $errors, $claim_types;
	if(isset($claim_types[$s])) return true;
	$errors[] = 'Не указан тип заявки';
	return false;
}

function check_fio($s) {
	if(normalize_fio($s)) return true; else return false;
}

function check_claim_for_save($r) {
	global $config, $errors, $DEBUG;
	$fields = array('type','rid');
	$result = true;
	if($r['type']!=4) $fields = array_merge($fields,array('fio','address'));
	foreach($fields as $fname) {
		$func = 'check_'.$fname;
		if(isset($r[$fname]) && function_exists($func)) {
			if(!$func($r[$fname])) $result = false;
		}
	}
	if($r['type']==2 || $r['type']==3) {
		if(!is_numeric($r['uid']) || !($r['uid']>0) || !is_string($r['user']) || !(mb_strlen($r['user'])>0)) {
			$result = false;
			$errors[] = 'Для этого типа заявки требуется логин';
		}
	}
	if($r['status']==5 && $r['perform_note']==''){
			$result = false;
			$errors[] = 'Не указа причина!';
	}

	if(!$result) stop(array('result'=>'ERROR','desc'=>implode(",<br>",$errors)));
	return $result;
}

function onsave_claim($id,$s,$my) {
	global $config, $claim_types, $claim_status, $errors, $opdata, $q;
	if($id == 'new'){
		$s['type'] = $claim_types[$s['type']];
		send_notify('new_claim',$s);
	}
	if(isset($s['status']) && $s['status']>=3){
		$c = array_merge($my->row,$s);
		$status = $c['status'];
		$c['claimtime'] = cyrdate($c['claimtime'],'%d %b %Y %H:%M');
		$c['type'] = $claim_types[$c['type']];
		$c['status'] = $claim_status[$c['status']];
		if($status==3) $c['perform_note'] = $claim_status[$status];
		if($status==4) send_notify('end_job',$c);
		if($status==3 || $status==5) send_notify('cancel_job',$c);
	}
	return true;
}

function allow_delete_claim($r) {
	global $config, $q, $DEBUG;
	if($DEBUG>0) log_txt(__function__.": id='{$r['unique_id']}'");
	if(!$q) $q = new sql_query($config['db']);
	$cp = $q->select("SELECT count(*) FROM claimperform WHERE cid='{$r['unique_id']}'",4);
	if($cp>0){
		log_txt(__function__.": Удаление заявления {$r['unique_id']} невозможно! Присутствуют связанные данные");
		return "Удаление невозможно! Присутствуют связанные данные.";
	}
	return 'yes';
}

function claim_status_list($r) {
	global $claim_status;
	$out=$claim_status;
	if($r['status']!=2 & $r['status']!=4){
		unset($out[2]);
		unset($out[4]);
	}
	return $out;
}

function claim_auto_address(){
	global $config, $q;
	$req = (isset($_REQUEST['req']))? str($_REQUEST['req']) : '';
	$out['result'] = 'OK';
	$out['complete'] = $q->select("
		SELECT DISTINCT address as label
		FROM users 
		WHERE address like '%$req%'
		HAVING label!='' 
		ORDER BY address
		LIMIT 20
	");
	return $out;
}

function claim_auto_user($req){
	global $config, $q;
	$req = (isset($_REQUEST['req']))? str($_REQUEST['req']) : '';
	$out['result'] = 'OK';
	$out['complete'] = $q->select("
		SELECT DISTINCT 
			user as label, address, fio, phone, uid, concat('_',rid) as rid
		FROM users 
		WHERE user like '%$req%'
		HAVING label!='' 
		ORDER BY user
		LIMIT 20
	");
	return $out;
}

function claim_auto_fio($req){
	global $config, $q;
	$req = (isset($_REQUEST['req']))? str($_REQUEST['req']) : '';
	$out['result'] = 'OK';
	$out['complete'] = $q->select("
		SELECT DISTINCT 
			fio as label, address, user, phone, uid, concat('_',rid) as rid
		FROM users 
		WHERE fio like '%$req%'
		HAVING label!='' 
		ORDER BY fio
		LIMIT 20
	");
	return $out;
}

function claimsort() {
	if(!isset($_REQUEST['sort'])) return 'claimtime desc';
	$s = strict($_REQUEST['sort']);
	if($s == 'rid') return 'r_name, claimtime';
	return $s;
}

function build_period_for_claims($t) {
	return period2db('claims','claimtime');
}

function build_filter_for_claims($t) {
	return filter2db('claims');
}
?>
