<?php
class SMS{
    
	var $provider;

	function __construct($cfg){
		$this->error='';
	    $this->provider = new $cfg['provider']($cfg['providers'][$cfg['provider']]);
	}

	function send( $phone, $message, $id=0 ){
		$dst=false;
		if(preg_match('/^\+[0-9]{12,12}$/',preg_replace('/[^0-9\+]/','',$phone))){
			$dst=preg_replace('/[^0-9\+]/','',$phone);
		}elseif(preg_match('/^[0-9]{10,10}$/',preg_replace(array('/[^0-9]/','/^38/'),array('',''),$phone))){
			$dst='+38'.preg_replace(array('/[^0-9]/','/^38/'),array('',''),$phone);
		}elseif(preg_match('/^[0-9]{10,10}$/',preg_replace('/[^0-9]/','',$phone))){
			$dst='+38'.preg_replace('/[^0-9]/','',$phone);
		}
		if(!$dst) {
			$this->error='неправильный номер телефона';
			return false;
		}
		if(preg_replace('/[\r\n]/','',trim($message))=='') {
			$this->error='пустое сообщение';
			return false;
		}
 		$res=$this->provider->send($dst, $message, $id);
		if(@$this->provider->error) $this->error=mb_substr($this->provider->error,0,30);
		return $res;
	}

	function balance(){
	    return $this->provider->balance();
	}
}

class bsgroup{	// Для работы с BSgroup

    var $config;

    function __construct( $config ){
		$this->config = $config;
    }

    function send($phone, $message){
		$phone2 = '';
		foreach( preg_split( '/[,|; ]/', $phone ) as $k=>$v ){
			$v = "+38".preg_replace(array('/[^0-9]/','/^38/','/^8/'),array('','',''),$v);
			$phone2 .= "<abonent phone='" . $v . "' number_sms='$k'></abonent>";
		}

		$xml =  "<?php xml  version='1.0' encoding='utf-8' ?>".
				"<request>".
					"<message type='sms'>".
						"<sender>{$this->config['sender']}</sender>".
						"<text>$message</text>$phone2".
					"</message>".
					"<security>".
						"<login value='{$this->config['login']}' />".
						"<password value='{$this->config['password']}' />".
					"</security>".
				"</request>";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: text/xml; charset=utf-8'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CRLF, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_URL, 'http://sms.bs-group.com.ua/xml/');
		$result = curl_exec($ch);
		curl_close($ch);
		if($result)
			return true;
		else
			return false;
	}

	function balance(){
		$xml = "<?php xml  version='1.0' encoding='utf-8' ?>".
				"<request>".
					"<security>".
						"<login value='{$this->config['login']}' />".
						"<password value='{$this->config['password']}' />".
					"</security>".
				"</request>";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: text/xml; charset=utf-8'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CRLF, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_URL, 'http://sms.bs-group.com.ua/xml/balance.php');

		$result = curl_exec($ch);
		curl_close($ch);

		$xml = new SimpleXMLElement( $result );

		if( $xml->sms ){
			$result = intval($xml->sms);
		}else{
			$result = 0;
		}
		return $result;
	}


    function __destruct(){
    }
}


class turbosms{	//	Для работы с turboSMS

    var $config, $error, $balance;

    function __construct( $config ){
		$this->config = $config;
		$this->error = false;
		$this->provider = new SoapClient ($this->config['server']);

		log_txt("Подключения к sms шлюзу turbosms!");
		$auth_result = $this->provider->Auth(array('login'=>$config['login'], 'password'=>$config['password']));
		if( $auth_result->AuthResult != 'Вы успешно авторизировались' ){
			log_txt("Ошибка подключения к sms шлюзу turbosms!");
			$this->error = $auth_result->AuthResult;
			return false;
		}
		$balance = $this->provider->GetCreditBalance();
		$this->balance = round( $balance->GetCreditBalanceResult );
		return true;
    }

