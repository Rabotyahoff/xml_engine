<?php

interface i_cache {
  function set($hash, $str);
  function get($hash);
  function remove($hash);
}

abstract class a_cache {

  protected $expire_sec=5;//время кеша в секундах
  public  $is_debug=false;

  function __construct($expire_sec){
    $this->is_debug =(_GL_DEBUG===true && ($_REQUEST['debug_cache']==1 || $_REQUEST['debug_cache']==get_class($this)));
    $this->set_expire_sec($expire_sec);
  }

  function set_expire_sec($expire_sec){
    $this->expire_sec=$expire_sec;
  }

  function make_hash($params, $is_add_class=true){
    if (is_array($params)) {
      ksort($params);
      foreach ($params as $k=>$v){
        $params[$k]=$this->make_hash($v,false);
      }
    }
    $res=serialize($params);
    if ($is_add_class) $res=get_class($this).$res;
    return md5($res);
  }

}

class c_filecache extends a_cache implements i_cache{

  function __construct($expire_sec){
    parent::__construct($expire_sec);
  }

  function set($hash, $str){
    global $o_global;
    if ($this->is_debug) echo "set filecache hash='$hash'<BR>";
    file_put_contents($o_global->cache_site_root.$hash,$str);
  }

  function get($hash){
    global $o_global;
    $path=$o_global->cache_site_root.$hash;
    $res=false;
    if (file_exists($path)){
      if (filemtime($path)+$this->expire_sec>=$_SERVER['REQUEST_TIME']){
        if ($this->is_debug) echo "result from filecache hash='$hash'<BR>";
        $res=file_get_contents($path);
      }
      else {
        $this->remove($hash);
      }
    }

    return $res;
  }

  function remove($hash){
    global $o_global;
    if ($this->is_debug) echo "remove from filecache hash='$hash'<BR>";
    unlink($o_global->cache_site_root.$hash);
  }

}

global $GL_memcache_connections;
if (!isset($GL_memcache_connections)){
  $GL_memcache_connections=array();
}

class c_memcache extends a_cache implements i_cache{

  /**
   *
   * @var Memcache
   */
  protected $memcache_lnk=null;

  /**
   *
   * @param $host хост мемкеша
   * @param $port порт мемкеша
   * @param $expire_sec время жизни кеша в секундах не более 2592000 (30 days). Или 0 - кеш навсегда
   */
  function __construct($expire_sec, $host='localhost', $port=11211){
    $this->host=$host;
    $this->port=$port;
    parent::__construct($expire_sec);
  }

  /**
   * @return Memcache
   */
  function memcache(){
    global $GL_memcache_connections;
    if ($this->memcache_lnk==null){
      $connection_key=$this->host.':'.$this->port;
      if (!isset($GL_memcache_connections[$connection_key])){
        $tmp=new Memcache();
        $tmp->pconnect($this->host, $this->port) or die ("Could not connect to memcache ".get_class($this));
        if (_GL_DEBUG===true && $_REQUEST['debug_memcache_flush']==1) $tmp->flush();//для сброса кеша
        $GL_memcache_connections[$connection_key]=$tmp;
      }
      $this->memcache_lnk=$GL_memcache_connections[$connection_key];
    }
    return $this->memcache_lnk;
  }

  function set($hash, $str){
    global $o_global;

    $set_time=$this->expire_sec;
    if ($this->is_debug) echo "set memcache hash='$hash'<BR>";
    $this->memcache()->set($hash, $str, false,  $set_time);
  }

  function get($hash){
    $res=$this->memcache()->get($hash);
    if ($res!==false && $this->is_debug) echo "result from memcache hash='$hash'<BR>";
    return $res;
  }

  function remove($hash){
    if ($this->is_debug) echo "remove from memcache hash='$hash'<BR>";

    $cur_val=$this->memcache()->get($hash);
    $this->memcache()->set($hash, $cur_val, false  , 1);//вместо удаления, которое не работает ставим кеш на 1 секунду
    //$this->memcache()->delete($hash);//удаление как-то плохо работает. Не удаляет из кеша, а только делает вид. Т.к. в текущей сесии данных не будет, но в следующей будут
  }
}

/**
 * класс кеша. Создаёт либо мемкеш либо файловый кеш.
 *
 * @author RA
 *
 */
class c_cache extends a_cache implements i_cache{

  /**
   * @var $o_cache i_cache
   */
  protected $o_cache;

  function __construct($expire_sec){
    //@TODO создать мемкеш или файловый в зависимости от настроек и доступности
    global $o_global;
    if (isset($o_global->settings_array['memcache'])){
      $this->o_cache=new c_memcache($expire_sec, $o_global->settings_array['memcache']['host']['.'], $o_global->settings_array['memcache']['port']['.']);
    }
    else {
      $this->o_cache=new c_filecache($expire_sec);
    }
  }

  function set($hash, $str){
    return $this->o_cache->set($hash, $str);
  }

  function get($hash){
    return $this->o_cache->get($hash);
  }

  function remove($hash){
    return $this->o_cache->remove($hash);
  }

}