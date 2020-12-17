<?php
include_once("defines.php");
include_once("classes.php");
include_once("table.php");

class Form extends HtmlElement {

	function __construct($conf) {
		global $DEBUG;
		if($DEBUG>0) log_txt(__METHOD__.":");
		$this->q = new sql_query($conf['db']);
		$this->footer = array(
			'cancelbutton'=>array('type'=>'button','class'=>'submit-button','value'=>'Отменить'),
			'submitbutton'=>array('type'=>'submit','class'=>'submit-button','value'=>'Сохранить')
		);
		$this->form_pattern = array( // разрешенные имена полей формы к отправке клиенту
		'name'=>0,'header'=>0,'footer'=>0,'label'=>0,'id'=>0,'action'=>0,'method'=>0,'class'=>0,'style'=>0,'sub'=>0,
		'fields'=>0,'layout'=>0,'force_submit'=>0,'setNewVal'=>0,'focus'=>0);
		$this->fattr = array(
			'hidden'=>	array('id'=>0,'name'=>0,'type'=>0,'value'=>0),
			'text'=>	array('id'=>0,'class'=>0,'style'=>0,'name'=>0,'type'=>0,'value'=>0,'tabindex'=>0,'readonly'=>0),
			'checkbox'=>array('id'=>0,'class'=>0,'style'=>0,'name'=>0,'type'=>0,'value'=>0,'tabindex'=>0,'checked'=>0),
			'date'=>	array('id'=>0,'class'=>0,'style'=>0,'name'=>0,'type'=>0,'value'=>0,'tabindex'=>0,'readonly'=>0),
			'nofield'=>	array('id'=>0,'class'=>0,'style'=>0,'name'=>0),
			'password'=>array('id'=>0,'class'=>0,'style'=>0,'name'=>0,'type'=>0,'value'=>0,'tabindex'=>0,'readonly'=>0),
			'textarea'=>array('id'=>0,'class'=>0,'style'=>0,'name'=>0,'tabindex'=>0),
			'autocomplete'=>array('id'=>0,'class'=>0,'style'=>0,'name'=>0,'type'=>0,'value'=>0,'tabindex'=>0,'extra'=>0),
			'photo'=>	array('id'=>0,'class'=>0,'style'=>0,'src'=>0),
			'map'=>		array('id'=>0,'class'=>0,'style'=>0,'type'=>0,'value'=>0),
			'subform'=>	array('id'=>0,'class'=>0,'style'=>0,'sub'=>0)
		);
	}
	
	private function checkCondition($o,$new=false,$old=false) { // делает проверки структуры конфигурации
		global $_REQUEST, $DEBUG;
		if(@is_string($o['before_check']) && function_exists($o['before_check'])) $o = $o['before_check']($o,$this);
		if(isset($o['q']) && is_object($o['q'])){
			$this->q_main = $this->q; $this->q = $o['q'];
		}elseif(@$this->q_main){
			$this->q = $this->q_main;  $this->q_main = false;
		}
		// name
		if(isset($o['form_name']) && $o['form_name']!='') $this->name = $o['form_name'];
		elseif(isset($o['name']) && $o['name']!='') $this->name = $o['name'];
		else stop(array('result'=>'ERROR','desc'=>"Ошибка в параметрах формы: name='{$o['name']}'"));
		if($DEBUG>0) log_txt(__METHOD__.":{$this->name}");

		// key
		if(isset($o['form_key']) && $o['form_key']!='') $this->key=$o['form_key'];
		elseif(isset($o['key']) && $o['key']!='') $this->key=$o['key'];
		elseif($tmpkey = $this->q->table_key($this->name)) $this->key = $tmpkey;
		else $this->key='id';
		$fields = array();

		// id
		if(isset($o['id'])) $this->id=$o['id'];
		elseif(isset($new['id']) && !isset($new[$this->key])) $this->id = $new['id'];
		elseif(isset($new[$this->key])) $this->id = $new[$this->key];
		elseif(isset($_REQUEST['id']) && !isset($_REQUEST[$this->key])) $this->id=str($_REQUEST['id']);
		elseif(isset($_REQUEST[$this->key])) $this->id = str($_REQUEST[$this->key]);
		else stop(array('result'=>'ERROR','desc'=>"Ошибка в параметрах формы: id не определен"));

		// log
		if(isset($o['form_log']) && $o['form_log'] == 'no') $this->log = false;
		else $this->log = true;

		// record
		if(is_string(@$o['record']) && function_exists(@$o['record'])){
			$o['record'] = $o['record']($this->id,$this);
		}
		if(is_array(@$o['record']) && count($o['record'])>0) {
			$chk = array_intersect_key($o['record'],$o['fields']);
			if(is_array($chk) && count($chk)>0){
				$this->record = $chk;
				foreach($chk as $k=>$v) $fields[$k] = '';
			}else log_txt(__METHOD__.": record do not match fields, difference="
				.arrstr($this->q->compare_keys($o['fields'],$o['record'])));
		}

		if(isset($o['table_triggers'])) $this->table_triggers = $o['table_triggers'];
		$this->obj = (isset($o['object']))? $o['object'] : '';
		$this->condition = (isset($o['filter']))? $o['filter'] : '';
		if(!isset($o['fields']) || !is_array($o['fields']) || count($o['fields'])==0){
			log_txt(__METHOD__.": fields: ".count($o['fields']));
			stop(array('result'=>'ERROR','desc'=>"form `{$this->name}`. Ошибка в конфигурации!"));
		}

		// query
		if(isset($o['table_query']) && $o['table_query']!='') $this->table_query=sqltrim($o['table_query']);
		elseif(isset($o['query']) && $o['query']!='') $this->table_query=sqltrim($o['query']);
		else $this->table_query = false;

		if(!isset($this->record)){
			if(isset($o['form_query']) && $o['form_query']!='') $this->query=sqltrim($o['form_query']);
			elseif(isset($o['query']) && $o['query']!='') $this->query=sqltrim($o['query']);
			else stop(array('result'=>'ERROR','desc'=>"Ошибка в параметрах формы [{$this->name}]: query не определено"));

			$this->query = preg_replace('/:FILTER:/',$this->condition,$this->query);
			$this->query = preg_replace('/:OBJECT:/',$this->obj,$this->query);

			if($this->id!='' && $this->key!='') $this->query = $this->add_filter_to_query($this->query,$this->id);

			// fields: в первую очередь берутся названия полей из SQL запроса, если встречена * то поля берутся из таблицы в базе
			$this->star = false;
			if(preg_match('/SELECT (.*) FROM/i',$this->query,$m)) {
				$tmp = preg_split('/,/',$m[1]);
				$this->star = preg_match('/\*/',$m[1]);
				foreach($tmp as $f) $fn[] = preg_replace(array('/`/','/^.*\s+/','/.*\./'),array('','',''),trim($f));
				$fields = array_flip($fn);
			}else{
				log_txt(__METHOD__.": `{$this->name}` ERROR SQL: {$this->query}");
				stop(array('result'=>'ERROR','desc'=>"form `{$this->name}`. Ошибка в SQL!"));
			}
		}
		if(count($fields)>0 && !@$this->star)
			$this->fields = array_intersect_key($o['fields'],$fields);
		else 
			$this->fields = $this->q->table_fields($this->name);
		if($DEBUG>1) log_txt(__METHOD__." \$this->fields = ".arrstr($this->fields));

		// type
		$o['type']='form';

		$this->_go=(isset($o['go']))? $o['go'] : $this->strict(@$_REQUEST['go']);
		$this->_do=(isset($o['do']))? $o['do'] : $this->strict(@$_REQUEST['do']);
		$this->cfg = $o;
		return $o;
	}

