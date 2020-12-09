<?php
include_once("defines.php");
include_once("classes.php");
include_once("log.php");

$curlopt = array(
	CURLOPT_HTTPHEADER=>'CURLOPT_HTTPHEADER',
	CURLOPT_HTTPAUTH=>'CURLOPT_HTTPAUTH',
	CURLAUTH_BASIC=>'CURLAUTH_BASIC',
	CURLOPT_USERPWD=>'CURLOPT_USERPWD',
	CURLOPT_CONNECTTIMEOUT=>'CURLOPT_CONNECTTIMEOUT',
	CURLOPT_PUT=>'CURLOPT_PUT',
	CURLOPT_POSTFIELDS=>'CURLOPT_POSTFIELDS',
	CURLOPT_RETURNTRANSFER=>'CURLOPT_RETURNTRANSFER',
	CURLOPT_SSL_VERIFYPEER=>'CURLOPT_SSL_VERIFYPEER',
	CURLOPT_USERAGENT=>'CURLOPT_USERAGENT',
	CURLOPT_POST=>'CURLOPT_POST',
	CURLOPT_HEADERFUNCTION=>'CURLOPT_HEADERFUNCTION',
	CURLOPT_URL=>'CURLOPT_URL',
	CURLINFO_HEADER_OUT=>'CURLINFO_HEADER_OUT',
	CURLOPT_CUSTOMREQUEST=>'CURLOPT_CUSTOMREQUEST',
);

function sectime($stri,$tota) {
	$hour=floor($tota / 3600); $rel=preg_replace("/h/","$hour",$stri);
	$min=floor($tota % 3600 / 60); if ($min<10) $min="0$min"; $rel=preg_replace("/m/","$min",$rel);
	$sec=floor($tota % 3600 % 60); if ($sec<10) $sec="0$sec"; $rel=preg_replace("/s/","$sec",$rel);
	return($rel);
}

function uptime($str,$utime) {
	$d=strpos($str,'d');
	$days=$utime / 86400; settype($days,'integer'); $r = ($days>0)?"$days дн":"";  $rel=preg_replace("/d/",$r,$str);
	$hour=($d!==false)? ($utime % 86400 / 3600):($utime / 3600); settype($hour,'integer'); $rel=preg_replace("/h/","$hour",$rel);
	$min=$utime % 3600 / 60; settype($min,'integer'); if ($min<10) $min="0$min"; $rel=preg_replace("/m/","$min",$rel);
	$sec=$utime % 3600 % 60; settype($sec,'integer'); if ($sec<10) $sec="0$sec"; $rel=preg_replace("/s/","$sec",$rel);
	return($rel);
}

function time2int($str) {
	$t = preg_split('/:/',$str);
	foreach($t as $i=>$v) $t[$i] = numeric($v);
	if(count($t) == 1) $r = $t[0];
	elseif(count($t) == 2) $r = $t[0] * 60 + $t[1];
	else $r = $t[0] * 3600 + $t[1] * 60 + $t[2];
	return $r;
}

function normalize_fio($a) {
	global $errors, $opdata;
	$s = trim($a);
	if($opdata['level']>4) return $s;
	if($m = preg_split('/\s+/u',trim($s))){
		if(preg_match('/[0-9A-Za-z]/',$s)) { $errors[] = "В имени присутствуют нерусские символы!"; return false; }
		if(!$m[0] || !$m[1] || !$m[2]) { $errors[] = "Имя должно быть трёхсоставное!"; return false; }
		if(mb_strlen($m[1])<3 || mb_strlen($m[2])<3) { $errors[] = "Длина слов слишком короткая"; return false; }
		if(!isset($m[3]) && !preg_match('/(ич|на)$/u',$m[2])) { $errors[] = "Отчество неправильное!"; return false; }
		if(isset($m[3]) && !preg_match('/(ызы|лы)$/u',$m[3])) { $errors[] = "Для мусульман неправильная приставка!"; return false; }
		if(count($m)>4) { $errors[] = "Превышено кол-во слов!"; return false; }
		return "{$m[0]} {$m[1]} {$m[2]}".(isset($m[3])?" {$m[3]}":'');
	}else return false;
}

function normalize_ip($a) {
	if(preg_match("/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/",trim($a),$m)){
		return $m[0];
	}else return false;
}

function normalize_mac($a) {
	$a = strtoupper(preg_replace('/[^A-Fa-f0-9]/','',$a));
	$a = preg_replace('/(..)(..)(..)(..)(..)(..)/','$1:$2:$3:$4:$5:$6',$a);
	if(strlen($a)==17){
		return $a;
	}else return '';
}

function normalize_email($a) {
	if(preg_match("/^([a-z0-9._\-][a-z0-9._\-]*)@([a-z0-9._\-][a-z0-9._\-]*)/i",trim($a),$m)){
		return $m[0];
	}else return false;
}

function parse_address($a,$force=false) {
	if(preg_match('/[a-z]/u',$a)) { $errors[] = 'Присутствуют английские буквы!'; return false; }
	$a = preg_replace(
		array('/\bулица[:. ]?/u','/\bпереулок[:. ]?/u','/\bплощадь[:. ]?/u','/\bпроспект[:. ]?/u','/\bбульвар[:. ]?/u',
			'/\bпос[ёе]лок[:. ]?/u','/\bпгт[:. ]?/u','/\bмикро[-]?район[:. ]?/u','/\bрайон[:. ]?/u','/\bдом\b[:. ]?/u',
			'/\bкв(артира)?[:. ]?/u','/\.\s+/'),
		array('ул.','пер.','пл.','пр.','б.','п.','п.','м-н.','р.','д.','кв.','.'),$a
	);
	if(!preg_match("/^((ул\.|пер\.|пл\.|пр\.|б\.)|(п\.|пос\.|р\.|рн\.|мр\.|м-н\.))([0-9А-ЯЁа-яёXVI]*[А-ЯЁа-яё\- ]*) (д.)?(\d+)(\w)?( корпус (\d+))?((\/| кв\.)(\d+))?/u",$a,$m)) return false;
	return array(
		'rayon'=>(($m[3])?$m[3].$m[4]:''),
		'street'=>(($m[2])?$m[2].$m[4]:''),
		'home'=>$m[6],
		'litera'=>@$m[7],
		'housing'=>((@$m[8])?$m[9]:''),
		'apartment'=>@$m[12],
		'addr'=>$m[1].$m[4].' '.$m[6].@$m[7].((@$m[8])?' корпус '.@$m[9]:''),
		'full'=>$m[1].$m[4].' '.$m[6].@$m[7].((@$m[8])?' корпус '.@$m[9]:'').((@$m[12])?'/'.@$m[12]:'')
	);
}

function normalize_address($a) {
	if(CHECK_ADDRESS){
		if($r = parse_address($a)) return $r['full'];
		return $r;
	}
	return $a;
}

function normalize_phone($p) {
	$myph=''; $fr='';
	$myphone = preg_replace(array('/[\.,;:]/','/\s+/','/[^0-9 ]/','/^\s+/','/\s+$/'),array(" "," ","","",""),$p);
	foreach(preg_split("/ /",$myphone) as $i => $n) {
		$n=preg_replace('/^\+?38/','',$n);
		if (mb_strlen($n)<5 && !$fr) {
			$fr = preg_replace(array('/ /','/^\+?38/'),array('',''),$myphone);
			if(($len = mb_strlen($fr))>=10 && $len<=12) $n=$fr;
			elseif(mb_strlen($fr)>12) $n = mb_substr($fr,0,10);
			else return '';
		}
		if(mb_strlen($n)==5) {
			$tmp = preg_replace('/^(\d)(\d\d)(\d\d)/','\1-\2-\3',$n);
			$myph=$tmp;
		}
		if(mb_strlen($n)==6) {
			$tmp = preg_replace('/^(\d\d)(\d\d)(\d\d)/','\1-\2-\3',$n);
			$myph=$tmp;
		}
		if(mb_strlen($n)>=7 && mb_strlen($n)<10) {
			$myph = preg_replace('/^(\d+)(\d\d)(\d\d)$/','\1-\2-\3',$n);
		}
		if(mb_strlen($n)>=10) {
			$myph = preg_replace('/^(\d+)(\d\d\d)(\d\d)(\d\d)$/','\1-\2-\3-\4',$n);
		}
	}
	return ($myph)? $myph : '';
}

