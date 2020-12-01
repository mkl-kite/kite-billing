<?php
include_once("classes.php");
include_once("table.php");

$tables['radippool']=array(
	'title'=>'Платежные ведомости',
	'target'=>"html",
	'name'=>"radippool",
	'module'=>"stdform",
	'key'=>'id',
	'class'=>'normal',
	'delete'=>'yes',
	'limit'=>'yes',
	'table_query'=>"
		SELECT 
			id,
			pool_name,
			framedipaddress,
			callingstationid,
			expiry_time,
			nasipaddress,
			username,
			pool_key
		FROM radippool
		WHERE 1 :FILTER:
		ORDER BY :SORT:
		",
	'form_query'=>"
		SELECT 
			id,
			pool_name,
			framedipaddress,
			nasipaddress,
			calledstationid,
			callingstationid,
			expiry_time,
			username,
			pool_key
		FROM radippool
		",
	'table_menu'=>array(
		'edit'=>array('label'=>"<img src=\"pic/gtk-edit16.png\"> изменить",'to'=>'form','query'=>"go=stdform&do=edit&table=radippool"),
		'delete'=>array('label'=>"<img src=\"pic/del.png\"> удалить",'to'=>'form','query'=>"go=stdform&do=delete&table=radippool"),
		'disconnect'=>array('label'=>"<img src=\"pic/del.png\"> сбросить",'to'=>'edit','query'=>"go=clients&do=userkill&table=radippool"),
	),
 	'table_footer'=>array(
		'pool_name'=>'Всего:',
		'pool_key'=>'fcount',
 	),
	'table_triggers'=>array(
		'operator' => 'get_radippool_operator',
		'expiry_time' => 'cell_atime',
		'summa'=> 'cell_summ',
	),
	'form_triggers'=>array(
	),
	'group'=>'',
	'defaults'=>array(
		'sort'=>'framedipaddress',
		'filter'=>'build_filter_for_radippool',
	),
	'filters'=>array(
		'start'=>array(
			'type'=>'text',
			'origin'=>'framedipaddress',
			'label'=>'',
			'style'=>"display:none",
			'title'=>'начальный ip',
			'value'=>''
		),
		'stop'=>array(
			'type'=>'text',
			'origin'=>'framedipaddress',
			'label'=>'',
			'style'=>"display:none",
			'title'=>'конечный ip',
			'value'=>''
		),
		'pool_name'=>array(
			'type'=>'select',
			'label'=>'название',
			'title'=>'название пула',
			'style'=>"width:110px",
			'list'=>all2array($q->fetch_all("SELECT distinct pool_name as id, pool_name as name FROM radippool ORDER BY name")),
			'value'=>isset($_REQUEST['pool_name'])? strict($_REQUEST['pool_name']): "",
		),
	),

	// поля
	'fields'=>array(
		'id'=>array(
			'label'=>'id',
			'type'=>'hidden',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'pool_name'=>array(
			'label'=>'название',
			'type'=>'text',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'framedipaddress'=>array(
			'label'=>'ip',
			'type'=>'text',
			'class'=>'ctxt',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'nasipaddress'=>array(
			'label'=>'сервер',
			'type'=>'text',
			'class'=>'ctxt',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'calledstationid'=>array(
			'label'=>'mac сервера',
			'type'=>'text',
			'class'=>'csid',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'callingstationid'=>array(
			'label'=>'mac клиента',
			'type'=>'text',
			'class'=>'csid',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'accept'=>array(
			'label'=>'Принята',
			'type'=>'date',
			'class'=>'date',
			'style'=>'width:80px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'expiry_time'=>array(
			'label'=>'аренда',
			'type'=>'date',
			'class'=>'date',
			'style'=>'width:180px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'username'=>array(
			'label'=>'клиент',
			'type'=>'text',
			'class'=>'ltxt',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'pool_key'=>array(
			'label'=>'ключ',
			'type'=>'text',
			'class'=>'ltxt',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5)
		),
		'start'=>array(
			'label'=>'Начало',
			'type'=>'text',
			'class'=>'ctxt',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5),
			'onkeyup'=>"
				if(event.keyCode==8 || event.keyCode==46 || (event.keyCode>47 && event.keyCode<59)||(event.keyCode>95 && event.keyCode<106)) {
					console.log('keyCode = '+event.keyCode);
					var f=$(this).parents('form'),
						b=$(this).val().replace(/[^0-9.]/g,''),
						e=f.find('[name=stop]').val().replace(/[^0-9.]/g,'');
					if(b.match(/\d+.\d+.\d+.\d/) && e.match(/\d+.\d+.\d+.\d/)){
						var r = $.ip2int(e) - $.ip2int(b);
						if(r>=0) f.find('[name=ips]').val(r+1)
						else f.find('[name=ips]').val('0')
						return false;
					}else f.find('[name=ips]').val('0');
				}
			"
		),
		'stop'=>array(
			'label'=>'Конец',
			'type'=>'text',
			'class'=>'ctxt',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5),
			'onkeyup'=>"
				if(event.keyCode==8 || event.keyCode==46 || (event.keyCode>47 && event.keyCode<59)||(event.keyCode>95 && event.keyCode<106)) {
					var f=$(this).parents('form'),
						e=$(this).val().replace(/[^0-9.]/g,''),
						b=f.find('[name=start]').val().replace(/[^0-9.]/g,'');
					if(b.match(/\d+.\d+.\d+.\d/) && e.match(/\d+.\d+.\d+.\d/)){
						var r = $.ip2int(e) - $.ip2int(b);
						if(r>=0) f.find('[name=ips]').val(r+1)
						else f.find('[name=ips]').val('0')
						return false;
					}else f.find('[name=ips]').val('0');
				}
			"
		),
		'ips'=>array(
			'label'=>'кол-во',
			'type'=>'text',
			'class'=>'ctxt',
			'style'=>'width:60px',
			'native'=>true,
			'access'=>array('r'=>3,'w'=>5),
			'onkeyup'=>"
				if(event.keyCode==8 || event.keyCode==46 || (event.keyCode>47 && event.keyCode<59)||(event.keyCode>95 && event.keyCode<106)) {
					var f=$(this).parents('form'), v = $(this).val().replace(/[^0-9]/g,''),
						b=f.find('[name=start]').val().replace(/[^0-9.]/g,''),
						e=f.find('[name=stop]'), r, s;
					if(b.match(/\d+.\d+.\d+.\d/)){
						r = $.int2ip($.ip2int(b) + (v*1) - 1);
						e.val(r)
						return false;
					}else f.find('[name=ips]').val('0');
				}
			"
		),
	),
	'before_new'=>'before_new_radippool',
	'before_edit'=>'before_edit_radippool',
	'before_save'=>'before_save_radippool',
	'form_save'=>'save_radippool',
	'form_onsave'=>'onsave_radippool',
	'form_delete'=>'delete_radippool',
	'allow_delete'=>'allow_delete_radippool',
	'before_delete'=>'before_delete_radippool',
	'before_table_load'=>'before_load_radippool',
	'before_check'=>'before_check_radippool',
);

function short_pools($filter='') {
	global $config, $q, $DEBUG;
	if(!$q) $q = new sql_query($config['db']);
	$filter = ($filter)? "WHERE $filter" : "";
	$q->query("SELECT pool_name, INET_ATON(framedipaddress) as ip FROM radippool $filter ORDER BY pool_name, ip");
	$r = array();
	if($q->result){
		$start = true; $ip = 0;
		while ( $v = $q->result->fetch_assoc() ){
			if($start) {
				$start = !$start;
				$d = array('id'=>long2ip($v['ip']),'pool_name'=>$v['pool_name'],'start'=>long2ip($v['ip']),'stop'=>long2ip($v['ip']), 'ips'=>0);
			}elseif($v['pool_name'] != $d['pool_name'] || $ip+1 != $v['ip']) {
				$d['stop'] = long2ip($ip); $d['id'] .= ":".long2ip($ip);
				$r[] = $d;
				$d = array('id'=>long2ip($v['ip']),'pool_name'=>$v['pool_name'],'start'=>long2ip($v['ip']),'stop'=>long2ip($v['ip']), 'ips'=>0);
			}
			$ip = $v['ip']; $d['ips']++;
		}
		$d['stop'] = long2ip($ip); $d['id'] .= ":".long2ip($ip); $r[] = $d;
	}
	return $r;
}


function before_load_radippool($t) {
	global $config, $q, $DEBUG;
	if(!$q) $q = new sql_query($config['db']);
	$pool_name = strict($_REQUEST['pool_name']);
// 	log_txt(__function__.": pool_name:$pool_name");
	if(!isset($_REQUEST['pool_name'])){
		$p = short_pools();
		$t['data'] = $p;
		if(isset($t['query'])) unset($t['query']);
		if(isset($t['table_query'])) unset($t['table_query']);
		unset($t['filters']);
		$t['limit'] = 'no';
		unset($t['table_menu']['disconnect']);
	}else{
		$slice = isset($_REQUEST['slice'])? str($_REQUEST['slice']) : '';
		if(preg_match('/(\d+\.\d+\.\d+\.\d+):(\d+\.\d+\.\d+\.\d+)/',$slice,$m)){
			if(!isset($_REQUEST['start'])) $_REQUEST['start'] = $m[1];
			if(!isset($_REQUEST['stop'])) $_REQUEST['stop'] = $m[2];
		}
		$t['target'] = 'form';
		$t['limit'] = 'yes';
		$t['delete'] = 'no';
		$t['add'] = 'no';
	}
	return $t;
}


function radippool_expired($r) {
	$ct = is_array($r)? $r['created'] : $r;
	return cyrdate($ct,'%d/%m %Y');
}

function before_new_radippool($f){
	$fld = array('id'=>'new','pool_name'=>'','ips'=>'','start'=>'','stop'=>'');
	$f['fields'] = array_intersect_key($f['fields'],$fld);
	$f['defaults'] = $fld;
	$f['record'] = $fld;
	return $f;
}

function before_edit_radippool($f){
	global $config, $q, $DEBUG;
	if(isset($_REQUEST['id']) && preg_match('/(\d+\.\d+\.\d+\.\d+):(\d+\.\d+\.\d+\.\d+)/',$_REQUEST['id'],$m)){
		$r['id'] = $m[0];
		$start = ip2long($m[1]);
		$stop = ip2long($m[2]);
		$r['pool_name'] = $q->select("SELECT pool_name FROM radippool WHERE framedipaddress='{$m[1]}'",4);
		$r['ips'] = $q->select("SELECT count(*) FROM radippool WHERE INET_ATON(framedipaddress)>=$start AND INET_ATON(framedipaddress)<=$stop",4);
		$r['start'] = $m[1];
		$r['stop'] = $m[2];
		$f['record'] = $r;
	}
	return $f;
}

function before_check_radippool($f,$my) {
	global $config, $q, $DEBUG;
	$fld = array('pool_name','start','stop','ips'); $other = false;
	if(isset($_REQUEST['id']) && preg_match('/(\d+\.\d+\.\d+\.\d+):(\d+\.\d+\.\d+\.\d+)/',$_REQUEST['id'],$m)){
		$start = ip2long($m[1]);
		$stop = ip2long($m[2]);
		$pn = $q->select("SELECT distinct pool_name FROM radippool WHERE INET_ATON(framedipaddress)>=$start AND INET_ATON(framedipaddress)<=$stop");
		if($q->num_rows != 1) stop(array('result'=>'ERROR','desc'=>"Диапазон не принадлежит одному пулу!"));
		$pool_name = $pn[0]['pool_name'];
		if($start > $stop) stop(array('result'=>'ERROR','desc'=>"Неправильный диапазон!"));
		$cnt = $q->select("SELECT count(*) FROM radippool WHERE INET_ATON(framedipaddress)>=$start AND INET_ATON(framedipaddress)<=$stop",4);
		if($cnt != $stop - $start + 1) stop(array('result'=>'ERROR','desc'=>"Кол-во ip не совпадает!"));
		$my->record = array('id'=>$m[0],'pool_name'=>$pool_name,'start'=>$m[1],'stop'=>$m[2]);
	}
	return $f;
}

function allow_delete_radippool($id) {
	global $DEBUG, $config, $q, $opdata, $tables;
	if(isset($_REQUEST['id']) && preg_match('/(\d+\.\d+\.\d+\.\d+):(\d+\.\d+\.\d+\.\d+)/',$_REQUEST['id'],$m)){
		$start = ip2long($m[1]);
		$stop = ip2long($m[2]);
		$pn = $q->select("SELECT distinct pool_name FROM radippool WHERE INET_ATON(framedipaddress)>=$start AND INET_ATON(framedipaddress)<=$stop");
		if($q->num_rows != 1) stop(array('result'=>'ERROR','desc'=>"Диапазон не принадлежит одному пулу!"));
		$pool_name = $pn[0]['pool_name'];
		if($start > $stop) stop(array('result'=>'ERROR','desc'=>"Неправильный диапазон!"));
		$cnt = $q->select("SELECT count(*) FROM radippool WHERE INET_ATON(framedipaddress)>=$start AND INET_ATON(framedipaddress)<=$stop",4);
		if($cnt != $stop - $start + 1) stop(array('result'=>'ERROR','desc'=>"Кол-во ip не совпадает!"));
		return 'yes';
	}else{
		foreach($tables['radippool']['fields'] as $n=>$f) {
			if(!write_access($f['access'],'radippool.'.$n)) return 'Доступ запрещён!';
		}
		return 'yes';
	}
	return 'Удаление не разрешено';
}

function before_save_radippool($c,$o,$my) {
	global $DEBUG, $config, $q, $opdata;
	$r = array_merge($my->row,$o,$c);
	if($DEBUG>0) log_txt(__function__.":{$my->cfg[name]}\n\tcmp=".arrstr($c)."\n\told=".arrstr($o));
	if($DEBUG>0) log_txt(__function__.":{$my->cfg[name]} r=".arrstr($r));
	if(isset($r['id']) && preg_match('/(\d+\.\d+\.\d+\.\d+):(\d+\.\d+\.\d+\.\d+)/',$r['id'],$m)){ // изменяется диапазон пула
		$start = ip2long($m[1]);
		$stop = ip2long($m[2]);
		$pn = $q->select("SELECT distinct pool_name FROM radippool WHERE INET_ATON(framedipaddress)>=$start AND INET_ATON(framedipaddress)<=$stop");
		if($q->num_rows != 1) stop(array('result'=>'ERROR','desc'=>"Диапазон не принадлежит одному пулу!"));
		$pool_name = $pn[0]['pool_name'];
		if($start > $stop) stop(array('result'=>'ERROR','desc'=>"Неправильный диапазон!"));
		$cnt = $q->select("SELECT count(*) FROM radippool WHERE INET_ATON(framedipaddress)>=$start AND INET_ATON(framedipaddress)<=$stop",4);
		if($cnt != $stop - $start + 1) stop(array('result'=>'ERROR','desc'=>"Кол-во ip не совпадает!"));
		if($DEBUG>0) log_txt(__function__.": count = $cnt");
		if(isset($c['start']) || isset($c['stop'])){
			if($DEBUG>0) log_txt(__function__.": start = {$r['start']} stop = {$r['stop']}");
			$n_start = ip2long($r['start']);
			$n_stop = ip2long($r['stop']);
			if($n_start > $n_stop) stop(array('result'=>'ERROR','desc'=>"Неправильный диапазон!"));
			if($q->select("
				SELECT count(*) FROM radippool 
				WHERE pool_name = '$pool_name'
					AND INET_ATON(framedipaddress)<$start AND INET_ATON(framedipaddress)>$stop
					AND INET_ATON(framedipaddress)>=$n_start AND INET_ATON(framedipaddress)<=$n_stop
			",4)>0) stop(array('result'=>'ERROR','desc'=>"Пересечение диапазонов!"));
		}
	}elseif($my->id == 'new' && (isset($c['start']) || isset($c['stop']) || isset($c['ips']))){ // новый диапазон пула
		if((!isset($c['start']) || !isset($c['stop'])) && !isset($c['ips']))
			stop(array('result'=>'ERROR','desc'=>"Невожмножно определить диапазон!"));
		if(!isset($c['start']) && isset($c['stop']) && isset($c['ips'])) $c['start'] = ip2long($c['stop']-$c['ips']+1);
		if(!isset($c['stop']) && isset($c['start']) && isset($c['ips'])) $c['stop'] = ip2long($c['start']+$c['ips']-1);
		if(!isset($c['start']) || !isset($c['stop'])) stop(array('result'=>'ERROR','desc'=>"Невожмножно определить диапазон!"));
		if($n_start > $n_stop) stop(array('result'=>'ERROR','desc'=>"Неправильный диапазон!"));
		if($q->select("
			SELECT count(*) FROM radippool 
			WHERE pool_name = '$pool_name'
				AND INET_ATON(framedipaddress)<$start AND INET_ATON(framedipaddress)>$stop
				AND INET_ATON(framedipaddress)>=$n_start AND INET_ATON(framedipaddress)<=$n_stop
		",4)>0) stop(array('result'=>'ERROR','desc'=>"Пересечение диапазонов!"));
	}else{ // изменяется ip адрес пула
		if($my->id == 'new' || isset($c['pool_name']) || isset($c['framedipaddress'])){
			if($q->select("SELECT distinct pool_name FROM radippool WHERE pool_name='{$r['pool_name']}' AND framedipaddress='{$r['framedipaddress']}'"))
				stop(array('result'=>'ERROR','desc'=>"Такой ip уже есть!"));
		}
	}
	return $c;
}

function del2ippool($pool_name,$start,$stop){
	global $DEBUG, $config, $q, $opdata;
	if(!$q->query("DELETE FROM radippool WHERE pool_name='{$pool_name}' AND
		INET_ATON(framedipaddress)>=$start AND INET_ATON(framedipaddress)<=$stop"))
			stop(array('result'=>'ERROR','desc'=>"Ошибка удаления ip адресов!"));
	return $q->modified();
}

function add2ippool($pool_name,$start,$stop){
	global $DEBUG, $config, $q, $opdata;
	for($i=$start; $i<=$stop; $i++) {
		$ins = array('pool_name'=>$pool_name,'framedipaddress'=>long2ip($i));
		if(!$q->insert('radippool',array('pool_name'=>$pool_name,'framedipaddress'=>long2ip($i)))){
			stop(array('result'=>'ERROR','desc'=>"Ошибка добавления ip адреса (".long2ip($i).") в пул!"));
			return false;
		}
	}
	return $start-$stop+1;
}

function save_radippool($s,$my) {
	global $DEBUG, $config, $q, $opdata;
	if(!$q) $q = new sql_query($config['db']);
	$r = array_merge($my->row,$s);
	if(isset($s['id']) && preg_match('/(\d+\.\d+\.\d+\.\d+):(\d+\.\d+\.\d+\.\d+)/',$s['id'],$m)){  // изменяется диапазон пула
		$start = ip2long($m[1]);
		$stop = ip2long($m[2]);
		if(isset($s['pool_name'])) $q->query("
			UPDATE radippool SET pool_name='{$s['pool_name']}'
			WHERE pool_name='{$my->row['pool_name']}' 
			AND INET_ATON(framedipaddress)>=$start
			AND INET_ATON(framedipaddress)<=$stop
		");
		$n_start = ip2long($r['start']);
		$n_stop = ip2long($r['stop']);
		log_txt(__function__.": n_start:".long2ip($n_start)."  n_stop:".long2ip($n_stop)." start:".long2ip($start)." stop:".long2ip($stop));
		if($n_stop < $start || $n_start > $stop){
			log_txt(__function__.": n_stop < start || n_start > stop");
			$q->query("DELETE FROM radippool WHERE pool_name='{$r['pool_name']}'");
			if($n = add2ippool($r['pool_name'], $n_start, $n_stop)) log_txt(__function__.": вставлено $n ip адресов");
		}elseif($n_stop < $stop && $n_stop > $start){
			if($n = del2ippool($r['pool_name'], $n_stop+1, $stop)) log_txt(__function__.": удалено $n ip адресов");
		}elseif($n_start > $start && $n_start < $stop){
			if($n = del2ippool($r['pool_name'], $start, $n_start-1)) log_txt(__function__.": удалено $n ip адресов");
		}elseif($n_start < $start && $n_stop > $stop){
			if($n = add2ippool($r['pool_name'],$n_start, $start-1)) log_txt(__function__.": вставлено $n ip адресов");
			if($n = add2ippool($r['pool_name'], $stop+1, $n_stop)) log_txt(__function__.": вставлено $n ip адресов");
		}elseif($n_start < $start){
			if($n = add2ippool($r['pool_name'], $n_start, $start-1)) log_txt(__function__.": вставлено $n ip адресов");
		}elseif($n_stop > $stop){
			if($n = add2ippool($r['pool_name'], $stop+1, $n_stop)) log_txt(__function__.": вставлено $n ip адресов");
		}
		$res = $r['start'].":".$r['stop'];
	}elseif($my->id == 'new' && (isset($s['start']) || isset($s['stop']) || isset($s['ips']))){ // новый диапазон пула
		$n_start = ip2long($r['start']);
		$n_stop = ip2long($r['stop']);
		if($n = add2ippool($r['pool_name'],$n_start, $n_stop))
			log_txt(__function__.": вставлено $n ip адресов");
		$res = $s['start'].":".$s['stop'];
	}else{
		if($my->id == 'new'){  // new ip
			$res = $q->insert('radippool',$s);
		}else{
			if(!isset($s['id']) || !is_numeric($s['id'])) return false;
			$res = $q->update_record('radippool',$s);
		}
	}
	return $res;
}

function onsave_radippool($id,$s,$my) {
	global $DEBUG, $config, $q, $opdata;
	if(!$q) $q = new sql_query($config['db']);
	$r = array_merge($my->row,$s);
	$out = array('result'=>'OK');
	if(preg_match('/(\d+\.\d+\.\d+\.\d+):(\d+\.\d+\.\d+\.\d+)/',$id,$m)){  // изменяется диапазон пула
		$out['modify'] = array(array('id'=>$id,'start'=>$r['start'],'stop'=>$r['stop'],'ips'=>$r['ips']));
	}elseif($my->id == 'new' && (isset($s['start']) || isset($s['stop']) || isset($s['ips']))){
		$out['append'] = array(array($s['id'],$s['pool_name'],$s['start'],$s['stop'],$r['ips']));
	}elseif($my->id == 'new'){
		$out['append'] = array($q->select('radippool',$s['id']));
	}else{
		$out['modify'] = array($q->select('radippool',$id));
	}
	return $out;
}


function before_delete_radippool($r,$my) {
	global $DEBUG, $config, $q, $opdata;
	if(!isset($q)) $q = new sql_query($config['db']);
	$out = true;
	if(preg_match('/(\d+\.\d+\.\d+\.\d+):(\d+\.\d+\.\d+\.\d+)/',$my->id,$m)){  // изменяется диапазон пула
		$start = ip2long($m[1]);
		$stop = ip2long($m[2]);
		$pn = $q->fetch_all("SELECT distinct pool_name FROM radippool WHERE INET_ATON(framedipaddress)>=$start AND INET_ATON(framedipaddress)<=$stop");
		if(count($pn)>1) stop(array('result'=>'ERROR','desc'=>"Диапазон ip не найден!"));
		$out = array('id'=>$my->id, 'pool_name'=>$pn[0], 'start'=>$m[1], 'stop'=>$m[2]);
	}
	return $out;
}

function delete_radippool($r,$my) {
	global $DEBUG, $config, $q, $opdata;
	if(!isset($q)) $q = new sql_query($config['db']);
	log_txt(__function__.": id={$my->id} del_record=".arrstr($r));
	if(preg_match('/(\d+\.\d+\.\d+\.\d+):(\d+\.\d+\.\d+\.\d+)/',$r['id'],$m)){  // изменяется диапазон пула
		if($n = del2ippool($r['pool_name'],ip2long($r['start']),ip2long($r['stop']))){
			log_txt(__function__.": удалено $n ip адресов");
			$n = 1;
		}
	}elseif(is_numeric($r['id'])){
		$n = array($q->del('radippool',$r['id']));
	}
	return $n;
}

function build_filter_for_radippool($t) {
	return filter2db('radippool');
}

?>
