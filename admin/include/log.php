<?php
include_once("defines.php");

function nibs_log($user,$userid,$action,$content) {
	global $opdata, $config, $q;
	if(!$q) $q = new sql_query($config['db']);
	if(!$user) $user='';
	elseif($userid && !$user) $user = $q->select("SELECT user FROM users WHERE uid='$userid'",4);
	return $q->insert('log', array('admin'=>$opdata['login'], 'user'=>$user, 'uid'=>$userid, 'action'=>$action, 'content'=>$content));
}

function log_db() {
	$arg = func_get_args();
	$c = count($arg);
	if($c<2 || $c>4) log_txt(__function__.": ERROR кол-во параметров $c!");
	if($c==2) { $user = ''; $userid=0; $action=$arg[0]; $content=$arg[1]; }
	if($c==3) { $user = ''; $userid=$arg[0]; $action=$arg[1]; $content=$arg[2]; }
	if($c==4) { $user = $arg[0]; $userid=$arg[1]; $action=$arg[2]; $content=$arg[3]; }
	if($userid === '') $userid = 0;
	return nibs_log($user,$userid,$action,$content);
}

function show_error($what) {
	if(is_array($what)) { if(isset($what['desc'])) $what = $what['desc']; else $what = arrstr($what); }
	echo "<CENTER><BR><BR><BR><BR><DIV class=\"error\">$what</DIV></CENTER>";
}

function log_txt($what) {
	global $opdata, $client, $guest, $DEBUG;
	if(isset($opdata['login'])) $login = $opdata['login'];
	elseif(isset($client['login'])) $login = $client['user'];
	elseif(isset($guest['username'])) $login = $guest['username'];
	else $login = "???";
	$now = DateTime::createFromFormat('U.u', microtime(true));
	$now->setTimezone(new DateTimeZone(date_default_timezone_get()));
	$date_format = ($DEBUG > 0)? "Y-m-d H:i:s.u" : "Y-m-d H:i:s";
	$d = $now->format($date_format);
	if(isset($_SERVER['SHELL'])){
		echo "$d $login $what\n";
		return 0;
	}
	if($pfile = @fopen(LOG_FILE,"a")){
		fputs($pfile,$d);
		fputs($pfile," $login ");
		fputs($pfile,"$what\n");
		fclose($pfile);
	}
	return 0;
}

function log_query($what) {
	log_txt(" SQL: ".sqltrim($what)."\n");
	return 0;
}

function sprint_r($val,$intend="    ") { // возвращает в строчную переменную все элементы масива
	if(is_array($val)||is_object($val)) {
		if(count($val)==0) $out="[] \n"; else $out=" ".gettype($val)."\n";
		foreach($val as $k => $v) {
			if(is_array($v)) { 
				$out.=$intend."[".$k."]: ".sprint_r($v,$intend."    "); 
			}elseif(is_object($v)){
				$out.=$intend."[".$k."]: ".sprint_r($v,$intend."    "); 
			}elseif(is_bool($v)){
				$out.=$intend."[".$k."]: ".($v?'true':'false')."\n";
			}elseif(is_null($v)){
				$out.=$intend."[".$k."]: NULL\n";
			}else{
				$out.=$intend."[".$k."]: ".$v."\n";
			}
		}
		return $out;
	}elseif(is_null($val)){
		return "NULL";
	}elseif(is_bool($val)){
		return ($val)? 'true':'false';
	}elseif(is_string($val) && $val == ''){
		return "'$val'";
	}else{
		return gettype($val)."($val)";
	}
}

function sqltrim($sql) {return trim(preg_replace(array('/\n/','/\s+/'),array(' ',' '),$sql));}

function arrstr($arr=array()) {
	if(is_array($arr)) {
		$s=array(); $i = 0;
		foreach($arr as $k=>$v){
			if(is_numeric($k) && $k==$i) $key = ""; else $key="$k:";
			if(is_null($v)) $v='NULL'; elseif(is_bool($v)) $v = $v? 'true':'false';
			$s[] = (!is_array($v))? $key."$v" : $key.arrstr($v);
			$i++;
		}
		return "[".implode(', ',$s)."]";
	}else return sprint_r($arr);
}

