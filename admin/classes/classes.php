<?php
include_once("utils.php");
include_once("log.php");
include_once("form.php");


define("SELECT_FIRSTRECORD",1); // просто берем первую запись
define("SELECT_ARRWITHOUTKEY",2); // делае масив с ключем из указанного или первого поля каждой записи с удалением из записи этого поля
define("SELECT_ARRFIRSTFIELD",3); // делает масив из единственного указанного или первого по списку поля
define("SELECT_SINGLEVAL",4); // делает переменную из единственного указанного или первого по списку поля
define("SELECT_WITHOUTFNAME",5); // делает масив с цифровыми ключами (по порядку)


define("RESULT_NONE","0");
define("RESULT_YES","1");
define("RESULT_ERROR","2");
define("RESULT_WARNING","3");

function mysqltypes() {
	$types = array();
    $constants = get_defined_constants(true);
    foreach ($constants['mysqli'] as $c => $n) if (preg_match('/^MYSQLI_TYPE_(.*)/', $c, $m)) $types[$n] = $m[1];
    return $types;
}
$mysqli_types = mysqltypes();
$mysql_number = array(0=>0,2=>0,3=>0,8=>0,9=>0);
$mysql_string = array(1=>0,253=>0,254);

class sql_query{

    function __construct($connect){
		$this->connect($connect);
		$this->fields = array();
    }

    function connect($connect){
		$this->cfg = $connect;
		$result_connect = new mysqli(@$connect['server'], $connect['username'], $connect['password'], $connect['db']);
		if ($result_connect->connect_error){
			log_txt('CONNECT ERROR: '.$result_connect->connect_error.' ERRNO: '.$result_connect->connect_errno." CONF: ".sprint_r($connect));
		}else{
			$result_connect->set_charset($connect['charset']);
			$this->result_connect = $result_connect;
			$this->result_connect->query("SET lc_time_names = 'ru_RU'");
			$this->result_connect->query("SET SESSION group_concat_max_len = 1000000");
			$this->refresh_tables();
		}
	}

	function set_error($message,$type=false){
		log_txt((($type)? $type.' ' : '').$message);
		$this->error = (($type)?$type.' ':'').$message;
		$this->errors[] = $message;
	}

	function refresh_tables() {
		global $DEBUG, $cache;
		if(!isset($cache[$this->cfg['db']]['tables']['all']))
			$cache[$this->cfg['db']]['tables']['all'] = $this->fetch_all("show tables");
		$this->tables = $cache[$this->cfg['db']]['tables']['all'];
		return $this->tables;
	}

	function table_fields($table,$prefix='') {
		global $cache;
		if(array_search($table,$this->tables)===false) {
			$this->set_error(__METHOD__." таблица `$table` не найдена в базе `{$this->cfg['db']}`");
			return false;
		}
		if(!isset($cache[$this->cfg['db']]['table_fields'][$table])) {
			$f=$this->fetch_all("desc `$table`");
			foreach($f as $k=>$v) {
				if($v['Key']=='PRI') $cache[$this->cfg['db']]['table_keys'][$table] = $v['Field'];
				$cache[$this->cfg['db']]['table_fields'][$table][$v['Field']]='';
			}
		}
		if($prefix != '') {
			$r=array();
			foreach($cache[$this->cfg['db']]['table_fields'][$table] as $k=>$v) $r[$prefix.$k]='';
			return $r;
		}else{
			return $cache[$this->cfg['db']]['table_fields'][$table];
		}
	}

	function table_key($table) {
		global $cache;
		if(!@isset($cache[$this->cfg['db']]['table_keys'][$table])) {
			@$cache[$this->cfg['db']]['table_keys'][$table] = false;
			if(!($f=$this->fetch_all("desc `".strict($table)."`"))) return false;
			foreach($f as $k=>$v) {
				if($v['Key']=='PRI') $cache[$this->cfg['db']]['table_keys'][$table] = $v['Field'];
			}
		}
		return $cache[$this->cfg['db']]['table_keys'][$table];
	}

	function escape_string($data) {
		if(is_array($data)) {
			$out = array();
			foreach ($data as $k=>$v) $out[$k] = $this->escape_string($v);
		}elseif(is_string($data)){
			$out = $this->result_connect->real_escape_string( $data );
		}else{
			$out = $data;
		}
		return $out;
    }

    function query( $sql ){
		global $DEBUG;
		if(@$DEBUG>2) log_txt(__METHOD__." \$sql = ".sqltrim($sql));
		if(isset($this->result) && method_exists($this->result,'free')) $this->result->free();
		if(isset($this->modrecord)) unset($this->modrecord);
		$this->sql = $sql;
		if(!is_string($sql)) {
			$this->set_error(__METHOD__.": error sql is not string! - ".arrstr($sql));
			return false;
		}
		$this->result = $this->result_connect->query( $sql );
		if($this->result){
			$this->fields = $this->sql_fields();
			$this->num_rows = (isset($this->result->num_rows))? $this->result->num_rows : 0;
		}elseif(!preg_match('/^desc /',$sql)) {
			$this->set_error(__METHOD__.": SQL: ".sqltrim($sql)."\n\tERROR: ".$this->result_connect->error."\n".parse_backtrace(debug_backtrace()));
		}
		return $this->result;
    }

    function get_blob( $table, $id, $field='image', $key='' ){
		global $DEBUG;
		if(@$DEBUG>2) log_txt(__METHOD__." table:$table load [$id] from {$field}");
		if(isset($this->result) && method_exists($this->result,'free')) $this->result->free();
		if(isset($this->modrecord)) unset($this->modrecord);
		if($key == '') $key = $this->table_key($table);
		$res = false;
		if(!is_numeric($id) || $id <= 0) {
			$this->set_error(__METHOD__.": Ошибка id ($id)!");
			return false;
		}
		$this->result = $this->result_connect->prepare("SELECT `$field` FROM `$table` WHERE `$key`=?");
		$this->result->bind_param("i", $id);
		if(!$this->result->execute()){
			$this->set_error(__METHOD__.": execute ERROR: ".$this->result_connect->error);
			return false;
		}
		$this->result->store_result();
		$this->result->bind_result($image);
		$this->result->fetch();
		$res = $image;
		return $res;
    }

    function file2blob( $table, $file='', $field='image' ){
		global $DEBUG;
		if(@$DEBUG>2) log_txt(__METHOD__." table:$table load $file into `$field`");
		if(isset($this->result) && method_exists($this->result,'free')) $this->result->free();
		if(isset($this->modrecord)) unset($this->modrecord);
		if($fd = fopen($file,"r")){
			$this->result = $this->result_connect->prepare("INSERT INTO `$table` (`$field`) VALUES (?)");
			$null = NULL;
			$this->result->bind_param("b", $null);
			while (!feof($fd)) {
				$this->result->send_long_data(0, fread($fd, 8192));
			}
			fclose($fd);
			if(!$this->result->execute()){
				$this->set_error(__METHOD__.": execute ERROR: ".$this->result_connect->error);
				return false;
			}
		}else{
			$this->set_error(__METHOD__.": ERROR ошибка открытия файла ($file)!");
			return false;
		}
		return $this->result_connect->insert_id;
    }

    function select( $sql, $type = '', $key = false ){
		$this->query( $sql );
		if( $this->result && $this->result->num_rows>0 ){
			$result = array();
			switch( $type ){
				case SELECT_FIRSTRECORD:
					$result = $this->result->fetch_assoc();
					break;
				case SELECT_ARRWITHOUTKEY:
					while ( $v = $this->result->fetch_assoc() ){
						if( $key ){
							$k = $v[$key]; unset($v[$key]);
							$result[$k] = $v;
						}else{
							$result[array_shift($v)] = $v;
						}
					}
					break;
				case SELECT_ARRFIRSTFIELD:
					while ( $v = $this->result->fetch_assoc() ){
						if( $key ){
							array_push($result,$v[$key]);
						}else{
							array_push($result,array_shift($v));
						}
					}
					break;
				case SELECT_SINGLEVAL:
					while ( $v = $this->result->fetch_assoc() ){
						if( $key ){
							$result=$v[$key];
						}else{
							$result=array_shift($v);
						}
						break;
					}
					break;
				case SELECT_WITHOUTFNAME:
					while ( $v = $this->result->fetch_row() ){
						if( is_string($key) && $key!='' ) {
							$key = array_search($key,$this->sql_fields());
						}
						if( is_numeric($key) && $key>0 ){
							$result[$v[$key]]=$v;
						}else{
							$result[]=$v;
						}
					}
					break;
				default: // делае масив с ключем из указанного поля или просто номера записи по порядку
					$result = array();
					while ( $v = $this->result->fetch_assoc() ){
						if( $key ){
							$result[$v[$key]] = $v;
						}else{
							$result[] = $v;
						}
					}
				break;
			}
			return $result;
		}else{
			return false;
		}
    }

	function fetch_all( $sql, $key='id' ) {
		$r = array();
		if( $this->query($sql) ){
			if($this->result->num_rows > 0){
				if(count($this->fields)>2){
					while($tmp = $this->result->fetch_assoc()) {
						if(isset($tmp[$key])) $r[$tmp[$key]]=$tmp;
						else $r[]=$tmp;
					}
				}elseif(count($this->fields)==2){
					if(array_search($key,$this->fields)==0) 
						$n=$this->fields[1]; 
					else 
						$n=$this->fields[0];
					while($tmp = $this->result->fetch_assoc()){
						if(isset($tmp[$key])) $r[$tmp[$key]]=$tmp[$n];
						else $r[$tmp[$this->fields[0]]]=$tmp[$this->fields[1]];
					}
				}else{
					while($tmp = $this->result->fetch_assoc()){
						$r[]=$tmp[$this->fields[0]];
					}
				}
			}
		}
		return $r;
	}

    function last(){
		return $this->result_connect->insert_id;
    }

