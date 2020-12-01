<?php
include_once("defines.php");
include_once("log.php");
include_once("classes.php");

class Table{

	private $id, $key, $fullkey, $query, $cfg, $attr;

	public function __construct($opt=false) {
		global $config, $DEBUG;
		$this->error_counter=0;
		$this->old_error = '';
		$this->errors = array();
		$this->q = new sql_query($config['db']);
		if(is_array($opt)) {
			if(isset($opt['before_table_load']) && function_exists($opt['before_table_load']))
				$opt = $opt['before_table_load']($opt);
			$this->parse_conf($opt);
			$this->load();
		}
		if($DEBUG>5)log_txt(__CLASS__.": ".$this->name." cfg = ".@sprint_r($this->cfg));
	}

	private function parse_conf($opt){
		global $DEBUG;
		if(is_array($opt)) {
			
			if(isset($opt['q']) && is_object($opt['q'])) $this->q = $opt['q'];
			
			$this->foundrows=0;
			if(isset($opt['table_style'])) $opt['style'] = $opt['table_style'];
			$this->cfg=$opt;

			if(isset($opt['table_name'])) $this->name = $opt['table_name'];
			elseif(isset($opt['name'])) $this->name = $opt['name'];
			else $this->name = '_table';

			$this->type = (isset($opt['type']))? $opt['type'] : 'table';
			if(isset($opt['module'])) $this->module = $opt['module'];

			if(isset($opt['table_footer'])) $this->footer = $opt['table_footer'];
			else $this->footer = array();

			if(isset($opt['currentpage'])) $this->currentpage = $opt['currentpage'];
			elseif(isset($_REQUEST['page'])) $this->currentpage = $_REQUEST['page'];
			else $this->currentpage = 1;
			if(isset($opt['pagerows'])) $this->pagerows = $opt['pagerows'];
			elseif(isset($_REQUEST['pagerows'])) $this->pagerows = strict($_REQUEST['pagerows']);
			else $this->pagerows = 10;

			if(isset($opt['table_id'])) $this->id = $opt['table_id'];
			elseif(isset($opt['id'])) $this->id = $opt['id'];
			else $this->id = 0;

			if(isset($opt['table_key'])) $this->key = $opt['table_key'];
			elseif(isset($opt['key'])) $this->key = $opt['key'];
			elseif($tmpkey = $this->q->table_key($this->name)) $this->key = $tmpkey;
			else $this->key = false;
			
			$this->limit = (isset($opt['limit']))? $opt['limit'] : 'no';
			$this->linkdatatype = (isset($opt['target']))? $opt['target'] : false; // ???

			// query
			if(isset($opt['table_query'])) $this->query = $this->prepare_query($opt['table_query']);
			elseif(isset($opt['query'])) $this->query = $this->prepare_query($opt['query']);
			elseif(isset($opt['data'])) $this->query = false;
			else{
				$this->log("table->parse_conf: {$opt['name']} query not found!");
				$this->query = false;
			}

			$this->group = (isset($opt['group']))? $opt['group'] : false;

			$this->attr = array_intersect_key(array_merge(array('delete'=>'no'),$opt),
								array('class'=>0,'module'=>0,'target'=>0,'delete'=>0,'style'=>0));
			$this->attr['id'] = "{$this->type}_{$this->name}";
			if(!isset($this->attr['class'])) $this->attr['class'] = 'normal';

			if($DEBUG>5) $this->log("table->parse_conf: {$this->name} cfg = ".@sprint_r($this->cfg));
		}else{
			$this->log("table->parse_conf: opt is not array!  opt=".@sprint_r($opt));
		}
	}

	private function query_word_positions($q) {
		$sql_words = array('select','from','where','order','group','having','limit');
		$this->qlen = mb_strlen($q);
		foreach($sql_words as $w) {
			if($w=='select') $this->pos[$w] = mb_stripos($q,$w);
			else $this->pos[$w] = mb_strripos($q,$w);
		}
	}