    function send( $phone, $message ){
		$result = $this->provider->SendSMS(array( 
			'sender' => $this->config['sender'], 
			'destination' => $phone,
			'text' => $message 
		));
		$result = (is_array($result->SendSMSResult->ResultArray))? $result->SendSMSResult->ResultArray[0] : $result->SendSMSResult->ResultArray;
		$balance = $this->provider->GetCreditBalance();
		$this->balance = round( $balance->GetCreditBalanceResult );
		if($result!="Сообщения успешно отправлены") return $result;
		else return $this->balance.":";
    }

    function balance(){
		return $this->balance;
    }

    function __destruct(){
    }
}


class mirage{	//	Для работы с Mirage

    var $config;

    function __construct( $config ){
		$this->config = $config;
    }

    function send( $phone, $message ){
		$result=false;

		$socket = fsockopen($this->config['server'], $this->config['port'], $errno, $errstr, 10);

		if (!$socket){
			$this->error = "$errstr";
			return false;
		}else{
			foreach($this->config['dongle'] as $k=>$v) { 
				if(($conts=$this->balance())<=$v) {$dongle = $k;} 
			} 

			$data=array(
				'0'=>"Action: Login\r\n",
				'1'=>"UserName: {$this->config['login']}\r\n",
				'2'=>"Secret: {$this->config['password']}\r\n\r\n",
				'3'=>"Action: DongleSendSMS\r\n",
				'4'=>"Device: {$dongle}\r\n",
				'5'=>"Number: {$phone}\r\n",
				'6'=>"Message: {$message}\r\n\r\n",
				'7'=>"Action: Logoff\r\n\r\n"
			);
			foreach($data as $k=>$v) fputs($socket, $v);

			while (!feof($socket)){
				$str=fgets($socket);
				$res=preg_split('/:\s+/',$str,2);
				if(count($res)==2) $result[]=$res;
				echo $str;
			}
			fclose($socket);

			foreach($result as $k=>$v) {
				if(preg_match('/error/i',$v[1])) {
					$this->error=$result[$k+1][1];
					return false;
				}
			}
			return $conts.":".$dongle.":";
		}
    }

    function balance(){
		global $q, $config;
		if(!$q) $q = sql_query($config['db']);
		$c = $q->select("SELECT count(*) as c FROM sms WHERE updated>DATE(now()) AND status=2",4);
		return $c;
    }

    function __destruct(){
    }
}

class star{

	var $config, $provider;
	
    function __construct( $config ){
		$this->config = $config;
		$this->limit = 0;
		foreach($this->config['linelimit'] as $k=>$v) $this->limit += $v;
    }

	function curl_send( $param ){
		$data = array();
		$ch = curl_init();
		$server = $param['server']; unset($param['server']);
		foreach( $param as $k=>$v ) $data[] = $k.'='.urlencode($v);
		$server .= '?'.join('&',$data);

		curl_setopt($ch, CURLOPT_URL, $server );
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);

		$data = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);
		if($httpcode>=200 && $httpcode<300) {
			return $data;
		}else{ 
			$this->error = "httpcode=$httpcode";
			log_txt("star:ERROR httpcode=$httpcode URL = $server ");
			return false; 
		}
	}

	function send( $phone, $message, $id=0 ){
        $attempt = (isset($this->config['attempt']))? $this->config['attempt'] : 100;
        $repeat = false;
        $phone = preg_replace( '/^\+/', '', $phone );
		$data = array(
			'server' => $this->config['server'],
			'u' => $this->config['login'],
			'p' => $this->config['password'],
			'n' => preg_replace('/.*(\d{10,})$/','\1',$phone),
			'm' => $message
		);
		if($id>0) $data['messageid']=$id;
		$balance = $this->balance(); $limit = $this->limit;
		foreach($this->config['linelimit'] as $k=>$v) {
			$limit -= $v;
			if($balance<=$limit) continue;
			$data['l'] = $k;
			break;
		} 
		if(!isset($data['l'])) {
			$this->error = "Лимит исчерпан! ($count)";
			return false;
		}

		do {
			if($repeat) usleep(500000);
			$attempt--;
			$res = $this->curl_send($data);
			$answer = ($res)? trim(preg_replace(array('/[\r\n]/','/<[^>]*>/','/\s+/'),array(' ',' ',' '), $res)):'';
			$repeat = ($attempt>0 && $answer!='' && preg_match('/\bbusy\b/i',$answer))? true : false;
		}while($repeat);

		if($res && preg_match("/.*\b(L[0-9])\b.*to:([0-9\-]*).*\bID:([^\b]*)/",$answer,$m)) {
			$result = $balance.':'.$m[1].':'.$m[3].': '; 
		}else{
			log_txt($this->error);
			$result=false;
		}
		return $result;
	}

    function balance(){
		global $q, $config;
		if(!$q) $q = new sql_query($config['db']);
		$c = $q->select("SELECT count(*) FROM sms WHERE updated>DATE(now()) AND status=2",4);
		$c = $this->limit - $c;
		return $c;
    }

    function __destruct(){
    }
}


