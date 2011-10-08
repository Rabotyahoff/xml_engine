<?php
//привязка validate к имени элемента формы

/*
 * TODO:
 * required chekbox
 *
 */
abstract class a_filter {

  protected $fields = array();
  protected $patterns = array();
  protected $is_panic = false;

  function __construct() {
    $this->init();
    if($this->patterns) {
      foreach($this->fields as $field=>&$rule) {
        foreach($this->patterns as $pattern=>$patternRule) {
          if(preg_match($pattern,$field)) {
            $rule = array_merge($patternRule, $rule);
          }
        }
      }
    }
  }

  abstract function init();

  function add_rule($field_name,$err_text='', $is_required=false, $default=false, $is_email=false, $is_url=FALSE){
    $tmp=array('err_text'=>$err_text, 'err_text_js'=>str_js($err_text));
    if ($is_required) $tmp['required']=$is_required;
    if ($default!==false) $tmp['default']=$default;
    if ($is_email) $tmp['email']=$is_email;
    if ($is_url) $tmp['url']=$is_url;
    $this->fields[$field_name]=$tmp;
  }

  public function form_to_db($post, $add_defaults = false, $is_panic=true) {
    $this->is_panic = $is_panic;
    $db = array();

    foreach($this->fields as $field=>$rules) {

      if(!isset($post[$field])) {
        if($add_defaults) {
          if(isset($rules['default'])) {
            $post[$field] = $rules['default'];
          } else {
            throw new Exception("No default value for field '$field'");
          }
        } else {
          if(isset($rules['required'])) {
            $this->panic($field, 'required');continue;
          }
          else continue;
        }
      }

      $value = $post[$field];

      if(isset($rules['enum'])) {
        if(!isset($rules['enum'][$value])) {
          $this->panic($field, 'enum');continue;
        }
      }

      if(isset($rules['limit'])) {
        $value = (int)$value;
        if (isset($rules['limit']['min'])&&($value<$rules['limit']['min'])) {
          $this->panic($field, 'limit_min');continue;
        } else
        if (isset($rules['limit']['max'])&&($value>$rules['limit']['max'])) {
           $this->panic($field, 'limit_max');continue;
        }
      }

      if(isset($rules['length'])) {
        $len = mb_strlen($value,'utf-8');
        if (isset($rules['length']['min'])&&($len<$rules['length']['min'])) {
          $this->panic($field,'length_min');continue;
        } else
        if (isset($rules['length']['max'])&&($len>$rules['length']['max'])) {
          $this->panic($field,'length_max');continue;
        }
      }

      if(isset($rules['regexp'])) {
        if(!preg_match($rules['regexp'], $value)) {
          $this->panic($field, 'regexp');continue;
        }
      }

      if(isset($rules['id'])) { //todo?
        $value = (int)$value;
        if($value<=0) {
          $this->panic($field, 'id');continue;
        }
      }

      if(isset($rules['url'])) {
        if(!is_url_valid($value)) {
          $this->panic($field, 'url');continue;
        }
      }

      if(isset($rules['email'])) {
        if(!is_email_valid($value)) {
          $this->panic($field, 'email');continue;
        }
      }

      if(isset($rules['required'])) {
        if(!$value) {
          $this->panic($field, 'required');continue;
        }
      }

      if(isset($rules['checkbox'])) {
        if(!in_array($value, array('y','n'))) {
          $this->panic($field, 'checkbox');continue;
        }
      }

      $db[$field] = $value;
    }
    return $db;
  }

  protected function panic($field, $reason) {
    if(!$this->is_panic) {
      return;
    }
    throw new Exception("Illegal value of field '$field', check '$reason' ");
  }

  public function get_defaults() {
    $db = array();
    foreach($this->fields as $field=>$rules) {
      $db[$field]=$rules['default'];
    }
    return $db;
  }

  function get_enum() {
    $r = array();
    foreach($this->fields as $field=>$rules) {
      /*
      if(isset($rules['checkbox'])) {
        $rules['enum'] = array(
          'y'=>'y',
          'n'=>'n',
        );
      }
       */

      if(!isset($rules['enum'])) {
        continue;
      }

      $t = array();
      foreach($rules['enum'] as $value=>$text) {
      $t[] = array('value'=>$value,'text'=>$text);
      }
      $r[$field]=$t;

    }
    return $r;
  }

  function get_limits() {
    $r = array();
    foreach($this->fields as $field=>$rules) {
      if(!isset($rules['limit'])) {
        continue;
      }
      $r[$field]=$rules['limit'];
    }
    return $r;
  }



  function get_validation_rules() {
    $tmp=$_REQUEST['debug_xsl'];
    $_REQUEST['debug_xsl']=0;

    $text = xsl_out("common/validation_rules.xsl",'rules',$this->fields,false);

    $_REQUEST['debug_xsl']=$tmp;
    $text = preg_replace("#,[\s\n]*?}#","}",$text);
    return $text;
  }
}
