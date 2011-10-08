<?php

class c_xls {

  protected $out;

  /**
  * @return c_xls
  */
  function __construct(){
    $this->clean();
    return $this;
  }

  /**
  * @return c_xls
  */
  function download_header($file_name='Excel.xls'){
    ob_clean();
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");
    header("Content-Disposition: attachment;filename=$file_name");
    header("Content-Transfer-Encoding: binary ");
    return $this;
  }

  /**
  * @return string
  */
  protected function get_xlsBOF() {
    return pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
  }

  /**
  * @return string
  */
  protected function get_xlsEOF() {
    return pack("ss", 0x0A, 0x00);
  }

  /**
  * @return c_xls
  */
  function xlsWriteNumber($Row, $Col, $Value) {
    $this->out.=pack("sssss", 0x203, 14, $Row, $Col, 0x0);
    $this->out.=pack("d", $Value);
    return $this;
  }

  /**
  * @return c_xls
  */
  function xlsWriteLabel($Row, $Col, $Value ) {
    $L = strlen($Value);
    $this->out.=pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
    $this->out.=$Value;
    return $this;
  }

  /**
  * @return c_xls
  */
  function out(){
    echo $this->out.$this->get_xlsEOF();
    return $this;
  }

  /**
  * @return c_xls
  */
  function clean(){
    $this->out=$this->get_xlsBOF();
    return $this;
  }

}