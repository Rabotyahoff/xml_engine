<?php

load_class('c_db');

class c_db_manager {

  private $dbs=array();

  function __construct() {

  }

  /**
   *
   * @param str $db_id
   * @return c_db
   */
  function get_db($db_id){
    if (!isset($this->dbs[$db_id])){
      global $o_global;
      $nodes=$o_global->settings_array['databases'];
      $res_node='';
      foreach ($nodes as $k=>$node){
        if (c_xml::is_system_key($k)) continue;
        if ($node['@id']==$db_id){
          $res_node=$node;
          break;
        }
      }
      if (empty($res_node)) {
        $this->dbs[$db_id]=false;
      }
      else {
        $this->dbs[$db_id]=new c_db($res_node['name']['.'],$res_node['user']['.'],$res_node['password']['.'],$res_node['host']['.']);
      }
    }
    return $this->dbs[$db_id];
  }
}

