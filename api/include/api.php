<?php
include_once 'classes.php';
include_once 'utils.php';
include_once 'log.php';

class API {

	function __construct($provider,$cfg){
		$this->q = new sql_query($cfg['db']);
		$this->tr = array('contract'=>0,'payment_id'=>0,'money'=>0,'currency'=>0,'paytime'=>0,'operator'=>0,'uid'=>0);
		$this->providerName = $name = $provider['name'];
		$this->providerClass = $class = $provider['type'];
		$this->cfg = $cfg;
		$methods = array_keys($cfg['options'][$name]['output']);
		if(!is_array($methods)) {
			log_txt(__method__.": WARNING! no found methods! check config! \$cfg['options'][$name] = ".arrstr($cfg['options'][$name]));
			$methods = array('check','auth','get','set','pay','sms');
		}
		$this->methods = $methods;
		$this->myMethods = array('responce','patterning','check_input','logout','login','perform');
		$options = $cfg['options'][$name];
		if(!is_array($options)) {
			log_txt(__method__.": WARNING! no found options! check config!");
			$options = array(
				'result'=>array('NONE'=>0,'OK'=>1,'ERROR'=>3,'FATAL'=>2),'input'=>array(),
				'output'=>array('check'=>array(),'pay'=>array())
			);
		}
		$this->options = $options;
		if(class_exists($class))
			$this->provider = new $class($cfg['options'][$name]);
		else{
			log_txt(__method__.": class '$class' not exists ");
			$this->provider = new DefaultProvider($cfg['options'][$name]);
		}
	}

	function check_input($val) { // Проверка входящих данных ключ, значение
		if(is_array($val)||is_object($val)) {
			foreach($val as $k => $v) {
				if(preg_match('/[^0-9A-Za-z_]/',$k)) {
					$key = preg_replace('/[^0-9A-Za-z_]/','',$k);
					log_txt(__method__.": ERROR forbidden key: '$k'");
					unset($val[$k]);
					$val[$key] = $v;
					$k = $key;
				}
				if(is_array($v) || is_object($v)) { 
					$val[$k] = $this->check_input($v); 
				}elseif(is_string($v)){
					$val[$k] = $this->q->escape_string($v);
				}
			}
			return $val;
		}elseif(is_string($val)){
			return $this->q->escape_string($val);
		}else{
			return $val;
		}
	}

	function perform($r){ // Основная обработка запроса
		global $_COOKIE, $_SESSION, $_SERVER;
		if(isset($r[session_name()]) || isset($_COOKIE[session_name()])) {
			session_start();
			if(isset($_SESSION['sess_ip'])) {
				$this->op = isset($_SESSION['sess_op'])? $_SESSION['sess_op'] : 'TERMINAL';
				if($_SESSION['sess_ip'] != $_SERVER['REMOTE_ADDR']) {
					unset($this->op);
					session_destroy();
					$this->responce(array('result'=>'ERROR'));
					return false;
				}
			}else{ session_destroy(); }
		}
		if(!($in = $this->provider->recive($r))){
			$this->responce(array('result'=>'ERROR','note'=>"unknown data recive"));
			log_txt(__method__.": {$this->providerClass}->resive return ".arrstr($in));
			return false;
		}
		$in = $this->check_input($in);
		if(!isset($in['method']) || !method_exists('API',$in['method'])) {
			$this->responce(array('result'=>'ERROR','note'=>"method not found"));
			log_txt(__method__.": method not found! ".arrstr($in));
			return false;
		}
		$this->method = $method = $in['method']; unset($in['method']);
		if(array_search($method,$this->methods) === false || array_search($method,$this->myMethods)) {
			$this->responce(array('result'=>'ERROR','note'=>"method '$method' not allowed"));
			log_txt(__method__.": method '$method' not found in ".arrstr($this->methods));
			return false;
		}
		return $this->$method($in);
	}