	private function add_filter_to_query($q, $id) { // Добавляет условие в SQL запрос
		global $DEBUG;
		unset($this->table_aliases);
		$q = preg_replace('/:[A-Z][A-Z]*:/','',$q);

		if(preg_match('/WHERE/i',$q)) {
			$wherepos=mb_strrpos($q,'WHERE');
			$wextst=true;
		}else{
			$wherepos=mb_strlen($q);
			$wextst=false;
		}
		$frompos=mb_strrpos($q,'FROM'); if($frompos===false) $frompos=$wherepos;
		$limitpos=mb_strrpos($q,'LIMIT'); if($limitpos!==false && !$wextst) $wherepos=min($wherepos,$limitpos);
		$havingpos=mb_strrpos($q,'HAVING'); if($havingpos!==false && !$wextst) $wherepos=min($wherepos,$havingpos);
		$orderpos=mb_strrpos($q,'ORDER'); if($orderpos!==false && !$wextst) $wherepos=min($wherepos,$orderpos);
		$grouppos=mb_strrpos($q,'GROUP'); if($grouppos!==false && !$wextst) $wherepos=min($wherepos,$grouppos);
		if($frompos<mb_strlen($q)) {
			$tn = mb_substr($q,$frompos+5,$wherepos-($frompos+5));
			$tn = preg_replace(array("/LEFT|RIGHT/i","/INNER|OUTER/i","/\s*JOIN/i"),array("","",","),$tn);
			$tn = preg_split('/,\s*/',preg_replace(array('/ as /i','/\s+/'),array(' ',' '),$tn));
			foreach($tn as $i=>$n) $tn[$i] = preg_replace('/\bON\s+.*/i','',$n);
			foreach($tn as $i=>$n) {
				if(preg_match('/\s+/',trim($n))) $tmp = preg_split('/\s+/',trim($n));
				else $tmp = trim(preg_replace('/[^A-Za-z_]/','',trim($n)));
				if(is_array($tmp)) $this->table_aliases[$tmp[0]] = $tmp[1];
			}
		}
		if($DEBUG>2) log_txt(__METHOD__.": `{$this->name}` \$this->table_aliases=".sprint_r($this->table_aliases));
		if(isset($this->table_aliases) && isset($this->table_aliases[$this->name])) {
			$this->keyprefix = $this->table_aliases[$this->name].".";
			if($DEBUG>0) log_txt(__METHOD__.": `{$this->name}` keyprefix={$this->keyprefix}");
		}else{
			$this->keyprefix = '';
		}
		if($wherepos<mb_strlen($q) && $wextst) 
			$r=substr_replace($q,' WHERE '.$this->keyprefix.$this->key."=".(is_string($id)? "'$id'":$id)." AND ",$wherepos,6);
		elseif($wherepos<mb_strlen($q) && !$wextst)
			$r=substr_replace($q,' WHERE '.$this->keyprefix.$this->key."=".(is_string($id)? "'$id'":$id)." ",$wherepos);
		else
			$r=$q.' WHERE '.$this->keyprefix.$this->key."=".(is_string($id)? "'$id'":$id);

		if($DEBUG>0) log_txt(__METHOD__.":{$this->name} return: {$r}");
		return $r;
	}
	