    function rows(){
		if(is_object($this->result))
		return $this->result->num_rows;
		else return false;
	}

    function modified(){
		return $this->result_connect->affected_rows;
	}

	function get($table, $req, $key='', $fields='*', $op='AND') {
		global $DEBUG;
		$r=array();
		if($key == '') $key = $this->table_key($table);
		if(is_array($fields)) {
			foreach($fields as $k=>$f) $fields[$k] = "`$f`"; 
			$fields=implode(', ',$fields);
		}
		if(is_array($req) && count($req) == 0) {
			$this->set_error(__METHOD__." ERROR array \$req is empty!");
			return false;
		}
		if(is_array($req)) $a = $req; else $a = array($key=>$req);
		if($DEBUG>2) log_txt(__METHOD__.": `$table` \$a: ".arrstr($a)." fields: $fields op: $op");
		if(!is_string($table) || $table == '') {
			$this->set_error(__METHOD__.": ERROR parametrs `$table`:'\na:".arrstr($a));
			return false;
		}

		foreach($a as $k=>$v) {
			if (is_bool($v)){
				$r[] = "`$k`=".($v?'true':'false');
			}elseif (is_null($v)){
				$r[] = "`$k` is NULL";
			}elseif (is_string($v)){
				if($k=='login') $r[] = "`$k`=binary('".$this->escape_string($v)."')";
				elseif(preg_match('/^[<>]/',$v)) $r[] = "`$k`".$this->escape_string($v);
				elseif(preg_match('/%/',$v))  $r[] = "`$k` like '".$this->escape_string($v)."'";
				elseif(is_null($v))  $r[] = "`$k` is NULL";
				else $r[] = "`$k`='".$this->escape_string($v)."'";
			}elseif (is_numeric($v)){
				$r[] = "`$k`=$v";
			}else{
				$this->set_error(__method__.": ERROR `$table`: type of `$k`:'".gettype($v)."'\na=".sprint_r($a));
			}
		}
		$condition=implode(" $op ",$r);
		$tmp = $this->select("SELECT $fields FROM `$table` WHERE $condition");
		if($this->rows() == 1) {
			if(count($this->sql_fields()) == 1) { // если выбрано только одно поле - возвращать единственное значение
				$tmp = array_shift(array_shift($tmp));
			}elseif(!is_array($req)){ // если запрос не масив - возвращать единственную запись
				$tmp = array_shift($tmp);
			}
		}elseif($this->rows() == 0) {
			return false;
		}
		return $tmp;
	}

	function insert($table, $array, $keyfield='') {
		global $DEBUG;
		if(isset($this->nof)) unset($this->nof);
		$this->count_set = 0;
		if($keyfield === '') $keyfield = $this->table_key($table);
		$tmp = $this->create_insert($table, $array, $keyfield);
		if($tmp && $this->query($tmp)) {
			if($this->count_set>1){
				log_txt(__METHOD__.": в `$table` вставлено {$this->count_set} записей");
				$res = true;
			}elseif($this->count_set==1) $res = $this->last();
			if($res === 0) $res=true;
			return $res;
		}else{
			return false;
		}
	}

	function create_set($a,$kf,$table='unknown') {
		$r=array();
		if(!isset($this->count_set)) $this->count_set = 0;
		if(!is_array($a)) return false;
		foreach($a as $k=>$v) {
			if(isset($this->nof) && isset($this->nof[$k])) continue;
			if($k!==$kf) {
				if (is_bool($v)){
					$r[$k] = $v?'true':'false';
				}elseif (is_null($v)){
					$r[$k] = strval('NULL');
				}elseif (is_string($v)){
					$r[$k] = "'".$this->escape_string($v)."'";
				}elseif (is_numeric($v)){
					$r[$k] = "$v";
				}else{
					$this->set_error(__method__.": ERROR `$table`: type of `$k`:'".gettype($v)."' \$a=".sprint_r($a));
					return false;
				}
			}
		}
		$this->count_set++;
		return "(".implode(',',$r).")";
	}

	function create_fields($a,$kf,$table) {
		if(!is_array($a)) return false;
		$fl = $this->table_fields($table);
		$c = array_intersect_key($a,$fl);	// проверяем на неродные поля
		if(!$c || count($c)==0){			// совсем всё плохо
			log_txt(__method__.": нет полей из таблицы `$table` ".arrstr($c));
			return false;
		}
		$this->nof = array_diff_key($a,$c);
		if(count($this->nof)==0) unset($this->nof);// записываем неродные поля
		$r=array();
		foreach($c as $k=>$v) {
			if($k!==$kf) {
				$r[]="`$k`";
			}
		}
		return "(".implode(',',$r).")";
	}

	function create_insert($table,$a,$keyfield='id') {
		if(is_array($a) && count($a)>0 && $table!='') {
			$set=array();
			$fld=array();
			$multi=false;
			foreach($a as $k=>$v) { 
				if(is_array($v)) {
					if(!$multi) {
						if(!($fields=$this->create_fields($v,$keyfield,$table))) return false;
						$multi=true;
					}
					if(!$set[]=$this->create_set($v,$keyfield,$table)) {
						return false;
					}
				}else{
					if(!($fields=$this->create_fields($a,$keyfield,$table))) return false;
					if(!$set=$this->create_set($a,$keyfield,$table)) {
						return false;
					}
					break;
				}
			}
			if($multi) $sqlset=implode(',',$set); else $sqlset=$set;
			$sql="INSERT INTO `$table` $fields VALUES $sqlset;";
			return $sql;
		}else{
			$this->set_error(__METHOD__.": Ошибка формирования SQL: '{$table}[{$keyfield}]' arr=".sprint_r($a));
			return false;
		}
	}

	function create_update($table,$update,$keyfield=''){
		if($keyfield == '') $keyfield = $this->table_key($table);
		if(!is_array($update) || !($update[$keyfield]>0) || $table=='') {
			$this->set_error(__METHOD__.": ERROR {$table}[$keyfield]='{$update[$keyfield]}' \$update=".arrstr($update));
			return false;
		}
		$set=array();
		if(!isset($update[$keyfield])) {
			$this->set_error(__METHOD__.": undefined keyfield {$table}[$keyfield]='{$update[$keyfield]}' \$update=".arrstr($update));
			return false;
		}
		foreach($update as $k=>$v) {
			if($k!=$keyfield) 
				if (is_bool($v)){
					$set[] = "`$k`=".($v?'true':'false');
				}elseif (is_null($v)){
					$set[] = "`$k`=NULL";
				}elseif (is_string($v)){
					if(mb_substr($v,0,1) == '=' && mb_substr($v,1,1) != '=') $set[] = "`$k`=".preg_replace('/^=/','',$v);
					else $set[] = "`$k`='".$this->escape_string($v)."'";
				}elseif (is_numeric($v)){
					$set[] = "`$k`=".$v;
				}else{
					$this->set_error(__METHOD__.": ERROR `$table` type of variable $k ".gettype($v));
				}
		}
		if(count($set)==0) return 0; else $sqlset=implode(',',$set);
		$sqlstr="UPDATE `$table` SET $sqlset WHERE `$keyfield`='".$update[$keyfield]."'";
		return $sqlstr;
	}

	function update_record($table,$update,$keyfield='') {
		global $DEBUG;
		$sqlstr = $this->create_update($table,@$update,$keyfield);
		if(!$sqlstr) return $sqlstr;
		if(!$this->query($sqlstr)) {
			$this->set_error(__METHOD__.": ERROR {$table}[{$update[$keyfield]}]: ".arrstr($update));
			return false;
		}else{
			$this->modrecord = $update;
			$mod = $this->modified();
			if($DEBUG>0 && $mod>1) log_txt(__METHOD__.": в `$table` модифицировано $mod записей");
			if($DEBUG>2 && $mod == 0) log_txt(__METHOD__.":Данные не были изменены!");
			return $mod;
		}
	}

	function compare($old,$new,$full=false) { // сравнение масивов. При full=true ответ сост. из 3 подмножеств dif:отличающиеся, add:новые, del:удаляемые
		global $DEBUG;
		$r=array(); if($full) $out=array();
		if(is_array($old)&&is_array($new)) {
			foreach($new as $k=>$v) {
				if(!key_exists($k,$old)) {
					if($DEBUG>0) log_txt(__METHOD__.": \$old['$k'] остутствует!");
					if($full) $out['add'][$k]=$v; else $r[$k]=$v;
				}else{
					if((is_null($v) && $old[$k]==='') || ($v==='' && is_null($old[$k])) || (is_null($v) && is_null($old[$k]))) continue;
					if($v!=$old[$k]) $r[$k]=$v;
				}
			}
			if($full) {
				foreach($old as $k=>$v) {
					if(!key_exists($k,$new)){
						if($DEBUG>0) log_txt(__METHOD__.": \$new['$k'] остутствует!");
						$out['del'][$k] = $v;
					}
				}
				if(count($r)>0) $out['dif'] = $r;
				$r = $out;
			}
		}
		return $r;
	}

	function compare_keys($old,$new) {
		$r=array();
		if(is_array($old)&&is_array($new)) {
			foreach($new as $k=>$v) {
				if(isset($old[$k])) continue;
				$r[]=$k;
			}
		}
		return $r;
	}

	function sql_tables() { //кол-во используемых таблиц в запросе
		$f=array();
		if($this->result && method_exists($this->result,'fetch_fields')) {
			$tmp = $this->result->fetch_fields();
			foreach($tmp as $k=>$o) @$f[$o->orgname]++;
		}
		return $f;
	}