	function redirect($r){
		$rd = @$this->options['redirect'];
		if(!$rd || !is_array($rd)) return false;
		foreach($rd as $name=>$a){
			// добавляем в адрес запроса по шаблону в конфиге нужные параметры
			if(preg_match_all('/:([A-Z][A-Z_]*):/',$a,$act)) {
				foreach($act[1] as $k=>$v) {
					$f=mb_strtolower($v);
					$tmp = isset($r[$f])? $r[$f] : '';
					if(!$tmp) {
						log_txt(__method__.": Ошибка в параметре `$f` \$r[$f] = ".arrstr($r[$f]));
						return false;
					}
					$a = preg_replace("/:{$v}:/","$tmp",$a);
				}
			}
			$result = request_http($a);
			if($result && is_array($result)){
				log_txt("Перенаправление на '$name' - результат: {$result['result']}".(isset($result['note'])?" {$result['note']}":""));
			}elseif($result && preg_match('/<result>(.*)<\/result>/',$result,$m))
				log_txt("Перенаправление на '$name' - результат: {$m[1]}");
		}
	}

	function check($r){
		$p = new payment($this->cfg);
		$client = $p->check($r);
		if(!$client) {
			$this->responce(array('result'=>'NONE','note'=>"client not found"));
			log_txt(__method__.": client not found! ".implode('; ',$client->errors));
			return false;
		}
		return $this->responce($client);
		return true;
	}

	function auth($r) {
		global $_SESSION, $_SERVER;
		$q = new sql_query($this->cfg['db']);
		if(isset($r['login']) && isset($r['pass'])) {
			$this->op = $q->select("SELECT *, unique_id as id, status as level FROM operators WHERE login='{$r['login']}' and pass='{$r['pass']}' and blocked=0",1);
			if ($this->op) {
				session_start();
				$_SESSION['sess_ip'] = $_SERVER['REMOTE_ADDR'];
				$_SESSION['sess_op'] = $this->op;
				log_txt("подключился с IP=".$_SESSION['sess_ip']);
				$this->responce();
				return true;
			}else{
				log_txt("API: попытка подключения login:{$r['login']} pass:{$r['pass']} с IP=".$_SERVER['REMOTE_ADDR']);
			}
		}
		$this->responce(array('result'=>'ERROR','note'=>"login filed"));
		return false;
	}

	function logout($r){
		global $_COOKIE, $_SESSION, $_SERVER;
		if(isset($r[session_name()]) || isset($_COOKIE[session_name()])) {
			session_start();
			$this->op = $_SESSION['sess_op'];
			log_txt("отключился с IP=".$_SESSION['sess_ip']);
			session_destroy();
			$this->responce();
			return true;
		}
		$this->responce(array('result'=>'ERROR','note'=>"logout filed"));
		return false;
	}

	function get($r){
		$client = new user($r);
		if(!$client->data){
			$this->responce(array('result'=>'NONE','note'=>"client not found"));
			log_txt(__method__.": client not found! ".implode('; ',$client->errors));
			return false;
		}
		$c = $client->data;
		$c['tarif'] = $client->tarif['name'];
		$this->responce($c);
	}

	function set($r){
		$client = new user($r);
		if(!$client->data){
			$this->responce(array('result'=>'NONE','note'=>"client not found!"));
			return false;
		}
		$new = $client->change($r);
		$this->responce($new);
	}