	function getnew($f) { // создает скелет формы для ввода новых данных через jquery.popupForm.js
		global $DEBUG, $_REQUEST;
		if($DEBUG>0) log_txt(__METHOD__.":{$this->name}");
		if(isset($f['before_new']) && function_exists($f['before_new'])) $f = $f['before_new']($f,$this);
		if(!isset($f['id']) || !is_numeric($f['id'])) $f['id']='new';
		$f=$this->checkCondition($f);
		$this->_do=(@$f['do'])? $f['do']:'save';
		if($f['id']=='new') $f['force_submit']=1;
		return $this->prepareForm($f);
	}
	
	function get($f) { // создает скелет формы для редактирования данных через jquery.popupForm.js
		global $DEBUG, $_REQUEST;
		if($DEBUG>0) log_txt(__METHOD__.":{$f['name']}");
		if((@$f['id']=='new' || @$_REQUEST['id']=='new' || @$_REQUEST[@$f['key']]=='new') && @$_REQUEST['table']==$f['name']){
			if(isset($f['before_new']) && function_exists($f['before_new'])) $f = $f['before_new']($f,$this);
			$f['id']='new';
			$f['force_submit']=1;
		}else{
			if(isset($f['before_edit']) && function_exists($f['before_edit'])) $f = $f['before_edit']($f,$this);
		}
		$f=$this->checkCondition($f);
		if(@$f['id'] != 'new') $f['id']=$this->id;
		$this->_do=(@$f['do'])? $f['do']:'save';
		return $this->prepareForm($f);
	}
	
	function buildLayout($fields,$layout=array()) { // Функция формурующая fieldset
		global $DEBUG;
		if($DEBUG>0) log_txt(__METHOD__.":{$this->name}");
		$done=array(); $cr=array('id'=>0,'class'=>0,'style'=>0);
		$html="<div ".$this->properties(array('class'=>'field-container')).">\n";
		foreach($layout as $k=>$v) {
			if(isset($v['type']) && $v['type']=='fieldset') {
				$v['id'] = $k;
				$html.="<fieldset ".$this->properties(array_intersect_key($v,$cr)).">\n";
				$html.="<legend>{$v['legend']}</legend>\n";
				if(isset($v['fields'])) foreach($v['fields'] as $i=>$fieldname) {
					$html.=$this->create($fields[$fieldname]);
					$done[$fieldname]=1;
				}
				if(isset($v['target'])) {}
				$html.="</fieldset>\n";
			}else{
				$html.=$this->create($fields[$k]);
				$done[$k]=1;
			}
		}
		// Если поле не попало в шаблон то добавить его
		foreach($fields as $k=>$f) if(!isset($done[$k])) $html.=$this->create($f);
		return $html."</div>\n";
	}
	
	function getHTML($form){ // формирует html форму
		global $DEBUG;
		if($DEBUG>0) log_txt(__METHOD__.":{$this->name}");
		$f=$this->get($form);
		$f['form']['method']='POST';
		$fields = $f['form']['fields'];
		if(isset($f['form']['layout'])) {
			$layout = $f['form']['layout'];
			unset($f['form']['layout']);
		}else{
			$layout = false;
		}
		$footer = $this->footer;
		if(isset($f['form']['footer']) && is_array($f['form']['footer']) && count($f['form']['footer'])>0) {
			$footer = array_merge_recursive($footer,$f['form']['footer']);
			unset($f['form']['footer']);
		}
		unset($f['form']['fields']);
		$html="<form ".$this->properties($f['form']).">\n";
		if(isset($f['form']['header'])) $html.="<div id=\"header\" class=\"form-item\">{$f['form']['header']}</div>\n";
		
		foreach($fields as $k=>$d) {
			if(!isset($d['name']) || $d['name']!=$k) $fields[$k]['name']=$k;
			$d['name']='old_'.$k;
			$d['type']='hidden';
			$html.=$this->create($d);
		}
		$html.=$this->buildLayout($fields,@$form['layout']);
		$html.="<div class=\"submit-container\">\n";
		foreach($footer as $k=>$v) {
			$v['id']=$k; $html.=$this->create($v);
		}
		$html.="</div>\n</form>\n";
		return $html;
	}