class smstosrv{

    var $config;

    function __construct( $config ){
		$this->config = $config;
		$this->counter = 0;
    }

	function curl_send( $param ){
		$data = array();
		$ch = curl_init();
		$server = $param['server']; unset($param['server']);
		foreach( $param as $k=>$v ) $data[] = $k.'='.urlencode($v);
		$server .= '?'.join('&',$data);

		curl_setopt($ch, CURLOPT_URL, $server );
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);

 		$data = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);
		if($httpcode>=200 && $httpcode<300){
			return $data;
		} else { 
			$this->error = "httpcode=$httpcode";
			log_txt("smstosrv:ERROR httpcode=$httpcode URL = $server ");
			return false; 
		}
	}

	function send( $phone, $message, $id=0 ){
        $phone = preg_replace( '/^\+/', '', $phone );
		$data = array(
			'smsnum' => $phone,
			'Memo' => preg_replace(array("/\\\\n/","/\\\\r/"),array(chr(10),chr(13)),$message)
		);
		if(preg_match('/^[\+]?38071/',$phone)) $data['smsprovider'] = 5;

		$data = $this->curl_send( array_merge($this->config,$data) );

		if($data) {
			preg_match('/messageid=(\d{1,})/', $data, $matches );
			if(isset($matches[1]) && is_numeric($matches[1])) {
				$result = $matches[1].":";
				$this->counter++;
			}else{
				$result = false; 
				$this->error = trim(preg_replace(array('/[\r\n]/','/\s+/','/.*<body[^>]*>/i','/<\/body>.*/i','/<[^>]*>/'),array(' ',' ','','',''),$data));
				log_txt($this->error);
			}
		}else{
			$result=false;
		}
		return $result;
	}

    function balance(){
		global $config;
		$filename = "/usr/local/sbin/scripts/sms/countsrv.txt";
		if($fdcount = @fopen( $filename,"r" )){
			$counts = fread( $fdcount, filesize( $filename ) );
			fclose( $fdcount );
		}elseif(isset($config['sms_sess_ilmit'])){
			$counts = $config['sms_sess_ilmit'] - $this->counter;
		}else{
			$counts = 300 - $this->counter;
		}
		$result = $counts;
        return $result;
    }
}

class phoenix{

    var $config, $error, $balance;

    function __construct( $config ){
		$fld = array('host','port','portAPI','login','password','attempt');
		$this->cfg = $config;
		$err = array();
		foreach($fld as $n) {
			if(!key_exists($n,$this->cfg)) $err[] = "отсутствует $n";
			elseif(!isset($this->cfg[$n])) $err[] = "$n - пустой";
		}
		if(count($err)>0) log_txt(__method__.": ERROR: ".implode(', ',$err));
		$this->cookies = false;
		$this->cookie = false;
		$this->token = false;
		if(isset($this->cfg['token'])) $this->token = $this->cfg['token'];

		$this->login_headers = array('X-Requested-With: XMLHttpRequest');
		$this->curl_opt = array(CURLOPT_USERAGENT => "Mozilla/5.0 (X11; Linux x86_64…) Gecko/20100101 Firefox/57.0");
		$this->login_opt = array(
			CURLOPT_POSTFIELDS => "username={$this->cfg['login']}&password={$this->cfg['password']}",
			CURLOPT_POST => 1,
			CURLOPT_USERAGENT => "Mozilla/5.0 (X11; Linux x86_64…) Gecko/20100101 Firefox/57.0",
			CURLOPT_HTTPHEADER => $this->login_headers
		);
    }