	function sql_fields($table='') {
		$f=array();
		if($table=='' && $this->result && method_exists($this->result,'fetch_fields')) {
			$tmp = $this->result->fetch_fields();
			foreach($tmp as $k=>$o) $f[] = $o->name;
		}elseif($table!='') {
			$tmp = $this->result_connect->query("SELECT * FROM `$table` LIMIT 1");
			if($tmp){
				$fld = $tmp->fetch_fields();
				foreach($fld as $k=>$o) $f[] = $o->name;
			}else{
				$this->set_error(__METHOD__.": ERROR - ".$this->result_connect->error);
			}
			if(isset($tmp) && method_exists($tmp,'free')) $tmp->free();
		}
		return $f;
	}

	function sql_orig_fields() {
		$f=array();
		if($this->result && method_exists($this->result,'fetch_fields')) {
			$tmp = $this->result->fetch_fields();
			foreach($tmp as $k=>$o){
				if($o->orgname) $f[] = $o->orgname;
				else $f[] = $o->name;
			}
		}
		return $f;
	}

	function sql_fieldtype($param1,$param2='') {
		global $cache;
		$f=null;
		if($param2 && array_search($param1,$this->tables) === false) return $f;
		if($param2 && isset($cache[$this->cfg['db']]['field_types'][$param1][$param2])) {
			return $cache[$this->cfg['db']]['field_types'][$param1][$param2];
		}
		if($param2 == ''){
			$name = $param1;
			$tmp = $this->result;
		}else{
			$name = $param2;
			$tmp = $this->result_connect->query( "SELECT * FROM `$param1` LIMIT 1" );
		}
		if($tmp && method_exists($tmp,'fetch_fields')) {
			$l = $tmp->fetch_fields();
			foreach($l as $o){
				$cache[$this->cfg['db']]['field_types'][$o->orgtable][$o->orgname] = $o->type;
				if(is_null($f) && ($o->name === $name || $o->orgname === $name)) $f = $o->type;
			}
		}
		return $f;
	}

	function is_number($table,$name) {
		global $mysql_number;
		$type = $this->sql_fieldtype($table,$name);
		if(is_null($type)) return $type;
		return isset($mysql_number[$type]);
	}

	function is_string($table,$name) {
		global $mysql_string;
		$type = $this->sql_fieldtype($table,$name);
		if(is_null($type)) return $type;
		return isset($mysql_string[$type]);
	}

	function get_html($sql) {
		$s="<table>";
		if( $this->query($sql) ){
			if($this->result->num_rows > 0){
				$s.="<thead><tr><td>".implode("</td><td>",$this->sql_fields())."</td></tr></thead>";
				$s.="<tbody>";
				while($tmp = $this->result->fetch_assoc()) {
					$s.="<tr><td>".implode("</td><td>",$tmp)."</td></tr>";
				}
				$s.="</tbody>";
			}
		}
		return $s."</table>";
	}

	function del($table, $r, $key='', $op='', $limit=1) {
		global $DEBUG;
		if($op == '') $op = 'AND';
		$op = " $op ";
		if($key == '') $key = $this->table_key($table);
		if(is_numeric($r)||is_string($r)) $r=array($key=>$r);
		if(is_array($r)) {
			$fld = $this->table_fields($table);
			foreach($r as $k=>$v) {
				if(is_numeric($k)){
					if(is_string($v)){
						$kset[] = "'".$this->escape_string($v)."'";
					}elseif(is_numeric($v)){
						$kset[] = $v;
					}
				}elseif(isset($fld[$k])){
					if (is_bool($v)){
						$set[] = "`$k`=".($v?'true':'false');
					}elseif (is_null($v)){
						$set[] = "`$k` is NULL";
					}elseif (is_string($v)){
						$set[] = "`$k`='".$this->escape_string($v)."'";
					}elseif (is_numeric($v)){
						$set[] = "`$k`=".$v;
					}elseif(is_array($v) && isset($v['op']) && isset($v['value'])){
						$set[] = "`$k`{$v['op']}'{$v['value']}'";
					}else{
						$this->set_error(__METHOD__.": ERROR $table[$k] = ".sprint_r($v));
						return false;
					}
				}
			}
		}else{
			$this->set_error(__METHOD__.": ERROR table='$table', key='$key', is not array \$r =".sprint_r($r));
			return false;
		}
		if(!isset($set) && !isset($kset)){
			$this->set_error(__METHOD__.": Не определено что удалять table='$table', key='$key' r=".arrstr($r));
			return false;
		}
		if($table!='' && $set && count($set)>0) {
			$this->query("DELETE FROM `$table` WHERE ".implode($op,$set)).(($limit>0)?" LIMIT $limit":"");
		}elseif($table!='' && $kset && count($kset)>0){
			$this->query("DELETE FROM `$table` WHERE `$key` in (".implode(',',$kset).")");
		}
		$nr=$this->modified();
		if($DEBUG>0) log_txt(__METHOD__.": в `$table` удалено ($nr) записей");
		if($DEBUG>2 && $nr == 0) log_txt(__METHOD__.": SQL: {$this->sql}");
		return $nr;
	}
}

// -----------------------------------------------------------------------------------------------------------------------
// 					radius classes
// -----------------------------------------------------------------------------------------------------------------------

class radius {

	function __construct(){
		global $config, $opdata;
		$this->error="";
		$this->db=$config['db'];
		$this->q = new sql_query($this->db);
		$this->errors = array();
		$this->op = $opdata;
		$this->once = 0;
	}

	function set_error($message,$type=false){
		log_txt((($type)?"$type: ":'').$message);
		$this->error = (($type)?"$type: ":'').$message;
		$this->errors[] = $message;
	}

    function get($table,$r,$key=''){	//Получаем данные
		global $tables, $opdata, $DEBUG;
		if($DEBUG>0) log_txt(__METHOD__." <<$table>> data=".arrstr($r)." key='$key'");
		$this->table = $table;
		if(!isset($tables[$table]['fields'])){
			$this->set_error("Не найдена конфигурация таблицы `$table`!",__METHOD__." ERROR");
			return false;
		}
		if(is_array($r)) {
			$data = array_intersect_key($r,$this->q->table_fields($table));
			if($DEBUG>0) log_txt(__METHOD__." <<$table>> intersect data=".arrstr($data));
			if(count($cmp = array_diff_key($r,$data))>0) {
				$this->set_error("В таблице `$table` нет следующих полей: ".arrstr($cmp),__METHOD__." ERROR");
			}
			if(count($data)==0) return false;
			$r = $data;
		}
		return $this->q->get($table,$r,$key);
    }

    function set($table,$new,$key=''){    //Обновляем данные
		global $tables, $config, $DEBUG;
		if($DEBUG>0) log_txt(__METHOD__.": <<$table>> new=".arrstr($new)." key='$key'");
		if(!isset($tables[$table]['fields'])) {
			$this->set_error("Не найдена конфигурация таблицы `$table`!",__METHOD__." ERROR");
			return false;
		}
		$this->table = $table;
		if($key == '') $key = $this->q->table_key($table);
		if(!isset($new[$key])){
			$this->set_error("Не определен ключ `$key` в данных для сохранения `$table`!",__METHOD__." ERROR");
			return false;
		}

		$old = $this->q->get($table,$new[$key]);
		$new = array_intersect_key($new,$old);
		$cmp = $this->q->compare($old,$new);
		if(count($cmp)>0){
			$cmp[$key] = $old[$key];
			if(!$this->q->update_record($table,$cmp,$key)) return false;
			if(function_exists('dblog')) dblog($table,$old,$cmp);
			return $cmp;
		}
		return false;
    }