	function save($f,$new=array(),$old=array()) { // сохраняет введенные пользователем данные в таблице
		global $DEBUG;
		$ftypes = array('text'=>0,'photo'=>1);
		$rename=array();
		$opt=$this->checkCondition($f,$new,$old);
		if($DEBUG>0) log_txt(__METHOD__.": `{$this->name}`\n---------------");
		if(count($old)==0) $old = $this->separate($opt['fields'],'old_');
		if(count($new)==0){
			$new=$this->separate($opt['fields']);
		}else{
			if(!isset($this->key) && isset($this->name)) $this->key = $this->q->table_key($this->name);
			if(isset($new[$this->key])) $this->id=$new[$this->key];
			elseif(isset($old[$this->key])) $this->id=$old[$this->key];
			elseif(!isset($this->id))
				stop(array('result'=>'ERROR','desc'=>"Ошибка в параметрах формы:<br>не существует new[{$this->key}] = ".arrstr($new[$this->key])));
		}
		if($DEBUG>0) log_txt(__METHOD__.": `{$this->name}` id={$this->id} key={$this->key}");
		if($DEBUG>0) log_txt(__METHOD__.": `{$this->name}` new=".arrstr($new));
		if($DEBUG>0) log_txt(__METHOD__.": `{$this->name}` old=".arrstr($old));
		$id = $this->id;

		// Проверка на задвоенность данных если есть в схеме form_check_exist
		if(isset($f['form_check_exist'])) {
			if($DEBUG>0) log_txt(__METHOD__.": form_check_exist");
			if(is_array($f['form_check_exist'])) {
				$chk = array_intersect_key($new,array_flip($f['form_check_exist']));
				$dbl = $this->q->get($this->name,$chk,$this->key);
				if($dbl && ($count_exist = $this->q->rows()) > 0) {
					if($this->id=='new' || $count_exist>1 || ($count_exist==1 && $this->id!=$dbl[0][$this->key])){
						log_txt(__METHOD__.": SQL: ".$this->q->sql);
						stop(array('result'=>"ERROR",'desc'=>"В таблице `{$this->name}` уже есть такая запись!"));
					}
				}
			}else{
				log_txt(__METHOD__.": `{$this->name}` ERROR: form_check_exist is not array!");
			}
		}
		if($DEBUG>1) log_txt(__METHOD__.": fetch \$row");

		if($this->id=='new') $row = array_intersect_key($old,$opt['fields']);
		elseif(isset($this->record)) $row = $this->record;
		elseif(isset($opt['form_record']) && function_exists($opt['form_record'])) $row = $opt['form_record']($new,$old,$this);
		else $row = $this->q->select($this->query,1);
		if($DEBUG>0) log_txt(__METHOD__.": SQL: ".sqltrim($this->q->sql));
		if($DEBUG>0) log_txt(__METHOD__.": `{$this->name}` row=".arrstr($row));

		// приводим поля к виду в котором отправляли клиенту (для проверки конфликта доступа)
		if($this->id!='new') foreach($row as $n=>$v) {
			if(@$opt['fields'][$n]['type']=='date' && @$old[$n] != $v) $row[$n]=cyrdate($v);
			$func=@$opt['form_triggers'][$n];
			$ftype = @$opt['fields'][$n]['type'];
			if(is_string($func) && function_exists($func) && (isset($ftypes[$ftype]) || $opt['fields'][$n]['native'] === false)) {
				if(preg_match('/_by_id$/',$func)) $row[$n]=$func($v,$this); else $row[$n]=$func($v,$row,$this);
				if($DEBUG>0) log_txt(__METHOD__.": form_triggers {$func}([{$n}]:$v) = {$row[$n]}");
			}
		}
		$this->row = $row;

		// сравниваем веосию данных посланных клиенту и хранящихся в базе (для проверки на изменение другим оператором)
		if($DEBUG>1) log_txt(__METHOD__.": compare row old");
		$cmpold = ($this->id!='new')? $this->q->compare($row,array_intersect_key($old,$this->fields)) : array();
		if($DEBUG>1) log_txt(__METHOD__.": `{$this->name}` cmpold=".arrstr($cmpold));
		if(count($cmpold)>0) {
			foreach($cmpold as $n=>$v){
				if($opt['fields'][$n]['native'] === false) unset($cmpold[$n]);
				else log_txt(__METHOD__.": `{$this->name}` cmpold['$n']:  '{$row[$n]}' -> '{$old[$n]}'");
			}
		}

		// сравниваем версию данных из базы и данных посланных клиенту до редактирования
		if($DEBUG>1) log_txt(__METHOD__.": compare row new");
		$cmp = ($this->id=='new')? $new : $this->q->compare($row,$new);
		if($DEBUG>1) log_txt(__METHOD__.": `{$this->name}` cmp=".sprint_r($cmp));

		if(count($cmpold)>0) {
			return array('result'=>"ERROR",'desc'=>"Исходные данные были изменены другим оператором. Обновите!");
		}
		if(count($cmp) == 0) {
			if($DEBUG>0) log_txt(__METHOD__.": `{$this->name}` Различий не найдено. Обновление не произведено!");
			$out['result']="OK";
			return $out;
		}
		// проверяем доступы
		foreach($cmp as $k=>$v) {
			$acl=@$opt['fields'][$k]['access'];
			if(!$acl) $acl=@$opt['access'];
			if(!write_access($acl,$this->name.".".$k)) {
				if($DEBUG>0) log_txt(__function__.": {$this->name}:\n\trow: ".arrstr($row)."\n\told: ".arrstr($old)."\n\tcmp: ".arrstr($cmp)."\n");
				stop(array('result'=>"ERROR",'desc'=>"Доступ запрещен!"));
			}
		}

		// **********
		// *  save  *
		// **********
		// запускаем триггер перед сохранением
		if(@$opt['before_save']!='' && function_exists($opt['before_save'])) {
			if($DEBUG>0) log_txt(__METHOD__.": exec function: {$opt['before_save']}");
			$cmp = $opt['before_save']($cmp,$row,$this);
		}
		$fullnew=array_merge($row,$cmp);
		// выполнение проверок для всех полей (в том числе измененных)
		if($opt && @$opt['checks'] && function_exists(@$opt['checks']['save'])) {
			if($DEBUG>0) log_txt(__METHOD__.": exec function: {$opt['checks']['save']}");
			$opt['checks']['save']($fullnew,$this);
		}
		foreach($cmp as $n=>$v) {
			if($opt['fields'][$n]['type']=='date') {
				if(!(is_null($v)||$v=='')) $cmp[$n]=$this->date2db($v);
			}
			// оставляем только поля, присутствующие в таблице
			if($opt['fields'][$n]['native']===false) {
				$unsets[$n]=$cmp[$n]; unset($cmp[$n]);
			}elseif(is_string($opt['fields'][$n]['native'])) {
				$rename[$n]=$opt['fields'][$n]['native'];
			}
			if($this->id=='new' && $v === '') unset($cmp[$n]);
		}
		if($DEBUG>1 && count($unsets)>0) log_txt(__METHOD__.": `{$this->name}` Были удалены неродные поля: ".arrstr($unsets));
		if(count($rename)>0) foreach($rename as $k=>$v) {
			$tmpvalue=$cmp[$k];
			unset($cmp[$k]);
			$cmp[$v]=$tmpvalue;
		}
		// записываем данные в базу
		$save = $cmp;
		if($DEBUG>0) log_txt(__METHOD__.": \$save= ".arrstr($save));
		if(isset($save['id']) && $this->key!='id' && !isset($save[$this->key])) {
			$save[$this->key]=$save['id'];
			unset($save['id']);
		}
		if($this->id!='new'){
			$save[$this->key]=$id;
			$fullsave = isset($unsets)? array_merge($save,$unsets) : $save;
			if(isset($opt['form_save']) && function_exists($opt['form_save'])) { // внешняя функция для сохранения данных
				$res = $opt['form_save']($fullsave,$this);
			}else{
				$res = $this->q->update_record($this->name,$save,$this->key);
				if($DEBUG>0) log_txt(__METHOD__.": SQL: ".sqltrim($this->q->sql));
			}
			if($res === false && !isset($unsets)) {
				return array('result'=>"ERROR",'desc'=>"form: Запись данных не выполнена!");
			}else{
				if($DEBUG>0) log_txt(__METHOD__.": `{$this->name}`: ".arrstr($fullsave));
				if($res === 0) log_txt(__METHOD__.": update WARNING table={$this->name}, key={$this->key} no record updated!");
				elseif($this->log && array_search($this->name,$this->q->tables)!==false) dblog($this->name,$old,$fullsave);
			}
		}else{
			$fullsave = isset($unsets)? array_merge($save,$unsets) : $save;
			if(isset($opt['form_save']) && function_exists($opt['form_save'])) {
				$id = $opt['form_save']($fullsave,$this);
			}elseif(isset($opt['form_save_new']) && function_exists($opt['form_save_new'])){
				$id = $opt['form_save_new']($fullsave,$this);
			}else{
				if(key_exists($this->key,$save) && $save[$this->key] == 'new') unset($save[$this->key]); 
				$id = $this->q->insert($this->name,$save,(@$save[$this->key])? true : $this->key); // если есть id
				if($DEBUG>0) log_txt(__METHOD__.": SQL: ".sqltrim($this->q->sql));
			}
			if(!$id) {
				log_txt(__METHOD__.": insert ERROR table:{$this->name}, key:{$this->key}, save: ".arrstr($save));
				return array('result'=>"ERROR",'desc'=>"form: Вставка записи не выполнена!");
			}else{
				$save[$this->key] = $fullsave[$this->key] = $id;
				if($this->log) dblog($this->name,'new',$fullsave);
			}
		}
		
		// записываем данные из субформ
		foreach($cmp as $n=>$v) 
			if(@$opt['subform_save_triggers'][$n]!='' && function_exists($opt['subform_save_triggers'][$n])) {
				if($DEBUG>0) log_txt(__METHOD__.": `{$this->name}` exec function: {$opt['subform_save_triggers'][$n]}");
				$opt['subform_save_triggers'][$n]($cmp,$this);
			}
		// готовим замену данных на странице клиента
		$opt=array_merge($opt, array(
			'type'=>'table',
			'name'=>$this->name,
			'id'=>$id
		));
		// выдаем результат для обновления страницы
		$out['result']="OK";
		$action=($this->id=='new')? "append" : "modify";
		if(isset($opt['form_onsave']) && function_exists($opt['form_onsave'])) {
			if($DEBUG>0) log_txt(__METHOD__.": exec function: {$opt['form_onsave']}");
			$onsave = $opt['form_onsave']($this->id,$save,$this);
			if(is_array($onsave)){
				if(isset($onsave['result'])) $out=$onsave; else $out[$action]=$onsave;
			}
		}
		if(!isset($out[$action])){
			$out[$action] = $this->get_table_row($id);
		}
		unset($this->row);
		return $out;
	}
	