function data4log($v,$func='',$a=array()){
	if($func && function_exists($func)) $v = $func($v,$a,null);
	$v = preg_replace('/<\/?span[^>]*>/','',$v);
	return $v;
}

function dblog($table,$old,$cmp=null) { // Заносит выполненные операции в лог
	global $opdata, $client, $tables, $q, $config, $DEBUG;
	if($table == 'map' || $table == 'map_xy') return true;
	if(!$q) $q = new sql_query($config['db']);
	if($table == 'log' && is_array($old)){
		$old['admin'] = isset($opdata['login'])? $opdata['login']: @$client['user'];
		$old = array_intersect_key($old,array('admin'=>0,'user'=>0,'uid'=>0,'action'=>0,'content'=>0));
		if(!$q->insert('log',$old)) return false;
		return true;
	}
	$fld = $q->table_fields($table);
	if(!isset($tables[$table])){
		if((@include_once "$table.cfg.php") != true && ($file = include_find("$table.cfg.php"))) include_once $file;
		if(!isset($tables[$table])){
			log_txt(__function__.": ERROR таблица `$table` не найдена!");
			return false;
		}
	}
	if(!isset($tables[$table])) log_txt(__function__.": ERROR Не найден шаблон для таблицы `$table`!");
	$f = $tables[$table];
	$key = $q->table_key($table);
	if(!$key){ log_txt(__function__.": ERROR Не найден ключ ($key) для таблицы '$table'"); return false; }
	$tname = isset($config['log']['tables'][$table])? $config['log']['tables'][$table] : $table;
	$ins=false;
//	log_txt(__function__.": table:{$table}\n\told: ".arrstr($old)."\n\tcmp: ".arrstr($cmp));
	if(is_array($cmp) && is_array($old)) {
		if($fld && !function_exists(@$tables[$table]['form_save'])) $cmp = array_intersect_key($cmp,$fld);
		foreach($cmp as $k=>$v) {
 			if($k==$key || (isset($f['fields'][$k]['native']) && !$f['fields'][$k]['native']) || (isset($old[$k]) && $v == $old[$k])) continue;
			$label = (@$f['fields'][$k]['label']!='')? $f['fields'][$k]['label'] : $k;
			if(isset($f['table_triggers'][$k])) {
				$vconv = data4log($v,$f['table_triggers'][$k]);
				$v = preg_match('/[^0-9]/',$v)? $vconv : "$vconv ($v)"; // если код - то добавить его расшифровку
				if(isset($old[$k])){
					$oldconv = data4log($old[$k],$f['table_triggers'][$k]);
					$old[$k] = preg_match('/[^0-9]/',$old[$k])? $oldconv :"$oldconv ({$old[$k]})";
				}
			}
			$content[] = "$label:".mb_substr(((@$old[$k] != '')? $old[$k].' -> ' : '').$v,0,100);
		}
		if(isset($cmp['uid'])) $uid=$cmp['uid']; elseif(isset($old['uid'])) $uid=$old['uid']; else $uid='';
		if(isset($cmp['user'])) $user=$cmp['user']; elseif(isset($old['user'])) $user=$old['user']; else $user='';
		if($uid && !$user) $user=$q->select("SELECT user FROM users WHERE uid=$uid",4);
		$ins = array(
			'admin'=>(isset($opdata['login']))? $opdata['login']: @$client['user'],
			'user'=>$user, 'uid'=>intval($uid), 'action'=>"Изменил {$tname}[{$cmp[$key]}]",
			'content'=>implode(' ',$content),
		);
	}else{
		if(!is_array($old) && $old == 'new') { $d = $cmp; $act = 'добавил'; }
		elseif(!is_array($cmp) && $cmp == 'del') { $d = $old; $act = 'удалил'; }
		if(!$d) { log_txt(__function__.": ERROR неопределённые данные '$table' old: ".arrstr($old)." cmp: ".arrstr($cmp)); return false; }
		if($fld) $d = array_intersect_key($d,$fld);
		$issue = array();
		foreach($d as $k=>$v) {
			if($k!=$key && $v!='') {
				$label = (@$f['fields'][$k]['label']!='')? $f['fields'][$k]['label'] : $k;
				if(isset($f['table_triggers'][$k])){
					$vconv = data4log($v,$f['table_triggers'][$k]);
					$v = preg_match('/[^0-9]/',$v)? $vconv : "$vconv ($v)";
				}
				$issue[] = "$label: $v";
			}
		}
		$ins = array(
			'admin'=>$opdata['login'],
			'user'=>strval($d['user']),
			'uid'=>intval($d['uid']),
			'action'=>$act." {$tname}[{$d[$key]}]",
			'content'=>implode(', ',$issue)
		);
	}
	if($ins && !is_null($ins['content'])){
		$q->insert('log',$ins);
		if($DEBUG>0) log_txt(__function__.": insert ".arrstr($ins));
	}else{
		if($DEBUG>0) log_txt(__function__.": запись не выполнена! cmp: ".arrstr($cmp));
	}
}