function normalize_client($client){
	global $config, $uid, $packet, $valute, $user, $home, $acct;
	if(!is_array($client)) return false;
	if(!@is_object($q)) $q = new sql_query($config['db']);
	if(!isset($uid)) $uid = $client['uid'];
	$client['rayon'] = $q->select("SELECT r_name FROM `rayon` WHERE rid='{$client['rid']}'",4);
	$packet = $q->select("SELECT * FROM `packets` WHERE pid='{$client['pid']}'",1);
	$acct = $q->select("SELECT * FROM `radacct` WHERE username='{$client['user']}' AND nasipaddress!='' ORDER BY acctstarttime DESC LIMIT 1",1);
	$valute = get_valute();
	$time_left = strtotime($client['expired']) - strtotime('now');
	$user = $client['user'];
	if($mo = $q->select("SELECT id, subtype FROM map WHERE type='client' AND name='$user'",1)){
		$client['type'] = $mo['subtype'];
	}
	$a = parse_address($client['address']);
	if($a && ($h = $q->select("SELECT * FROM map WHERE type='home' AND address = '{$a['addr']}' AND rayon='{$client['rid']}'",1)) &&
		($home = $q->select("SELECT * FROM homes WHERE object='{$h['id']}'",1))){
		$e = $q->select("SELECT * FROM entrances WHERE home={$home['id']} AND apartinit<='{$a['apartment']}' AND apartfinal>='{$a['apartment']}'",1);
		if($e) {
			$client['entrance'] = $e['entrance'];
			$client['floor'] = ceil(($a['apartment']-$e['apartinit']+1) / floor(($e['apartfinal']-$e['apartinit']+1)/($home['floors'])));
		}
	}
	$client['packetname'] = $packet['name'];
	$client['fixed'] = $packet['fixed'];
	$client['fixed_cost'] = $packet['fixed_cost'];
	$client['live'] = ($acct || strtotime($client['last_connection'])>strtotime('-3 month'))? 1 : 0;
	foreach(array('groupname','nasipaddress','nasportid','acctstarttime','acctstoptime','acctsessiontime','framedipaddress','callingstationid') as $n)
		$client[$n] = $acct[$n];
	return $client;
}

# Функция для отображения причины ненормального подключения пользователя в статистике
function get_explaine_packet($p) {
	global $explain_packet;
	if(key_exists($p,$explain_packet)) return $explain_packet[$p]; else return($p);
}
# Функция для создания логина пользователя на основе фамилии

function strRuEng($s){
	$charconv=array(
	'а'=>'a',  'б'=>'b', 'в'=>'v',  'г'=>'g',  'д'=>'d',  'е'=>'e',   'ё'=>'e',
	'ж'=>'zh', 'з'=>'z', 'и'=>'i',  'й'=>'y',  'к'=>'k',  'л'=>'l',   'м'=>'m',
	'н'=>'n',  'о'=>'o', 'п'=>'p',  'р'=>'r',  'с'=>'s',  'т'=>'t',   'у'=>'u',
	'ф'=>'f',  'х'=>'h', 'ц'=>'ts', 'ч'=>'ch', 'ш'=>'sh', 'щ'=>'sch', 'ъ'=>'',
	'ы'=>'y',  'ь'=>'',  'э'=>'e',  'ю'=>'yu', 'я'=>'ya',
	'А'=>'A',  'Б'=>'B', 'В'=>'V',  'Г'=>'G',  'Д'=>'D',  'Е'=>'E',   'Ё'=>'E',
	'Ж'=>'Zh', 'З'=>'Z', 'И'=>'I',  'Й'=>'Y',  'К'=>'K',  'Л'=>'L',   'М'=>'M',
	'Н'=>'N',  'О'=>'O', 'П'=>'P',  'Р'=>'R',  'С'=>'S',  'Т'=>'T',   'У'=>'U',
	'Ф'=>'F',  'Х'=>'H', 'Ц'=>'Ts', 'Ч'=>'Ch', 'Ш'=>'Sh', 'Щ'=>'Sch', 'Ъ'=>'',
	'Ы'=>'Y',  'Ь'=>'',  'Э'=>'E',  'Ю'=>'Yu', 'Я'=>'Ya'
	);
	$out = '';
	$farr=preg_split('//u',$s);
	for($i=0;$i<count($farr);$i++) {
		$out .= (isset($charconv[$farr[$i]]))? $charconv[$farr[$i]] : $farr[$i];
	}
	return $out;
}

