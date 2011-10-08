var gl_url_left='';
var gl_url_right='';

function set_gl_left_right_loction(){
  var s=''+document.location;
  var tmp=s.split('#');
  gl_url_left = tmp[0];
  gl_url_right = tmp[1];  
}

function get_ajax_url_params(){
  set_gl_left_right_loction();
  if (gl_url_right==null) return '';
  return gl_url_right.split('/');
}

function set_ajax_url(ajax_location){
  set_gl_left_right_loction();
  document.location=gl_url_left+'#'+ajax_location;  
}

function set_ajax_url_reload(ajax_location){
  set_gl_left_right_loction();
  var rnd='rnd='+new Date().getTime().toString();
  
  var tmp=gl_url_left.split('?');
  if (tmp[1]!=null && tmp[1]!=''){
    document.location=gl_url_left+'&'+rnd+'#'+ajax_location;
  }
  else {
    document.location=gl_url_left+'?'+rnd+'#'+ajax_location;
  } 
}


// ошибка при передаче/сохранении
function reload_document_on_error(err_mes){
  if (err_mes!=''){
    hide_mess();
    show_err_mes(err_mes);
    reload_document(5);
  }
  else {
    reload_document(); 
  }
}

function reload_document(sec_time){
  //return;
  if (sec_time==null) document.location.href=document.location.href;
  else setTimeout(function(){document.location.href=document.location.href;}, 1000*sec_time);
}