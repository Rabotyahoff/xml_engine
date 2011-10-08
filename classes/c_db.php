<?php
load_lib('db_mysql');

class c_db {

	/**
	 * db
	 *
	 * @var DB_Sql
	 */
  public $db;
  public $sql;
  public $last_id;
  /**
   * =true when last query return a data
   *
   * @var boolean
   */
  public  $enable_data;

  private $is_monitor=false;
  private $start_time=0;
  private $is_conneted=false;


  function __construct($db_name, $user, $password, $host) {
    $this->is_monitor=($_REQUEST['debug_db']==1 && _GL_DEBUG===true);
  	$this->enable_data=FALSE;
  	$p_last_id=0;
    $this->db=new DB_Sql();

    $this->db->Database=$db_name;
    $this->db->User=$user;
    $this->db->Password=$password;
    $this->db->Host=$host;
  }

  function __destruct(){
  	$this->db->free();
  	$this->db->close();
  }

  function monitor($is_monitor=true){
    $this->is_monitor=$is_monitor;
  }

  protected function after_query(){
    if ($this->is_monitor){
      $stop_time=get_microtimestamp();
      $count_time=round($stop_time-$this->start_time, 3);
      if ($count_time>=0.1) echo '<label style="color:red">';
      else echo '<label>';
      echo ' <b>'.$count_time.' sec</b>';
      echo '</label>';
      echo " <BR/>\n<BR/>\n";
    }
  }

  function connect_to_db() {
    if (!$this->is_conneted) $this->db->connect();
  }

  function transaction_start(){
    $this->db_query('START TRANSACTION');
  }

  function transaction_commit(){
    $this->db_query('COMMIT');
  }

  function transaction_rollback(){
    $this->db_query('ROLLBACK');
  }


  protected function set_sql($sql){
  	$sql=trim($sql);
  	$this->sql=$sql;

  	if ($this->is_monitor){
  	  echo $sql;
  	  $this->start_time=get_microtimestamp();
  	}
  }

  /**
   * return like
   * $sql="select id, email from forward order by email";
   *
   * Array
   * (
   *     [2] => first1@mail.com
   *     [3] => first2@mail.com
   *     [4] => first3@mail.com
   *     [5] => first4@mail.com
   *     [1] => first@mail.com
   * )
   *
   * @param string $sql
   * @param unknown_type $default
   * @return array
   */
  function get_fill_array($sql='',$default=array()) {
    $this->connect_to_db();
  	$this->enable_data=FALSE;
    $sql=trim($sql);
    if (mb_strlen($sql)>0) $this->set_sql($sql);

    $this->db->query($this->sql);
    $this->after_query();
    if ($this->db->next_record ()) {
      do {
        $ar_lookup [$this->db->f ( 0 )]=$this->db->f ( 1 );
      } while ( $this->db->next_record () );
      $this->enable_data=TRUE;
      return $ar_lookup;
    }
    else return $default;
  }

  /**
   * return array like [param_name0]->val0, [param_name1]->val1, ...
   *
   * @param string $sql
   * @param unknown_type $default
   * @param int $max_rows default=0 - all rows
   * @return array
   */
  function get_array($sql='',$default=array(),$max_rows=0) {
    $this->connect_to_db();
  	$this->enable_data=FALSE;
    $sql=trim($sql);
    if (mb_strlen($sql)>0) $this->set_sql($sql);

    $this->db->query($this->sql);
    $this->after_query();
    if ($this->db->next_record ()) {
      $cur_rows=0;
      do {
        $ar_lookup[]=$this->db->Record;
        $cur_rows++;
      } while (($this->db->next_record ())&&(($max_rows<$cur_rows)||($max_rows==0)));

      $this->enable_data=TRUE;
      return $ar_lookup;
    }
    else {
      return $default;
    }
  }

  /**
   * результат запроса в виде массива, где в качестве ключей - значения поля $key
   *
   * @param str $sql - запрос
   * @param str $key - название поля для ключа массива
   * @param bool $group_by_key - если =true, то каждым элементом массива будет массив значений, которые сгруппированы по key
   * @param unknown_type $default
   * @param int $max_rows
   */
  function get_array_key($sql='',$key='',$group_by_key=false,$default=array(),$max_rows=0){
    $res=$this->get_array($sql,$default,$max_rows);
    $res2=array();
    foreach ($res as $v){
      if ($group_by_key) $res2[$v[$key]][]=$v;
      else $res2[$v[$key]]=$v;
    }
    return $res2;
  }

  /*
   * Return first record (array) of query
   * res[0]
   */
  function get_array_first_record($sql='',$default=array()) {
    return $this->get_array_record_num($sql,0,$default);
  }

  /*
   * Return record number (array) of query
   * res[num_record]
   */
  function get_array_record_num($sql='',$num_record=0,$default=array()) {
    $res=$this->get_array($sql,$default,$num_record+1);
    if (is_array($res)) return $res[$num_record];
    else return $res;
  }

