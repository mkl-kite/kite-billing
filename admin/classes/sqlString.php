<?php

include_once("utils.php");

class SQLstring{

	function __construct($sql){
		$sql = trim(preg_replace(array('/\n/','/\s+/'),array(' ',' '),$sql));
		$this->pos = array();
		$this->word_positions($sql);
		$this->table_aliases($sql);
		$this->query = $sql;
		$this->sql_words = array('select','from','where','group','order','having','limit');

    }

	private function word_positions($q) {
		$sqlw = $this->sql_words;
		$this->qlen = mb_strlen($q);
		foreach($sqlw as $w) $this->pos[$w] = mb_strripos($q,$w);
		foreach($sqlw as $i=>$w) {
			if(!$this->pos[$w]) {
				for($k=$i+1; $k < count($sqlw); $k++) {
					if($this->pos[$sqlw[$k]] !== false) {
						$this->vpos[$w] = $this->pos[$sqlw[$k]];
						break;
					}
				}
				if(!isset($this->vpos[$w])) $this->vpos[$w] = $this->qlen;
			}else{
				$this->vpos[$w] = $this->pos[$w];
			}
			if($i>0) $this->section[$sqlw[$i-1]] = mb_substr($q,$this->vpos[$sqlw[$i-1]],$this->vpos[$w]-$this->vpos[$sqlw[$i-1]]);
		}
	}

	function add_limit($page=1,$pagerows=10,$q='') {
		$limit = '';
		if($q == '') $q = $this->query;
		if(!is_numeric($page)) $page = 1;
		if(!is_numeric($pagerows) && $pagerows!='all') $pagerows = 10;
		if($this->pos['limit'] !== false) {
			$q=mb_substr($q,0,$this->pos['limit']);
		}
		if(!preg_match('/SQL_CALC_FOUND_ROWS/',$q)) 
			$q=substr_replace($q,'SELECT SQL_CALC_FOUND_ROWS',$this->pos['select'],6);
		if($pagerows != 'all') $limit = ' LIMIT '.($page-1)*$pagerows.','.$this->pagerows;
		$q=substr_replace($q,$limit,$this->vpos['limit'],5);
		return $q;
	}

	private function table_aliases($q) { // парсим список таблиц в запросе
		global $DEBUG;
		$all = array();
		$t = $this->q->tables;
		if($this->pos['from']) {
			$len = $this->vpos['where'] - $this->pos['from'] - 4;
			$from = mb_substr($q,$this->pos['from']+4,$len);
			$words = preg_split("/\s+,?\s*|\s*,\s*/",trim($m));
			$lock = false; $table = '';
			foreach($words as $i=>$v) {
				$w = preg_replace('/[^a-z_]/','',$v);
				if($w=='') continue;
				$res = array_search($w,$t);
				if($lock && $table!='') {
					$all[$table] = $w;
					$lock=false;
					$table = '';
				}elseif($lock && $table=='') {
					log_txt(__METHOD__.": ERROR, from='$from'");
					$lock=false;
				}elseif($res!==false && $table=='') {
					$table = $w;
				}elseif($res!==false && $table!='') {
					$all[$table] = $table;
					$table = $w;
				}elseif($res===false && $table!='' && $w=='as') {
					$lock=true;
				}elseif($res===false && $table!='' && $w!='as') {
					$all[$table] = $w;
					$lock=false;
					$table = '';
				}
			}
			if($res!==false && $table!='' && $w!='as') $all[$table] = $w;
		}
		if($DEBUG>2) log_txt(__METHOD__.": `{$this->name}` aliases = ".sprint_r($all));
		$this->aliases = $all;
		return $all;
	}

	private function insert_id($key, $id, $q='') {
		if($q == '') $q = $this->query;
		if(!is_numeric($id)) $id = '""';
		$key = preg_replace('/[^0-9a-z_.]/','',strtolower($key));
		return substr_replace($q," WHERE $key=$id",$this->vpos['where'],5);
	}

	private function prepare($cfg=false,$q='') {
		global $DEBUG, $_REQUEST;

		// выбираем конструкции типа :ABCD: и заменяем их данными из структуры $cfg['abcd'] если таковая имеется
		if(preg_match_all('/:([A-Z][A-Z]*):/',$q,$mods)) {
			if($DEBUG>0) log_txt(__METHOD__." filters: ".arrstr($mods[1]));
			foreach($mods[1] as $k=>$v) {
				$f=strtolower($v); $tmp=false;
				if(key_exists($f,$cfg) && $cfg[$f] != '') {
					$tmp = $cfg[$f]; $method = 'cfg';
				}elseif(key_exists($f,$_REQUEST) && is_string($_REQUEST[$f]) && $_REQUEST[$f] != '') {
					$tmp = strict($_REQUEST[$f]); $method = 'req';
				}
				if($tmp) {
					if($DEBUG>0) log_txt(__METHOD__." $method замена: /:$v:/ на '{$tmp}'");
					if($f == 'sort') $tmp = preg_replace('/([a-z]*ipaddress\b)/','INET_ATON(\1)',$tmp);
					$q = preg_replace("/:$v:/",$tmp,$q);
				}else{
					// проверяем пуст ли раздел
					$sw = $this->sql_words;
					foreach($sw as $i=>$w)  {
						$pos = mb_strripos($q,":$v:");
						$pos1 = (isset($this->vpos[$sw[$i+1]]))? $this->vpos[$sw[$i+1]] : $this->qlen;
						if($pos >= $this->vpos[$w] && $pos < $pos1) {
							
						}
					$len = $this->vpos[];
					}
					
					$q = preg_replace("/:$v:/",'',$q);
				}
			}
		}

		// если задано id добавляем условие
		if($this->id>0) $q = $this->query_insert_id($q);

		// если задано лимитирование кол-ва строк то...
		if($this->limit=='yes') $q=$this->add_limit($q);

		if($DEBUG>0) $this->log(__METHOD__." `{$this->name}` \$this->key: {$this->key} \$this->fullkey: {$this->fullkey}");
		if($DEBUG>2) $this->log(__METHOD__." `{$this->name}` \$this->query: $q");
		return $q;
	}
}
?>