	function delete($f){ // удаляет записи из таблицы
		global $DEBUG;
		if($DEBUG>0) log_txt(__METHOD__.":{$this->name} name: {$f['name']} id: {$f['id']}");
		$opt = $this->checkCondition($f);
		if($DEBUG>0) log_txt(__METHOD__.":{$this->name} checkCondition: ".arrstr(is_array($opt)));

		if(isset($this->record)) $old = $this->row = $this->record;
		elseif(isset($opt['form_record']) && function_exists($opt['form_record'])) $old = $this->row = $opt['form_record']($opt['id'],$this);
		else $old = $this->row = $this->q->select($this->query,SELECT_FIRSTRECORD);
		if($DEBUG>0) log_txt(__METHOD__.":{$this->name} select: ".arrstr(is_array($old)));

		if(!isset($this->id) || $this->id=='' || $this->id==0){
			log_txt(__METHOD__.": `{$opt['name']}` не определен id = ".arrstr($this->id));
			return array('result'=>"ERROR",'desc'=>"Ошибка при удалении");
		}
		$cmp=$opt['fields'];
		// проверка уровня доступа
		foreach($cmp as $k=>$v) {
			if(! @$v['native']) continue;
			$acl=(isset($opt['access']))? $opt['access'] : 0;
			$acl=(isset($v['access']))? $v['access'] : $acl;
			if(!write_access($acl,$this->name.".".$k)) {
				return array('result'=>"ERROR",'desc'=>"{$opt['name']}[$k]<br>Доступ запрещен!");
			}
		}
		if($DEBUG>0) log_txt(__METHOD__.":{$this->name} access: OK");
		// проверка на возможность уделения (используется в осн. для проверки сущ-я связанных записей)
		if(isset($opt['allow_delete']) && function_exists($opt['allow_delete'])) {
			if(($chk = $opt['allow_delete']($this->id,$this))!='yes') {
				return array('result'=>"ERROR",'desc'=>$chk);
			}
		}
		if($DEBUG>0) log_txt(__METHOD__.":{$this->name} allow_delete: $chk");
		$out['result']="OK";
		if(isset($opt['before_delete']) && function_exists($opt['before_delete'])) {
			if(($old = $opt['before_delete']($old,$this)) === false) {
				log_txt(__METHOD__.": `{$opt['name']}` Ошибка before_delete(".arrstr($this->row).")!");
				return array('result'=>"ERROR",'desc'=>"Ошибка before_delete!");
			}
			if($old && !is_array($old)) $old = $this->row;
			if($DEBUG>0) log_txt(__METHOD__.":{$this->name} before_delete: ".arrstr($mem));
		}
		$out['delete']=array($this->id);
		if(isset($old['id']) && $this->key!='id' && !isset($old[$this->key])) {
			$old[$this->key]=$old['id'];
			unset($old['id']);
		}
		// удаление
		if(isset($opt['form_delete']) && function_exists($opt['form_delete'])) {
			if(!($d = $opt['form_delete']($old,$this))) {
				log_txt(__METHOD__.": `{$opt['name']}` Ошибка form_delete(".arrstr($old).")!");
				return array('result'=>"ERROR",'desc'=>"Ошибка удаления!");
			}
		}else{
			$d=$this->q->del($this->name,$this->id,$this->key);
		}
		// запись лога
		if($this->log && isset($this->q->tables[$opt['name']])) dblog($this->name,$old,'del');
		// проверка уделения только одной записи
		if(is_numeric($d) && $d!=1) {
			log_txt("form->delete `{$opt['name']}` удалено: $d записей");
			return array('result'=>"ERROR",'desc'=>"ошибка удаления!");
		}elseif(is_array($d) && isset($d['result'])) {
			if($d['result'] != 'OK') log_txt("form->delete: ошика удаления");
			return $d;
		}
		return $out;
	}
	
