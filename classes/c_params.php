<?php

class c_params {
  public $overwrite_params=array();
  public $is_trim=true;

	private $is_only_our_host;
	private $is_our_host;

	function __construct($only_our_host=true, $is_trim=true) {
		$this->is_only_our_host=$only_our_host;
		$this->is_trim=$is_trim;

    if ($this->is_only_our_host){
      //test HTTP_REFERER
      if ((is_array($_SERVER))&&(array_key_exists('HTTP_REFERER',$_SERVER))) {
        $refer_page=$_SERVER['HTTP_REFERER'];
        $income_host=parse_url($refer_page);$income_host=$income_host['host'];
        $our_host=$_SERVER['HTTP_HOST'];
        $this->is_our_host=($income_host==$our_host);
      }
      else $this->is_our_host=false;//without HTTP_REFERER
    }
    else $this->is_our_host=true;//for succes tests in functions
	}

	function is_our_host(){
	  return $this->is_our_host;
	}

	function overwrite($param_name, $value){
		$this->overwrite_params[$param_name]=$value;
	}

	private function test_param($param_value,$default='',$is_number=false,$min='',$max=''){
    if (is_array($param_value)){
      foreach ($param_value as $key=>$val){
        $param_value[$key]=$this->test_param($val,$default,$is_number,$min,$max);
      }
      return $param_value;
    }

    if (mb_strlen ( $param_value )==0) $param_value=$default;
    if ($is_number) {
      if (!is_numeric ( $param_value ))
        $param_value=$default;
      if (($min!='')&&(is_numeric ( $min ))&&($param_value<$min))
        $param_value=$min;
      if (($max!='')&&(is_numeric ( $max ))&&($param_value>$max))
        $param_value=$max;
    }
    if ($this->is_trim && !is_numeric($param_value)) $param_value=trim($param_value);
    return $param_value;
	}

	function quotes_remove($value){
	  if (is_array($value)){
	    foreach ($value as $key=>$val){
	      $value[$key]=$this->quotes_remove($val);
	    }
	    return $value;
	  }

    if (get_magic_quotes_gpc()) {
      return stripslashes($value);
    }
    return $value;
	}

	private function _get($_array, $is_test_host, $param_name,$default='',$is_number=false,$min='',$max=''){
		if (array_key_exists($param_name,$this->overwrite_params)) return $this->test_param($this->overwrite_params[$param_name],$default,$is_number,$min,$max);
		$param_value=$default;
		if ($is_test_host && !$this->is_our_host) return $param_value;//if host-test is off p_is_our_host=true

		if (isset ( $_array[$param_name] )) $param_value=$_array[$param_name];
		return $this->test_param($this->quotes_remove($param_value),$default,$is_number,$min,$max);
	}

	/**
	 * Read $_REQUEST
	 * Don't test host!
	 *
	 * @return string
	 */
  function get_any_param($param_name, $default='', $is_number=false, $min='', $max='') {
    return $this->_get($_REQUEST, false, $param_name,$default, $is_number, $min, $max);
  }

  function get_any_params($param_names=array(),$default='',$is_number=false,$min='',$max=''){
    make_array($param_names);
    $res=array();
    foreach ($param_names as $val) {
    	$res[$val]=$this->get_any_param($val,$default,$is_number,$min,$max);
    }
    return $res;
  }

	/**
	 * Read $_REQUEST
	 *
	 * @return param-value
	 */
  function get_param($param_name,$default='',$is_number=false,$min='',$max='') {
    return $this->_get($_REQUEST, true, $param_name,$default, $is_number, $min, $max);
  }

	/**
	 * Read POST
	 *
	 * @return param-value
	 */
  function get_param_POST($param_name,$default='',$is_number=false,$min='',$max='') {
    return $this->_get($_POST, true, $param_name,$default, $is_number, $min, $max);
  }

	/**
	 * Read GET
	 *
	 * @return param-value
	 */
  function get_param_GET($param_name,$default='',$is_number=false,$min='',$max='') {
    return $this->_get($_GET, true, $param_name, $default, $is_number, $min, $max);
  }

  function get_params_to_line($param_names=array(), $delim='&'){
    $res=array();
    foreach ($param_names as $param_name){
      $res[]=$param_name.'='.$this->get_any_param($param_name);
    }
    return implode($delim,$res);
  }

  function get_params_POST($param_names=array(),$default='',$is_number=false,$min='',$max=''){
    $res=array();
    $param_names=make_array($param_names);
    foreach ($param_names as $param_name){
      $res[$param_name]=$this->get_param_POST($param_name,$default,$is_number,$min,$max);
    }
    return $res;
  }
}


