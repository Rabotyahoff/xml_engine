<?php

load_class('c_xml');
load_class('c_db_manager');
load_class('c_params');

class c_global {

  public $engine_root;
  public $site_root;
  public $cache_site_root;
  public $themes_site_root;
  public $themes_engine_root;

  public $site_name, $site_root_url, $current_url;
  public $engine_root_url;
  public $res_site_url;
  public $res_engine_url;
  public $res_site_root;
  public $res_engine_root;

  /**
   *
   * @var c_xml
   */
  public $o_settings;
  /**
   *
   * @var c_xml
   */
  public $o_site;

  public $site_full_array=array();//настройки сайта без удаленных нодов по правам
  public $settings_array=array();//установки сайта
  public $site_array=array();//настройки сайта

  /**
   *
   * @var c_params
   */
  public $o_params;
  public $url_params=array();

  public $is_ajax=false;
  public $curr_page=false;

  private $count_checked_parents=0;

  function __construct($engine_root, $site_root) {
    $this->engine_root=$engine_root;
    $this->site_root=$site_root;
    $this->themes_site_root=$this->site_root.'themes/';
    $this->cache_site_root=$this->site_root.'_cache/';
    $this->themes_engine_root=$this->engine_root.'themes/';

    $xml_text='';
    $xml_text=file_get_contents($this->site_root.'_settings.xml');
    $this->o_settings=new c_xml($xml_text);
    $this->settings_array=$this->o_settings->toArray();
    //print_r($this->settings_array);die;

    $this->site_name=$this->settings_array['site']['name']['.'];
    $this->site_root_url=$this->settings_array['site']['root_url']['.'];
    $this->engine_root_url=$this->settings_array['engine']['root_url']['.'];

    $this->res_site_url=$this->site_root_url.'res/';
    $this->res_engine_url=$this->engine_root_url.'res/';
    $this->res_site_root=$this->site_root.'res/';
    $this->res_engine_root=$this->engine_root.'res/';

    /*Begin загрузка структуры сайта*/
    $xml_text=file_get_contents($this->site_root.'_site/_main.xml');//основная часть сайта
    $this->o_site=new c_xml($xml_text);
      /*Begin добавление в структуру сайта информации из плагинов*/
      //подгружаются только item'ы
      $no_file=array('.','..','_main.xml');
      $d = dir($this->site_root.'_site');
      while (false !== ($entry = $d->read())) {
        if (!in_array($entry,$no_file) && strtolower(substr($entry,-4))=='.xml'){
          $xml_text=file_get_contents($this->site_root.'_site/'.$entry);
          $this->o_site->addItemsFromXML(new c_xml($xml_text),true);
        }
      }
      $d->close();
      /*End добавление в структуру сайта информации из плагинов*/
    //$this->site_array=$this->o_site->toArray();// создаётся в check_site_rights()
    //print_r($this->o_site->toArray());die;
    /*End загрузка структуры сайта*/

    global $o_params;
    $this->o_params=new c_params(true);
    $o_params=$this->o_params;
    $this->url_params=explode('/',$this->o_params->get_any_param('page'));

    $this->current_url=$this->site_root_url.implode('/',$this->url_params);
    $this->is_ajax=$_REQUEST['ajax']==1;
  }

  /**
   *
   * @param array $nodes
   * @param array $rights
   * @return c_xml_node
   */
  private function check_nodes_right($nodes, $rights){
    foreach ($nodes as $k=>$node){
      if (c_xml::is_system_key($k) || $k[0]==='@') continue;
      $node_rights=$node['@rights'];
      $do_del=true;

      if (!isset($node_rights)) $do_del=false;
      if ($do_del){
        $node_rights_arr=explode(',', $node_rights);
        foreach ($node_rights_arr as $node_rights_itm){
          $tmp_node_rights_itm=trim($node_rights_itm);
          if (in_array($tmp_node_rights_itm, $rights)){
            $do_del=false;
            break;
          }
        }
      }

      if ($do_del) unset($nodes[$k]);
      else $nodes[$k]=$this->check_nodes_right($node, $rights);
    }
    return $nodes;
  }

