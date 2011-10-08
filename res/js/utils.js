  var T_show_err_mes=null;
  var T_show_info_mes=null;
  function hide_mess(){
    if (T_show_err_mes!=null) {clearTimeout(T_show_err_mes);T_show_err_mes=null;}
    if (T_show_info_mes!=null) {clearTimeout(T_show_info_mes);T_show_info_mes=null;}
    //jQuery('#id_err_message').clearQueue().stop().css('display','none');
    //jQuery('#id_info_message').clearQueue().stop().css('display','none');
    jQuery('#id_err_message').css('display','none');
    jQuery('#id_info_message').css('display','none');
  }
  function show_err_mes(mes){
		hide_mess();
    if (jQuery('#id_err_message').css('display')=='none') {
      jQuery('#id_err_message').html(mes);
      jQuery('#id_err_message').css('left', Math.floor(getDocumentWidth()/2-jQuery('#id_err_message').width()/2)+'px' ).fadeIn(300);
      T_show_err_mes=setTimeout(function(){jQuery('#id_err_message').fadeOut(400);if (T_show_err_mes!=null) {clearTimeout(T_show_err_mes);T_show_err_mes=null;}}, 5000);
    }
  }
  function show_info_mes(mes){
		hide_mess();
    if (jQuery('#id_info_message').css('display')=='none'){
      jQuery('#id_info_message').html(mes);
      jQuery('#id_info_message').css('left', Math.floor(getDocumentWidth()/2-jQuery('#id_info_message').width()/2)+'px' ).fadeIn(300);
      T_show_info_mes=setTimeout(function(){jQuery('#id_info_message').fadeOut(400);if (T_show_info_mes!=null) {clearTimeout(T_show_info_mes);T_show_info_mes=null;}}, 5000);
    }
  }
	
//остановка всплытия события
function stop_bubble(e){
  //use stop_bubble(event);
  if (e && e.stopPropagation) e.stopPropagation();
  else {
    if (e) e.cancelBubble = true;
    window.event.cancelBubble = true;
  }
}
	
	
//Размер окна по горизонтали(по X)
function getDocumentWidth(){
   return (window.innerWidth)?window.innerWidth:((document.all)?document.documentElement.offsetWidth:null);
};
//Размер окна по вертикали(по Y)
function getDocumentHeight(){
   return (window.innerHeight)?window.innerHeight:((document.all)?document.documentElement.offsetHeight:null);
};
// На сколько проскролена страница по X
function getBodyScrollLeft(){
   return self.pageXOffset ||   (document.documentElement && document.documentElement.scrollLeft) || (document.body && document.body.scrollLeft);
};
// На сколько проскролена страница по Y
function getBodyScrollTop() {
   return self.pageYOffset ||   (document.documentElement && document.documentElement.scrollTop) ||   (document.body && document.body.scrollTop);
};
// Ценрт монитора с учётом скрола по X
function getClientCenterX(){
   return parseInt(getDocumentWidth()/2)+getBodyScrollLeft();
};
// Ценрт монитора с учётом скрола по Y
function getClientCenterY(){
   return parseInt(getDocumentHeight()/2)+getBodyScrollTop();
}; 

/*юзается когда надо в xsl шаблоне, который получается аяксом, вывести данные в конкретный блок. 
  Когда <xsl:value-of select="" disable-output-escaping="yes"/> применить нельзя из-за тепого firefox'а
  
  encoded_text - текст закодированный в rawurlencode, чтобы не париться с переносом слов
	*/
function do_decode_and_write(id,encoded_text,cnt){
	if (cnt==null) cnt=0;
	if (cnt>100) return;
	var el=document.getElementById(id);
	if (el==null){
		//бывает в случае с ie
		setTimeout(function(){do_decode_and_write(id,encoded_text,cnt+1)},100);
	}
	else {
	  el.innerHTML=decodeURIComponent(encoded_text);	
	}	
}

function do_set_focus(id,cnt){
	if (cnt==null) cnt=0;
	if (cnt>100) return;
	var el=document.getElementById(id);
	if (el==null){
		//бывает в случае с ie
		setTimeout(function(){do_set_focus(id,cnt+1)},100);
	}
	else {
	  el.focus();	
	}	
}

var spool=new Array();
var spool_added=false;
function add_to_spool(funct){
  spool.push(funct);
	if (!spool_added){
		spool_added=true;
		jQuery(document).ready(function($) {
		  run_spool();
		});  		
	}
}
function add_to_spool_first(funct){
  spool.reverse();
  add_to_spool(funct);
  spool.reverse();
}
function run_spool(){
	l=spool.length;
  for(var i=0;i<l;i++){
    if(typeof(spool[i])=='function') spool[i]();
  }
	spool=new Array();
	spool_added=false;
}

