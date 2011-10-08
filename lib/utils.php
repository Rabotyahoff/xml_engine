<?php

/**
* показывает стек вызовов функций
*/
function show_callstack(){
  echo "<B>Callstack:<B><BR>";
  $st=debug_backtrace();
  foreach ($st as $itm){
    echo '<B>File:</B> <i>'.$itm['file'].'</i><BR>';
    if (!empty($itm['class'])){
      echo '<B>Class:</B> '.$itm['class'].'<BR>';
    }
    echo '<B>Function:</B> '.$itm['function'].'<BR>';
    echo '<B>Line:</B> '.$itm['line'].'<BR>';
    if (!empty($itm['args'])){
      echo '<B>Arguments:</B> ';
      print_r($itm['args']);
      echo '<BR>';
    }
    echo '<BR>';
  }//$st
}

function redirect_to($page="/") {
  if (empty($page)) $page='/';
  //echo "redirect to $page";show_callstack();
  header ( 'HTTP/1.1 301 Moved Permanently' );
  header ( "Location: ".$page );
  exit();
}

function url_to_self($params=array()){
  $query=$_SERVER['REQUEST_URI'];
  $parts=explode('?',$query);
  $left=$parts[0];
  $right=$parts[1];

  if (!empty($right)){
    $rigth_params=explode('&',$right);
    $new_r_p=array();
    foreach ($rigth_params as $itm){
      $tmp=explode('=',$itm);
      $new_r_p[$tmp[0]]=$tmp[1];
    }
  }
  else {
    $new_r_p=array();
  }

  $rigth_params=array_merge($new_r_p, $params);
  $new_r_p=array();
  foreach ($rigth_params as $k=>$itm){
    $new_r_p[]=$k.'='.$itm;
  }


  $right=implode('&',$new_r_p);
  if (!empty($right)){
    return $left.'?'.$right;
  }
  else return $left;
}

function get_microtimestamp(){
  $timeofday=gettimeofday();
  return $timeofday['sec']+$timeofday['usec']/1000000;
}

/**
 *
 * размер width и height для графического текста
 *
 * @param unknown_type $size
 * @param unknown_type $font_file
 * @param unknown_type $text
 */
function get_text_size($size,$font_file,$text){
	/* Функция imagettfbbox возвращает нам массив из восьми элементов,
	    содержащий всевозможные координаты минимального прямоугольника,
	    в который можно вписать данный текст. Индексы массива
	    удобно обозначить на схеме в виде координат (x,y):

	     (6,7)           (4,5)
	       +---------------+
	       |Всем привет! :)|
	       +---------------+
	     (0,1)           (2,3)

     Число элементов массива может на первый взгляд показаться избыточным,
     но не следует забывать о возможности вывода текста под произвольным
     углом.

     По этой схеме легко вычислить ширину и высоту текста:
  */

	$coord=imagettfbbox($size,0,$font_file, $text);
  $width = $coord[2] - $coord[0];
  $height = $coord[1] - $coord[7];

  return array('width'=>$width, 'height'=>$height);
}