  private function merge_item(array $parent_item, array $item){
    $res=$item;
    foreach ($parent_item as $parent_k=>$parent_v){
      if (c_xml::is_system_key($parent_k)) continue;
      $res_v=$res[$parent_k];
      if (!isset($res_v)){
        $res_v=$parent_v;
      }
      else {
        if (is_array($res_v) && is_array($parent_v)) $res_v=$this->merge_item($parent_v, $res_v);
      }
      $res[$parent_k]=$res_v;
    }
    return $res;
  }

  /**
   * добавим данные о парентах
   */
  function check_parents($site_array=array()){
    $enable_parent=false;
    if (empty($site_array)) $this->site_array=$this->o_site->toArray();
    else $this->site_array=$site_array;
    //print_r($this->site_array);die;
    foreach ($this->site_array['pages'] as $k=>$v){
      if (c_xml::is_system_key($k)) continue;
      $parent=$v['@parent'];
      if (!empty($parent)){
        $enable_parent=true;
        $parent_screen=$this->get_page_by_name($parent);
        unset($v['@parent']);
        $res=$this->merge_item($parent_screen, $v);//array_merge - не подходит($parent_screen, $v);

        $this->site_array['pages'][$k]=$res;
      }
    }

    $set_o_site=true;
    if ($enable_parent){
      $this->count_checked_parents++;
      if ($this->count_checked_parents<3) {
        $set_o_site=false;
        $this->check_parents($this->site_array);
      }
    }

    if ($set_o_site) $this->o_site->fromArray('site',$this->site_array);
  }

  /**
   * Удаляем из $this->o_site все ноды, которые не подходят по правам текущем уюзеру
   */
  function check_site_rights(){
    global $o_cur_user;
    if ($o_cur_user->is_logined){
      $cur_rights=array('',$o_cur_user->rights);
      if ($o_cur_user->rights=='root'){
        $cur_rights[]='admin';
      }
    }
    else {
      $cur_rights=array('_unloggined_');
    }
    $this->site_full_array=$this->o_site->toArray();
    $this->site_array=$this->check_nodes_right($this->site_full_array, $cur_rights);
    //print_r($this->site_array);die;
    $this->o_site->fromArray('site',$this->site_array);
  }

  /*
   * Возвращает экран по имени
   */
  function get_page_by_name($name){
    global $o_cur_user;

    $pages=$this->site_array['pages'];

    /*Begin собираем все экраны с именем $screen*/
    $pages_like_screen=array();
    foreach ($pages as $k=>$v){
      if (c_xml::is_system_key($k)) continue;
      if ($v['@name']==$name){
        return $v;
      }
    }

    return false;
  }

  /*
   * Возвращает экран, который наиболее подходит по заданым правам
   */
  function get_page_by_screen($screen, $pages=false){
    global $o_cur_user;

    if (!is_array($screen)) $screen=explode('/',$screen);
    if ($pages===false) $pages=$this->site_array['pages'];
    $cur_screen=array_shift($screen);

    /*Begin собираем все экраны с именем $screen*/
    $pages_like_screen=array();
    foreach ($pages as $k=>$v){
      if (c_xml::is_system_key($k)) continue;
      if ($v['@screen']==$cur_screen){
        $pages_like_screen[]=$v;
      }
    }
    //die;
    /*End собираем все экраны с именем $screen*/

    //ничего не нашли
    if (count($pages_like_screen)==0) return false;

    $res_page=$pages_like_screen[0];

    /*if ($res_page===false){
      //ни один экран не подошел по правам.
      //root'у выдадим первый попавшийся
      if (isset($cur_user_rights) && $cur_user_rights=='root') $res_page=$pages_like_screen[0];
      else {
        //перебросим на экран логина
        if (!$this->is_ajax){
          global $o_session;
          $o_session->set_session('login_from', '', $_SERVER['REQUEST_URI']);
          redirect_to('/login');
        }
        else {
          echo 'error';
          exit();
        }
      }
    }*/

    if (empty($res_page['pages']) || empty($screen)) return $res_page;
    else return $this->get_page_by_screen($screen, $res_page['pages']);
  }

}

