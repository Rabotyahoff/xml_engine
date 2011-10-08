<?php
load_class('c_xml');

//XSL-пейджер
class c_pager {

  public $pid = 'p';
  public $xsl_file='common/pager.xsl';
  public $params=array();
  private $page_selected;

  function __construct($parametr_name=false) {
    if ($parametr_name!==false) $this->pid=$parametr_name;
    $this->page_selected=$_REQUEST[$this->pid];
    if (empty($this->page_selected) || !is_numeric($this->page_selected) || $this->page_selected<=0) $this->page_selected=1;
  }

  function get_limit($count_lines_on_page){
    $res=array();
    $res['from'] = ($this->page_selected-1)*$count_lines_on_page;
    $res['to']   = $count_lines_on_page;
    return $res;
  }

  private function make_page($num){
    $params[$this->pid]=$num;
    return array('num'=>$num, 'link'=>url_to_self($params));
  }

  function get_pages($count_total,$count_line_on_page,$count_pages_on_pager=10){
    $count_pages=ceil($count_total/$count_line_on_page);
    if ($count_pages<1) $count_pages=1;

    $dta=array();
    $dta['total_items']=$count_total;
    $dta['items_per_page']=$count_line_on_page;
    $dta['page_selected']=$this->page_selected;
    $dta['page_last']=$this->make_page($count_pages);
    $dta['page_first']=$this->make_page(1);

    if ($this->page_selected-1>0){
      $dta['page_prev']=$this->make_page($this->page_selected-1);
    }
    if ($this->page_selected+1<=$count_pages){
      $dta['page_next']=$this->make_page($this->page_selected+1);
    }

    $cur_left=$this->page_selected;
    $cur_right=$this->page_selected;
    $tmp_pages=array();
    $limit_pages=min($count_pages,$count_pages_on_pager);
    while (count($tmp_pages)<$limit_pages){
      if ($cur_left>0){
        $tmp_pages[$cur_left]=$this->make_page($cur_left);
        $cur_left--;
      }

      if (count($tmp_pages)<$limit_pages){
        if ($cur_right<=$count_pages){
          $tmp_pages[$cur_right]=$this->make_page($cur_right);
          $cur_right++;
        }
      }
    }//while

    ksort($tmp_pages);
    $tmp_pages=array_values($tmp_pages);
    $dta['pages']=$tmp_pages;

    if (isset($_REQUEST['debug_xsl']) && _GL_DEBUG===true){
      $old_is_debug=$_REQUEST['debug_xsl'];
      unset($_REQUEST['debug_xsl']);
    }
    $res=$dta;
    $res['html']=xsl_out($this->xsl_file,'pager',$dta,false,$this->params);
    if (isset($old_is_debug)){
      $_REQUEST['debug_xsl']=$old_is_debug;
    }
    return $res;
  }

}
