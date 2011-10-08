<?
/**
   * Русский upcase
   *
   * @param string $term
   * @param bool $leavefirst если true оставляет первую букву неизменной
   * @return string
   */
function str_upcase($term, $leavefirst=false) {
	$first  = $term{0};
	$result = strtr($term, "абвгдежзийклмнопрстуфхцчшщьыъэюяіїє", "АБВГДЕЖЗИЙКЛМНОПРСТУФХЦЧШЩЬЫЪЭЮЯІЇЄ");
	$result = strtr($result, "abcdefghijklmnopqrstuvwxyz", "ABCDEFGHIJKLMNOPQRSTUVWXYZ");
	if ($leavefirst) $result{0} = $first;
	return $result;
}

/**
   * Русский lowcase
   *
   * @param string $term
   * @param bool $leavefirst если true оставляет первую букву неизменной
   * @return string
   */
function str_lowcase($term, $leavefirst=false) {
	$first  = $term{0};
	$result = strtr($term, "АБВГДЕЖЗИЙКЛМНОПРСТУФХЦЧШЩЬЫЪЭЮЯІЇЄ", "абвгдежзийклмнопрстуфхцчшщьыъэюяіїє");
	$result = strtr($result, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz");
	if ($leavefirst) $result{0} = $first;
	return $result;
}

/**
 * Возвращает окончание для числа
 * (1 день, 2 дня, 11 дней)
 * @param int $num
 * @param array $endings
 * @example str_ending(120,array('день','дня','дней'))
 */
function str_ending($num,$words){
  $lastN=$num%10;
  $lastT=$num%100;
  if($lastT>=10 && $lastT<=20){
    return $words[2];
  }
  switch ($lastN){
    case 1:
      return $words[0];
    case 2:
    case 3:
    case 4:
      return $words[1];
    default:
      return $words[2];
  }
}

function str_plural_form($n, $form1='штука', $form2='штуки', $form5='штук'){
	return str_ending($n,array($form1, $form2, $form5));
}

/**
   * Возвращает строку для вставки в JScript
   *
   * @param string $str
   * @return string
   */
function str_js($str) {
	$result = preg_replace("/'/m", "\'", $str);
	$result = preg_replace("/(?<!\\\)[\r\n]/m", "\\\n", $result);
	//$result = preg_replace("/(?<!\\\)(\r\n)/m", "\\n", $result);
	$result = preg_replace("/<script(.*?)>/m", "<\\script \$1>", $result);
	$result = preg_replace("/<\/script>/m", "<\/script>", $result);
	return $result;
}

/**
   * Возвращает псевдослучайный набор знаков
   *
   * @param int $length длина
   * @param string $type 'char' - только буквы, 'digit' - только цифры, 'mix' - и то, и другое
   * @return unknown
   */
function str_rand ($length, $type="char") {
	mt_srand ((double) microtime() * 1000000);
	for ( $i=1; $i<=$length;$i++ ) {
		$randval_s = mt_rand(0,1);
		$randval_t = mt_rand(0,1);
		$randval_c = $randval_t==0?mt_rand(65,90):mt_rand(97,122);
		$randval_d = mt_rand(48,57);
		switch ($type) {
			case "char":
				$result   .= chr($randval_c);
				break;
			case "digit":
				$result   .= chr($randval_d);
				break;
			case "mix":
				$result   .= $randval_s=='0'?chr($randval_c):chr($randval_d);
				break;
		}
	}
	return $result;
}

/**
   * Переводит стоку в или из транслита
   *
   * @param string $str
   * @param bool $from если true то переводит из транслита
   * @return string
   */
function str_translit($str, $from=false) {
	if ($from)
	$trans = array(   "a"  => "а",  "b"   => "б",  "v"  => "в",  "g"  => "г", "d"  => "д",
	"e"  => "э",  "jo"  => "ё",  "zh" => "ж",  "z"  => "з", "i"  => "и",
	"j"  => "й",  "k"   => "к",  "l"  => "л",  "m"  => "м", "n"  => "н",
	"o"  => "о",  "p"   => "п",  "r"  => "р",  "s"  => "с", "t"  => "т",
	"u"  => "у",  "f"   => "ф",  "kh" => "х",  "ts" => "ц", "ch" => "ч",
	"sh" => "ш",  "sch" => "щ",  "'"  => "ъ",  "y"  => "ы", "'"  => "ь",
	"e"  => "е",  "ju"  => "ю",  "ja" => "я",
	"A"  => "А",  "B"   => "Б",  "V"  => "В",  "G"  => "Г", "D"  => "Д",
	"E"  => "Э",  "Jo"  => "Ё",  "Zh" => "Ж",  "Z"  => "З", "I"  => "И",
	"J"  => "Й",  "K"   => "К",  "L"  => "Л",  "M"  => "М", "N"  => "Н",
	"O"  => "О",  "P"   => "П",  "R"  => "Р",  "S"  => "С", "T"  => "Т",
	"U"  => "У",  "F"   => "Ф",  "Kh" => "Х",  "Ts" => "Ц", "Ch" => "Ч",
	"Sh" => "Ш",  "SCH" => "Щ",                "Y"  => "Ы",
	"E"  => "Е",  "Ju"  => "Ю",  "Ja" => "Я");

	else $trans = array("а" => "a" ,  "б" => "b"  ,  "в" => "v" ,  "г" => "g" , "д" => "d" ,
	"е" => "e" ,  "ё" => "jo" ,  "ж" => "zh",  "з" => "z" , "и" => "i" ,
	"й" => "j" ,  "к" => "k"  ,  "л" => "l" ,  "м" => "m" , "н" => "n" ,
	"о" => "o" ,  "п" => "p"  ,  "р" => "r" ,  "с" => "s" , "т" => "t" ,
	"у" => "u" ,  "ф" => "f"  ,  "х" => "kh",  "ц" => "ts", "ч" => "ch",
	"ш" => "sh",  "щ" => "sch",  "ъ" => "'" ,  "ы" => "y" , "ь" => "'" ,
	"э" => "e" ,  "ю" => "ju" ,  "я" => "ja",
	"А" => "A" ,  "Б" => "B"  ,  "В" => "V" ,  "Г" => "G" , "Д" => "D" ,
	"Е" => "E" ,  "Ё" => "Jo" ,  "Ж" => "Zh",  "З" => "Z" , "И" => "I" ,
	"Й" => "J" ,  "К" => "K"  ,  "Л" => "L" ,  "М" => "M" , "Н" => "N" ,
	"О" => "O" ,  "П" => "P"  ,  "Р" => "R" ,  "С" => "S" , "Т" => "T" ,
	"У" => "U" ,  "Ф" => "F"  ,  "Х" => "Kh",  "Ц" => "Ts", "Ч" => "Ch",
	"Ш" => "Sh",  "Щ" => "SCH",  "Ъ" => "'" ,  "Ы" => "Y" , "Ь" => "'" ,
	"Э" => "E" ,  "Ю" => "Ju" ,  "Я" => "Ja");
	return strtr($str, $trans);
}

function is_url_valid($url) {
  if(preg_match("/^((ftp|https?):\/\/)?(www\.)?[a-z0-9(йцукенгшщзхъэждлорпавыфячсмитьбюё)\-\.]{2,}[\.][a-z(йцукенгшщзхъэждлорпавыфячсмитьбюё)]{2,4}.*$/i", $url)) return TRUE;
  else return FALSE;
}

function is_email_valid($email) {
  //http://en.wikipedia.org/wiki/E-mail_address#RFC_specification
  if(preg_match("/^[a-z0-9\.\+_-]+@+[a-z0-9(йцукенгшщзхъэждлорпавыфячсмитьбюё)\._-]+\.+[a-z(йцукенгшщзхъэждлорпавыфячсмитьбюё)]{2,}$/i", $email)) return TRUE;
  else return FALSE;
}

function iconv_array($in_arr,$from='cp1251',$to='utf-8'){
    if (is_array($in_arr)){
      foreach ($in_arr as $key=>$itm){
        $in_arr[$key]=iconv_array($itm,$from,$to);
      }
    }
    else {
      $in_arr=iconv($from,$to,$in_arr);
    }
    return $in_arr;
}

function crop_str($str,$max_len){
    if (strlen($str)>$max_len){
      $str=substr($str,0,$max_len-3).'...';
    }

    return $str;
}

if(!function_exists('mb_str_replace')) {
  function mb_str_replace($search, $replace, $subject) {
    if(is_array($subject)) {
      $ret = array();
      foreach($subject as $key => $val) {
        $ret[$key] = mb_str_replace($search, $replace, $val);
      }
      return $ret;
    }

    foreach((array) $search as $key => $s) {
      if($s == '') continue;

      $r = !is_array($replace) ? $replace : (array_key_exists($key, $replace) ? $replace[$key] : '');
      $pos = mb_strpos($subject, $s);
      while($pos !== false) {
        $subject = mb_substr($subject, 0, $pos) . $r . mb_substr($subject, $pos + mb_strlen($s));
        $pos = mb_strpos($subject, $s, $pos + mb_strlen($r));
      }
    }

    return $subject;
  }
}

if(!function_exists('mb_str_ireplace')) {
  function mb_str_ireplace($search, $replace, $subject) {
    if(is_array($subject)) {
      $ret = array();
      foreach($subject as $key => $val) {
        $ret[$key] = mb_str_ireplace($search, $replace, $val);
      }
      return $ret;
    }

    foreach((array) $search as $key => $s) {
      if($s == '') continue;

      $r = !is_array($replace) ? $replace : (array_key_exists($key, $replace) ? $replace[$key] : '');
      $pos = mb_stripos($subject, $s);
      while($pos !== false) {
        $subject = mb_substr($subject, 0, $pos) . $r . mb_substr($subject, $pos + mb_strlen($s));
        $pos = mb_stripos($subject, $s, $pos + mb_strlen($r));
      }
    }

    return $subject;
  }
}

function trim_array($arr){
  if (!is_array($arr)) return trim($arr);
  foreach ($arr as $k=>$v){
    $arr[$k]=trim_array($v);
  }
  return $arr;
}
