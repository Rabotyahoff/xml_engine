<?

class c_email {

	static function mail_subject_encode($string, $split = true) {
	  if (preg_match('/[^\x20-\x7E]/', $string)) {
	    if (!$split) {
	      return ' =?UTF-8?B?'.base64_encode($string).'?=';
	    }
	    $chunk_size = 47; // floor((75 - mb_strlen('=?UTF-8?B??=')) * 0.75);
	    $len = mb_strlen($string);
	    $output = '';
	    while ($len > 0) {
	      $chunk = mb_substr($string, 0, $chunk_size, 'utf-8');
	      $output .= ' =?UTF-8?B?'.base64_encode($chunk)."?=\n";
	      $c = mb_strlen($chunk);
	      $string = mb_substr($string, $c);
	      $len -= $c;
	    }
	    return trim($output);
	  }
	  return $string;
	}

  static function send_mail($to, $subject, $text_html, $sender=false){
    global $o_global;
    if (empty($sender)) $sender=$o_global->settings_array['emails']['robot']['.'];

    $head = "Content-type: text/html; charset=\"utf-8\"\nFrom: $sender\n";
    $res = mail($to, c_email::mail_subject_encode(trim($subject)),'<html>'.$text_html.'</html>',$head);

    return $res;
  }

  static function send_mail_plain($to, $subject, $text_plain, $sender=false){
    global $o_global;
    if (empty($sender)) $sender=$o_global->settings_array['emails']['robot']['.'];

    $head = "Content-type: text/plain; charset=\"utf-8\"\nFrom: $sender\n";
    $res = mail($to, c_email::mail_subject_encode(trim($subject)),$text_plain,$head);

    return $res;
  }

}