	private function add_control_fields($t,$r=false) { // добавляет к полям формы скрытые поля указывающие на выполнимую операцию
		global $tables, $DEBUG;
		if($DEBUG>0) log_txt(__METHOD__.":{$this->name}");
		$f=array(); $tmp=false;
		if(is_array($t['fields'])) {
			$f=array_merge($t['fields'],array(
				'go'=>array('type'=>'hidden','value'=>$this->_go),
				'do'=>array('type'=>'hidden','value'=>$this->_do),
				'table'=>array('type'=>'hidden','value'=>$this->name)
			));
		}
		foreach($f as $k=>$v) {
			if($DEBUG>3) log_txt(__METHOD__.": $k");
			// убираем лишние атрибуты
			foreach(array('access','native') as $name) {
				if(isset($v[$name])) unset($f[$k][$name]);
			}
			foreach($v as $name=>$attr) if(preg_match('/table_.*/',$name)) unset($f[$k][$name]);
			
			// если список выбора предоставляет функция, то запускаем её
			if(isset($v['list']) && @function_exists($v['list']) && is_array($r)) {
				$f[$k]['list'] = $v['list'] = $v['list']($r);
			}
			if($v['type']=='select') {
				if(is_array($v['list'])) {
					$tmp=$v['list'];
				}
				if($tmp === false){
					$f[$k]['type'] = 'text';
					unset($f[$k]['list']);
				}else{
					$f[$k]['list'] = array();
					foreach($tmp as $i=>$s) { // извращение ради отсортированного списка в браузере
						if(is_numeric($i) || $i === '') $f[$k]['list']['_'.$i] = $s; else $f[$k]['list'][$i] = $s;
					}
				}
			}
			// добавляем subform
			if($v['type']=='subform') {
				if(function_exists($v['sub'])) {
					$f[$k]['sub']=$v['sub']($t['id'],$this);
				}elseif(is_string($v['sub'])){
					$subt = @$tables[$f[$k]['tname']];
					$tbody = $this->q->select(preg_replace('/:OBJECT:/',$this->id,$v['sub']),5);
					$sf = $this->q->fields;
					foreach($sf as $i => $n) {
						$acl = (isset($subt['fields'][$n]['access']))? $subt['fields'][$n]['access'] : 1;
						if(read_access($acl,$this->name.".".$n)) { $mask[$i]=0; continue; }
						$thead[$i]=array_diff_key($subt[$n],array('native'=>0,'access'=>1));
						$thead[$i]['name'] = $n;
					}
					
					$key = array_search('id',$this->q->fields);
					if($key===false) $key = array_search($this->q->table_key($v['tname']),$this->q->fields);
					if($key===false) $key = 0;
					foreach($tbody as $i=>$d) $tbody[$i] = array_diff_key($d,$mask);
					
					$f[$k]['sub']=array(
						'class'=>'subform',
						'style'=>'width:100%;height:100%;overflow:auto',
						'table'=>array(
							'tname' => $f[$k]['tname'],
							'key'	=> $key,
							'target'=> @$subt['target'],
							'delete'=> @$subt['delete'],
							'module'=> @$subt['module'],
							'class' => @$subt['class'],
							'style' => @$subt['style'],
							'tbody' => $tbody,
							'thead' => $thead
						)
					);
				}
			}
		}
		$this->pf = $f;
		return $f;
	}
	
