<?php

class ProviderXXX {

	function __construct($cfg){
		$this->filter = $cfg;
	}

	public function recive($r) { // здесь надо конвертировать в стандартный вид (для API) запрос и метод
		header("Content-Type: application/json");
 		if(count($r)==0 && preg_match('/([^?]*)\/([^?]*)\?(.*)/',$_SERVER['REQUEST_URI'],$m)){
			$r['command'] = preg_replace('/[^0-9A-Za-z_]/','',$m[2]);
			foreach (explode('&', $m[3]) as $chunk) {
				$param = explode("=", $chunk);
				if ($param) $r[urldecode($param[0])] = urldecode($param[1]);
			}
		}
		if(!is_array($r) || count($r)==0) return false;
		$out = array();
		if(!isset($r['command'])) $r['command'] = preg_replace('/[^?]*\/([^?]*).*/','$1',$_SERVER['REQUEST_URI']);
		if($r['command'] == 'cancel') $r['command'] = 'removepay';
		if($r['command'] == 'reconciliation') $r['command'] = 'paymentlist';
		foreach($r as $k=>$v) {
			$fn = (isset($this->filter['input'][$k]))? $this->filter['input'][$k] : $k;
			$out[$fn] = $v;
		}
		if(count($out)==0) return false;
		if(isset($out['paytime'])) $out['paytime'] = preg_replace('/(\d\d\d\d)(\d\d)(\d\d)(\d\d)(\d\d)(\d\d)/','$1-$2-$3 $4:$5:$6',$out['paytime']);
		return $out;
	}

	private function prepareResponce($r,$iteration=1){
		if(is_array($r)){
			foreach($r as $k=>$v){
				if(is_array($v)){
					$r[$k] = $this->prepareResponce($v,++$iteration);
				}elseif($k=='Status'){
					if(isset($this->filter['result'][$v])) $r[$k] = $this->filter['result'][$v];
				}elseif($k=='result'){
					$r['Status'] = $r[$k];
					unset($r[$k]);
				}elseif($k=='S'){
					$r[$k] = sprintf("%.2f",$v);
				}elseif($k=='DTran' || $k=='DStart'){
					$r[$k] = preg_replace('/[^0-9]/','',$r[$k]);
				}else $r[$k] = $v;
			}
		}
		return $r;
	}

	public function relay($r) {
		$r = $this->prepareResponce($r);
		$out = $this->createResponce($r);
		echo $out;
		return $out;
	}

	private function createResponce($r) {
#		$r = conv_utf($r);
		$result = json_encode($r);
		if(!$result) log_txt(__method__.": ERROR: ".json_error_str(json_last_error()));
		return $result;
	}
}

class ProviderXML {

	function __construct($cfg){
		$this->filter = $cfg;
	}

	public function recive($r) { // здесь надо конвертировать в стандартный вид (для API) запрос и метод
		header("Content-Type: text/xml");
		if(!is_array($r) || count($r)==0) return false;
		$out = array();
		foreach($r as $k=>$v) {
			$fn = (isset($this->filter['input'][$k]))? $this->filter['input'][$k] : $k;
			if($fn=='method' && isset($this->filter[$fn][$v])) $v = $this->filter[$fn][$v];
			$out[$fn] = $v;
		}
		if(count($out)==0) return false;
		if(isset($out['acttime'])) $out['acttime'] = preg_replace('/(\d\d\d\d)(\d\d)(\d\d)(\d\d)(\d\d)(\d\d)/','$1-$2-$3 $4:$5:$6',$out['paytime']);
		return $out;
	}

	private function prepareResponce($r,$iteration=1){
		if(is_array($r)) foreach($r as $k=>$v){
			if(is_array($v)){
				$r[$k] = $this->prepareResponce($v,++$iteration);
			}elseif(isset($this->resultName) && $k==$this->resultName && isset($this->filter['result'][$v])){
				$r[$k] = $this->filter['result'][$v];
			}elseif(preg_match('/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/',$v)){
				$r[$k] = preg_replace('/[^0-9]/','',$r[$k]);
			}else $r[$k] = $v;
		}
		return $r;
	}
	
	public function relay($r) {
		$r = $this->prepareResponce($r);
		$out = $this->createResponce($r);
		echo $out;
		return $out;
	}

	private function createResponce($r) {
		$domtree = new XmlDomConstructor('1.0', 'UTF-8');
		$domResponce = $domtree->appendChild($domtree->createElement("Response"));
		$mixed = $domtree->fromMixed($r,$domResponce);
		return $domtree->saveXML();
	}
}

class ProviderJSON {

	function __construct($cfg){
		$this->filter = $cfg;
	}

	public function recive($r) { // здесь надо конвертировать в стандартный вид (для API) запрос и метод
		header("Content-Type: application/json");
		if(!is_array($r) || count($r)==0) return false;
		$out = array();
		foreach($r as $k=>$v) {
			$fn = (isset($this->filter['input'][$k]))? $this->filter['input'][$k] : $k;
			$out[$fn] = $v;
		}
		if(count($out)==0) return false;
		if(isset($out['paytime'])) $out['paytime'] = preg_replace('/(\d\d\d\d)(\d\d)(\d\d)(\d\d)(\d\d)(\d\d)/','$1-$2-$3 $4:$5:$6',$out['paytime']);
		return $out;
	}
	
	public function relay($r) {
		if(isset($this->resultName) && isset($this->filter['result'][$r[$this->resultName]])) 
			$r[$this->resultName] = $this->filter['result'][$r[$this->resultName]];
		$out = $this->createResponce($r);
		echo $out;
		return $out;
	}

	private function createResponce($r) {
#		$r = conv_utf($r);
		$result = json_encode($r);
		if(!$result) log_txt(__method__.": ERROR: ".json_error_str(json_last_error()));
		return $result;
	}
}
?>
