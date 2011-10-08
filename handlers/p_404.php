<?php
load_class('c_handler');

class p_404 extends a_handler {

  function ajax_process(){
    header("HTTP/1.0 404 Not Found");
    $this->h_result='error';
  }

  function process(){
    header("HTTP/1.0 404 Not Found");
    $this->h_result='';
  }
}

