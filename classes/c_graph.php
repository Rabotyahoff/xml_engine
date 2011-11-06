<?php
class c_graph {

  public static function get_im($src){
    if (empty($src)) return false;
    $info=@getimagesize($src);
    if (is_array($info)){
      $type_img=$info[2];
      /* 1 = GIF,
         2 = JPG,
         3 = PNG,
         4 = SWF,
         5 = PSD,
         6 = BMP,
         7 = TIFF(intel),
         8 = TIFF(motorola),
         9 = JPC,
         10 = JP2,
         11 = JP*/
      switch ($type_img) {
        case 1:
          $im=@imageCreateFromGIF($src);
        break;
        case 2:
          $im=@imageCreateFromJPEG($src);
        break;
        case 3:
          $im=@imageCreateFromPNG($src);
        break;
        case 6:
          $im=@imagecreatefromwbmp($src);
        break;
        default:
          $im=false;
        break;
      }
    }
    else {
      $im=false;
    }
    return $im;
  }

  public static function resize_im($im, $width, $height){
    $im_tmp=@imagecreatetruecolor($width,$height);

    if ((@ImageSX($im)>=@ImageSY($im))&&(@ImageSX($im)> $width)) {
      $thumb_ratio=(@ImageSY($im)/(@ImageSX($im)/$width))/$height;
      //$im_new_th = @ImageCreateTrueColor($this->max_attach_width, $height);
      @imagecopyresampled($im_tmp, $im, 0, 0, (@ImageSX($im)-@ImageSX($im)*$thumb_ratio)/1.5, 0, $width, $height, ImageSX($im)*$thumb_ratio, ImageSY($im));
      $im=$im_tmp;
    }
    else {
      if (@ImageSY($im)>$height) {
        $thumb_ratio=(@ImageSX($im)/(@ImageSY($im)/$height))/$width;
        //$im_new_th = @ImageCreateTrueColor($this->max_attach_width, $height);
        @imagecopyresampled($im_tmp, $im, 0, 0, 0, (@ImageSY($im)-@ImageSY($im)*$thumb_ratio)/5, $width, $height, @ImageSX($im), @ImageSY($im)*$thumb_ratio);
        $im=$im_tmp;
      }
    }

    return $im;
  }

  /**
   *
   * размер width и height для графического текста
   *
   * @param unknown_type $size
   * @param unknown_type $font_file
   * @param unknown_type $text
   */
  public static function get_text_size($size,$font_file,$text){
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

  function utf8_to_uni($utf8_txt) {
    return $utf8_txt;
    for($i=0; $i<mb_strlen($utf8_txt); $i++) {
      $thischar=mb_substr($utf8_txt, $i, 1);
      $thischar=iconv('UTF-8', 'cp1251', $thischar);
      $thischar=convert_cyr_string($thischar, "w", "i");
      $charcode=ord($thischar);
      $uniline.=($charcode>175)?"&#".(1040+($charcode-176)).";":$thischar;
    }
    return $uniline;
  }
}