  /*
   * Run query whitout result
   * (insert, update, ...)
   * return last inserted autoincrement id
   */
  function db_query($sql='') {
    $this->connect_to_db();
  	$this->enable_data=FALSE;
    $sql=trim($sql);
    if (mb_strlen($sql)>0) $this->set_sql($sql);
    $this->db->query($this->sql);
    $this->last_id=@mysql_insert_id($this->db->Link_ID);
    $this->after_query();
    return $this->last_id;
  }

  function get_db_value($sql='',$default='') {
    $this->connect_to_db();
    $this->enable_data=FALSE;
  	$sql=trim($sql);
    if (mb_strlen($sql)>0) $this->set_sql($sql);

    $this->db->query($this->sql);
    $this->after_query();
    if ($this->db->next_record ()){
    	$this->enable_data=TRUE;
      return $this->db->f( 0 );
    }
    else return $default;
  }

  /**
   * $where_condition - whithout "where"
   *
   * @param unknown_type $table_name
   * @param unknown_type $field_name
   * @param string $where_condition
   * @param unknown_type $default
   * @return unknown
   */
  function db_lookup($table_name,$field_name,$where_condition='',$default='') {
    $table_name=trim($table_name);
    $field_name=trim($field_name);
    $where_condition=trim($where_condition);

    $where='';
    if (mb_strlen($where_condition)>0) $where=' WHERE '.$where_condition;
    $sql="SELECT ".$field_name." FROM ".$table_name.$where;
    return $this->get_db_value($sql,$default);
  }

  function get_unique_values_to_field($table_name,$field_name,$wheres=false,$limit=false) {
  	$sql="SELECT distinct $field_name FROM $table_name".$this->make_where($wheres).$this->make_limit($limit);
  	$res=$this->get_array($sql);
  	$res2=array();
  	foreach ($res as $item){
  	  $res2[]=$item[$field_name];
  	}
  	sort($res2,SORT_STRING);
  	return $res2;
  }


  //c_db::strip($val);
  static public function strip($value) {
    if (get_magic_quotes_gpc ()==0)
    return $value;
    else  return stripslashes ( $value );
  }

  //c_db::get_checkbox_value(...);
  public function get_checkbox_value_tosql($value,$checked_value,$unchecked_value,$type='Text') {
    if (!mb_strlen ( $value ))
      return $this->tosql ( $unchecked_value, $type );
    else return $this->tosql ( $checked_value, $type );
  }

  public function tosql($value, $type_='Text', $empty_is_null=false) {
    $this->connect_to_db();
    $type=strtolower ( $type_ );
    if (!mb_strlen ( $value )) {
    	if ($empty_is_null) return "NULL";
    	return "''";
    }

    else if (($type=="number")||($type=="n")) {
      $value=preg_replace('/[^0-9,.]/','',$value);
      return doubleval ( str_replace ( ",", ".", $value ) );
    } else {
    	// Stripslashes
    	if (get_magic_quotes_gpc()) {
    		$value = stripslashes($value);
    	}
    	// Quote if not integer
    	if (!is_int($value)) {
    		$value = "'" . mysql_real_escape_string($value) . "'";
    	}
    	return $value;
    }
  }

  public function tosql_array($inArray) {
  	if (!is_array($inArray)) return $this->tosql($inArray);
  	foreach ($inArray as $k=>$itm){
  	  $inArray[$k]=$this->tosql($itm);
  	}
  	return $inArray;
  }


/**
 * remove "'" and "\\"
 * if you send get or post parametr you mustn't use it
 * use it only when you send to db self-created string
 */
  public function remove_quotes($value){
    $this->connect_to_db();
    $value=mysql_real_escape_string($value,$this->db->Link_ID);

    return $value;
  }

  static function make_where($wheres,$delim='AND',$word='WHERE') {
    if (is_array($wheres)){
      foreach ($wheres as $k=>$itm){
        $wheres[$k]="($itm)";
      }
      $where=implode(" $delim ",$wheres);
    }
    else $where=$wheres;
    if (!empty($where)) $where=' '.$word.' '.$where.' ';
    return $where;
  }

  /**
   * Создаём условие IN для SQL-запроса
   *
   * @param string $param_name - имя параметра
   * @param array $in_values - массив значений
   * @param string $word - IN
   * @return string
   */
  public function make_in($param_name,$in_values=array(),$word='IN'){
    if (!empty($in_values)) {
    	load_lib('array');
      $in_values=make_array($in_values);
      foreach ($in_values as $key=>$itm){
        $in_values[$key]=$this->tosql($itm);
      }
      $res=implode(',',$in_values);
      $res=" $param_name $word ($res) ";
    }
    else $res='';
    return $res;
  }

  static function make_limit($limit='',$word='LIMIT'){
    if (is_array($limit)) $s_limit=implode(',', array_values($limit));
    else {
      $s_limit=$limit;
    }
    if (!empty($s_limit)) $s_limit=" $word $s_limit ";
    return $s_limit;
  }

  static function make_order($order,$word='ORDER BY'){
    $res='';
    $order=trim($order);
    if (!empty($order)) {
      $res=" $word $order ";
    }
    return $res;
  }

  static function make_group($group,$word='GROUP BY'){
    $res='';
    $group=trim($group);
    if (!empty($group)) {
      $res=" $word $group ";
    }
    return $res;
  }
}


