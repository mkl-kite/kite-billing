<SCRIPT language="JavaScript">

var userkill;

$(document).ready(function() {
	$('tbody tr').click(function(){
		var td = $('td',this).get(0), ip = $(td).text();
		window.open('http://'+ip,'_blank');
		return false;
	})
})
</SCRIPT>
<?php

function mytime($s){
	$d = new DateTime(preg_replace('/\//','-',$s[2])." ".$s[3], new DateTimeZone('UTC'));
	$d->setTimezone(new DateTimeZone(date_default_timezone_get()));
	return $d->format('Y-m-d H:i:s');
}
$file = "/var/lib/dhcp/dhcpd.leases";
$filds = array('start','end','cltt','state','next state','mac','host');

echo "<h3>Список хостов</h3>";

if(filetype($file)=='file') {
	$pfile=@fopen($file,"r");
	if ($pfile) {
		$ip = false;
		while(($buffer = fgets($pfile, 4096)) !== false) {
			$buffer = trim($buffer);
			if(preg_match('/\s*([0-9\.][0-9\.]*)\s*{/',$buffer,$m)) {
				$get_host=true;
				$ip = $m[1];
				$client = array('ip'=>ip2long($ip));
				foreach($filds as $k) $client[$k] = '';
			}elseif(preg_match('/}/',$buffer)) {
				if($ip && count($client)>0) {
					$clients[$ip]=$client;
				}
				$get_host=false;
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
					elseif($s[0] == 'client-hostname') { $n = "host"; $v = $s[1]; }
					if($n && $v) $client[$n] = $v;
				}
			}
		}
		fclose($pfile);
	}else stop(array('result'=>'ERROR','desc'=>"Ошибка чтения файла $file"));
}else stop(array('result'=>'ERROR','desc'=>"Файл $file не найден!"));


if(isset($clients) && count($clients)>0){
	foreach($clients as $client) $s1[] = $client['ip'];
	array_multisort($s1, SORT_ASC, SORT_NUMERIC, $clients);
	$h = reset($clients);
	echo "<table class=\"normal\"><thead>\n<tr>";
	foreach(array_keys($h) as $k => $v){
		printf("<td>%s</td>",$v);
	}
	echo "</tr>\n</thead><tbody>\n";
	foreach($clients as $client){
		if($client['state'] != 'active') continue;
		foreach($client as $k => $v){
			printf("<td>%s</td>",(($k == 'ip')? long2ip($v):$v));
		}
		print("</tr>\n");
	}
	echo "</tbody></table>";
}else stop(array('result'=>'ERROR','desc'=>"IP адреса не найдены!"));
?>