function parse_backtrace($d){
	$log = "\n";
	foreach($d as $k=>$v) {
		$log .= "\t".sprintf("%4d)  ",$k).$v['file'].":".$v['line']." function: ".$v['function']."\n";
	}
	return $log;
}

function tableout($t,$maxFieldLength=40){
	if(!isset($_SERVER['SHELL'])) return false;
	if(!is_array($t) || !isset($t[0])){
		var_export($t);
		echo "\n";
		return false;
	}
	$out = ''; $line = ''; $header = '';
	$hdr = array_keys($t[0]);
	foreach($hdr as $i=>$h){
		$width[$h] = mb_strlen($h);
		if($width[$h]>$maxFieldLength) $width[$h] = $maxFieldLength;
	}
	$r = array();
	while($row = array_shift($t)){
		$next = array();
		foreach($row as $k=>$v){
			if(is_string($v)) $row[$k] = $v = preg_replace('/^\s+|\s+$|\r/','',$v);
			$ml = (is_string($v))? preg_split('/\n/',$v) : array($v);
			$i = 0;
			while(count($ml)>0){
				$val = array_shift($ml);
				$l = is_null($val)? 4 : mb_strlen("$val");
				if($width[$k] < $l){
					if($l > $maxFieldLength){
						$x=0; $p=0;
						while(($x = mb_strpos($val," ",$x+1)) !== false && $x < $maxFieldLength) $p = $x;
						if($p == 0) $p = $maxFieldLength - 1;
						$ml[0] = mb_substr($val,$p+1).(isset($ml[0])? " ".$ml[0] : "");
						$val = mb_substr($val,0,$p);
						if($width[$k] < $p) $width[$k] = $p;
					}else{
						$width[$k] = $l;
					}
				}
				if($i==0){
					$row[$k] = $val;
				}else{
					if(count($next)!=$i) foreach(array_keys($row) as $n) $next[$i-1][$n] = "";
					$next[$i-1][$k] = $val;
				}
				$i++;
			}
		}
		$r[] = $row;
		if(count($next)>0) while($n = array_shift($next)) $r[] = $n;
	}
	foreach($hdr as $i=>$n){
		$line .= "+".str_repeat('-',$width[$n]+2);
		$header .= "| ".$hdr[$i].str_repeat(" ",$width[$n]-mb_strlen($hdr[$i]))." ";
	}
	$line .= "+";
	$header .= "|";
	$out .= $line."\n".$header."\n".$line."\n";
	foreach($r as $i=>$row){
		$r = '';
		foreach($row as $k=>$v){
			$l = "$v";
			if(!is_string($v)){
				if(is_bool($v)) $l = $v? 'true' : 'false';
				elseif(is_null($v)) $l = 'NULL';
				$r .= "| ".str_repeat(" ",$width[$k]-mb_strlen($l)).$l." ";
			}else{
				$r .= "| ".$l.str_repeat(" ",$width[$k]-mb_strlen($l))." ";
			}
		}
		$out .= $r."|\n";
	}
	$out .= $line."\n";
	$out .= count($t)." rows\n";
	echo $out;
}
?>
