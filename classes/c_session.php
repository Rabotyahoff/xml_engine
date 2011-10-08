<?php

class c_session {

  function __construct() {
    session_start();
  }

  public function get_session($param_name,$screen_name='') {
    if (empty($screen_name)) $screen_name='def';
    return $_SESSION[$screen_name][$param_name];
  }

  public function set_session($param_name,$screen_name='',$param_value) {
    if (empty($screen_name)) $screen_name='def';
    $_SESSION[$screen_name][$param_name]=$param_value;
  }

  public function unregister_session($param_name,$screen_name='') {
    if (empty($screen_name)) $screen_name='def';
    unset($_SESSION[$screen_name][$param_name]);
  }

  public function destroy_session() {
    session_destroy();
  }

}


