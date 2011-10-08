<?php
class c_file {

  public static function set_chmod_chown_chgrp ($pathfile) {
    //params for uploaded pics (0-don't change default value)
    $pic_chmod = 0777;
    $pic_chown = 0;
    $pic_chgrp = 0;
    if ($pic_chmod > 0) @chmod($pathfile, $pic_chmod);
    if ($pic_chown > 0) @chown($pathfile, $pic_chown);
    if ($pic_chgrp > 0) @chgrp($pathfile, $pic_chgrp);
  }

  public static function get_ext ($file_name) {
    return mb_strtolower(mb_substr($file_name, mb_strrpos($file_name, '.')));
  }

  public static function create_file_name ($Ext, $dir, $first_part_of_name) {
    $flddatetime = date("Y_m_d_G_i_s");
    $nom = 0;

    $first_part_of_name=trim($first_part_of_name);
    if (mb_strlen($first_part_of_name)>0) $add=$first_part_of_name."_" ;
    else $add='';

    $new_file_name = $add.$flddatetime . '_' . $nom . $Ext;
    $new_file_name = str_replace(' ', '_', $new_file_name);
    $FullPicPath = $dir . "/" . $new_file_name;
    while (file_exists($FullPicPath)) {
      $nom ++;
      $new_file_name = $add. $flddatetime . '_' . $nom . $Ext;
      $new_file_name = str_replace(' ', '_', $new_file_name);
      $FullPicPath = $dir . "/" . $new_file_name;
    }
    return $new_file_name;
  }

  public static function resize_image ($path_from, $path_to, $content_ext, $max_width, $max_height) {
    //if ($content_ext=='') $content_ext=c_file::get_ext($path_from);
    //else
    $content_ext = mb_strtolower($content_ext);
    $im = '';
    switch ($content_ext) {
      case ".jpg":
      case ".jpeg":
        $im = @imageCreateFromJPEG($path_from);
      break;
      case ".png":
        $im = @imageCreateFromPNG($path_from);
      break;
      case ".gif":
        $im = @imageCreateFromGIF($path_from);
      break;
      default:
        return false;
      break;
    }
    if ($im == '') {
      //may be ext is wrong
      $im = @imageCreateFromJPEG($path_from);
      if ($im == '') $im = @imageCreateFromPNG($path_from);
      if ($im == '') $im = @imageCreateFromGIF($path_from);
    }
    if ($im == '') {
      return false;
    }
    if ((ImageSX($im) >= ImageSY($im)) && (ImageSX($im) > $max_width)) {
      $thumb_ratio = (ImageSY($im) / (ImageSX($im) / $max_width)) / $max_height;
      $im_new_th = @ImageCreateTrueColor($max_width, $max_height);
      @imagecopyresampled($im_new_th, $im, 0, 0, (ImageSX($im) - ImageSX($im) * $thumb_ratio) / 1.5, 0, $max_width, $max_height, ImageSX($im) * $thumb_ratio, ImageSY($im));
      @ImageJPEG($im_new_th, $path_to);
      @ImageDestroy($im_new_th);
    }
    else {
      if (ImageSY($im) > $max_height) {
        $thumb_ratio = (ImageSX($im) / (ImageSY($im) / $max_height)) / $max_width;
        $im_new_th = @ImageCreateTrueColor($max_width, $max_height);
        @imagecopyresampled($im_new_th, $im, 0, 0, 0, (ImageSY($im) - ImageSY($im) * $thumb_ratio) / 5, $max_width, $max_height, ImageSX($im), ImageSY($im) * $thumb_ratio);
        @ImageJPEG($im_new_th, $path_to);
        @ImageDestroy($im_new_th);
      }
      else
        @ImageJPEG($im, $path_to);
    }
    @ImageDestroy($im);
    c_file::set_chmod_chown_chgrp($path_to);

    return true;
  }

  /**
   * move uploaded file to $full_file_name
   */
  public static function move_uploaded_file($field_name,$full_file_name) {
  	$res=FALSE;
  	if (isset($_FILES[$field_name]['tmp_name'])){
  	  $res=move_uploaded_file($_FILES[$field_name]['tmp_name'],$full_file_name);
  	}
  	return $res;
  }

}