	function pay($r){
		$filter = array('card'=>0,'unique_id'=>1,'money'=>2,'acttime'=>3,'paytime'=>4,'from'=>5,'note'=>6,'fio'=>7);
		foreach(array('payment_id','money') as $n) if(!isset($r[$n])){
			$opt = array_flip($this->options['input']);
			log_txt(__method__.": '{$opt[$n]}' not exists in ".arrstr($r));
			$this->responce(array('result'=>'NONE','note'=>"payment record not valid"));
			return false;
		}
		$p = new payment($this->cfg);
		$povod = $this->q->select("SELECT povod_id FROM povod WHERE povod like 'TERMINAL%' LIMIT 1",4);
		if($povod) $r['povod_id'] = $povod;
		if(!isset($r['paytime'])) $r['paytime'] = date2db();
		$id = $p->pay($r);
		if($p->payment_result == 'PAY_ERROR') {
			$this->responce(array('result'=>'NONE','note'=>implode(', ',$p->errors)));
			return false;
		}
		if($p->payment_result == 'PAY_EXIST') {
			$payment = array_merge($p->user->data,$p->payment);
			$out = array_intersect_key($payment,$filter);
			$out['result'] = 'ERROR';
			if($this->options['reverce_result_on_pay_exists']) $out['result'] = 'OK';
			$out['note'] = 'pay exists';
			$this->responce($out);
			return false;
		}
		if($p->payment_result == 'PAY_ACCEPT') {
			$o = array_intersect_key(array_merge($p->user->data,$p->payment),$filter);
			$this->responce($o);
			$this->redirect($r);
			return true;
		}
		$this->responce(array('result'=>'ERROR','note'=>"payment module filed"));
		log_txt(__method__.": ERROR payment module filed!");
		return true;
	}

	function removepay($r){
		$filter = array('card'=>0,'unique_id'=>1,'money'=>2,'acttime'=>3,'paytime'=>4,'from'=>5,'note'=>6, 'fio'=>7);
		$opt = array_flip($this->options['input']);
		if(!isset($r['from'])) $r['from'] = $this->providerName;
		foreach(array('payment_id') as $n) if(!isset($r[$n])){
			log_txt(__method__.": '$n(".(isset($opt[$n])? $opt[$n]:'').")' not exists in ".arrstr($r));
			$this->responce(array('result'=>'NONE','note'=>"payment record not valid"));
			return false;
		}
		$p = new payment($this->cfg);
		if($res = $p->remove_pay($r)) {
			$payment = array_merge($p->user->data,$res);
			$this->responce(array_intersect_key($payment,$filter));
			return true;
		}
		$this->responce(array('result'=>'ERROR','note'=>"{$p->error}"));
		return true;
	}

	function report($r){
		$period = array('begin'=>1,'end'=>2);
		$opt = $this->options['output'][$this->method];
		if(isset($this->providerName)) $r['from'] = $this->providerName;
		foreach($period as $n=>$v) {
			if(!isset($r[$n])){
				log_txt(__method__.": '$n(".(isset($opt[$n])? $opt[$n]:'').")' not exists in ".arrstr($r));
				$this->responce(array('result'=>'NONE','note'=>"payment record not valid"));
				return false;
			}
			$r[$n] = preg_replace('/[^0-9]/','',$r[$n]);
			if(!preg_match('/^(....)(..)(..)(..)?(..)?(..)?/',$r[$n],$m)){
				log_txt(__method__.": '$n ({$opt[$n]})' not valid ".arrstr($r));
				$this->responce(array('result'=>'NONE','note'=>"{$opt[$n]} not valid"));
				return false;
			}
			for($i=4;$i<7;$i++) if(!isset($m[$i])) $r[$n] .= "00";
			$r[$n] = preg_replace('/^(....)(..)(..)(..)(..)(..)/','$1-$2-$3 $4:$5:$6',$r[$n]);
		}
		$p = new payment($this->cfg);
		if($res = $p->payment_list($r)) {
			$out = array();
			foreach($res as $i=>$pay){
				foreach($pay as $n=>$v) {
					if(isset($opt[$n])) $out[$i][$opt[$n]] = $v;
				}
				if(isset($opt['object'])) $out[$i]['object'] = $opt['object'];
			}
			$this->responce(array('report'=>$out,'result'=>'OK'));
		}else{
			$this->responce(array('result'=>'ERROR','note'=>"{$p->error}"));
		}
		return true;
	}

