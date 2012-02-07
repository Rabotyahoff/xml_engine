<?php

header('Content-type: text/html; charset=utf-8');
mb_http_output("UTF-8");
//mb_http_input("UTF-8");
mb_language("uni");//=utf-8
mb_internal_encoding("UTF-8");

load_class('c_session');
load_lib('utils');
load_class('c_global');
load_class('c_user');
load_class('c_cache');

/*Begin создаём сесию*/
global $o_session;
$o_session=new c_session();
/*End создаём сесию*/

/*Begin создаём глобальные константы*/
global $o_global, $site_root;
$o_global=new c_global(dirname(__FILE__).'/', $site_root);
/*End создаём глобальные константы*/

/*Begin main functions*/
function load_class($class_name){
  global $engine_root;
  include_once $engine_root.'classes/'.$class_name.'.php';
}

function load_lib($lib_name){
  global $engine_root;
  include_once $engine_root.'lib/'.$lib_name.'.php';
}

/**
 * загрузка класса обработчика
 * вначале ищем в папке handlers сайта,
 * а затем, если не нашли, в папке handlers движка
 *
 * @param $page_name
 */
function load_handler($handler){
  global $o_global;
  $path_part='handlers/'.$handler.'.php';
  $path_site=$o_global->site_root.$path_part;
  $path_engine=$o_global->engine_root.$path_part;
  if (file_exists($path_site)) {
    include_once $path_site;
    return true;
  }
  elseif (file_exists($path_engine)){
    include_once $path_engine;
    return true;
  }
  else {
    echo "Can't load PAGE '$handler' by path '$path_part' <BR/>\n";
    return false;
  }
}

function load_class_local($class_name){
  global $o_global;
  $path=$o_global->site_root.'classes/'.$class_name.'.php';
  if (file_exists($path)) {
    include_once $path;
    return true;
  }
  else {
    echo "Can't load CLASS '$class_name' by path '$path' <BR/>\n";
    return false;
  }
}

function load_lib_local($lib_name){
  global $o_global;
  $path=$o_global->site_root.'lib/'.$lib_name.'.php';
  if (file_exists($path)) {
    include_once $path;
    return true;
  }
  else {
    echo "Can't load CLASS '$lib_name' by path '$path' <BR/>\n";
    return false;
  }
}

function get_handler_class($handler){
  $arr=explode('/',$handler);
  return array_pop($arr);
}

function make_cache_key($with_session=true){
  global $o_global, $o_cur_user;
  $str='';
  if ($o_cur_user->is_logined) $str.='user_id='.$o_cur_user->user_id.':';
  $str.=$o_global->current_url.'?';
  //здесь не юзаем $_SERVER['REQUEST_URI'], т.к. надо отсортировать параметры
  if (is_array($_GET)){
    $arr=$_GET;
    ksort($arr);
    foreach ($arr as $k=>$v){
      if (is_array($v)) {
        ksort($v);
        $v=serialize($v);
      }
      $str.=$k.'='.$v.'&';
    }
  }
  if ($with_session){
    if (is_array($_SESSION)){
      $arr=$_SESSION;
      ksort($arr);
      foreach ($arr as $k=>$v){
        if (is_array($v)) {
          ksort($v);
          $v=serialize($v);
        }
        $str.=$k.'='.$v.'&';
      }
    }
  }
  return md5($str);
}

/**
 * показать страницу 404
 */
function show_404(){
  global $o_global;
  if (!$o_global->is_ajax) {
    $o_global->url_params=array('404');
    run_site();
  }
  else {
    echo 'error';
  }
  exit();
}