    function AbonPl($now='') {
		global $DEBUG;
		$this->once=1;
		$this->q1 = new sql_query($this->db); $i=0;
		if(!$now) $now = date('Y-m-d');
		$res = $this->q1->query("
			SELECT u.*, r.value as ip
			FROM users as u LEFT OUTER JOIN radreply as r ON u.user=r.username AND attribute='Framed-IP-Address'
			WHERE u.disabled=0 AND u.late_payment<'$now'
		");
		if(!$res){
			$this->set_error("Ошибка запроса к таблице пользователей по подневной абонплате");
			return false;
		}
		$users = 0;
		while($usr = $res->fetch_assoc()) {
			$a = $this->activate_user($usr,$now);
			if($a && $a !== true) $i++;
			$users++;
		}
		if($DEBUG>0) log_txt(__METHOD__.": найдено $users; активировано $i");
		return $i;
	}

	function activate_user($u=false,$now="") {
		global $DEBUG;
		$acct = array();
		if(!is_array($u)){
			$this->set_error("user not define!");
			return false;
		}
		if(!key_exists('ip',$u))
			$u['ip'] = $this->q->select("SELECT value FROM radreply WHERE username='{$u['user']}' AND attribute='Framed-IP-Address'",4);

		if($u['disabled']) {
			$this->release_ip($u,"пользователь выключен");
			return false;
		}
		if(!isset($this->packets)) $this->packets = $this->q->select("SELECT * FROM packets",'','pid');

		if(!$now) $now = date('Y-m-d');
		$set = array('uid'=>$u['uid']);
		if(!isset($this->packets[$u['pid']])) log_txt("WARNING! пакет для {$u['user']} ({$u['pid']}) не найден! ".arrstr($u));
		$p = $this->packets[$u['pid']];
		if($u['next_pid']>0) { // указан следующий пакет
			if(($p['fixed']>0 || $p['tos']>0) && $p['fixed']<8) $pid = $u['next_pid'];
			if($p['fixed']>7 && $p['fixed']<10 && date('d')==1) $pid = $u['next_pid'];
			if($p['fixed']==10 && strtotime($u['expired']) <= strtotime($now)) $pid = $u['next_pid'];
			if(isset($pid)) {
				$set['pid'] = $pid;
				$set['next_pid'] = 0;
				if($p['fixed']!=10 && @$this->packets[$pid]==10) $u['expired'] = $now;
			}
		}elseif($u['prev_pid']>0) { // указан предыдущий пакет
			if($p['fixed']==10 && strtotime($u['expired']) <= strtotime($now) && $u['deposit']+$u['credit']<$p['fixed_cost']) {
				$pid = $u['prev_pid'];
				$set['pid'] = $pid;
				$set['prev_pid'] = 0;
			}
		}
		if(isset($pid)) {
			if(!isset($this->packets[$pid])) { $this->set_error("Не найден пакет $pid для {$u['user']}"); return false; }
			$p = $this->packets[$pid];
		}
		if($p['fixed']==0 && $p['tos']==0) {
			if(!$this->once) log_txt("{$u['user']} Активация не требуется");
			return true;
		}
		
		$a = array('acctuniqueid'=>rand().rand(),'uid'=>$u['uid'],'username'=>$u['user'],'pid'=>$p['pid'],'credit'=>$u['credit'],
			'before_billing'=>$u['deposit'],'billing_minus'=>0,'acctstarttime'=>$now,'acctstoptime'=>$now,'calledstationid'=>'',
			'callingstationid'=>'Абонплата-'.$p['name'],'acctterminatecause'=>'Admin-AbonPl');

		if($u['blocked']) {
			if(!$u['last_connection'] || $u['last_connection']=='0000-00-00' || strtotime($u['last_connection']) > strtotime('-1 year')){
				$act = $this->count_connect($u,$this->once? "-1 day":"now");
				# сдвиг даты окончания пакета
				if(!$act && $p['fixed']==10 && strtotime($u['expired']) > strtotime($now) && $this->once)
					$set['expired'] = date("Y-m-d",strtotime($u['expired'])+86400);
				elseif($act && $this->once) $this->dropuser($u);
				# возврат денег на счёт 
 				if(!$act && $p['fixed']>7 && $p['fixed']!=10) {
# 					$day_of_month = (strtotime(date("Y-m-01"))-strtotime(date("Y-m-01",strtotime("-1 month"))))/86400;
# 					$set['deposit'] = $u['deposit'] + $p['fixed_cost']/$day_of_month;
 				}
			}else{
				$set['disabled'] = 1;
				$set['blocked'] = 0;
			}
		}else{
			if($p['fixed']==1 && $this->once) { // (Cron) Раз в сутки, если было подключение
				if($u['deposit']+$u['credit'] <= 0) {
					$this->dropuser($u);
					if(IP_COST > 0 && $u['ip'] && $this->count_pays($u)==0) $this->release_ip($u);
					return false;
				}
				if(!$this->count_connect($u,"-1 day")) return true;
				$acct[0] = $a;
				$acct[0]['billing_minus'] = $p['fixed_cost'];
				$set['deposit'] = $u['deposit'] - $p['fixed_cost'];
				$set['late_payment'] = $now;
			} 
			if($p['fixed']==7 && $this->once) { // (Cron) Каждый День
				if($u['deposit']+$u['credit'] <= 0) {
					$this->dropuser($u);
					if(IP_COST > 0 && $u['ip'] && $this->count_pays($u)==0) $this->release_ip($u);
					return false;
				}
				$acct[0] = $a;
				$acct[0]['billing_minus'] = $p['fixed_cost'];
				$set['deposit'] = $u['deposit'] - $p['fixed_cost'];
				$set['late_payment'] = $now;
			}
			if($p['fixed']==8 && $this->once) { // (Cron) Каждый Месяц 1-го числа
				if($u['deposit']+$u['credit'] <= 0) {
					$this->dropuser($u);
					if(IP_COST > 0 && $u['ip'] && strtotime($now) > strtotime(date('Y-m-01'))+86400*7) $this->release_ip($u);
					return false; 
				}elseif(date('d')!=1) {
					if(!$this->once) log_txt("{$u['user']} Активация не требуется");
				}else{
					$acct[0] = $a;
					$acct[0]['billing_minus'] = $p['fixed_cost'];
					$set['deposit'] = $u['deposit'] - $p['fixed_cost'];
					$set['late_payment'] = $now;
				}
			}
			if($p['fixed']==9 && $this->once) { // (Cron) Каждый месяц обязан потратить
				if($u['deposit']+$u['credit'] <= 0) {
					$this->dropuser($u);
					if(IP_COST > 0 && $u['ip'] && strtotime($now) > strtotime(date('Y-m-01'))+86400*7) $this->release_ip($u);
					return false; 
				}elseif(date('d')!=1) {
					if(!$this->once) log_txt("{$u['user']} Активация не требуется");
				}else{
					$sum = $p['fixed_cost'] - $this->count_sum($u,date('Y-m-01',strtotime('-1 month')),date('Y-m-01'));
					if($sum>0) {
						$acct[0] = $a;
						$acct[0]['billing_minus'] = $diff;
						$acct[0]['callingstationid'] = "Доначислено-".$p['name'];
						$set['deposit'] = $u['deposit'] - $sum;
					}
				}
			}
			if($p['fixed']==10) { // (Cron) Каждый раз в момент начала пакета
				if(strtotime($u['expired']) > strtotime($now) && $u['deposit']+$u['credit'] >= 0) {
					if(!$this->once) log_txt("{$u['user']} Активация не требуется");
				}elseif(strtotime($u['expired']) > strtotime($now) && $u['deposit']+$u['credit'] < 0) {
					$this->dropuser($u);
					return false;
				}elseif($u['deposit']+$u['credit'] < $p['fixed_cost']) {
					if($u['expired'] == $now) $this->dropuser($u);
					if(IP_COST > 0 && $u['ip'] && strtotime($u['expired'])<strtotime('-7 day')) $this->release_ip($u);
					return false;
				}else{
					$acct[0] = $a;
					$acct[0]['billing_minus'] = $p['fixed_cost'];
					$set['expired'] = date("Y-m-d",strtotime($p['period']." month"));
					$set['deposit'] = $u['deposit'] - $p['fixed_cost'];
					$set['late_payment'] = $now;
					$this->dropuser($u,'on');
				}
			}
			$deposit = isset($set['deposit'])? $set['deposit'] : $u['deposit'];
			if(IP_COST > 0 && $u['ip'] && $p['fixed']>0 && (isset($set['late_payment']) || !$this->once) && !$this->gray_ip($u['ip'])){
				$ipcost = IP_COST * (($p['fixed']!=10)? 1 : $p['period']);
				if($deposit + $u['credit'] >= $ipcost) {
					$lastPay = strtotime($this->lastPayIP($u));
					if($p['fixed']!=10) $firstDay = strtotime(date('Y-m-01')); // если абонплата подневная или помесячная
					elseif(isset($set['late_payment'])) $firstDay = strtotime($set['late_payment']); // если абонплата плавающая и прошла оплата
					else $firstDay = strtotime($u['late_payment']);
					if($lastPay < $firstDay){
						$acct[1] = $a;
						$acct[1]['acctuniqueid'] = rand().rand();
						$acct[1]['before_billing'] = $deposit;
						$acct[1]['billing_minus'] = $ipcost;
						$acct[1]['callingstationid'] = "Абонпл-IP_".$u['ip'];
						$set['deposit'] = $deposit - $ipcost;
					}
				}
			}
			if($DEBUG>1 && count($acct)>0) log_txt("{$u['user']} insert SQL: ".$this->q->create_insert('radacct',$acct));
			if(!$DEBUG && count($acct)>0) $this->q->insert('radacct',$acct) or $this->set_error("Ошибка записи в таблицу radacct! uid={$u['uid']}");
			if($DEBUG>0 || !$this->once && count($acct)>0)
				foreach($acct as $ac) log_txt("{$u['user']} снято ".cell_summ($ac['billing_minus'])." ({$ac['callingstationid']})");
			if(!$this->once && $p['fixed']!=10 && $deposit+$u['credit']>0) $this->dropuser($u,'on');
			if($p['fixed']!=10 && $deposit+$u['credit']<=0) $this->dropuser($u,'off');
		}
		if(count($set)>1) { // данные пользователя изменены
			if($DEBUG>0) {
				$new = $this->q->compare($u,$set);
				if($new) {
					foreach($new as $k=>$v) $tmp[] = "`$k`='{$u[$k]}'->'$v'";
					log_txt("{$u['user']} ({$u['uid']}) ".implode(" ",$tmp));
				}
			}else $this->q->update_record("users",$set);
		}

		if(!$this->once && isset($set['late_payment'])) log_txt("{$u['user']} активирован");
		return $u['uid'];
	}

	function gray_ip($ip){
		$d = ip2long($ip);
		if(($d & 0xFFFF0000) == 3232235520 || ($d & 0xFF000000) == 167772160 || ($d & 0xFFF00000) == 2886729728) return true;
		return false;
	}

	function lastPayIP($u){
		return $this->q->select("SELECT date(acctstarttime) FROM radacct WHERE username='{$u['user']}' AND acctterminatecause='Admin-AbonPl' AND callingstationid like 'Абонпл-IP_%' ORDER BY acctstarttime DESC LIMIT 1",4);
	}

	function count_pays($u,$date='-7 day') {
		$d = date2db($date,false);
		return $this->q->select("SELECT count(*) FROM radacct WHERE username='{$u['user']}' AND acctstarttime>'$d' AND acctterminatecause='Admin-AbonPl'",4);
	}

	function count_connect($u,$date='now') {
		$d = date2db($date,false); $d1 = $d." 00:05:00";
		return $this->q->select("SELECT count(*) FROM radacct WHERE username='{$u['user']}' AND date(acctstarttime)<='$d' AND (acctstoptime>='$d1' OR acctstoptime is NULL) AND framedprotocol is not NULL AND pid>0",4);
	}

	function count_sum($u,$begin='-1 month',$end='now') {
		$begin = date2db($begin,false);
		$end = date2db($end,false);
		return $this->q->select("SELECT sum(billing_minus) FROM radacct WHERE username='{$u['user']}' AND acctstarttime between '$begin' AND '$end'",4);
	}

	function dropuser($u,$sw='off'){
		global $DEBUG;
		if($sw=='off') $filter = "AND pid>0"; elseif($sw='on') $filter = "AND pid=0"; else $filter = "";
		$a = $this->q->select("SELECT acctsessionid,uid,username,framedipaddress,nasipaddress,nasportid
			FROM radacct WHERE username='{$u['user']}' $filter AND acctstoptime is NULL");
		if($a) {
			if($DEBUG>0) {
				log_txt("$sw {$u['user']} включена процедура пересоединения filter: '$filter'");
				return true;
			}else{
				$this->q->insert('raddropuser',$a);
				if(!$this->once) log_txt("{$u['user']} включена процедура пересоединения");
			}
		}
	}

	function release_ip($u,$note="пакет закончился и прошло более 7 дней"){
		global $DEBUG;
		if($this->gray_ip($u)) return true;
		if($DEBUG>0) {
			log_txt($u['user']." освобожден IP ".$u['ip']);
			return true;
		}
		if($this->q->query("DELETE FROM radreply WHERE username='{$u['user']}' AND attribute='Framed-IP-Address'")) {
			log_db($u['user'],$u['uid'],"освобожден IP",$u['ip'].($note?" $note":""));
			log_txt($u['user']." освобожден IP {$u['ip']} $note");
		}
	}

	function read_access($fieldname,$acl) {
		global $opdata, $DEBUG;
		if($DEBUG>6) log_txt("form->read_access: [status]={$opdata['status']} поле `$fieldname` = access={$acl['r']}");
		if(is_array($acl) && @$acl['r'] && is_numeric($acl['r']) && $opdata['status']>=$acl['r']) return true;
		elseif(is_string($acl) && function_exists($acl)) return $acl($opdata['login'],$opdata['status']);
		elseif(is_numeric($acl) && $opdata['status']>=$acl) return true;
		else {
			if($DEBUG>3) log_txt("доступ (чтение) к {$this->table}[{$fieldname}] запрещен");
			return false;
		}
	}

	function write_access($fieldname,$acl) {
		global $opdata, $DEBUG;
		if($DEBUG>6) log_txt("form->write_access: opdata[status]='{$opdata['status']}' поле `$fieldname` access='{$acl['w']}'");
		if(is_array($acl) && @$acl['w'] && is_numeric($acl['w']) && $opdata['status']>=$acl['w']) return true;
		elseif(is_string($act) && function_exists($acl)) return $acl($opdata['login'],$opdata['status']);
		elseif(is_numeric($act) && $opdata['status']>=$acl) return true;
		else {
			if($DEBUG>0) log_txt("доступ (запись) к {$this->name}[{$fieldname}] запрещен");
			return false;
		}
	}

	public function send_sms($phone,$message) {
		$out = $this->q->insert('sms',array('op'=>$this->op['login'],'phone'=>$phone,'message'=>$message));
		return $out;
	}
}


class user extends radius {

	function __construct($r){
		global $config;
		parent::__construct('');
		$this->read_data($r);
	}

	function read_data($r){
		global $DEBUG;
		if(is_numeric($r) && $r > CITYCODE * 10000 && $r < (CITYCODE + 1) * 10000){
			$req = array("contract"=>$r);
			if($DEBUG>0) log_txt(__method__.": search by contract=$r");
		}if(is_numeric($r)){
			$req = array("uid"=>$r);
			if($DEBUG>0) log_txt(__method__.": search by uid=$r");
		}elseif(is_string($r)){
			$req = array('user'=>$r);
			if($DEBUG>0) log_txt(__method__.": search by user=$r");
		}elseif(is_array($r)){
			$req = array_intersect_key($r,$this->q->table_fields('users'));
			foreach($req as $k=>$v) if(function_exists($f = 'normalize_'.$k)) $req[$k] = $f($v);
			if($DEBUG>0) log_txt(__method__.": search by array=".arrstr($req));
		}else{
			$this->data=false;
			if($DEBUG>0) log_txt(__method__.": no data for search=".arrstr($req));
			return;
		}
		if($u = $this->get('users',$req)) {
			if(isset($u[0])){
				if(count($u) > 1){
					$this->set_error("Найдено пользователей ".count($u)." Запрос: ".arrstr($req),__METHOD__);
					$new = false;
				}else{
					$new = array_shift($u);
				}
			}else $new = $u;
			if($DEBUG>2) log_txt(__METHOD__.": \$new = ".arrstr($new));
		}else{
			$new=false;
		}

		if(!isset($this->data) || $new['pid']>0 && $new['pid'] != $this->data['pid']) {
			$this->tarif = $this->q->get('packets',$new['pid']);
			if($DEBUG>1) log_txt(__METHOD__.": \$this->tarif = ".arrstr($this->tarif));
		}

		$this->ip = $this->q->select("SELECT value FROM radreply WHERE username='{$new['user']}' AND attribute='Framed-IP-Address'",SELECT_SINGLEVAL);
		$this->last_connect();
		if($DEBUG>0) log_txt(__METHOD__." user = `".@$new['user']."`");
		$this->data = $new;
		$this->active = false;
	}

	function last_connect(){
		if(!isset($this->data)) return false;
		$this->connect = $this->q->select("SELECT * FROM radacct WHERE username='{$this->data['user']}' AND nasipaddress!='' AND pid>0 ORDER BY acctstarttime DESC LIMIT 1",SELECT_FIRSTRECORD);
		return $this->connect;
	}

	function is_online(){
		if(!isset($this->data)) return false;
		if(!isset($this->connect)) $this->last_connect();
		$this->online = $this->q->select("SELECT * FROM radacct WHERE username='{$new['user']}' AND acctstoptime is NULL ORDER BY acctstarttime DESC LIMIT 1",SELECT_FIRSTRECORD);
		return $this->online;
	}

	function change($r){
		global $DEBUG;
		$filter = array(
			'address'=>'normalize_address',
			'phone'=>'normalize_phone',
			'email'=>'normalize_mail',
			'credit'=>'allow_credit',
			'password'=>'',
		);
		if(!is_array($r)) {
			$this->set_error(": Запрос должен быть массивом!",__METHOD__);
			return false;
		}
		$r = array_intersect_key($r,$this->q->table_fields('users'));
		$new = array();
		foreach($r as $k=>$v) {
			if(!isset($filter[$k])) {
				$this->set_error(": Параметр $v не может быть изменён!",__METHOD__);
				continue;
			}
			if(method_exists('user', $method = $filter[$k])) $v = $this->$method($v);
			$new[$k] = $v;
		}
		if(count($new)>0) {
			$new['uid'] = $this->data['uid'];
			$res = $this->set("users",$new);
		}
		if(!$res) {
			log_txt(__METHOD__.": no change: ".arrstr($r));
			return false;
		}
		$this->read_data($this->data['uid']);
		return $res; 
	}

	public function localization($csid='',$opt82=''){
		global $opdata, $client, $guest;
		if(!$csid || !$opt82){
			$this->set_error("Пустые данные");
			return false;
		}
		if(!preg_match('/^[0-9A-F]{2}[-:][0-9A-F]{2}[-:][0-9A-F]{2}[-:][0-9A-F]{2}[-:][0-9A-F]{2}[-:][0-9A-F]{2}$/i',$csid)) {
			$this->set_error("Ошибка аппаратного адреса");
			return false;
		}
		if(!preg_match('/^0x[0-9A-F][0-9A-F]*/i',$opt82)) {
			$this->set_error("Ошибка опции 82");
			return false;
		}
		if($opt82 && $this->data['opt82'] && $opt82 != $this->data['opt82'] && $opdata['level']<3){
			$this->set_error("Запрещено менять точку подключения");
			return false;
		}
		$new['uid'] = $this->data['uid'];
		if($csid) $new['csid'] = $csid; if($opt82) $new['opt82'] = $opt82;
		$dbl = $this->q->fetch_all("SELECT uid, address FROM users WHERE csid='$csid' AND uid!='{$this->data['uid']}'");
		if($dbl) {
			if($this->q->query("UPDATE users SET csid='' WHERE uid in (".implode(',',array_keys($dbl)).")")){
				log_db($this->data['user'],$this->data['uid'],"удалил csid у ",implode(', ',$dbl));
				log_txt("удалил csid у клиентов ".implode(', ',$dbl));
			}
		}
		if($new = $this->set('users',$new)){
			$this->update();
			return $new;
		}else return false;
	}
	
	function update() {
		global $DEBUG;
		if($DEBUG>0) log_txt(__METHOD__." user: ".$this->data['user']);
		if(!$this->data) return false;
		if(!($new = $this->q->get('users',$this->data['uid']))) return false;
		if(!isset($new['uid'])) {
			$this->set_error(__METHOD__." обновление неудачно \$new: ".arrstr($new));
			return false;
		}
		$up = $this->q->compare($this->data,$new);
		if(count($up)>0) {
			$this->data = $new;
			if(isset($up['pid'])) $this->tarif = $this->q->get('packets',$up['pid']);
			if($DEBUG>0) log_txt(__METHOD__." обновлены поля: ".arrstr($up));
		}
		$this->ip = $this->q->select("SELECT value FROM radreply WHERE username='{$new['user']}' AND attribute='Framed-IP-Address'",SELECT_SINGLEVAL);
		$this->last_connect();
		return true;
	}

	public function activate() {
		global $DEBUG;
		if(isset($this->data['uid'])){
			$this->activate_user($this->data);
			$this->update();
			return true;
		}else{
			return false;
		}
	}

	private function normalize_address($a) {
		$a=trim($a);
		return normalize_address($a);
	}

	private function normalize_mail($m) {
		$m=trim(mb_strtolower($m));
		$m=preg_replace('/[^0-9a-z@\-_.]*$/','',$m);
		return $m;
	}

	public function normalize_phone($p) {
		return normalize_phone($p);
	}

	public function disconnect($id=''){
		$fld = array('acctsessionid','uid','username','framedipaddress','nasipaddress','nasportid','callingstationid');

		if($id) $r = $this->q->get('radacct',$id,'',$fld);
		else $r = $this->q->get('radacct',array('acctstoptime'=>null,'username'=>$this->data['user']),'',$fld);
		if(!$r){ $this->set_error('соединение не найдено!'); return false; }
		if(!isset($r[0])) $r = array($r);

		$nas = $this->q->select("SELECT nasipaddress, nastype, secret FROM nas",2,'nasipaddress');
		foreach($r as $k=>$v) $s[] = $v['acctsessionid'];
		$dropped = $this->q->select("SELECT * FROM raddropuser WHERE acctsessionid in (".implode(',',quote($s)).")");
		if($dropped) { $this->set_error('Повторное обращение!'); return true; }
		foreach($r as $k=>$v){
			$this->q->insert('raddropuser',$v);
			$kdir = preg_match('/[^\/]$/',trim(USERKILLDIR))? trim(USERKILLDIR)."/" : USERKILLDIR;
			$cmd = "/usr/bin/sudo -u root ".$kdir.$nas[$v['nasipaddress']]['nastype'].".userkill ";
			$cmd .= "{$v['nasipaddress']} {$v['framedipaddress']} {$v['nasportid']} {$v['username']} {$nas[$v['nasipaddress']]['secret']}";
			@exec($cmd." 2>&1",$output,$res);
			if($res!==0) {
				$out = (count($output)>0)? '<p>'.implode("\n",$output).'</p>' : '';
				$this->set_error("Ошибка выключения пользователя! ($res)".$out);
				$this->q->del('raddropuser',$v['acctsessionid'],'acctsessionid');
				return false;
			}else{
				dblog('log',array(
					'user'=>$v['username'],
					'uid'=>($this->data)? $this->data['uid']:0,
					'action'=>"закрыл соединение",
					'content'=>"NAS={$v['nasipaddress']}:{$v['nasportid']} MAC={$v['callingstationid']}"
				));
			}
			$this->q->del('raddropuser',$v['acctsessionid'],'acctsessionid');
		}
		return $s;
	}

	private function allow_credit($c) {
		$sum = $this->q->select("SELECT sum(abs(credit)) FROM users;",SELECT_SINGLEVAL);
		if($sum + $c > CREDIT_LIMIT) {
			$this->set_error("Превышен кредитный лимит!");
			return false;
		}
		return true;
	}
}

class payment {

	function __construct($conf){
		$this->error = "";
		$this->errors = array();
		if(!isset($conf['db'])) log_txt(__method__.": ERROR Нет конфигурации базы данных radius!");
		$this->q = new sql_query($conf['db']);
		if(!isset($conf['db_cards'])) log_txt(__method__.": ERROR Нет конфигурации базы данных cards!");
		$this->q1 = new sql_query($conf['db_cards']);
		$this->mydate = date("Y-m-d", time());
		$this->user = false;
		$this->operator = $this->db_operator();
		$this->order = $this->db_order();
		$this->base_valute = $this->q->select("SELECT * FROM currency WHERE rate=1.0",SELECT_FIRSTRECORD);
		if(!$this->base_valute) $this->base_valute = array('id'=>1,'name'=>'рубль','rate'=>1.00,'short'=>'руб');
		$this->valute = $this->base_valute;
		$this->povod = false;
		$this->payment = array(
			'from' => $this->operator['login'],
			'oid' => $this->order['oid']
		);
    }

	function set_error($message,$type=false){
		log_txt((($type)?"$type: ":'').$message);
		if($type == '' || $type == 'ERROR') {
			$this->error = (($type)?"$type: ":'').$message;
			$this->errors[] = $message;
		}
	}

    function check($r) {    // Проверка существования абонента
		if(is_array($r)){
			$this->user = $this->db_user($r);
			return $this->user->data;
		}
		return false;
    }

    function auth($r){    // Проверка авторизации абонента
		if($DEBUG>0) log_txt(__METHOD__);
		if(isset($r['login']) && isset($r['password'])){
			if($user = $this->db_user($r)) {
				if($user['user'] == $r['login'] && $user['password'] == $r['password']) return $user['uid'];
			}
		}
		return false;
    }

    function pay($r){    // Производим оплату
		global $DEBUG;
		if($DEBUG>0) log_txt(__METHOD__." ".arrstr($r));
		$this->user = $this->db_user($r);
		if(isset($this->user->data['uid'])) { // пользователь найден
			$currency = (isset($r['currency']))? $r['currency'] : 0;
			if(isset($r['card'])) { // присутствует N карточки (клиент)
				if($DEBUG>0) log_txt(__METHOD__." type=cards");
				if(!($card = $this->card = $this->check_card($r['card']))) return false;
				if($DEBUG>1) log_txt(__METHOD__." \$card = ".arrstr($card));
				if(!($pay_id = $this->db_user_pay_exist($r['card']))) {
					$r['povod_id']=1;
					$r['money'] = $card['nominal'];
					$r['currency'] = $card['currency'];
					if(!isset($r['contract']) && isset($this->user->data['contract']))
						$r['contract'] = $this->user->data['contract'];
					if($pay = $this->db_create_pay($r)) {
						$this->q1->update_record('cards',array(
							'cards_id'=>$card['cards_id'],
							'status'=>'u',
							'uid'=>$this->user->data['uid'],
							'user'=>$this->user->data['user']
						));
					}
					$this->payment_result = "PAY_ACCEPT";
					return $pay;
				}else{ // карточка использована
					$this->pay_id = $pay_id;
					$this->set_error("Платеж существует!");
					$this->payment_result = "PAY_EXIST";
					return false;
				}
			}elseif(isset($r['payment_id'])) { // присутствует N платежа (банк)
				if($DEBUG>0) log_txt(__METHOD__." type=payment_id");
				if(!($pay_id = $this->db_user_pay_exist($r['payment_id']))) {
					$r['card'] = $r['payment_id']; unset($r['payment_id']);
					if($pay = $this->db_create_pay($r)) $this->payment_result = "PAY_ACCEPT";
					else $this->payment_result = "PAY_ERROR";
					return $pay;
				}else{ // платеж существует
					$this->pay_id = $pay_id;
					$this->set_error("Платеж существует!");
					$this->payment_result = "PAY_EXIST";
					return false;
				}
			}else{ // ввод оператора
				if($DEBUG>0) log_txt(__METHOD__." type=other");
				if($pay = $this->db_create_pay($r)) $this->payment_result = "PAY_ACCEPT";
				else $this->payment_result = "PAY_ERROR";
				return $pay;
			}
		}else{
			foreach($r as $k=>$v) if(key_exists($k,array_flip(array('contract','user','uid')))) break;
			$this->set_error("пользователь {$k}:{$r[$k]} не найден!","ERROR"); 
			return false;
		}
    }

	function closeorder(){
		if($this->order > 0)
			return $this->db_closeorder($this->order);
		else
			return false;
	}

    function remove_pay($r){    // Удаляем Платеж
		global $DEBUG;
		$pay = false;
		if(is_array($r)){
			$this->user = $this->db_user($r);
			if(isset($this->user->data['uid'])) {
				if(isset($r['card']) && $r['card']!='') {
					if(!($pay = $this->check_use_card($r['card']))){
						$this->set_error('card not found');
					}
				}elseif(isset($r['payment_id'])) {
					if($pay_id = $this->db_user_pay_exist($r['payment_id'])){
						$pay = $this->q->get('pay',$pay_id);
					}else{
						$this->set_error("платеж '{$r['payment_id']}' пользователя '{$this->user->data['user']}' не найден");
					}
				}elseif(isset($r['unique_id'])){
					if(!($pay = $this->q->get('pay',$r['unique_id'])))
						$this->set_error("платеж '{$r['unique_id']}' пользователя '{$this->user->data['user']}' не найден!");
				}
			}else{
				$this->set_error('user not found');
			}
		}else{
			$pay = $this->q->get('pay',$r);
		}
		if($pay){
			if(isset($pay[0])) $pay = array_shift($pay);
			$pvd = $this->get_povod($pay['povod_id']);
			if($this->operator['status']>2){
				if($pay['oid']>0 && ($order = $this->q->get('orders',$pay['oid']))) {
					if($order['close'])
						$this->set_error("Ведомость закрыта!");
					if($this->operator['status'] < 5 && (strtotime($pay['acttime']) < strtotime('-3 day')))
						$this->set_error("Время возможного удаления истекло!");
					if($this->operator['status'] < 4 && $this->operator['login'] != $order['operator'])
						$this->set_error("Вы не можете удалять<br> платежи другого оператора!");
					if($this->operator['status'] < 3) $this->set_error("Доступ запрещён!");
					if(count($this->errors)>0) return false;
				}
			}elseif(isset($r['from']) && $pay['from']!=$r['from']){
				$this->set_error("access denied");
				return false;
			}
			if($this->q->del('pay',$pay['unique_id'])){
				if($this->card){
					$this->q1->update_record('cards',array(
						'cards_id'=>$this->card['cards_id'],
						'status'=>'a',
						'uid'=>0,
						'user'=>''
					));
				}
				if($pvd['calculate']==1) {
					if(!$this->q->query("UPDATE users SET deposit=deposit-{$pay['summ']} WHERE uid='{$pay['uid']}'"))
						$this->set_error("Ошибка восстановления deposit!");
				}
				if($pvd['kassa']>0) {
					$this->q->query("UPDATE kassa SET balance=balance-{$pay['summ']} WHERE kid='{$pay['kid']}'");
				}
				if(isset($r['contract'])) $pay['contract'] = $r['contract'];
				log_txt("удалил платеж {$pay['unique_id']} {$pay['user']} {$pay['acttime']} ".cell_summ($pay['summ']));
			}else $this->set_error('Ошибка удаления платежа!');
		}
		return $pay;
    }

	function payment_list($r) {
		if(isset($r['from'])){
			$begin = isset($r['begin'])? date2db($r['begin']) : $begin = date('Y-m-d');
			$end = isset($r['end'])? date2db($r['end']) : $end = date('Y-m-d 23:59:59');
			$out = $this->q->select("
				SELECT contract, card, paytime, service, money 
				FROM pay p, users u 
				WHERE p.uid = u.uid AND `from`='{$r['from']}' AND paytime between '$begin' AND '$end'");
			if(!$out){
				$this->set_error("list is empty!");
				$out = array();
			}
			return $out;
		}else{
			$this->set_error("not found param from!");
			return false;
		}
	}

    private function db_operator() {
		global $opdata, $client;
		$op = $this->q->get('operators',$opdata['login'],'login');
		if(!$op && isset($client['uid'])) {
			$op = array('blocked'=>0,'status'=>2,'login'=>'CLIENT','fio'=>shortfio($client['fio']),'document'=>0,'oid'=>0,'kid'=>0);
			$this->set_error("operator установлен в CLIENT ({$op['fio']})","WARNING"); 
		}
		if(!$op && isset($opdata['login'])) {
			$op = array('blocked'=>0,'status'=>1,'login'=>$opdata['login'],'fio'=>$opdata['login'],'document'=>0,'oid'=>0,'kid'=>0);
		}
		if(!$op && !isset($opdata)) {
			$op = array('blocked'=>0,'status'=>1,'login'=>'GUEST','fio'=>'Неизвестный','document'=>0,'oid'=>0,'kid'=>0);
			$this->set_error("operator установлен в GUEST","WARNING"); 
		}
		return $op;
    }

	private function db_order() {
		global $DEBUG, $opdata;
		$now = date('Y-m-d');
		$doc = $this->q->get('orders',$this->operator['document']);
		if(!$doc) $doc = $this->q->select("
			SELECT * FROM `orders` WHERE operator='{$this->operator['login']}'
			AND `close` is NULL ORDER BY `open` DESC LIMIT 1
		",1);
		if(!$doc) {
			$doc = $this->db_neworder();
		}elseif(date('Y-m-d',strtotime($doc['open'])) != date('Y-m-d')) {
			$this->db_closeorder($doc);
			$doc = $this->db_neworder();
		}
		if($DEBUG>0) log_txt(__METHOD__." order = ".arrstr($doc));
		return $doc;
	}

	private function db_neworder($kassa=0) {
		global $DEBUG, $opdata;
		if(isset($this->operator['login'])) {
			$kassa = isset($opdata['kid'])? $opdata['kid'] : 0;
			if($oid = $this->q->insert('orders',array('operator'=>$this->operator['login'],'kassa'=>$kassa))) {
				if(isset($this->operator['unique_id'])) {
					$this->q->update_record('operators',array('unique_id'=>$this->operator['unique_id'],'document'=>$oid));
					$opdata['document'] = $oid;
				}
				$this->operator['document'] = $oid;
				return $this->q->get('orders',$oid);
			}
		}
		$this->set_error("Вызов ".__METHOD__." для несуществующего оператора!","WARNING"); 
		return false;
	}

	private function db_closeorder($doc) {
		global $DEBUG;
		if(!is_array($doc)) {
			log_txt(__METHOD__.": ERROR! doc is not array!");
			return false;
		}
		$id = $doc[$this->q->table_key('orders')];
		if(!($id>0)) return false;
		// Берем из базы сумму по ведомости
		$summ = $this->q->select("
			SELECT sum(p.summ) FROM pay as p, orders as o, povod as pv 
			WHERE p.oid=o.oid AND p.povod_id=pv.povod_id AND pv.kassa=1 AND p.oid='{$doc['oid']}'",SELECT_SINGLEVAL);
		if(!$summ) $summ=0;
		if(AUTO_CLOSE_KO > 0){
			$c = $this->q->select("SELECT count(*) FROM pay WHERE oid='{$doc['oid']}'",SELECT_SINGLEVAL);
			if($c == 0) {
				log_txt("ведомость N ".$doc['oid']." пуста! Удаляется...");
				$this->q->del('orders',$doc['oid']);
			}else{
				$o = array(
					'oid' => $doc['oid'],
					'close' => now(),
					'summa' => $summ,
				);
				if(AUTO_ACCEPT_KO > 0){
					$o['acceptor'] = 'auto';
					$o['accept'] = now();
				}
				$this->q->update_record('orders',$o);
				log_txt("ведомость N ".$doc['oid']." закрыта!");
			}
		}
	}

	private function db_user($r) {
		global $DEBUG;
		if($DEBUG>0) log_txt(__METHOD__);
		$filter = array('uid'=>0,'contract'=>0,'user'=>0);

		if(is_numeric($r) && mb_strlen($r)==6 && preg_match('/^'.CITYCODE.'/',$r)) $req = array("contract"=>$req);
		if(is_numeric($r) && mb_strlen($r)==4) $req = array("uid"=>$req);
		if(is_string($r)) $req = array('user'=>$req);
		if(!is_array($r) || count($r)==0) return false;

		$req = array_intersect_key($r, $filter);
		$user = new user($req);
		if($DEBUG>0) log_txt(__METHOD__." \$user: ".$user->data['user']);
		return $user;
	}

	private function db_user_pay_exist($payment_id) {
		global $DEBUG;
		if($DEBUG>0) log_txt(__METHOD__);
		if(is_array($payment_id) && isset($payment_id['full'])) $payment_id = $payment_id['full'];
		$res = false;
		if($payment = $this->q->get("pay",$payment_id,'card')) {
			if(isset($payment[0])) $payment = array_shift($payment);
			$this->payment = $payment;
			$res = $payment['unique_id'];
		}
		return $res;
	}

	private function get_valute($currency){
		global $DEBUG;
		$valute=false;
		if(is_numeric($currency)){
			if(is_array($this->valute) && $this->valute['id']==$currency) return $this->valute;
			$valute=$this->q->select("SELECT * FROM currency WHERE id='$currency'",SELECT_FIRSTRECORD);
		}
		if(is_string($currency)){
			if(is_array($this->valute) && $this->valute['short']==$currency) return $this->valute;
			$valute=$this->q->select("SELECT * FROM currency WHERE short='$currency'",SELECT_FIRSTRECORD);
		}
		if(!$valute) $valute = $this->base_valute;
		$this->valute = $valute;
		if($DEBUG>0) log_txt(__METHOD__." \$this->valute: ".$valute['short']);
		return $valute;
	}

	private function get_povod($id,$r=null,$fn=null){
		if(is_array($this->povod) && $this->povod['povod_id']==$id) return $this->povod;
		$povod = $this->q->select("SELECT * FROM povod WHERE povod_id='$id'",SELECT_FIRSTRECORD);
		if(!$povod) $povod = $this->q->select("SELECT * FROM povod WHERE povod_id='6'",SELECT_FIRSTRECORD);
		if(!$povod) $povod = array('povod_id'=>6,'povod'=>'Пополнение лицевого счета','calculate'=>1,'kassa'=>1,'diagram'=>1,'typeofpay'=>2);
		$this->povod = $povod;
		return $povod;
	}

	private function db_create_pay($r=array()) {
		global $DEBUG;
		if($DEBUG>2) log_txt(__METHOD__.": input=".arrstr($r));
		// Проверка входных данных
		$needed = array('contract'=>0, 'money'=>0, 'currency'=>0, 'payment_id'=>0, 'povod_id'=>0);
		$default = array('currency'=>'', 'payment_id'=>'', 'povod_id'=>6);
		foreach($needed as $k=>$v) if(!isset($r[$k])) { // если нет нужных полей
			if(isset($default[$k])) { // если есть данные по умолчанию
				$r[$k] = $default[$k];
			}else{
				$this->set_error(__METHOD__.": Запрос не содержит `$k`\n\t\$r:".arrstr($r));
				return false;
			}
		}
		if($DEBUG>0) log_txt(__METHOD__.": \$r: ".arrstr($r));

		$r['currency'] = arrfld($valute = $this->get_valute($r['currency']),'id');
		$r['povod_id'] = arrfld($pvd = $this->get_povod($r['povod_id']),'povod_id');
		$r['summ'] = $summ = $r['money'] * $valute['rate'];
		$r['acttime'] = date("Y-m-d H:i:s");

		if($pvd['diagram']>0 && $summ<0) {
			$this->set_error("Статья платежа не разрешает отрицательных сумм!");
			return false;
		}
		if(!($pvd['diagram']==0 && $pvd['typeofpay']==2) && $summ<0 && 
		$this->user->data['deposit'] + $this->user->data['credit'] + $summ < 0) { // отр. сумма - заменить на услугу
			$this->set_error("Недостаточно денег на счету");
			return false;
		}
		if($this->user->data['contract'] != $r['contract']) {
			unset($this->user);
			$this->user = $this->db_user($r['contract']);
			if($this->user->data['contract'] != $r['contract']) {
				$this->set_error("Не удалось найти пользователя в базе! (N контракта: {$r['contract']})");
				return false;
			}
		}
		$usr = array_intersect_key($this->user->data, array('uid'=>0,'user'=>0,'pid'=>0,'rid'=>0));
		$req = array_intersect_key($r, $this->q->table_fields('pay'));
		$this->payment = array_merge($this->payment, $usr, $req);
		if($DEBUG>0) log_txt(__METHOD__.": \$this->payment: ".arrstr($this->payment));

		$mod = 0;
		if(isset($r['payment_id']) && $r['payment_id'] && $summ == 0) {
			$this->set_error("Сумма = 0",'ERROR');
			return false;
		}
		if($summ <> 0){
			if($insert_id = $this->q->insert('pay', $this->payment)) {
				$this->payment['unique_id'] = $insert_id;
				if($pvd['kassa']>0 && ($kid = $this->operator['kid'])>0) {
					$this->q->query("UPDATE kassa SET balance=balance+{$summ} WHERE kid='{$kid}'");
				}
				if($pvd['calculate']) {
					$mypay = array_intersect_key($this->user->data,array('uid'=>0,'deposit'=>1));
					$mypay['deposit'] += $summ;
					if(@CREDIT_AUTOREMOVE>0 && $this->user->data['credit'] > 0 && $mypay['deposit'] >=0){
						$mypay['credit'] = 0;
						log_db($this->payment['user'],$this->payment['uid'],"автоудаление кредита","кредит: 0");
						log_txt("{$this->payment['user']} автоудаление кредита");
					}
					if($this->q->update_record('users',$mypay)) {
						$mod = $this->q->modified();
						$this->user->update();
						$s1 = cell_summ($this->payment['summ'])." {$valute['short']}.";
						$s2 = "deposit=".cell_summ($this->user->data['deposit'])." {$this->base_valute['short']}.";
						log_db($this->payment['user'],$this->payment['uid'],$pvd['povod'],"$s1 $s2");
						log_txt("{$this->payment['user']} {$pvd['povod']} $s1 $s2");
					}
				}
			}else{
				$this->set_error('ошибка записи оплаты');
				return false;
			}
		}
		if($summ>0) send_notify('pay',array(
			'summa'=>cell_summ($summ),
			'fio'=>$this->user->data['fio'],
			'contract'=>$this->user->data['contract'],
			'uid'=>$this->user->data['uid'],
			'valute'=>$valute['short'],
			'basevalute'=>$this->base_valute['short'],
			'email'=>$this->user->data['email'],
			'phone'=>$this->user->data['phone'],
			'deposit'=>cell_summ($this->user->data['deposit'])
		));
		if($mod>1) $this->set_error("таблица `users`, изменено $mod записей",'WARNING');
		if(!isset($r['activate']) || (isset($r['activate']) && $r['activate']>0)) $this->user->activate();
		return $insert_id;
	}

	private function check_use_card($card) {
		global $DEBUG;
		$card = trim($card);
		if(!($pay = $this->q->get('pay',$card,'card'))){
			$this->set_error("Платеж не найден"); return false;
		}
		if(isset($pay[0])) $pay = array_shift($pay);
		if (preg_match("/^([0-9]{4})-([0-9]{4})-([0-9]{4})-([0-9]{4})-([0-9]{2})$/", $card, $m)) {
			$this->card = false;
			$serie = @$m[1];
			$sn = @$m[2].@$m[3].@$m[4].@$m[5];
			if(!($db_card = $this->q1->select("SELECT * FROM `cards` WHERE series='$serie' AND sn='$sn'",SELECT_FIRSTRECORD))) {
				$this->set_error("Карточка не существует"); return false;
			}
			if (strtotime($db_card['expired']) <= time()) {
				$this->set_error("Срок действия карточки истек");
				return false;
			}
			if ($db_card['status'] == "l") { $this->set_error("Карточка блокирована"); return false; }
			if ($db_card['status'] != "u") { $this->set_error("Карточка использована"); return false; }
			$this->card = $db_card;
		}
		return $pay;
	}

	private function check_card($card) {
		global $DEBUG;
		$card = trim($card);
		if(mb_strpos($card,"-") === false) {
			// карточка "старого" типа: серия не применяется
			$serie = "0000";
			$sn = $card;
		}else{
			// проверка синтасиса строки кода
			if (!preg_match("/^([0-9]{4})-([0-9]{4})-([0-9]{4})-([0-9]{4})-([0-9]{2})$/", $card, $m)) {
				$this->set_error("Ошибка ввода"); return false;
			}
			// Разделение и компоновка серии и номера
			$serie = @$m[1];
			$sn = @$m[2].@$m[3].@$m[4].@$m[5];
		}
			
		// поиск в таблице кода карточки
		if(!($db_card = $this->q1->select("SELECT * FROM `cards` WHERE series='$serie' AND sn='$sn'",SELECT_FIRSTRECORD))) {
			$this->set_error("Карточка не найдена"); return false;
		}

		// проверка на блокировку и использованность карточки
		if ($db_card['status'] == "u") { $this->set_error("Карточка использована"); return false; }
		if ($db_card['status'] == "l") { $this->set_error("Карточка блокирована"); return false; }
		if ($db_card['status'] == "d") { $this->set_error("Карточка удалена"); return false; }
	
		// проверка истечения времени использования
		if (strtotime($db_card['expired']) <= time()) {
			$this->set_error("Срок действия карточки истек");
			return false;
		}
		return $db_card;
	}
}

class HtmlElement {

	private
		$tabindex = 0,
		$filter = array(
			'field' => array('in'=>array('id'=>0,'label'=>0)),
			'input' => array('out'=>array('label'=>0,'native'=>0)),
			'nofield' => array('out'=>array('label'=>0,'value'=>0)),
			'area' => ''
		);
	
	function __construct(){
		$this->tabindex = 0;
	}

	public function create($o) {
		if(!($type = ($o['type']!='')? $o['type'] : false)) return false;
		$method = '_'.$type;
		if(method_exists($this, $method)) return $this->$method($o);
		else return "HtmlElement->create: method = '$method' is unknown!";
	}
	
	private function keysin($o,$filter) {
		return array_intersect_key($o,$filter);
	}
	
	private function keysout($o,$filter) {
		return array_diff_key($o,$filter);
	}

	private function check($o,$type='') {
		if(isset($this->filter[$type])) {
			foreach($this->filter[$type] as $k=>$v) {
				$filter = 'keys'.$k;
				if(method_exists($this,$filter))
					$o = $this->$filter($o,$v);
			}
		}
		return $o;
	}

	function properties($o) {
		$r=array();
		foreach($o as $k=>$v) if(is_string($v)) {
			$s = preg_replace('/"/','&#8243;',$v);
			$r[] = "$k=\"$s\"";
		}
		return implode(' ',$r);
	}

	private function getField($o,$s) {
		$d = array('class' => 'form-item');
		if(isset($o['name'])) $d['id'] = "field-".$o['name'];
		if(isset($o['id'])) $d['id'] = $o['id'];
		$obj = "<div ".$this->properties($d).">\n";
		$obj .= "<span class=\"label\">{$o['label']}</span>\n";
		$obj .= "<span class=\"field\">\n$s</span>\n</div>\n";
		return $obj;
	}

	function _text($o) {
		$obj = "<input ".$this->properties($this->check($o,'input'))." />\n";
		return $this->getField($o,$obj);
	}

	function _hidden($o) {
		return "<input ".$this->properties($this->check($o,'input'))." />\n";
	}

	function _button($o) {
		return "<input ".$this->properties($this->check($o,'input'))." />\n";
	}

	function _submit($o) {
		return $this->_button($o);
	}

	function _cancel($o) {
		return $this->_button($o);
	}

	function _date($o) {
		$o['type']='text';
		$obj = "<input ".$this->properties($this->check($o,'input'))." />\n";
		return $this->getField($o,$obj);
	}

	function _nofield($o) {
		$obj = "<div ".$this->properties($this->check($o,'nofield')).">".$o['value']."</div>\n";
		return $this->getField($o,$obj);
	}

	function _password($o) {
		$obj = "<input ".$this->properties($this->check($o,'input'))." />\n";
		return $this->getField($o,$obj);
	}

	function _textarea($o) {
		$obj = "<textarea ".$this->properties($this->check($o,'area')).">".$o['value']."</textarea>\n";
		return $this->getField($o,$obj);
	}

	function _select($o) {
		$obj = "<select ".$this->properties($this->check($o,'select')).">\n";
		if(is_array($o['list'])) foreach($o['list'] as $k=>$v) 
			$obj .= "<option".(($o['value']==$k)?" selected":"")." value=\"$k\">".$v."</option>\n";
		$obj .= "</select>\n";
		return $this->getField($o,$obj);
	}

	function _autocomplete($o) {
		$o['class'] .= ((@$o['class']=='')?'':' ').'autocomplete';
		$obj = "<input ".$this->properties($this->check($o,'input'))." />\n";
		return $this->getField($o,$obj);
	}

	function _ac($o) {
		return $this->_autocomplete($o);
	}
	
	function _subform($o) {
		// log_txt("HtmlElement->_subform: \$o: ".sprint_r($o));
		$cr = array(
			'td'=>array('class'=>0,'style'=>0),
			'table'=>array('id'=>0,'class'=>0,'style'=>0,'target'=>0,'delete'=>0,'module'=>0,'tname'=>0)
		);
		if(!isset($o['sub']) || !is_array($o['sub']) || !isset($o['sub']['table']) || !is_array($o['sub']['table']) || 
			!isset($o['sub']['table']['thead']) || !isset($o['sub']['table']['tbody'])) {
			log_txt("HtmlElement->_subform: ошибка \$o: ".sprint_r($o));
			return '';
		}
		$tbody="<tbody>\n";
		foreach($o['sub']['table']['tbody'] as $id=>$v) {
			$tbody .= "<tr id=\"$id\">\n";
			foreach ($o['sub']['table']['thead'] as $cell=>$c) {
				$tbody .= "<td ".$this->properties(array_intersect_key($c,$cr['td'])).">".$v[$cell]."</td>";
			}
			$tbody.="<td class=\"del\"><img class=\"button\" src=\"pic/delete.png\" /></td></tr>\n";
		}
		$tbody.="</tbody>\n";
		$t="<table ".$this->properties(array_intersect_key($o['sub']['table'],$cr['table'])).">\n".$tbody."</table>\n";
		return '<div '.$this->properties(array_intersect_key($o['sub'],$cr['td'])).">\n".$t."</div>\n";
	}
}
?>