    function recive_cookies($n=''){
		global $cookies;
		$port = ($this->cfg['port'] == 443 || $this->cfg['port'] == 80)? '' : ':'.$this->cfg['port'];
		$url = ($this->cfg['port'] == 443)? "https://" : "http://";
		$url .= $this->cfg['host'].$port."/login";
		$result = request_http($url, false, $this->login_opt);
		if(isset($cookies) && is_array($cookies)){
			$this->cookies = $cookies;
		}else{
			log_txt(__method__.": ERROR не удалось получить cookie!");
		}
    }

    function get_cookie($n=''){
		if(!is_array($this->cookies)) $this->recive_cookies();
		if(!$n && is_array($this->cookies) && count($this->cookies)>0){
			$c = reset($this->cookies);
			$this->cookie = "Cookie: ".$c[1];
			return $this->cookie;
		}
		if($n && is_array($this->cookies) && isset($this->cookies[$n])){
			$c = $this->cookies[$n];
			$this->cookie = "Cookie: ".$c[1];
			return $this->cookie;
		}
		return false;
    }

    function get_token(){
		if($this->token) return $this->token;
		if($cookie = $this->get_cookie()){
			$url = "http://{$this->cfg['host']}:{$this->cfg['port']}/account/token";
			$headers = $this->login_headers;
			$headers[] = $cookie;
			$opt = $this->curl_opt;
			$opt[CURLOPT_HTTPHEADER] = $headers;
			if($result = request_http($url, false, $opt)){
				$this->token = trim($result);
				return $this->token;
			}
		}
		log_txt(__method__.": \tНе удалось получить токен! result = ".arrstr($result));
		return false;
    }

    function send( $phone, $message ){
		global $errors;
		if(!($token = $this->get_token())) return false;
		if(!$phone || !$message) return false;
		if(!preg_match('/(071\d\d\d\d\d\d\d)$/',$phone,$m)) return "номер др.оператора";
		$phone = $m[1];
		$SMS=array("dateSend"=>"","message"=>urlencode($message),"phonesList"=>array("$phone"));
		$url = "http://{$this->cfg['host']}:{$this->cfg['portAPI']}/sms-api/dispatches?token=$token";
		$data = json_encode(array("message" => $message, "phonesList" => array($phone)));
		$opt = array(
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($data)),
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $data
		);
		if($result = request_http($url,false,$opt)){
			return $result;
		}else return false;
	}

    function balance($type=''){
		if(!$type) return 200;
		if($type == 'all'){
			if(!($token = $this->get_token())) return false;
			$url = "http://{$this->cfg['host']}:{$this->cfg['portAPI']}/sms-api/finance/balance?token=$token";
			if($result = request_http($url)) return $result;
		}
		return false;
    }

    function __destruct() {
		if($cookie = $this->cookie){
			$url = "http://{$this->cfg['host']}:{$this->cfg['port']}/logout";
			$headers = $this->login_headers;
			$headers[] = $cookie;
			$opt = $this->curl_opt;
			$opt[CURLOPT_HTTPHEADER] = $headers;
			$result = request_http($url,false,$opt);
		}
	}
}

class dummy{

    var $config;

    function __construct( $config ){
		$this->config = $config;
		$this->counter = 0;
    }

	function send( $phone, $message, $id=0 ){
		log_txt($this->config['prefix'].": $phone : $message");
		return 1;
	}

    function balance(){
		$result = 9999;
        return $result;
    }
}
?>