function fiotologin($s) {
	global $fam, $config, $q, $DEBUG;
	if(!$q) $q = new sql_query($config['db']);
	$fam=preg_split("/\s+/",trim($s));
	$login = mb_strtolower(strRuEng($fam[0]));
	$usr = $q->get('users', $login, 'user');
	if(!$usr) {
		return $login;
	}else{
		$u4claims = $q->fetch_all("SELECT cast(replace(user,'$login','') as unsigned) as n 
			FROM `claims` as c, `claimperform` as cp, `workorders` as wo 
			WHERE c.unique_id=cp.cid and cp.woid=wo.woid and c.type=1 and c.uid=0 and 
				cp.status!=2 and (c.status=2 or (c.status=4 and wo.status<2)) and 
				c.user rlike '^{$login}[1-9]' ORDER BY n");
		if(!$u4claims) $u4claims = array();
		$u4users = $q->fetch_all("SELECT cast(replace(user,'$login','') as unsigned) as n FROM users WHERE user rlike '^{$login}[1-9]' ORDER BY n");
		if(!$u4users) $u4users = array();
		$all = array_unique(array_merge($u4users,$u4claims));
		sort($all,SORT_NUMERIC);
		$u=0;
		if($DEBUG>0) log_txt(__FUNCTION__.": all: ".arrstr($all));
		foreach($all as $n) {
			if($n == 0) continue;
			if($n-1 != $u) break;
			$u=$n;
		}
		$u++;
		return $login.$u;
	}
}

function now() { return date('Y-m-d H:i:s'); }
function date2db($d=false, $tm=true) { // 	подготавливает дату для сохранения в базе данных
	global $DEBUG;
	if(!$d) {
		$res = date('Y-m-d'.($tm?' H:i:s':''));
	}elseif(is_numeric($d)){
		$res = date('Y-m-d'.($tm?' H:i:s':''),$d);
	}elseif(is_string($d)){
		if($tm && preg_match('/^\d\d\d?\d?-\d\d-\d\d$/',$d)) $tm = false;
		if(($t = strtotime($d)) === false){
			log_txt(__METHOD__." ERROR: convert data '$d'");
			$res = '0000-00-00'.(($tm)?' 00:00:00':'');
		}else{
			$res = date('Y-m-d'.($tm?' H:i:s':''),$t);
		}
	}
	if($DEBUG>2) log_txt(__METHOD__.": date='$d' tm=".(($tm)?'true':'false')." res='$res'");
	return $res;
}

function cyrdate($d=false, $pattern=false) { // 	подготавливает дату в русском формате
	global $DEBUG;
	$a = array(array('/[йь]\b/u','/т\b([^.])/u'), array('я','та$1'));
	if($pattern === false) $pattern = '%d-%m-%Y';
	if(!$d) {
		if($DEBUG>5) log_txt(__FUNCTION__.": !date = '$d'");
		$res = strftime($pattern,time());
	}elseif(is_numeric($d)){
		if($DEBUG>5) log_txt(__FUNCTION__.": dnumeric ate = '$d'");
		$res = strftime($pattern,$d);
	}elseif(is_string($d)){
		if($DEBUG>5) log_txt(__FUNCTION__.": string date = '$d'");
		if($d == '' || $d == '0000-00-00' || $d == '0000-00-00 00:00:00') return '';
		if(($t = strtotime($d)) === false){
			log_txt(__METHOD__." ERROR: convert data '$d'");
			$res = '00-00-0000';
		}else{
			$res = strftime($pattern,$t);
		}
	}
	if(preg_match('/%B/',$pattern)) $res = preg_replace($a[0],$a[1],$res);
	return $res;
}

function shortfio($n,$r=null,$fn=null) {
	$fullname=trim($n);
	$fio=preg_split('/\s+/',$fullname);
	$name=$fio[0];
	if(@$fio[1]) $name.=" ".mb_substr($fio[1],0,1).".";
	if(@$fio[2]) $name.=mb_substr($fio[2],0,1).".";
	return $name;
}

function stop($out) {
	global $opdata, $DEBUG;
	if(@$_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest') {
		if(is_string($out)) $out=array('result'=>"ERROR",'desc'=>$out);
		if(file_exists(USOCKET_FILE)){
			if(@$out['delete']['objects']) $ws['delete'] = $out['delete'];
			if(@$out['feature']) $ws['feature'] = $out['feature'];
			if(@$out['modify']['GeoJSON']) $ws['modify']['GeoJSON'] = $out['modify']['GeoJSON'];
			if(@$out['append']['GeoJSON']) $ws['append']['GeoJSON'] = $out['append']['GeoJSON'];
			if(isset($ws)){ $ws['wstype'] = 'update'; use_ws_server($ws); }
		}
		$out = json_encode($out);
		if($cb = @$_REQUEST['callback']) echo $cb."($out)";
		else echo $out;
	}else{
		if(is_array($out)) {
			if(@$out['desc']) { 
				echo show_error($out['desc']);
			}else{
				if($opdata['level']>4) echo "\nStop without XMLHttpRequest: <br><pre>\n".sprint_r($out)."\n</pre>";
				elseif(isset($_SERVER['SHELL'])) echo sprint_r($out);
				else echo show_error("Несоответствие вывода типу запроса! '{$opdata['status']}'");
			}
		}else{
			echo $out;
		}
	}
	die();
}

// конвертирует все элементы масива в UTF-8
function conv_utf($a) {
	return charconv("KOI8-U","UTF-8",$a);
}
function conv_koi($a) {
	return charconv("UTF-8","KOI8-U//IGNORE",$a);
}
function charconv($from,$to,$a) {
	global $DEBUG;
	$my=array();
	if(is_array($a)) {
		foreach($a as $k => $v) {
			$k=iconv($from,$to,$k);
			if($from=='UTF-8' && is_string($v) && preg_match('/^\{.*\}$/',$v)) $v = json_decode($v,true);
			if(is_array($v)) { 
				$my[$k]=charconv($from,$to,$v); 
			}elseif(is_string($v)){
				$my[$k]=iconv($from,$to,$v);
				if($my[$k] === false) log_txt(__FUNCTION__.": ERROR! iconv(\"$from\",\"$to\",\"$v\") strlen: ".mb_strlen($v));
			}else{
				$my[$k]=$v;
			}
		}
		return $my;
	}elseif(is_string($a)){
		if(($res=iconv($from,$to,$a)) === false) log_txt("charconv: $from->$to str = '$a'");
		return $res;
	}elseif(is_object($a)){ 
		return "Object"; 
	}
}

function curlResponseHeaderCallback($ch, $headerLine) {
    global $cookies, $headers;
	if($line = preg_replace('/[\r\n]/','',$headerLine)) $headers[] = $line;
    if(preg_match('/^Set-Cookie:\s*([^;]*)/mi', $headerLine, $cookie)){
        $cookies[] = $cookie;
	}
    return strlen($headerLine); // Needed by curl
}

function getClientGroup($c,$p) {
	if(!$c || !$p) return false;
	if($c['blocked']) return "blocked";
	if($c['disabled']) return "blocked";
	if(($p['fixed'] == 10 && ($c['deposit'] + $c['credit'] <= -0.01 || strtotime($c['expired']) <= time())) ||
		($p['tos'] > 0 && $c['deposit'] + $c['credit'] < 0.01)) return "debtors";
	return $p['groupname'];
}

function send_coa($user,$conn='',$packet='') {
	global $config, $errors;
	$q = new sql_query($config['db']);
	if(is_numeric($user) && $user>CITYCODE * 10000 && $user<(CITYCODE+1) * 10000) $client = $q->get('users',$user,'contract');
	elseif(is_numeric($user)) $client = $q->get('users',$user,'uid');
	elseif(is_string($user)) $client = $q->get('users',$user,'user');
	elseif(is_array($user)) $client = $user;
	if(!$client) return false;

	if(!$packet) $packet = $q->get('packets',$client['pid']);
	if(!$conn){
		$acct = $q->select("SELECT * FROM radacct WHERE acctstoptime is NULL AND callingstationid='{$client['csid']}'",1);
	}elseif($conn && is_array($conn)){
		$acct = $conn;
	}elseif($conn && is_string($conn) && preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/',$conn)){
		$acct = $q->select("SELECT * FROM radacct WHERE acctstoptime is NULL AND framedipaddress='$conn'",1);
	}elseif($conn && is_string($conn) && preg_match('/^[0-9A-F]{2}[:-][0-9A-F]{2}[:-][0-9A-F]{2}[:-][0-9A-F]{2}[:-][0-9A-F]{2}[:-][0-9A-F]{2}$/i',$conn)){
		$conn = preg_replace('/-/',':',$conn);
		$acct = $q->select("SELECT * FROM radacct WHERE acctstoptime is NULL AND callingstationid='$conn'",1);
	}

	if(!$acct || $acct['framedprotocol'] != 'IPoE' || $acct['groupname']=='wrongpass') return false;

	$nas = $q->select("SELECT * FROM nas WHERE nasipaddress='{$acct['nasipaddress']}'",1);
	if(!$nas) {
		log_txt(__function__.": not found <{$acct['nasipaddress']}> nas!");
		return false;
	}
	$g = getClientGroup($client,$packet);
	if($g == $acct['groupname']){
		log_txt(__FUNCTION__.": обновление не требуется. профиль: $g");
		return false;
	}
	$oldgrp = $acct['groupname'];
	$relay = array("Framed-IP-Address={$acct['framedipaddress']}","User-Name={$acct['callingstationid']}");
	if($g != $packet['groupname']) {
		array_push($relay,"Class=\"$g\"");
		array_push($relay,'L4-Redirect=1');
		array_push($relay,"L4-Redirect-ipset=\"$g\"");
	}else{
		array_push($relay,'L4-Redirect=0');
	}
	$relay = array_merge($relay, $q->fetch_all("SELECT concat(attribute,'=',value) FROM radgroupreply WHERE groupname='$g' AND attribute like 'pppd%limit'"));
	$coa = implode(',',$relay);
	@exec("echo '$coa' | radclient -t3 -r1 -x {$nas['nasipaddress']}:3799 coa {$nas['secret']}",$output,$res);
	if($res!==0) {
		$out = (count($output)>0)? '<p>'.implode("\n",$output).'</p>' : '';
		$errors[] = "Ошибка изменения (coa) пользователя! ($res)".$out;
		log_txt("Ошибка изменения (coa) пользователя! ($res) \n\tcoa: $coa  : ".$out);
		return false;
	}else{
		$q->query("UPDATE radacct SET acctsessionid=substr(rand(),3), acctstoptime=now() WHERE radacctid='{$acct['radacctid']}'") or log_txt("не удалось закрыть сессию {$acct['radacctid']}");
		unset($acct['radacctid']);
		$acct = array_merge($acct,array('acctstarttime'=>date2db(), 'username'=>$client['user'], 'groupname'=>$g, 'acctsessiontime'=>0, 'acctinputoctets'=>0, 'acctoutputoctets'=>0, 'inputgigawords'=>0, 'outputgigawords'=>0, 'billing_minus'=>0, 'before_billing'=>$client['deposit']));
		$q->insert('radacct',$acct) or log_txt("не удалось добавить новую сессию acct = ".arrstr($acct));
		log_txt("выполнено обновление coa {$nas['nasipaddress']} {$oldgrp} -> $g");
	}
	return $g;
}

function use_ws_server($m){
	global $opdata, $DEBUG;
	if($DEBUG>0) log_txt("sed message:".arrstr($m));
	if($opdata){
		$sender = array_intersect_key($opdata,array('login'=>0,'fio'=>1,'photo'=>2,'level'=>3));
		$sender['fio'] = shortfio($sender['fio']);
		$sender['photo'] = photo_link($sender['photo']);
	}
	if(!is_array($m)){
		$m = array("sender"=>$sender,"message"=>$m);
	}elseif(is_array($m) && !key_exists('sender',$m)){
		if(isset($sender)) $m['sender'] = $sender;
	}
	if(is_array($m) && !isset($m['wstype'])) $m['wstype'] = 'message';
	$m = json_encode($m);
	$len = strlen($m);
	if(20*1024*1024 < $len){ log_txt(__FUNCTION__.": Ошибка! Слишком большое сообщение"); return false; }
	$sl = chr($len>>24&0xFF).chr($len>>16&0xFF).chr($len>>8&0xFF).chr($len&0xFF);
	$sock = stream_socket_client("unix://".USOCKET_FILE, $errno, $errstr);
	if(!$sock){
		log_txt(__FUNCTION__.": Ошибка сокета ($errno) ".$errstr);
		return false;
	}
	fwrite($sock, $sl.$m);
	$len = fread($sock, 4);
	$rl = ord(substr($len,0,1))<<24|ord(substr($len,1,1))<<16|ord(substr($len,2,1))<<8|ord(substr($len,3,1));
	$res = ""; $buf = 0;
	while(($rl-=$buf)>0){
		$buf=($rl<=4096)? $rl: 4096;
		$res .= fread($sock,$buf);
	}
	fclose($sock);
	if($res != "OK") log_txt(__FUNCTION__.": Socket Server return: $res");
	return $res;
}

function request_http($url,$hopt='',$copt=''){
	global $DEBUG, $curl_cookie, $errors, $curlopt, $headers, $last_request_http;
	if($DEBUG>0) log_txt(__FUNCTION__.": start $url");
	$r=false; $curl_cookie = false; $headers = "";
	$headers=array('Content-type: text/json; charset=utf-8');
	if(is_array($hopt) && count($hopt)>0) $headers=array_merge($headers,$hopt);
	$opt=array(
		CURLOPT_HTTPHEADER=>$headers,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_USERAGENT => "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)",
		CURLOPT_CONNECTTIMEOUT => 3,
		CURLOPT_POST => false,
		CURLOPT_HEADERFUNCTION => "curlResponseHeaderCallback",
		CURLOPT_URL => $url,
		CURLINFO_HEADER_OUT => 1
	);
	if(is_array($copt)) foreach($copt as $k=>$v) $opt[$k] = $copt[$k];
	if(($ch = curl_init()) && $url!='') {
		if(curl_setopt_array($ch, $opt)) {
			$tmp = $last_request_http = curl_exec($ch);
			if($tmp === false) {
				log_txt(__FUNCTION__.": {$url} ERROR = ".curl_error($ch));
			}else{
				if($DEBUG>1){
					$s = ""; foreach($opt as $k=>$v) $s.="\n\t".($curlopt[$k]?$curlopt[$k]:$k)." => ".(is_array($v)? arrstr($v):$v);
					log_txt(__FUNCTION__."\tCURL OPT: ".$s);
					log_txt(__FUNCTION__."\tCURL INFO: ".curl_getinfo($ch, CURLINFO_HEADER_OUT));
				}
				if(!preg_match('/^\{.*\}$/sm',$tmp)){
					log_txt(__FUNCTION__.": IS NOT JSON ! ".arrstr($tmp));
					$r=$tmp;
				}else{
					if(!($res=json_decode($tmp,true))) log_txt(__FUNCTION__.": UNABLE PARSE JSON! data: ".arrstr($tmp));
					$r=@$res;
				}
			}
			curl_close($ch);
		}else{
			log_txt(__FUNCTION__.": ERROR не установлены опции CURL !");
		}
	}else{
		log_txt(__FUNCTION__.": не удалось задействовать curl !");
	}
	if($DEBUG>0) log_txt(__FUNCTION__.": CURL HEADERS: \n\t".implode("\n\t",$headers));
	return $r;
}

function get_nagios($req,$name){
	global $NAGIOS_ERROR, $DEBUG;
	if($DEBUG>0) log_txt(__FUNCTION__.": req = ".urldecode($req));
	$opt = array(
		CURLOPT_HTTPHEADER => array(
			'X-Requested-With: XMLHttpRequest',
			'Content-Length: ' . strlen($req)),
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => $req
	);
	$res = request_http(NAGIOS_URL, false, $opt);
	if(isset($res['result']) && $res['result']=='OK' && isset($res[$name])) {
		$r=$res[$name];
	}else{
		$r=false;
		$NAGIOS_ERROR = $res;
		if(!isset($res[$name])) log_txt(__FUNCTION__.": в ответе отсутствует res[$name]!");
		log_txt(__FUNCTION__.": запрос возвратил ошибку! ".sprint_r($NAGIOS_ERROR));
	}
	return $r;
}

/*
 * Расстояние между двумя точками
 * $lng1, $lat1 - широта, долгота 1-й точки,
 * $lng2, $lat2 - широта, долгота 2-й точки
 * Написано по мотивам http://gis-lab.info/qa/great-circles.html
 * Михаил Кобзарев <kobzarev@inforos.ru>
 *
 */
function Distance ($a, $b) {
	if(!is_array($a) || !is_array($b)) return 0;
	// перевести координаты в радианы
	if(isset($a['x']) && isset($a['y'])){
		$lng1 = $a['x'] * M_PI / 180;
		$lat1 = $a['y'] * M_PI / 180;
	}
	if(isset($b['x']) && isset($b['y'])){
		$lng2 = $b['x'] * M_PI / 180;
		$lat2 = $b['y'] * M_PI / 180; 
	}
	if(isset($a[0]) && isset($a[1])){
		$lng1 = $a[0] * M_PI / 180;
		$lat1 = $a[1] * M_PI / 180;
	}
	if(isset($b[0]) && isset($b[1])){
		$lng2 = $b[0] * M_PI / 180;
		$lat2 = $b[1] * M_PI / 180; 
	}
	if(!isset($lat1) || !isset($lng1) || !isset($lat2) || !isset($lng2)) return 0;
	$EARTH_RADIUS=6372795;

    // косинусы и синусы широт и разницы долгот
    $cl1 = cos($lat1);
    $cl2 = cos($lat2);
    $sl1 = sin($lat1);
    $sl2 = sin($lat2);
    $delta = $lng2 - $lng1;
    $cdelta = cos($delta);
    $sdelta = sin($delta);

    // вычисления длины большого круга
    $y = sqrt(pow($cl2 * $sdelta, 2) + pow($cl1 * $sl2 - $sl1 * $cl2 * $cdelta, 2));
    $x = $sl1 * $sl2 + $cl1 * $cl2 * $cdelta;

    $ad = atan2($y, $x);
    $dist = $ad * $EARTH_RADIUS;
    return $dist;
}

function auto_address(){
	global $config, $_REQUEST;
	$q=new sql_query($config['db']);
	$req = (key_exists('req',$_REQUEST))? str($_REQUEST['req']) : '';
	$out['result'] = 'OK';
	$out['complete'] = $q->select("
		SELECT DISTINCT 
			trim(substr(address,1,if(locate('/',address)>0,locate('/',address)-1,CHAR_LENGTH(address)))) as label
		FROM users 
		WHERE address like '%$req%'
		HAVING label!='' 
		ORDER BY address
	");
	return $out;
}

// проверяем строку, если не UTF-8 -> true
function isUTF($s){
	if(!is_string($s)) return 0;
	if(count(preg_split('//u',$s,-1,PREG_SPLIT_NO_EMPTY)) == 1 && strlen($s)>2) return false;
	else return true;
}

function create_cfg_nagios($pfile, $a, $dir='/tmp/'){
	global $err;
	if(!file_exists($pfile)){ $err[] = "файл шаблона не найден!"; return false; }
	$p = make_cfg(file_get_contents($pfile), $a);
	if(!$p) return false;
	$file = $dir.$a['host'].'.cfg';
	if($pf = @fopen($file,"w")){
		fputs($pf,"$p");
		fclose($pf);
	}else{ $err[] = "не могу записать файл! $file";  return false; }
}

function make_cfg($pat, $a, $dir='/tmp/'){
	global $err;
	if(!preg_match_all('/:([A-Z][A-Z]*):/s',$pat,$m)) {
		$err[] = "неправильный шаблон";
		return false;
	}
	foreach($m[1] as $k=>$v) {
		$f=mb_strtolower($v);
		if(!isset($a[$f]) || !$a[$f]){
			$err[] = "Отсутствует параметр '$f'";
			return false;
		}
		$pat = preg_replace("/:$v:/s",$a[$f],$pat);
	}
	return $pat;
}

function send_mail($m) {
	global $MAIL_ERROR, $DEBUG;
	if(!is_array($m) || !isset($m['to']) || !isset($m['subject']) || !isset($m['body']) || 
		$m['to']=='' || $m['subject']=='' || $m['body']==''){
		$MAIL_ERROR = array('result'=>'ERROR','desc'=>"Неверные входные данные\n<br>".arrstr($m));
		return false;
	}
	$to = $m['to'];
 	if($DEBUG>0) log_txt(__FUNCTION__.": to: {$m['to']}");
	$from = "\"=?UTF-8?B?".base64_encode("Биллинг ".COMPANY_NAME." ".COMPANY_UNIT)."?=\"";
	$m['header'] = "From: $from <".COMPANY_MAIL.">\nContent-type: text/plain; charset=\"utf-8\"";
	if(USE_EMAIL==1 && !mail($m['to'],$m['subject'],$m['body'],$m['header'])){
		$MAIL_ERROR = array('result'=>'ERROR','desc'=>"Отправка почты завершилась ошибкой!");
		return false;
	}
 	log_txt(__FUNCTION__.": почта {$to} отправлена!");
 	if(USE_EMAIL>1) log_txt("\n\tКому: {$m['to']}\n\tТема: {$m['subject']}\n\tСообщение:\n\t{$m['body']}");
	return array('result'=>'OK','to'=>$m['to'],'subject'=>$m['subject']);
}

function assemble_msg($pat, $m, $data){ // вставляет данные в шаблон
	$d = array('new_claim'=>'claimtime','new_job'=>'begintime','move_job'=>'begintime');
	if(preg_match_all('/<([A-Z][A-Z_]*)>/',$m,$act)) {
		foreach($act[1] as $k=>$v) {
			$f=mb_strtolower($v);
			if(!isset($data[$f])||!$data[$f]) $m = preg_replace("/<$v>.*<\/$v>/s","",$m);
			else $m = preg_replace("/<\/?$v>/s","",$m);
		}
	}
	if(preg_match_all('/:([A-Z][A-Z_]*)(%\d?\.?\d[a-z])?:/',$m,$mod)) {
		foreach($mod[1] as $k=>$v) {
			$f=mb_strtolower($v);
			$tmp = isset($data[$f])? $data[$f] : '';
			if($mod[2][$k]) $tmp = sprintf($mod[2][$k],$tmp);
			if(!$tmp && $f=='date' && isset($d[$pat]) && isset($data[$d[$pat]])) $tmp = cyrdate($data[$d[$pat]]);
			if(!$tmp && $f=='to' && isset($data[$d[$pat]])){
				$t = strtotime($data[$d[$pat]]); $td = date('Y-m-d',$t);
				$d1 = strtotime("$td 00:00"); $d2 = strtotime("$td 12:00"); $d3 = strtotime("$td 23:59:59");
				if( $d1 < $t && $t < $d2 ) $tmp = 1;
				elseif( $d2 < $t && $t < $d3 ) $tmp = 2;
			}
			if(!$tmp) {
				log_txt(__FUNCTION__.": $pat Ошибка в параметре `$f` \$data[$f] = ".arrstr($data[$f]));
				return false;
			}
			$m = preg_replace("/:{$v}(%[^:]*)?:/","$tmp",$m);
		}
	}
	return $m;
}

function send_notify($pat='', $data=array()) { // формирует и посылает уведомление
	global $config, $q, $opdata, $claim_types, $errors;
	if(USE_SMS>0 && isset($config['sms']['pattern'][$pat])){
		if(isset($config['sms']['mobile_operators']) && isset($config['sms']['phone_filter'])){
			$mo = array_flip($config['sms']['mobile_operators']);
			$op = $config['sms']['phone_filter'];
			if(isset($mo[$op]) && !preg_match("/^(38)?({$mo[$op]})/",preg_replace('/[^0-9]/',"",$data['phone']))) $data['phone'] = '';
		}
		if(!is_object($q)) $q = new sql_query($config['db']);
		if($pat=='pay' && isset($data['summa']) && $data['summa']<$config['min_sum_sms'])
			log_txt(__FUNCTION__.": Cумма {$data['summa']} меньше {$config['min_sum_sms']}");
		elseif($pat=='new_claim' && SMS_SEND_OF_NEWCLAIM<1) $errors[] = 'Сообщения для нов. заявок не влкючены';
		elseif(($pat=='new_job'||$pat=='move_job'||$pat=='cancel_job'||$pat=='end_job') && SMS_SEND_OF_PLANECLAIM<1) $errors[] = 'Сообщения для исп. заявок не влкючены';
		elseif(!$data['phone']) log_txt(__FUNCTION__.": у ".shortfio($data['fio'])." ({$data['uid']}) нет телефона!");
		elseif($m = assemble_msg($pat, $config['sms']['pattern'][$pat], $data))
			$q->insert('sms',array('op'=>$opdata['login'],'uid'=>@$data['uid'],'phone'=>$data['phone'],'message'=>$m));
	}
	if(USE_EMAIL>0 && isset($config['email']['subject'][$pat]) && isset($config['email']['pattern'][$pat])){
		if(@$data['uid'] && !key_exists('email',$data)) $data['email'] = $q->select("SELECT email FROM users WHERE uid='{$data['uid']}'",4);
		elseif(!$data['email']) log_txt(__FUNCTION__.": Не найден почтовый адрес у ".shortfio($data['fio'])." ({$data['uid']})");
		elseif(($m = assemble_msg($pat, $config['email']['pattern'][$pat], $data)) &&
			($subj = assemble_msg($pat, $config['email']['subject'][$pat], $data))){
			send_mail(array('to'=>$data['email'],'subject'=>$subj,'body'=>$m));
		}
	}
}

function include_find($s) { // ищет файл по разрешённым каталогам
	$s = preg_replace('/^\s*.*\/|\s+$/','',$s);
	$dirs = preg_split("/:/",get_include_path());
	foreach($dirs as $d){
		if(is_dir($d) && ($f = find_file($s,$d))) return $f;
	}
	return false;
}

function find_file($s,$dir='') { // ищет файл по шаблону в каталоге
	if($dir == '') $dir = '/tmp';
	if (is_dir($dir)) {
		$res = array();
		if ($dh = opendir($dir)) {
			if(!preg_match("/\/$/",$dir)) $dir .= "/";
			while (($file = readdir($dh)) !== false) {
				if(filetype($dir . $file)=='file' && preg_match("/$s/",$file)) {
					$res[] = $dir.$file;
				} elseif(filetype($dir . $file) == 'dir' && $file!='.' && $file!='..') {
					$directoryes[] = $dir.$file;
				}
			}
			closedir($dh);
			if(count($res)>0) return $res[0];
			if(@$directoryes){ foreach($directoryes as $d) if($f = find_file($s,$d)) return $f; }
		}else{
			log_txt("невозможно открыть каталог: $dir");
			return false;
		}
	}
	return false;
}

function json_error_str($err){
	switch (json_last_error()) {
		case JSON_ERROR_NONE:
			return ' - Ошибок нет'; break;
		case JSON_ERROR_DEPTH:
			return ' - Достигнута максимальная глубина стека'; break;
		case JSON_ERROR_STATE_MISMATCH:
			return ' - Некорректные разряды или не совпадение режимов'; break;
		case JSON_ERROR_CTRL_CHAR:
			return ' - Некорректный управляющий символ'; break;
		case JSON_ERROR_SYNTAX:
			return ' - Синтаксическая ошибка, не корректный JSON'; break;
		case JSON_ERROR_UTF8:
			return ' - Некорректные символы UTF-8, возможно неверная кодировка'; break;
		default:
			return ' - Неизвестная ошибка'; break;
	}
}

function randStr($length = 10) {
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $len = mb_strlen($chars);
    $randStr= '';
    for ($i = 0; $i < $length; $i++) {
        $randStr .= $chars[rand(0, $len - 1)];
    }
    return $randStr;
}

function loadFile($dir=PHOTO_FOLDER){ // заружает файл на сервер в указанный каталог
	$_GET['qfile'] = $_GET['qfile'];
	$input = fopen("php://input", "r");
	if(!$input){
		log_txt(__FUNCTION__.": ERROR not open imput stream!");
		return false;
	}
	$temp = tmpfile();
	$realSize = stream_copy_to_stream($input, $temp);
	$temp_res = stream_get_meta_data($temp);
	fclose($input);
	if(!isset($_SERVER["CONTENT_LENGTH"]) || $realSize != (int)$_SERVER["CONTENT_LENGTH"]) 
		stop(array('result'=>'ERROR','desc'=>'ошибочный размер файла'));
	if($realSize > MAXPHOTOSIZE) 
		stop(array('result'=>'ERROR','desc'=>"Размер файла не должен<br>превышать ".MAXPHOTOSIZE." байт"));
	$clientfile = preg_replace('/.*[\\\\\/]/','',$_GET['qfile']);
	$filetype = preg_replace('/.*\./','',$clientfile);
	if(!(array_search($filetype, array('gif','jpg','jpeg','png'))>=0))
		stop(array('result'=>'ERROR','desc'=>'Данный тип файла не поддерживается!'));
	$tmpfile = 'IMG_'.randStr(16).'.'.$filetype;
	$file = $tmpfile;
	if(!preg_match('/\/$/',$dir)) $dir .= '/';
	if(is_dir($dir)) $file = $dir.$tmpfile;
	if(!($target = fopen($file, "w"))){
		log_txt(__FUNCTION__.": ERROR not open $file to write!");
		return false;
	}
	fseek($temp, 0, SEEK_SET);
	$trg_size = stream_copy_to_stream($temp, $target);
	log_txt(__FUNCTION__.": файл: $clientfile сохранен как $file ($trg_size)");
	fclose($target);
	return $tmpfile;
}

function fdelete($file){ // удаляет файл
	if(!file_exists($file)){
		log_txt(__FUNCTION__.": WARNING file $file not exists!");
		return true;
	}
	if(unlink($file)) {
		log_txt(__FUNCTION__." file $file is deleted!");
		return true;
	}else{
		log_txt(__FUNCTION__.": ERROR file $file not removed!");
		return false;
	}
}

function list_of_employers(){
	global $config, $cache, $q;
	if(!key_exists('employers',$cache)) {
		if(!isset($q)) $q=new sql_query($config['db']);
		$cache['employers'] = $q->fetch_all("select eid, fio from employers where blocked=0 order by fio",'eid');
		foreach($cache['employers'] as $k=>$v) $cache['employers'][$k] = shortfio($v);
	}
	$r = $cache['employers']; $r[0] = '';
	return $r;
}

function list_of_packets($rid=0) {
	global $cache, $config, $q;
	if(!key_exists('packets',$cache)) {
		if(!$q) $q = new sql_query($config['db']);
		$cache['packets'] = $q->fetch_all("SELECT pid, concat(name,' (',round(fixed_cost),'р.)') as name FROM packets ORDER by num",'pid');
	}
	$pk = $cache['packets'];
	if($rid && $rid>0){
		$rp = $q->fetch_all("SELECT gid as id, unique_id FROM rayon_packet WHERE rid='{$rid}'");
		if($rp) $pk = array_intersect_key($pk,$rp);
	}
	return $pk;
}

function user_rayon($i,$r=null,$fn=null) {
	global $cache;
	if(!@is_array($cache['tables']['rayons'])) list_of_rayons();
	$r = (isset($cache['tables']['rayons'][$i]))? $cache['tables']['rayons'][$i] : $i;
	return $r;
}

function list_of_rayons() {
	global $cache, $config, $q;
	if(!@is_array($cache['tables']['rayons'])) {
		if(!$q) $q = new sql_query($config['db']);
		$cache['tables']['rayons'] = $q->fetch_all("SELECT rid, r_name FROM rayon ORDER BY r_name",'rid');
	}
	return $cache['tables']['rayons'];
}

function all2array($a,$all='все',$id=''){ // добавляет в начало списка "все"
	$r = array('_'.$id=>$all);
	foreach($a as $k=>$v) if(!is_string($k)) $r['_'.$k] = $v; else $r[$k] = $v;
	return $r;
}

function list_operators($filter="AND status>2"){
	global $config, $cache, $q;
	if(!@is_array($cache['tables']['operators'])) {
		if(!$q) $q = new sql_query($config['db']);
		$cache['tables']['operators'] = array();
		$a = $q->fetch_all("SELECT login as id, fio as name FROM operators WHERE 1 $filter ORDER BY fio");
		if($a) foreach($a as $k=>$v) $cache['tables']['operators'][$k] = shortfio($v);
	}
	return $cache['tables']['operators'];
}

function get_client($r,$login) { // формирует html объект для отображения данных клиента
	global $explain_packet, $cache, $config, $q;
	$html = '';
	$online = ($r['acctstarttime']) && (!$r['acctstoptime']);
	$html.="<tr id=\"{$r['uid']}\"><td>";
	$html.="<span class=\"login\">".$login."</span>";
	if(isset($r['connects']) && $r['connects']>1) $html.="<BR>({$r['connects']} раз)";
	$html.="</td>";
	$img_comp = 'pic/computer.png';
	$explain = isset($explain_packet[$r['groupname']])? $explain_packet[$r['groupname']] : '';
	if($r['fixed']==10) $tmleft = strtotime($r['expired']) - strtotime('now');
	elseif($r['fixed']==1 || $r['fixed']==7) $tmleft = floor(($r['deposit'] + $r['credit']) / $r['fixed_cost'] * 86400);
	elseif($r['fixed']==8 || $r['fixed']==8) $tmleft = (($r['deposit'] + $r['credit']) > 0)? strtotime('first day of next month 00:00') - time():0;
	else $tmleft = false;
	if($tmleft > 0 && ($r['fixed']==1 || $r['fixed']==7)) $r['expired'] = date('Y-m-d',time() + $tmleft);
	if(!$online) $img_comp = 'pic/greycomp.png';
	if(!$r['acctstoptime'] && isset($explain_packet[$r['groupname']])) $img_comp = 'pic/redcomp.png';
	$live = $r['live']>0;
	$debtor = $r['deposit'] + $r['credit']<-0.004;
	if($r['fixed']==10 && ($r['deposit'] + $r['credit'] > $r['fixed_cost'])) $dep_color = "#0a0";
	elseif($r['deposit'] >= 0 && $r['fixed'] && floor($tmleft / 86400)>7) $dep_color = "#0a0";
	elseif($r['fixed'] && ($r['deposit'] + $r['credit'] <= 0)) $dep_color = "#f00";
	elseif($r['fixed'] && ($r['deposit'] + $r['credit'] < $r['fixed_cost']) && floor($tmleft / 86400)<=7) $dep_color = "#b4a";
	elseif(($r['fixed']==1 || $r['fixed']==7) && floor($tmleft / 86400)<7) $dep_color = "#b4a";
	elseif($r['fixed'] && $r['deposit'] >= 0 && floor($tmleft / 86400)>7) $dep_color = "#090";
	else $dep_color = "#de0000";
	$expired = ($r['fixed'])? strtotime($r['expired']) < time() : false;
	if($live&&!$debtor) $color='#ccc'; // светло-серый
	if($live&&!$debtor&&$expired) $color='#888'; // серый
	if($live&&$debtor) $color='#f44'; // светло-красный
	if(!$live&&$debtor) $color='#600'; // тёмно-красный
	if(!$live&&!$debtor) $color='#333'; // тёмно-серый
	if($online&&!$debtor) $color='#5d5'; // зелёный
	if($online&&$debtor) $color='#f94'; // оранжевый
	if(!$r['acctstarttime']) $r['acctstarttime'] = $r['acctstoptime'] = $r['last_connection'];
	$html.='<td style="background-color:'.$color.';width:1px;"></td>';
	$tmp=array();
	$tmp[0]="<span class=\"fio\">{$r['fio']}</span>&ensp;<span class=\"contract\">({$r['contract']})</span>";
	if($r['rid']>1 && ($rayon = user_rayon($r['rid']))) $rayon .= ' &ensp;';
	$tmp[1]="<span class=\"address\"><img src=\"pic/bighome.png\">&ensp;{$rayon}{$r['address']}";
	if(@$r['entrance']) $tmp[1].=" &ensp;<b>{$r['entrance']}</b><em> подъезд</em>";
	if(@$r['floor']) $tmp[1].=" &ensp;<b>{$r['floor']}</b><em> этаж</em>";
	$tmp[1].="</span>";
	$tmp[2]="<span class=\"phone\"><img src=\"pic/phone.png\">&ensp;{$r['phone']}</span>";
	$html.='<td style="white-space:nowrap">'.implode('<br>',$tmp).'</td><td>';

	$title = ($r['fixed'])? "title=\"абонплата: ".cell_summ($r['fixed_cost'])."\"" : "";
	$html.="<span class=\"packetname\" $title>{$r['packetname']}</span>&emsp;";
	if($r['fixed']) {
		$html.="<span class=\"deposit\" style=\"color:{$dep_color}\">".sprintf('%.2f',$r['deposit'])."</span>";
		$html.="<span class=\"credit\">/".sprintf('%.2f',$r['credit'])."</span>&emsp;";
		$html.="<span class=\"expired\">".(($tmleft>0)? "осталось <b>".time_left($tmleft)."</b> до <b>" : "закончился <b>");
		$html.=cyrdate($r['expired'],'%d %B')."</b></span>&emsp;";
	}
	$html.='<br>';
	$html.="<span class=\"acctstarttime\"".(!$online? "style=\"color:#889\"":"")." title=\"последнее подключение\">";
	$html.="<img src=\"$img_comp\"> ";
	if($online) $html.=cyrdate($r['acctstarttime'],'%d-%b-%y %H:%M');
	else $html.=($r['acctstarttime']!='0000-00-00' && $r['acctstarttime']!='')? 
		cyrdate($r['acctstoptime'],'%d-%b-%y %H:%M') : "подключений не было";
	$html.="</span>&emsp;";
	if($explain) $html.="<span class=\"acctstarttime\" style=\"color:#b00\">$explain</span>&emsp;";
	if($online) {
		$html.="nas <span class=\"nasipaddress\" title=\"сервер доступа\">{$r['nasipaddress']}</span>:";
		$html.="<span class=\"nasportid\">{$r['nasportid']}</span>&emsp;";
		$html.="ip <span class=\"framedipaddress\" title=\"IP адрес клиента\">{$r['framedipaddress']}</span>&emsp;";
		$html.="<span class=\"callingstationid\" title=\"MAC адрес клиента\">{$r['callingstationid']}</span>&emsp;";
	}
	$html.='<br>';
	if($r['note']!='') {
		$html.="<span class=\"note\"><img src=\"pic/note.png\"> {$r['note']}</span>&emsp;";
	}
	if(key_exists('type',$r)){
		$onclick = "";
		if($r['type'] == 'wifi') $onclick = "event.stopPropagation();event.preventDefault();window.open('http://{$r['framedipaddress']}/','_blank')";
		if($r['type'] == 'pon') $onclick = "event.stopPropagation();event.preventDefault();$.popupForm({data: 'go=devices&do=edit&uid={$r['uid']}',onsubmit:function(d){},loader:ldr})";
		$img = ($r['type'])? "<img onclick=\"$onclick\" src=\"pic/{$r['type']}.png\">" : "";
		$html.="</td><td class=\"subtype\">$img";
	}
	$html.="</td></tr>\n";
	return $html;
}

function make_client($data) {
	global $tables;
	if(is_numeric($data) && !isset($tables['search'])) {
		if($data>CITYCODE*10000 && $data<CITYCODE*100000) $filter = "contract = $data";
		else $filter = "uid = $data";
		include_once("search.php");
		include_once("search.cfg.php");
		$t = $tables['search'];
		$srch1=getresult($filter,$t['query']);
		$srch2=getresult($filter,$t['query1']);
		$srch=array_merge_recursive($srch1,$srch2);
		$html = "<table>";
		foreach($srch as $key=>$r) $html.=get_client($r,$key);
		$html .= "</table>";
	}elseif (is_array($data)) {
		$html = "<table>".get_client($data,$data['user'])."</table>";
	}
	return "<div class=\"searchdata\">$html</div>";
}

function make_menu($menu,$name='') {
	global $go, $opdata;
	$html = '';
	$myfile = preg_replace('/.*\//','',$_SERVER['SCRIPT_FILENAME']);
	foreach($menu as $n=>$a){
		if(isset($a['level']) && $opdata['status']<$a['level']) continue;
		if(isset($a['level'])) unset($a['level']);
		foreach(preg_split('/&/',preg_replace('/^[^\?]*(\?)?/','',@$a['HREF'])) as $s){ $o = preg_split('/=/',$s,2); $opt[@$o[0]]=@$o[1]; }
		$item = isset($opt['go'])? $opt['go'] : '';
		if($name && isset($go) && $go && $item == $go) $a['class'] = trim(@$a['class']." selected");
		if(!$name && $myfile == @$a['HREF']) $a['class'] = trim(@$a['class']." selected");
		$opt = array();
		foreach($a as $k=>$v) $opt[] = "{$k}=\"{$v}\"";
		$html.="<li><A ".implode(" ",$opt).">{$n}</A></li>";
	}
	if(!$name) $name = 'topmenu';
	return "<div class=\"menu {$name}\"><UL>".$html."</UL></div>";
}

function get_devname($device='',$node=false,$showtype=true,$ln=16) { // формирует название устройства
	global $config, $devtype, $q;
	if(!$device) return '';
	if(!isset($q)) $q = new sql_query($config['db']);
	if(!is_array($device)) $device = $q->get('devices',$device);
	if(!$device) return '';
	if($device['type'] == 'cable'){
		if(!key_exists('a1',$device)){
			$nodes = $q->fetch_all("SELECT id, address FROM `map` WHERE id='{$device['node1']}' OR id='{$device['node2']}'");
			$device['a1'] = $nodes[$device['node1']];
			$device['a2'] = $nodes[$device['node2']];
		}
		if(is_null($device['a1'])) $device['a1'] = "пустой конец";
		if(is_null($device['a2'])) $device['a2'] = "пустой конец";
		$name = $showtype? "{$devtype[$device['type']]}({$device['numports']}ж) ":"";
		$name .= "на ".(($node==$device['node1'])? $device['a2'] : $device['a1']);
	}elseif($device['type'] == 'divisor' || $device['type'] == 'splitter'){
		$name = "{$devtype[$device['type']]}({$device['subtype']})";
	}elseif($device['type'] == 'switch'){
		$name = $showtype? "{$devtype[$device['type']]}({$device['numports']}п) ":"";
		$name .= "{$device['name']} {$device['ip']}";
	}elseif($device['type'] == 'onu'){
		$name = $showtype? "{$devtype[$device['type']]} ":"";
		$name .= "{$device['name']}";
	}elseif($device['type'] == 'wifi'){
		$name = $showtype? "{$devtype[$device['type']]} ":"";
		$name .= "{$device['name']}".($device['ip']? " {$device['ip']}":"");
	}else{
		$name = $showtype? "{$devtype[$device['type']]}({$device['numports']}п) ":"";
		$name .= "{$device['name']}";
	}
//	if(@$device['note']) $name .= " <em>(".mb_substr($device['note'],0,$ln).((mb_strlen($device['note'])>$ln)?"...":"").")</em>";
	return $name;
}

function get_valute($currency=''){ // возвращяет данные по указанной валюте
	global $DEBUG, $cache, $conf, $q;
	if(!isset($cache['tables']['currency'])){
		if(!@$q) $q = new sql_query($conf['db']);
		if(!($c = $q->fetch_all("SELECT * FROM currency"))){
			log_txt(__FUNCTION__.": ERROR нет валют в таблице currency!");
			$c = array('1'=>array('id'=>1,'name'=>'рубль','rate'=>1.00,'short'=>'руб','blocked'=>0));
		}
		$cache['tables']['currency'] = $c;
		foreach($cache['tables']['currency'] as $k=>$v) if($v['rate']==1.0) $cache['def_currency'] = $k;
		if(!isset($cache['def_currency'])){
			log_txt(__FUNCTION__.": ERROR нет валюты по умолчанию!");
			$tmp = reset($c);
			if(!isset($c['id'])){
				log_txt(__FUNCTION__.": ERROR нет найден id валюты!");
			}else $cache['def_currency'] = $tmp['id'];
		}
	}
	$c = $cache['tables']['currency'];
	$valute = false;
	if(!$currency && isset($cache['def_currency'])) $currency = $cache['def_currency'];
	if(is_numeric($currency)){
		$valute = isset($c[$currency])? $c[$currency] : false;
	}elseif(is_string($currency)){
		foreach($c as $k=>$v)
			if($currency==$v['short'] || $currency==$v['name']) {
				$valute = $v;
				break;
			}
	}
	if(!$valute) $valute = $c[$cache['def_currency']];
	if($DEBUG>0) log_txt(__METHOD__.": ".$valute['short']);
	return $valute;
}

function timecmp($t1,$t2) {
	if(is_string($t1)) $a = strtotime($t1);
	elseif(is_numeric($t1)) $a = $t1;
	if(is_string($t2)) $b = strtotime($t2);
	elseif(is_numeric($t2)) $b = $t2;
	if(!$a || !$b){
		log_txt(__FUNCTION__.": ERROR  time1: '".arrstr($t1)."'  time2: '".arrstr($t2)."'");
		return false;
	}
	if($a > $b) return 1; elseif($a < $b) return -1; else return 0;
}

function period2db($table,$fn,$begin=false,$end=false) { // формирует вставку для SQL с условием по периоду
	global $tables;
	if(!$tables[$table]['filters']) log_txt(__FUNCTION__.": problem table: `$table`");
	$f = $tables[$table]['filters'];
	if(!$begin) $begin = isset($f['begin']['value'])? $f['begin']['value'] : 'first day of';
	if(!$end) $end = isset($f['end']['value'])? $f['end']['value'] : date('Y-m-d 23:59:59');
	if(!preg_match('/ \d\d:\d\d.*/',$end)) $end = $end.' 23:59:59';
	$b = isset($_REQUEST['begin'])? date2db(preg_replace('/[^0-9:\/\- ]/','',$_REQUEST['begin'])) : date2db(strtotime($begin),false);
	$e = isset($_REQUEST['end'])? date2db(preg_replace('/[^0-9:\/\- ]/','',$_REQUEST['end']).' 23:59:59') : date2db(strtotime($end));
	$s = "AND $fn BETWEEN '$b' AND '$e'";
//	log_txt(__FUNCTION__.": $s");
	return $s;
}

function filter2db($table) { // формирует вставку для SQL (обычно заменяет в SQL :FILTER:)
	global $tables;
	$r = array(); $s = ''; $in = array('/[^0-9,]/','/,,*/'); $out = array('',',');
	if(!($f = $tables[$table]['filters'])) log_txt(__FUNCTION__.": problem table: `$table`");
	if(isset($tables[$table]['field_alias'])) $fld = $tables[$table]['field_alias']; // если в запросе несколько таблиц
	if(is_array($f)){
		foreach($f as $k=>$v){
			$n = isset($v['origin'])? $v['origin'] : $k;
			$fn = isset($fld[$n])? "{$fld[$n]}.`$n`" : "`$n`";
			if($k == 'start') {
				if(isset($_REQUEST[$k]) && ($val = str($_REQUEST[$k]))!=''){
					if($v['type']=='text' && ($fn=='ip' || preg_match('/ipaddress/',$fn)) && ($ip = ip2long($val))>0) $r[] = "INET_ATON($fn) >= $ip";
					elseif($v['type']=='date' && ($d=preg_replace('/[^0-9:\/\- ]/','',$_REQUEST[$k]))!='') $r[] = "$fn >= '".date2db(strtotime($d),false)."'";
					else $r[] = "$fn > $val";
				}elseif($v['type']=='date' && $v['value']!='')
					$r[] = "$fn > '".date2db(strtotime($v['value']),false)."'";
			}elseif($k == 'stop') {
				if(isset($_REQUEST[$k]) && ($val = str($_REQUEST[$k]))!=''){
					if($v['type']=='text' && ($fn=='ip' || preg_match('/ipaddress/',$fn)) && ($ip = ip2long($val))>0) $r[] = "INET_ATON($fn) <= $ip";
					elseif($v['type']=='date' && ($d=preg_replace('/[^0-9:\/\- ]/','',$_REQUEST[$k]))!='') $r[] = "$fn > '".date2db(strtotime($d),false)."'";
					else $r[] = "$fn < $val";
				}elseif($v['type']=='date' && $v['value']!='')
					$r[] = "$fn > '".date2db(strtotime($v['value']),false)."'";
			}elseif($v['type']=='checklist') {
				if(isset($_REQUEST[$k]) && ($val = preg_replace($in,$out,$_REQUEST[$k]))!='') $r[] = "$fn in ($val)";
			}elseif($v['type']=='text'){
				if(isset($_REQUEST[$k]) && ($val = preg_replace('/^[^0-9a-z]*_/','',$_REQUEST[$k])) !='') $r[] = "$fn like '%$val%'";
			}elseif($v['type']=='select'){
				if(isset($_REQUEST[$k]) && isset($v['typeofvalue']) && $v['typeofvalue']=='time' && ($val = strtotime($_REQUEST[$k]))!='')
					$r[] = "$fn > '".date2db($val)."'";
				elseif(isset($_REQUEST[$k]) && isset($v['typeofvalue']) && $v['typeofvalue']=='active' && (($val = $_REQUEST[$k]) !=''))
					$r[] = (preg_match('/^\s*[<>=&|]/',$val)?$fn:'')." $val";
				elseif(isset($_REQUEST[$k]) && ($val = preg_replace('/^[^0-9a-z]*_/','',$_REQUEST[$k]))!='')
					$r[] = "$fn='$val'";
			}elseif($v['type']!='date'){
				if(isset($_REQUEST[$k]) && ($val = strict($_REQUEST[$k]))!='') $r[] = "$fn='$val'";
			}
		}
		$s = implode(' AND ',$r);
		if($s) $s = 'AND '.$s;
	}
//	log_txt(__FUNCTION__.": $s");
	return $s;
}

// function unpack accel-ppp DHCP-Option82
function unpack_opt82($val) {
	$val = preg_replace('/^0x/','',$val);
	$len = strlen($val); $cursr = $n = $l = $i = 0; $s=""; $a = array();
	while($cursr < $len) {
		$n = arrfld(unpack("C",pack("H2",substr($val,$cursr,2))),1); $cursr = $cursr + 2;
		$l = arrfld(unpack("C",pack("H2",substr($val,$cursr,2))),1) * 2; $cursr = $cursr + 2;
		$s = substr($val,$cursr,$l); $cursr = $cursr + $l;
		if(preg_match("/^".sprintf("00%02x",strlen($s)/2-2)."(.*)/",$s,$m)) $s = $m[1];
		$a[$n] = $s;
	}
	return $a;
}

function parse_opt82($opt) {
	$re = array('device'=>"",'circuit'=>"",'vlan'=>"",'port'=>"");
	$a = unpack_opt82($opt);
	$re['circuit'] = @$a[1];
	$re['device'] = preg_replace('/.*(..)(..)(..)(..)(..)(..)$/', "$1:$2:$3:$4:$5:$6", @$a[2]);
	if(preg_match('/^(....).*/',@$a[1],$m)) $re['vlan'] = arrfld(unpack("n",pack("H4", $m[1])),1);
	if(preg_match('/^......(..).*/',@$a[1],$m)) $re['port'] = arrfld(unpack("C",pack("H2", $m[1])),1);
	if(preg_match('/^........(..).*/',@$a[1],$m)) $re['subport'] = arrfld(unpack("C",pack("H2", $m[1])),1);
	if(!$re['device']) return false;
	return $re;
}

// формирует js процедуру что бы по изменению поля type спрятать/добавить необходимые поля в форме
function js_type_onchange($fields, $labels=array()) {
	global $tables;
	foreach($tables['devices']['fields'] as $k=>$v) $f[$k] = $v['label'];
	$all=array();
	foreach($fields as $k=>$v) {
		foreach($v as $i=>$n) $o[$k][]='#field-'.$n;
		$all=array_unique(array_merge($all,$o[$k]));
	}
	$s="var c=$(this),\nf=$(this).parents('form');\n";
	$nl = array();
	foreach($labels as $t=>$v) $nl[$v[0]][$t] = $v[1];
	foreach($nl as $n=>$a) {
		$i = 0;
		foreach($a as $t=>$l) {
			if($i==0) $s.="if(c.val()=='$t') f.find('#field-{$n} .label').html('{$l}');\n";
			else $s.="else if(c.val()=='$t') f.find('#field-{$n} .label').html('{$l}');\n";
			$i++;
		}
		$s.="else f.find('#field-{$n} .label').html('{$f[$n]}');\n";
	}
	foreach($o as $k=>$v) {
		$diff = implode(',',array_diff($all,$v));
		$s.="if(c.val()=='{$k}'){\n";
		$s.="\tf.find('".implode(',',$v)."').show();\n";
		if($diff) $s.="\tf.find('".$diff."').hide().val('');\n";
		$s.="}else ";
	}
	$s.="{\nf.find('".implode(',',$all)."').hide().val('');\n}";
	return $s;
}

function css($css,$add='') {
	if(!is_array($css)) $a=preg_split('/;/',$css);
	foreach($a as $k=>$v) if($v=='') unset($a[$k]);
	array_push($a,$add);
	return implode(';',$a);
}

function read_access($acl,$fieldname='???') { // проверяет доступ на чтение
	global $opdata, $DEBUG;
	if($DEBUG>6) log_txt(__FUNCTION__.": поле `$fieldname` = access={$acl['r']}|{$acl['g']}");
	if(is_array($acl) && @$acl['r'] && is_numeric($acl['r']) && $opdata['status']>=$acl['r']) return true;
	elseif(is_array($acl) && @$acl['g'] && is_string($acl['g']) && ingrp($acl['g'])) return true;
	elseif(is_string($acl) && function_exists($acl)) return $acl($k);
	elseif(is_numeric($acl) && $opdata['status']>=$acl) return true;
	else {
		if($DEBUG>0) log_txt("доступ (чтение) к {$fieldname} запрещен");
		return false;
	}
}

function write_access($acl,$fieldname='???') { // проверяет доступ на запись
	global $opdata, $DEBUG;
	if($DEBUG>6) log_txt("form->write_access: opdata[status]='{$opdata['status']}' поле `$fieldname` access='{$acl['w']}'");
	if(is_array($acl) && @$acl['w'] && is_numeric($acl['w']) && $opdata['status']>=$acl['w']){
		if($DEBUG>0) log_txt(__FUNCTION__.": $fieldname access[w] = {$opdata['login']}:{$opdata['status']} > {$acl['w']}");
		return true;
	}elseif(is_array($acl) && @$acl['g'] && is_string($acl['g']) && ($g = ingrp($acl['g']))){
		if($DEBUG>0) log_txt(__FUNCTION__.": $fieldname  {$acl['g']}] in {$opdata['groups']}");
		return true;
	}elseif(is_string($acl) && function_exists($acl)){
		$r = $acl($fieldname);
		if($DEBUG>0) log_txt(__FUNCTION__.": $fieldname external func {$acl}($fieldname) = ".arrstr($r));
		return $r;
	}elseif(is_numeric($acl) && $opdata['status']>=$acl){
		if($DEBUG>0) log_txt(__FUNCTION__.": $fieldname access: {$opdata['login']}:{$opdata['status']} > {$acl}");
		return true;
	}else{
		log_txt("доступ (запись) к {$fieldname} запрещен");
		return false;
	}
}

function ingrp($name,$groups='') {
	global $opdata, $DEBUG;
	if(!is_string($name) || $name=='') return false;
	if(is_string($groups) && $groups!='') $g = preg_split('/\s*,\s*/',trim($groups));
	else $g = preg_split('/\s*,\s*/',trim($opdata['groups']));
	$r = (array_search($name,$g)===false)? false : true;
	return $r;
}

function time_left($t) { // выдаёт в понятном виде сколько осталось дней в пакете клиента
	$days = 0; $hour = 0; $min = 0;
	if($t<=0) return '00:00';
	$days = floor($t / 86400);
	if($days>0) return ($days+1)." дн.";
	$hour = floor(($t - $days * 86400) / 3600);
	$min = floor(($t - $days * 86400 - $hour * 3600) / 60);
	return (($hour<10)?"0":'')."{$hour}:".(($min<10)?"0":'')."{$min}";
}

function acolumn($key,$a,$wrap=true) {
	if(is_array($a) && is_array(reset($a))){
		$out = array();
		foreach($a as $k=>$r) if(key_exists($key,$r)) $out[] = $wrap? "'{$r[$key]}'" : $r[$key];
		return $out;
	}
	return false;
}

function ids($a) {
	$b = $p = $d = $s = '';
	foreach($a as $k=>$v) if($p==='') $b=$p=$v; else{
		if($v!=$p+1){ if($b==$p){ $s.=$d.$b; $d=", ";
		}else{ $s.=$d."$b-$p"; $d=", "; } $b = $v; } $p = $v;
	}
	if($b==$p) $s.=$d.$b; else $s.=$d."$b-$p";
	return $s;
}

function unids($s) {
	$r = array();
	$a = preg_split('/,/',preg_replace('/[^0-9\-,]/','',$s));
	foreach($a as $k=>$v){
		$b = preg_split('/-/',$v,2);
		if(count($b)==2) for($i=$b[0];$i<=$b[1];$i++) $r[] = $i;
		else $r[] = $b[0];
	}
	return $r;
}

function arrfld($arr=array(),$field='id') {if(isset($arr[$field])) return $arr[$field]; else return false;}
function quote($a) {if(is_array($a)){foreach($a as $k=>$v) $a[$k]="'$v'";return $a;}else return "'$a'";}
function photo_link($s) {$l=(PHOTO_TARGET==1)? PHOTO_FOLDER:"photo.php?id="; return ($s)? $l.$s:"pic/unknown.png";}

// функции для форматирования вывода в ячейки таблицы
function cell_traf($v) { return sprintf("%.1f",$v/MBYTE); }
function cell_summ($v,$r=null,$fn=null) { return sprintf("%.2f",$v); }
function cell_login($v) { return sprintf("<a class=\"usrview\" href=\"users.php?go=usrstat&user=%s\">%s</a>",$v,$v); }
function cell_stime($v) { return sectime('h:m:s', $v); }
function cell_time($v,$r=null,$fn=null) { return cyrdate($v,'%d-%m-%y %H:%M'); }
function cell_ftime($v) { if(!$v) return $v; return cyrdate($v,'%d %b %y %H:%M:%S'); }
function cell_atime($v,$r=null,$fn=null) { return cyrdate($v,'%d %b %y &nbsp;<em>%H:%M</em>'); }
function cell_date($v,$r=null,$fn=null) { return cyrdate($v,'%d-%m-%Y'); }
function cell_bdate($v) { return cyrdate($v,'%d-%b-%Y'); }

// функции для безопасности работы с вводимыми данными
function numeric($c) { $r = preg_replace('/[^0-9\-]/','',$c); if($r=='') return 0; else return $r; }
function flt($c) { $r = preg_replace('/[^0-9\-\.]/','',$c); if($r=='') return 0; else return $r; }
function str($s) { global $q, $config; if(!isset($q)) $q = new sql_query($config['db']); return $q->escape_string($s); }
function strict($s) { return (preg_replace('/[^0-9A-Za-z\.\-_ ]/','',$s)); }
function strong($s) { return preg_replace("/[\/\"\'!@#%\^\&*\(\)\{\};\\\|<>]/",'',$s); } // используется в лич.каб.
?>