	// парсим список таблиц в запросе
	private function table_aliases($q) {
		global $DEBUG;
		$all = array();
		$t = $this->q->tables;
		if($this->pos['from']) {
			if($this->pos['where']!==false) $len = $this->pos['where'] - $this->pos['from'] - 4;
			else $len = $this->qlen - $this->pos['from'] - 4;
			$from = mb_substr($q,$this->pos['from']+4,$len);
			$words = preg_split('/\s+/',$from);
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

	private function query_insert_id($q) {
		if(!isset($this->pos)) $this->query_word_positions($q);
		if(!isset($this->aliases)) $this->table_aliases($q);
		$where = $this->qlen;
		if($this->pos['where'] === false) {
			if($this->pos['limit']!==false) $where = $this->pos['limit'];
			if($this->pos['order']!==false) $where = min($where,$this->pos['order']);
			if($this->pos['group']!==false) $where = min($where,$this->pos['group']);
		}
		return substr_replace($q,' WHERE '.$this->fullkey."=".((is_numeric($this->id))?$this->id:"'{$this->id}'"),$where,5);
	}

	private function query_add_limit($q) {
		if(!isset($this->pos)) $this->query_word_positions($q);
		if(!isset($this->aliases)) $this->table_aliases($q);
		$where = $this->qlen;
		if($this->pos['limit'] !== false) {
			$q=mb_substr($q,0,$this->pos['limit']);
		}
		if(!preg_match('/SQL_CALC_FOUND_ROWS/',$q)) 
			$q=substr_replace($q,'SELECT SQL_CALC_FOUND_ROWS',$this->pos['select'],6);
		$this->qlen = mb_strlen($q);
		if($this->pagerows != 'all') $q .= ' LIMIT '.($this->currentpage-1)*$this->pagerows.','.$this->pagerows;
		$this->qlen = mb_strlen($q);
		return $q;
	}

	private function prepare_query($q) {
		global $DEBUG, $_REQUEST;
		$q = sqltrim($q);

		// выбираем конструкции типа :ABCD: и заменяем их данными из структуры $this->cfg['abcd'] если таковая имеется
		if(preg_match_all('/:([A-Z][A-Z]*):/',$q,$mods)) {
// 			log_txt(__METHOD__." filters: ".arrstr($mods[1]));
			foreach($mods[1] as $k=>$v) {
				$f=mb_strtolower($v); $tmp=false;
				if(isset($this->cfg[$f]) && $this->cfg[$f] != '') { // берём значение из конфига
					$tmp = $this->cfg[$f]; $method = 'cfg';
				}elseif(isset($this->cfg['defaults'][$f]) && function_exists($this->cfg['defaults'][$f])){ // берём значение из масива defaults в конфиге
					$method = 'def';
					$tmp = $this->cfg['defaults'][$f]($this->name); $method = 'func';
				}elseif(isset($_REQUEST[$f]) && is_string($_REQUEST[$f]) && $_REQUEST[$f] != '') { // берём значение из масива запроса
					$tmp = strict($_REQUEST[$f]); $method = 'req';
				}elseif(isset($this->cfg['defaults'][$f])){
					$tmp = $this->cfg['defaults'][$f]; $method = 'str';
				}
				if($tmp) {
					if($f == 'sort' && preg_match('/([a-z\.]*)(ip\b|ipaddress\b)(.*)?( desc)?/i',$tmp,$m)) $tmp = "INET_ATON({$m[1]}{$m[2]}){$m[3]}{$m[4]}";
					if($DEBUG>0) log_txt(__METHOD__." method: $method замена: /:$v:/ на '{$tmp}'");
					$q = preg_replace("/:$v:/",$tmp,$q);
				}else{
					$q = preg_replace("/:$v:/",'',$q);
				}
			}
		}

		// если задано id добавляем условие
		if($this->id>0) $q = $this->query_insert_id($q);

		// если задано лимитирование кол-ва строк то...
		if($this->limit=='yes') $q=$this->query_add_limit($q);

		if($DEBUG>0) $this->log(__METHOD__." `{$this->name}` \$this->key: {$this->key} \$this->fullkey: {$this->fullkey}");
		if($DEBUG>2) $this->log(__METHOD__." `{$this->name}` \$this->query: $q");
		return $q;
	}

	private function load() {
		global $DEBUG;
		if($this->query) {
			$this->data = $this->q->select($this->query,SELECT_WITHOUTFNAME);
			if($DEBUG>0) log_txt(__METHOD__." SQL: ".sqltrim($this->q->sql));
			$this->fields = $this->q->fields;
			$this->numrows = $this->q->num_rows;
			$tables = $this->q->sql_tables();
			if(count($tables)==1) $this->name = array_shift(array_keys($tables));
		}elseif(is_array($this->cfg['data'])) {
			$this->fields = array_keys(reset($this->cfg['data']));
			foreach($this->cfg['data'] as $k=>$row) { // создаем простой масив данных (c числовыми ключами)
				$i = 0;
				foreach($row as $n=>$v) {
					$this->data[$k][$i] = $v;
					$i++;
				}
			}
			$this->numrows = count($this->data);
		}
        if(@$this->id==0 && @$this->limit=='yes') {
			$this->foundrows = $this->q->select("SELECT FOUND_ROWS()",SELECT_SINGLEVAL);
			$this->pages = ($this->pagerows>0)? abs(ceil($this->foundrows/$this->pagerows)) : 0;
        }else{
			$this->foundrows = $this->numrows;
			$this->pages=1;
        }
		$this->arrayTHead();
		$this->arrayData();
		$this->arrayTFoot();
		if($DEBUG>0) log_txt(__METHOD__.": `{$this->name}` \$this->data: (".count($this->data).") records, ({$this->foundrows}) foundrows, (".count($this->fields).") fields");
		if($DEBUG>1) log_txt(__METHOD__.": `{$this->name}` \$this->fields: ".sprint_r($this->fields));
		if($DEBUG>2) log_txt(__METHOD__.": `{$this->name}` \$this->fields: ".sprint_r($this->data));
	}
	
	public function reload($opt=false) {
		if(is_array($opt)) { $this->parse_conf($opt); $this->load(); }
		$this->load();
	}

	private function get_pager() {
		$res='';
		if($this->limit=='yes'&&$this->pages>1){
			$res='<div class="pager" tname="'.$this->name.'" numpages="'.$this->pages.'"></div>';
		}
		return $res;
	}

	public function getHTML() {
		global $DEBUG;
		if($DEBUG>0) log_txt(__METHOD__.": `{$this->name}`");
		$html = "";
		if(isset($this->cfg['table_header'])) $html .= "<h3>{$this->cfg['table_header']}</h3>";
		elseif(isset($this->cfg['header'])) $html .= "<h3>{$this->cfg['header']}</h3>";
		if($this->pages>1) $html.=$this->get_pager();
		$html .= $this->htmlTable();
		return $html;
	}

	public function htmlTable() {
		global $DEBUG;
		if($DEBUG>0) log_txt(__METHOD__.": `{$this->name}`");
		$html = "<table ".$this->properties($this->attr).">\n";
		$html .= "<thead>\n".$this->htmlTHead()."</thead>\n";
		$html .= "<tbody>\n".$this->htmlTBody()."</tbody>\n";
		$html .= "<tfoot>\n".$this->htmlTFoot()."</tfoot>\n";
		$html .= "</table>\n";
		if($DEBUG>6) log_txt(__METHOD__.": `{$this->name}` результат:\n".$html);
		return $html;
	}

	public function htmlTHead() {
		global $DEBUG;
		if($DEBUG>0) log_txt(__METHOD__.": `{$this->name}`");
		$html='<tr>';
		foreach($this->thead as $k => $v) {
			$attr = array('num'=>$v['num'],'field'=>$v['field']);
			if(isset($v['class'])) $attr['cellclass'] = $v['class'];
			$label = (isset($v['label']))? $v['label'] : $v['field'];
			$html .= "<th ".$this->properties($attr).">$label</th>";
		}
		$html .= "</tr>";
		if($DEBUG>2) log_txt(__METHOD__.":`{$this->name}`\n".$html);
		return $html;
	}

	public function htmlTBody() {
		global $DEBUG;
		if($DEBUG>0) log_txt(__METHOD__.": `{$this->name}`");
		$this->efilter = array('class'=>0,'style'=>1); // используется для фильтра свойств элемента в ф. $this->properties()
		$html='';
		foreach($this->data as $k => $v) {
			if($this->keyfield !== false) $id = $v[$this->keyfield];
			elseif($this->key!='') {
				$fn = array_flip($this->fields);
				$id = $this->name."_".$v[$fn[$this->key]];
			}else $id = $this->name."_".$k;
			if($this->keyfield!==false) unset($v[$this->keyfield]);
			$html.="<tr id=\"{$id}\">".$this->htmlRow($v)."</tr>\n";
		}
		unset($this->efilter);
		return $html;
	}

	public function htmlTFoot() {
		global $DEBUG;
		if($DEBUG>0) log_txt(__METHOD__.": `{$this->name}`");
		$fields=$this->cfg['fields'];
		$this->efilter = array('class'=>0,'style'=>1); // используется для фильтра свойств элемента в ф. $this->properties()
		$html = "<tr>";
		if(count($this->thead)>0) {
			foreach($this->thead as $k=>$f) {
				if($f['field']==$this->key) continue;
				if(isset($this->footer[$f['field']])) { // в $this->footer накапливается сумма или кол-во и т.д.
					$html .= "<td ".$this->properties($f).">{$this->footer[$f['field']]}</td>";
				}else{
					$html .= "<td></td>";
				}
			}
		}
		unset($this->efilter);
		$html .= "</tr>\n";
		return $html;
	}

	private function htmlRow($r) {
		$fields = $this->cfg['fields'];
		$html='';
		foreach($r as $n => $v) {
			if($this->fields[$n]==$this->key) continue;
			$fn = (isset($this->thead[$n]))? $this->thead[$n] : array();
			$html .= "<td ".$this->properties($fn).">$v</td>";
		}
		return $html;
	}

	private function arrayTHead() {
		global $DEBUG;
		if($DEBUG>0) log_txt(__METHOD__.": `{$this->name}` start");
		$ff = array('num'=>0,'field'=>0,'class'=>0,'style'=>0,'label'=>0,'title'=>0);
		if(isset($this->thead)) return $this->thead;
		$denytypes = array('hidden','password');
		if(isset($this->fields['id'])) $this->keyfield = 'id';
		elseif($this->key && isset($this->fields[$this->key])) $this->keyfield = $this->key;
		else $this->keyfield = false;
		if($this->keyfield === false) $this->attr['delete'] = 'no';
		// если нет в конфиге настройки полей берем все данные с SQL
		if(!isset($this->cfg['fields']) || !is_array($this->cfg['fields']) || count($this->cfg['fields'])==0) {
			foreach($this->fields as $i => $n) {
				$this->cfg['fields'][$n] = array('label'=>$n,'type'=>'text','access'=>0);
			}
		}
		if($DEBUG>0) log_txt(__METHOD__.": `{$this->name}` требуемые поля: ".arrstr(array_keys($this->cfg['fields'])));
		$fields=array_intersect_key($this->cfg['fields'],array_flip($this->fields));
		if($DEBUG>0) log_txt(__METHOD__.": `{$this->name}` остутствующие поля: ".arrstr(
						array_keys(array_diff_key($this->cfg['fields'],array_flip($this->fields)))));
		$th=array();
		if(count($this->fields)>0) {
			foreach($this->fields as $i => $n) {
				if(!isset($fields[$n])) $noex[] = $n; // поля нет в структуре 
				elseif(array_search(@$fields[$n]['type'],$denytypes)!==false) $hidn[] = $n; // поле скрыто
				elseif(!$this->read_access($n,@$fields[$n]['access'])) $noacc[] = $n; // доступ не разрешен
				else{
					if($DEBUG>0) $fn[] = $n;
					$fields[$n] = array_merge($fields[$n],array('field'=>$n,'num'=>$i)); // добавляем в структуру поля св-ва
					if(isset($fields[$n]['table_class'])) $fields[$n]['class'] = $fields[$n]['table_class'];
					if(isset($fields[$n]['table_style'])) $fields[$n]['style'] = $fields[$n]['table_style'];
					$th[$i]=array_intersect_key($fields[$n],$ff); // убираем ненужные св-ва
					if(isset($th[$i]['style'])) $th[$i]['style'] = preg_replace('/;?height:\s*\d+[^;]*/','',$th[$i]['style']); // убираем высоту из стиля
				}
			}
			if($DEBUG>0){ 
				if(isset($noex)) log_txt(__METHOD__.": `{$this->name}` неизвестные поля: ".arrstr($noex));
				if(isset($hidn)) log_txt(__METHOD__.": {$this->name} скрытые поля: ".arrstr($hidn));
				if(isset($noacc)) log_txt(__METHOD__.": {$this->name} поля без доступа: ".arrstr($noacc));
				log_txt(__METHOD__.": {$this->name} отображаемые поля: ".arrstr($fn));
			}
		}
		if($DEBUG>2) log_txt(__METHOD__.": `{$this->name}` thead=".@sprint_r($th));
		$this->thead = $th;
	}

	private function arrayData() {
		global $DEBUG;
		if($DEBUG>0) log_txt(__METHOD__.": `{$this->name}`");
		if(!isset($this->thead)) $this->arrayTHead();
		if($DEBUG>2) log_txt(__METHOD__.": `{$this->name}` \$th = ".sprint_r($th));
		$th[$this->keyfield]=$this->key;
		$th_proc=array();
		$this->tf_method=array();
		$tf_text=array();
		$this->fv = array();
		foreach($this->thead as $k => $v) {
			$th[$k]=$v['field'];
			if(isset($this->cfg['table_triggers'][$th[$k]]) && function_exists($this->cfg['table_triggers'][$th[$k]])) 
				$th_proc[$k] = $this->cfg['table_triggers'][$th[$k]];
			if(isset($this->footer[$th[$k]])) {
				if(method_exists($this, $method = $this->footer[$th[$k]])) $this->tf_method[$k] = $method;
				else $this->fv[$k] = $method;
			}
			if(isset($th_proc)) $this->th_proc = $th_proc;
		}
		if($DEBUG>0 && $this->tf_method) log_txt(__METHOD__.": `{$this->name}` inner footer methods: ".arrstr($this->tf_method));
		if(count($this->data)>0) foreach($this->data as $k=>$v) {
			$v = array_intersect_key($v,$th);
			foreach($v as $i=>$d) {
				if(isset($th_proc[$i])) $v[$i]=$th_proc[$i]($d,$v,$this->thead[$i]['field']);
				elseif(@$this->thead[$i]['type']=='date') $v[$i] = cyrdate($d);
				if(is_null($v[$i])) $v[$i] = "";
				if(isset($this->tf_method[$i])) { $m = $this->tf_method[$i]; $this->$m($i,$d,$this->thead[$i]['field']); }
			}
			$this->data[$k] = $v;
		}elseif($DEBUG>0) log_txt(__function__.": empty data! SQL: ".$this->q->sql);
 		if($DEBUG>0 && $this->fv) log_txt(__METHOD__.": `{$this->name}` outer footer methods: ".arrstr($this->fv));
	}

	private function arrayTFoot() {
		$th[$this->keyfield] = $this->key;
		foreach($this->thead as $k => $v) {
			$th[$k]=$v['field'];
		}
		foreach($this->fv as $i=>$d) {
			if(isset($this->th_proc[$i]) && isset($this->tf_method[$i]) && ($m = $this->tf_method[$i]) != 'fcount'){
				if($DEBUG>0) log_txt(__METHOD__.": {$this->th_proc[$i]}[$i]($d)");
				$this->fv[$i] = $this->th_proc[$i]($d);
			}
		}
	}

	public function get() {
		global $DEBUG;
		$upd = isset($_REQUEST['update']) && $_REQUEST['update'] == 'on';
		$r['tname']=$this->name;
		if(isset($this->key)) $r['key']=array_search($this->key,$this->fields);
		if(isset($this->cfg['target'])) $r['target']=$this->cfg['target'];
		if(isset($this->cfg['delete'])) $r['delete']=$this->cfg['delete'];
		if(isset($this->module)) $r['module']=$this->module;
		if(isset($this->cfg['table_menu']) && !$upd) $r['table_menu']=$this->cfg['table_menu'];
		if(isset($this->cfg['fixed_menu']) && !$upd) $r['fixed_menu']=$this->cfg['fixed_menu'];
		if(isset($this->cfg['class'])) $r['class']=$this->cfg['class'];
		if(isset($this->cfg['style'])) $r['style']=$this->cfg['style'];
		if(isset($this->cfg['add'])) $r['add']=$this->cfg['add'];
		if(isset($this->cfg['limit']) && $this->cfg['limit'] == 'yes') {
			$r['limit']=$this->cfg['limit'];
			$r['pagerows']=$this->pagerows;
			$r['page']=$this->currentpage;
			$r['pages']=$this->pages;
		}else{
			$r['limit']='no';
		}
		if(isset($this->cfg['filters']) && !$upd) {
			foreach($this->cfg['filters'] as $n=>$v) $this->cfg['filters'][$n]['name'] = $n;
			$r['filters'] = $this->cfg['filters'];
		}
		if(!$upd) $r['thead']=$this->thead;
		$r['tbody']=$this->data;
		if(isset($this->cfg['before_table_send']) && function_exists($this->cfg['before_table_send'])){
			$func = $this->cfg['before_table_send'];
			$r = $func($r);
		}
		if(count($this->fv) > 0) $r['tfoot'] = $this->fv;
		if($DEBUG>0) log_txt(__METHOD__.": `{$this->name}` записей ({$this->numrows})");
		return $r;
	}

	private function read_access($fieldname,$acl) {
		global $opdata, $DEBUG;
		if($DEBUG>6) log_txt(__METHOD__.": [status]={$opdata['status']} поле `$fieldname` = access={$acl['r']}");
		if(is_array($acl) && @$acl['r'] && is_numeric($acl['r']) && $opdata['status']>=$acl['r']) return true;
		elseif(is_string($acl) && function_exists($acl)) return $acl($fieldname);
		elseif(is_numeric($acl) && $opdata['status']>=$acl) return true;
		else {
			if($DEBUG>3) log_txt(__METHOD__.": доступ (чтение) к {$this->name}[{$fieldname}] запрещен");
			return false;
		}
	}

	private function fcount($field, $val) {
		if(!isset($this->fv[$field])) $this->fv[$field] = $this->foundrows;
	}

	private function fsum($field, $val) {
		if(!isset($this->fv[$field])) $this->fv[$field] = 0;
		$this->fv[$field] += $val;
	}

	private function fmax($field, $val) {
		if(!isset($this->fv[$field])) $this->fv[$field] = $val;
		if($this->fv[$field] < $val) $this->fv[$field] = $val;
	}

	private function fmin($field, $val) {
		if(!isset($this->fv[$field])) $this->fv[$field] = $val;
		if($this->fv[$field] > $val) $this->fv[$field] = $val;
	}

	private function stripekey($k) { 
		return preg_replace('/^[^\.]*\./','',$k);
	}

	private function log($txt) {
		$mylog = 'log_txt';
		if(function_exists($mylog)) {
			$mylog($txt);
		}else{
			$this->errors[]=$txt;
		}
	}

	private function properties($o) {
		if(!is_array($o)) return '';
		$r=array();
		if(isset($this->efilter)) $o = array_intersect_key($o,$this->efilter);
		foreach($o as $k=>$v) if(is_string($v)) {
			$s = preg_replace('/"/','&#8243;',$v);
			$r[] = "$k=\"$s\"";
		}
		return implode(' ',$r);
	}

	function __destruct() {
		global $DEBUG;
		if($e = (count($this->errors))>0) {
			echo "<BR>При работе объекта Table возникло $e ошибок\n<BR><BR>";
			if($DEBUG>0) echo "\n<pre>\n".implode("\n",$this->errors)."\n</pre>\n";
		}
	}
}

function cell_fio($v) { return shortfio($v); }
function cell_access($v) { return sprintf("<a class=\"opaccess\" href=\"#\"><IMG src=\"pic/encrypted.png\"></a>",$v); }
function cell_yes_no($v) { return ($v == 0) ? "" : "<img src=\"pic/ok.png\" />"; }

function cell_pvd_typeofpay($v) { global $typeofpay; return $typeofpay[$v]; }
function cell_op_status($v) { global $oplevel; return $oplevel[$v]; }
function cell_tos($v) { global $tosname; return "Деньги: ".$tosname[$v]; }
function cell_fixed($v) { global $abonpl; return $abonpl[$v]; }
?>
