<?php
load_lib('array');
load_class('c_db_manager');

  /**
   * абстрактный класс для получения данных из таблиц
   */

abstract class a_data {

  public $fields=null; //перекрываем запрошенные поля
  public $res;//все строки результата
  public $res_first;//только первая строка результата
  public $enable;//=true если запрос вернул результат
  public $count;//если в запросе $is_calck_count=true, то количество записей будет здесь
  /**
   * db
   *
   * @var c_db
   */
  public $db;
  public $is_debug=false;
  public $is_use_SQL_CALC_FOUND_ROWS=false;

  function __construct($db_id) {
    global $o_db_man;

    $this->res=array();
    $this->res_first=array();
    $this->enable=FALSE;
    $this->count=0;
    $this->db=$o_db_man->get_db($db_id);

    $this->is_debug=(_GL_DEBUG===true && ( $_REQUEST['debug_data']==1 || $_REQUEST['debug_data']==get_class($this) ));
  }

  function __destruct() {

  }

  /**
   *
   * @param unknown_type $fields
   * @param unknown_type $table
   * @param unknown_type $wheres
   * @param str $is_calck_count =true для подсчёта количества SQL_CALC_FOUND_ROWS (см $this->is_use_SQL_CALC_FOUND_ROWS). Можно задать свой SQL-запрос для подсчёта кол-ва
   * @param unknown_type $limit
   * @param unknown_type $order
   * @param unknown_type $group
   */
  protected function init_from_db_($fields='*',$table,$wheres=array(),$is_calck_count=false,$limit='',$order='',$group='') {
    if ($this->fields!=null) $fields=$this->fields;

    $this->res=array();
    $this->res_first=array();
    $this->enable=FALSE;
    $this->count=0;

    $wheres=make_array($wheres);
    $where=c_db::make_where($wheres);
    $limit=c_db::make_limit($limit);
    $order=c_db::make_order($order);
    $group=c_db::make_group($group);

    $add='';
    if ($is_calck_count===true && ($this->is_use_SQL_CALC_FOUND_ROWS || !empty($group))){
      //если группируем, то лучше считать по SQL_CALC_FOUND_ROWS, т.к. иначе
      // 1) в случае подсчёта count без группировки результат будет неверным
      // 2) с группировкой - агрегатная функция count(*) будет считать количество внутри группы и нам придётся считать кол-во рядов вручную, что не быстрее
      $add="SQL_CALC_FOUND_ROWS";
    }

    $sql="SELECT  $add
            $fields
          FROM $table
          $where
          $group
          $order
          $limit";

    if ($this->is_debug){
      echo '<BR>Class: <B>'.get_class($this).'</B><BR>';
    }

    $this->res=$this->db->get_array($sql,array());

    if (!empty($this->res)){
      $this->res_first=$this->res[0];
      $this->enable=true;
      if ($is_calck_count!==false){
        $c=array();
        if ($is_calck_count===true){
          if ($this->is_use_SQL_CALC_FOUND_ROWS ||  !empty($group)) $sql='SELECT FOUND_ROWS() as count';
          else{
            $sql="SELECT count(*) as count
                  FROM $table
                  $where";
          }
        }
        else $sql=$is_calck_count;

        $c=$this->db->get_array_first_record($sql);
        $this->count=$c['count'];
      }
    }

    return $this->res;
  }

  abstract function init_from_db($wheres=array(),$is_calck_count=false,$limit='',$order='', $group='');

}