	function sms($r){
		if(!isset($r['message'])){
			$this->responce(array('result'=>'FATAL','note'=>'no message'));
			return false;
		}
		if(!isset($r['phone'])){
			$this->responce(array('result'=>'FATAL','note'=>'phone not found'));
			return false;
		}
		$rad = new radius();
		if(!$rad->send_sms($r['phone'],$r['message'])){
			$this->responce(array('result'=>'ERROR','note'=>"sms not sended!"));
			return false;
		}
		$this->responce();
		return true;
	}

	function patterning($r, $template=false){ // расстановка данных по шаблону
		if(!is_array($r) || count($r)==0) {
			log_txt(__method__.": data is empty!");
			return false;
		}
		if($template===false) {
			$template = $this->options['output'][($this->method)?$this->method:'default'];
			if(!is_array($template)) {
				log_txt(__method__.": options[{$this->providerClass}][output] is not array! Check config! template: ".arrstr($template));
				return $r;
			}
		}
		$out = array();
		if($r['result'] != 'OK') $template['note'] = 'note';
		foreach($template as $k=>$v) {
			if(is_array($v)) {
				$out[$k] = $this->patterning($r, $v);
			}else{
				if($k == 'result') $this->provider->resultName = $v;
				if(isset($r[$k])) $out[$v] = $r[$k];
			}
		}
		return $out;
	}

	function responce($r=array()){ // использование объекта провайдера для ответа
		if(is_array($r) && !isset($r['result'])) $r['result'] = 'OK';
		$out = $this->patterning($r);
		$answer = $this->provider->relay($out,$this);
	}
}

class XmlDomConstructor extends DOMDocument {

	public function fromMixed($mixed, DOMElement $domElement = null) {
		if (is_null($domElement)) $domElement = $this;
		if (is_array($mixed)) {
			foreach( $mixed as $index => $mixedElement ) {
				if ( is_int($index) ) {
					if ( $index == 0 ) {
						$node = $domElement;
					}else{
						$node = $this->createElement($domElement->tagName);
						$domElement->parentNode->appendChild($node);
					}
				}else{
					if(preg_match('/^([^ ]+) ([^= ]*)=([^= ]*)$/',$index,$m)){
						$node = $this->createElement($m[1]);
						$node->setAttribute($m[2],$m[3]);
					}else{
						$node = $this->createElement($index);
					}
					$domElement->appendChild($node);
				}
				$this->fromMixed($mixedElement, $node);
			}
		}else{
			$domElement->appendChild($this->createTextNode($mixed));
		}
	}
}

class DefaultProvider {

	function __construct($cfg){
		$this->filter = $cfg;
	}

	public function recive($r) { // здесь надо конвертировать в стандартный вид (для API) запрос и метод
		if(!is_array($r) || count($r)==0) return false;
		$out = array();
		foreach($r as $k=>$v) {
			$fn = (isset($this->filter['input'][$k]))? $this->filter['input'][$k] : $k;
			$out[$fn] = $v;
		}
		if(count($out)==0) return false;
		if(isset($out['acttime'])) $out['acttime'] = preg_replace('/(\d\d\d\d)(\d\d)(\d\d)(\d\d)(\d\d)(\d\d)/','$1-$2-$3 $4:$5:$6',$out['paytime']);
		header("Content-Type: text/xml");
		return $out;
	}
	
	public function relay($r) {
		if(isset($this->filter['result'][$r['result']])) 
			$r['result'] = $this->filter['result'][$r['result']];
		$out = $this->createResponce($r);
		echo $out;
		return $out;
	}

	private function createResponce($r) {
		$domtree = new XmlDomConstructor('1.0', 'UTF-8');
		$domResponce = $domtree->appendChild($domtree->createElement("response"));
		$mixed = $domtree->fromMixed($r,$domResponce);
		return $domtree->saveXML();
	}
}
?>