	private function prepare_values($f,$r) { // делает массив для слияния настроек полей формы и их значений из БД
		global $DEBUG;
		if($DEBUG>0) log_txt(__METHOD__.":{$this->name}");
		$ftypes = array('text'=>0,'nofield'=>1,'photo'=>2);
		$res=array();
		if(is_array($r)) {
			foreach($r as $n=>$v) {
				if(isset($f['fields'][$n])) {
					$res[$n]['value']=$v;
					// поля устанавливаемые заранее (типа refs.field)
					if($this->id=='new' && isset($f['defaults'][$n]) && $f['defaults'][$n]!=''){
						$res[$n]['value'] = $v = $f['defaults'][$n];
					}
					// Запуск триггеров по неизменяемым полям
					$func=@$f['form_triggers'][$n];
					$ftype = @$f['fields'][$n]['type'];
					if(is_string($func) && function_exists($func) && isset($ftypes[$ftype])) {
						if(preg_match('/_by_id$/',$func)) $res[$n]['value']=$func($v,$this);
						elseif($this->id!='new') $res[$n]['value']=$func($v,$r,$this);
						else $res[$n]['value']=$func($v,array_merge($r,array($n=>$v)),$this);
					}
					// преобразует дату к русскому стилю (число-месяц-год)
					if($f['fields'][$n]['type']=='date') $res[$n]['value']=cyrdate($v);
					// берет данные из списка
					if($f['fields'][$n]['type']=='photolist' && $this->pf){
						$a=array();
						foreach($this->pf[$n]['list'] as $k=>$l) $a[] = $l['id'];
						$res[$n]['value']=implode(',',$a);
					}
					if($ftype == 'select' && (is_numeric($v) || $v === '')) $res[$n]['value']='_'.$v; //в браузере сбивается сортировка
				}
			}
		}
		return $res;
	}
	
	function prepareForm($f) { // на основе ./db/*.php формирует и отправляет пакет данных для создания формы
		global $DEBUG;
		if($DEBUG>0) log_txt(__METHOD__.": {$this->name} ");
		$f['type']='form';
		if(!isset($this->record) && ($this->query=='' || $this->key=='')) {
			log_txt(__METHOD__." `{$this->name}` ERROR query:'{$this->query}' key:'{$this->key}' record: ".arrstr($this->record));
			stop(array('result'=>'ERROR','desc'=>'Нет данных для формы!'));
		}
		foreach($f['fields'] as $n=>$v) if(!read_access($f['fields'][$n]['access'],$n)) unset($f['fields'][$n]);
		if($this->id!='new' && !($r = (isset($this->record))? $this->record : $this->q->select($this->query,1))) {
			if($DEBUG>0) log_txt(__METHOD__.": `{$this->name}` no return data SQL:'{$this->query}'");
			stop(array('result'=>'ERROR','desc'=>'Нет данных для формы!'));
		}elseif($this->id=='new') {
			if($DEBUG>0) log_txt(__METHOD__.": `{$this->name}` \$this->id={$this->id}");
			$r=array();
			foreach($this->fields as $k=>$v) $r[$k]='';
			$f['fields'] = array_intersect_key($f['fields'],$this->fields);
		}
		if($DEBUG>3) log_txt(__METHOD__.": SQL: {$this->q->sql}");
		// если нужно убрать некоторые поля
		if($this->id!='new' && count($r)>0) {
			if(isset($f['fields_filter'])) {
				if(is_array($f['fields_filter'])) $r=array_diff_key($r,$f['fields_filter']);
				elseif (is_string($f['fields_filter']) && function_exists($f['fields_filter']))
					$r=array_diff_key($r,@$f['fields_filter']($r,$this));
			}
			$f['fields']=array_intersect_key($f['fields'],$r);
		}

		// добавляем управляющие поля
		$pf=$this->add_control_fields($f,$r);

		// выбираем значения полей
		$pv=$this->prepare_values($f,$r);
		if($this->id=='new') $pv['id']['value']='new';

		// добавляем значения полей
		$a=array_merge_recursive($pf,$pv);

		// если определено ключевое поле и его значение, и название поля не "id" меняем название на id
		if($this->key!='id' && isset($a[$this->key])) {
			$a['id'] = $a[$this->key];
			if($this->id=='new') $a['id']['value'] = $this->id;
			unset($a[$this->key]);
		}

		$f['fields']=$a;
		$f['id']=$f['name'].'_'.$f['id'];
		if($this->id=='new') $f['force_submit']=1;
		// убираем лишние поля из настройки формы по шаблону
		$out['form']=array_intersect_key($f,$this->form_pattern);
		$out['result']="OK";
		return $out;
	}
	