function run_site(){
  global $o_global;
  /* Ищем обработчик для заданного пути
   * обработчики задаются в _site.xml
   *
   * Если для пути /one/two/three в _site.xml обработчик не задан, то ищется обработчик для /one/two,
   * если нет и для этого пути, то бурётся обработчик для /one. Иначе выводится 404
   *
   * */
  $url_params=$o_global->url_params;
  if ($url_params[0]=='res'){
    //для ресурсов сразу отдаём 404
    header("HTTP/1.0 404 Not Found");
    /*$tmp=$o_global->site_root.implode('/',$url_params);
    echo file_get_contents($tmp);*/
    exit();
  }
  do {
    $cur_page=$o_global->get_page_by_screen($url_params);
    array_pop($url_params);
  }
  while ($cur_page===false && !empty($url_params));
  $o_global->curr_page=$cur_page;

  if ($o_global->curr_page===false){
    //попробуем найти эту же страницу, но без учёта прав
    $url_params=$o_global->url_params;
    do {
      $cur_page_nr=$o_global->get_page_by_screen($url_params, $o_global->site_full_array['pages']);
      array_pop($url_params);
    }
    while ($cur_page_nr===false && !empty($url_params));

    if ($cur_page_nr===false){
      //этой страницы нет вообще
      $o_global->curr_page=$o_global->get_page_by_screen('404');
      if ($o_global->curr_page===false) {
        header("HTTP/1.0 404 Not Found");
        echo '404';
        exit();
      }
    }
    else {
      //эта страница есть, значит для просмтора не хватает прав
      //перебросим на страницу логина
      if (!$o_global->is_ajax){
        global $o_session;
        $o_session->set_session('login_from', '', $_SERVER['REQUEST_URI']);
        redirect_to('/login');
      }
      else {
        echo 'error';
        exit();
      }
    }
  }

  //если задан редирект
  if (isset($o_global->curr_page['redirect'])){
    redirect_to($o_global->curr_page['redirect']['.']);
  }

  if ($o_global->curr_page['@ajax']==1){
    $o_global->is_ajax=true;
  }

  $out_result=false;
  /*Begin проверим кеш*/
  if ($o_global->settings_array['enable_cache']['.']==1 && $o_global->curr_page['@cache_time']>0){
    //кеш включён
    $o_cache=new c_cache($o_global->curr_page['@cache_time']);
    if (empty($_POST) && !$o_global->is_ajax){
      $cache_key=make_cache_key();//если установлен, то кеш сохраним

      $out_result=$o_cache->get($cache_key);
    }
    else {
      //какие-то действия, значит надо очистить кеш
      $o_cache->remove(make_cache_key());
    }
  }
  /*End проверим кеш*/

  /*Begin из кеша не взяли*/
  if ($out_result===false){
    $handlers=$o_global->curr_page['handlers'];
    if (is_array($handlers)){
      if ($o_global->is_ajax){
        $handler=$handlers['content']['.'];
        if (load_handler($handler)){
          $handler_class=get_handler_class($handler);
          $o_handler=new $handler_class();
          $o_handler->handler_info=$handlers['content'];
          $out_result=$o_handler->run();
        }
      }
      else {
        $dta=array();
        foreach ($handlers as $part=>$handler_arr){
          if (c_xml::is_system_key($part)) continue;
          if (!isset($o_global->curr_page['handlers'][$part])) continue;//в обработчик мог изменить количество обработчиков

          $handler=$handler_arr['.'];
          if (load_handler($handler)){
            $handler_class=get_handler_class($handler);
            $o_handler=new $handler_class();
            $o_handler->handler_info=$handler_arr;
            $dta['parts'][$part]=$o_handler->run();
          }
        }

        /*устанавливаем параметры из раздела <params></params> страницы*/
        if (is_array($o_global->curr_page['params'])){
        	foreach ($o_global->curr_page['params'] as $k=>$itm){
        		if (c_xml::is_system_key($k)) continue;
        		$params[$k]=$itm['.'];
        	}
        }

        if (is_array($o_global->curr_page['title'])) $params['title']=$o_global->curr_page['title']['.'];
        else $params['title']=$o_global->curr_page['title'];

        if (is_array($o_global->curr_page['description'])) $params['description']=$o_global->curr_page['description']['.'];
        else  $params['description']=$o_global->curr_page['description'];

        if (is_array($o_global->curr_page['keywords'])) $params['keywords']=$o_global->curr_page['keywords']['.'];
        else $params['keywords']=$o_global->curr_page['keywords'];

        if (is_array($o_global->curr_page['theme'])) $out_result=xsl_out($o_global->curr_page['theme']['.'],'page',$dta, false, $params, false, false);
        else $out_result=xsl_out($o_global->curr_page['theme'],'page',$dta, false, $params, false, false);
      }
    }
    else {
      //если обработчики не заданы, то просто выводим xsl
      $params['title']=$o_global->curr_page['title']['.'];
      $params['description']=$o_global->curr_page['description']['.'];
      $params['keywords']=$o_global->curr_page['keywords']['.'];
      $out_result=xsl_out($o_global->curr_page['theme']['.'],'page',array(), false, $params, false, false);
    }

    /*Begin сохраним кеш*/
    if (isset($cache_key)){
      $o_cache->set($cache_key, $out_result);
    }
    /*End сохраним кеш*/
  }
  /*End из кеша не взяли*/

  echo $out_result;
}
/*End main functions*/

/*Begin создаём менеджер БД*/
global $o_db_man;
$o_db_man=new c_db_manager();
/*End создаём менеджер БД*/

/*Begin создаём текущего пользователя*/
global $o_cur_user;
$o_cur_user=new c_cur_user('main');
/*End создаём текущего пользователя*/

/*Begin зачистка структуры сайта*/
$o_global->check_parents();
$o_global->check_site_rights();//удалим неподходящие по правам записи структуры сайта
/*End зачистка структуры сайта*/