	function confirmForm($id, $do, $header='Вы действительно хотите удалить эту запись?', $table=false) { // создает скелет формы для поддтверждения
		global $_REQUEST, $DEBUG;
		if(!isset($this->name)) $this->name = 'form';
		if($table) $this->name = $table;
		if($DEBUG>0) log_txt(__METHOD__.":{$this->name}");
		$t=array(
			'id'=>'confirm_form',
			'force_submit'=>1,
			'header'=>$header,
			'name'=>$this->name.'_confirm',
			'query'=>'',
			'fields'=>array('id'=>array('type'=>'hidden','value'=>$id))
		);
		$this->_go=$this->strict($_REQUEST['go']);
		$this->_do=$do;
		$t['fields']=$this->add_control_fields($t);
		$form=array_intersect_key($t,$this->form_pattern);
		$form['type'] = 'confirm';
		$form['footer']=array(
				'cancelbutton'=>array('txt'=>'Нет'),
				'submitbutton'=>array('txt'=>'Да')
		);
		return array('result'=>'OK','form'=>$form);
	}
	
	function infoForm($info){ // создает скелет формы для поддтверждения
		if(!isset($this->name)) $this->name = 'form';
		global $_REQUEST, $DEBUG;
		if($DEBUG>0) log_txt(__METHOD__.":{$this->name}");
		$t=array(
			'id'=>'remove_'.$this->name,
			'class'=>"normal info",
			'header'=>$info,
			'name'=>$this->name.'_info',
			'query'=>'',
			'fields'=>array('id'=>array('type'=>'hidden','value'=>'none'))
		);
		$form=array_intersect_key($t,$this->form_pattern);
		$form['footer']=array(
				'cancelbutton'=>array('txt'=>'OK'),
		);
		return array('result'=>'OK','form'=>$form);
	}

	function separate($f,$prefix='') {
		global $_REQUEST, $DEBUG;
		$result = array();
		$fields=array();
		foreach($f as $k=>$v) $fields[$prefix.$k]=0;
		$tmp=array_intersect_key($_REQUEST,$fields);
		foreach($tmp as $k=>$v) { 
			$fn=($prefix!='')? preg_replace("/^$prefix/","",$k) : $k;
			$result[$fn]=(@$_REQUEST[$this->key]=='new' && $prefix!='')? '' : $v;
		}
		if($DEBUG>0) log_txt(__METHOD__.":{$this->name} $prefix ".mb_substr(arrstr($result),0,80));
		return $result;
	}

	function htmlField($o) {
		if(is_array($o)) {
//		log_txt("form->htmlField: o = ".sprint_r($o));
			foreach($o as $k=>$v) {
				if($v['class']=='') $v['class']="form-text";
				
			}
		}
	}

	function get_table_row($id) {
		global $DEBUG;
		if($DEBUG>0) log_txt(__METHOD__.":{$this->name}");
		$row=array();
		if($this->table_query) {
			$query = $this->add_filter_to_query($this->table_query, $id);
			$query = preg_replace('/ORDER BY.*/i','',$query);
			if($DEBUG>1) log_txt(__METHOD__." SQL: ".$query);
			$row = $this->q->select($query,SELECT_WITHOUTFNAME);
			$th = $this->q->sql_fields();
			foreach($row as $i => $r) {
				foreach($r as $k => $v) {
					$trig = (isset($this->table_triggers[$th[$k]]))? $this->table_triggers[$th[$k]] : false;
// 					log_txt(__METHOD__." : $k:{$th[$k]} = ".(($trig)? "$trig($v)":$v));
					if($trig && function_exists($trig)){
						$row[$i][$k] = $trig($v,$r,$th[$k]);
					}
				}
			}
		}else{
			log_txt("form->get_table_row: ERROR not found table query");
		}
		return $row;
	}

	function date2db($d=false, $tm=true) { // 	подготавливает дату для сохранения в базе данных
		global $DEBUG;
		if(!$d) {
			if($tm) $res = date('Y-m-d H:i:s'); else $res = date('Y-m-d');
		}elseif(is_numeric($d)){
			if($tm) $res = date('Y-m-d H:i:s',$d); else $res = date('Y-m-d',$d);
		}elseif(is_string($d)){
			if($tm && !preg_match('/ \d\d:\d\d\b/',$d)) $tm = false;
			if(($t = strtotime($d)) === false){
				log_txt(__METHOD__." ERROR: convert data '$d'");
				$res = '0000-00-00'.(($tm)?' 00:00:00':'');
			}else{
				if($tm) $res = date('Y-m-d H:i:s',$t); else $res = date('Y-m-d',$t);
			}
		}
		if($DEBUG>0) log_txt(__METHOD__." date='$d' tm=".(($tm)?'true':'false')." res='$res'");
		return $res;
	}

	function numeric($c) { $tmp = preg_replace(array('/[\.,].*/','/[^0-9\-]/'),array('',''),$c); if($tmp=='') return 0; else return $tmp; }
	function getid($c) { if($c=='new') return $c; else return $this->numeric($c); }
	function flt($c) { $tmp = preg_replace('/[^0-9\-\.]/','',$c); if($tmp=='') return 0; else return $tmp; }
	function strict($s) { return (preg_replace('/[^0-9A-Za-z\.\-_ ]/','',$s)); }
}
?>
