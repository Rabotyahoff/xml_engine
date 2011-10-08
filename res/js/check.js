var last_marked_element=false;
var glob_error_messages = new Array();

function check_form(f,skip,error_field){
  glob_error_messages = new Array();
  var enableErr = false;
	var focused=false;
  var err_html_int="Не корректно заполнено поле: ";
  var out_div=document.getElementById(error_field);
  var checked=false;
  var check_init=false;
  // цикл ниже перебирает все элементы в объекте f,
  // переданном в качестве параметра
  // функции, в данном случае - наша форма.
  for (var i = 0; i<f.elements.length; i++){
    // если текущий элемент имеет атрибут required
    // т.е. обязательный для заполнения
    var tmp_s=""+f.elements[i].tagName;
    tmp_s=tmp_s.toLowerCase();
    if (tmp_s=='object') continue;
    
    var label=f.elements[i].getAttribute("err_label");
		if (label=='') label='Check field';
		
    if (!(null==f.elements[i].getAttribute("alert") || isEmpty(f.elements[i].getAttribute("alert")))){
      if(!(f.elements[i].checked)){
        enableErr=true;
        glob_error_messages.push(label);
      }
    }
    if (!(null==f.elements[i].getAttribute("need") || isEmpty(f.elements[i].getAttribute("need")))){
      if(!f.elements[i].getAttribute("prevClass")){
        cl_name=f.elements[i].className;
        cl_name=cl_name.replace('empty_field','');        
        f.elements[i].setAttribute('prevClass',cl_name);
      }
      f.elements[i].className=f.elements[i].getAttribute("prevClass");
      
      if(need_check(f.elements[i].name,skip)){
        // проверяем, заполнен ли он в форме
        if(f.elements[i].getAttribute("type")!="radio"){
          if ((isEmpty(f.elements[i].value))
          ||
          (f.elements[i].getAttribute("type")=="checkbox" && f.elements[i].checked==false)
          ){ // пустой
            enableErr=true;
            glob_error_messages.push(label);
            mark_field(f.elements[i]);
          }
          else {
            //не пустой проверим длину
            need_min_len=f.elements[i].getAttribute("min_len");
            if ((need_min_len!=null)&&(need_min_len>0)){
              if (f.elements[i].value.length<need_min_len){
                enableErr=true;
                glob_error_messages.push(label);
                mark_field(f.elements[i]);
              }
            }           
          }
        }else{
          check_init=true;
          if(f.elements[i].checked){
            checked=true;
          }else{
            mark_field(f.elements[i]);
          }
        }
      }
    }
    
    // Проверки на типы
    if (f.elements[i].getAttribute("check")=="int"){
      f.elements[i].value=f.elements[i].value.replace(",",'.');
    }
    if(!(isEmpty(f.elements[i].value))){
      if(f.elements[i].getAttribute("check")){
        if(!f.elements[i].getAttribute("prevClass")){
          f.elements[i].setAttribute('prevClass',f.elements[i].className);
        }else{
          f.elements[i].className=f.elements[i].getAttribute("prevClass");
        }
      }
      var check_type=f.elements[i].getAttribute("check");
      if (check_type=="int" && !isNumeric(f.elements[i].value)){ // пустой
        enableErr=true;
        glob_error_messages.push(label);
        mark_field(f.elements[i]);

      }else if(check_type=="url" && !checkURL(f.elements[i].value)){
        enableErr=true;
        glob_error_messages.push(label);
        mark_field(f.elements[i]);
      }else if(check_type=="email" && f.elements[i].value!='' && !checkmail(f.elements[i].value)){
        enableErr=true;
        glob_error_messages.push(label);
        mark_field(f.elements[i]);

      }else if(check_type=="date"&& !isValidDate(f.elements[i].value)){
        enableErr=true;
        glob_error_messages.push(label);
        mark_field(f.elements[i]);

      }else if(check_type=="minutes" && (!isNumeric(f.elements[i].value) || f.elements[i].value>=60)){
        enableErr=true;
        glob_error_messages.push(label);
        mark_field(f.elements[i]);

      }else if(check_type=="hours" && (!isNumeric(f.elements[i].value) || f.elements[i].value>=24)){
        enableErr=true;
        glob_error_messages.push(label);
        mark_field(f.elements[i]);
      }
    }
    
    if (last_marked_element!=f.elements[i]){
      //текущий элемент не маркали значит с ним всё ок. 
      //Если про него есть сообще об ошибке, то скрываем её
      err_div_id=f.elements[i].getAttribute("ch_id");
      err_div_obj=document.getElementById(err_div_id);
      if (err_div_obj!=null) err_div_obj.style.display='none';
    }   
    
    if(enableErr && !focused){
      f.elements[i].focus();
			focused=true;
			show_err_mes(label);
      //break;
    }   
  }//i
  if(check_init && checked==false){
    enableErr=true;
    //glob_error_messages.push(label);
  }
	
	return !enableErr;
}

function need_check(name,params){
  if(params){
    var res;
    var tmp = eval ("if(params." + name+") res=false;else res=true;");
    return res;
  }else{
    return true;
  }
}

function mark_field(field){
  field.className='empty_field '+field.getAttribute("prevClass");
  last_marked_element=field;
  //var area = field.parentNode;
  //area.className='empty_field';
}

function isValidDate(str){
  str=str.replace('/','.');
  myregexp = new RegExp("^[0-9]{1,2}.[0-9]{1,2}.[0-9]{2,4}$");
  if(str.match(myregexp)){
    return true;
  }else{
    return false;
  }
}

function isEmpty(str) {
	if (str==null) return true;
  for (var i = 0; i < str.length; i++)
  if (" " != str.charAt(i))
  return false;
  return true;
}

function isNumeric(sText) {
  var validChars = "0123456789.,";
  var isNumber=true;
  var Char;

  for (i = 0; i < sText.length && isNumber == true; i++)
  {
    Char = sText.charAt(i);
    if (validChars.indexOf(Char) == -1)
    {
      isNumber = false;
    }
  }
  return isNumber;
}


function checkURL(value) {
  //var urlregex = new RegExp("^((ftp|https?):\/\/)?(www\.)?[a-z0-9\-\.]{2,}\.[a-z]{2,4}.*$");
  var urlregex = new RegExp("^((ftp|https?):\/\/)?(www\.)?[a-z0-9(йцукенгшщзхъэждлорпавыфячсмитьбюё)\-\.]{2,}[\.][a-z(йцукенгшщзхъэждлорпавыфячсмитьбюё)]{2,4}.*$");
  //var urlregex = new RegExp("^((ftp:\/\/|https?:\/\/(www\.)?))?[a-zA-Z0-9\-\.\_]{2,}[\.][a-z]{2,4}(\/[a-zA-Z0-9\-\.\_]+\/?)*$"); 
  if(urlregex.test(value.toLowerCase()))
  {
    return(true);
  }
  return(false);
}

function checkmail(email){
  var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9(йцукенгшщзхъэждлорпавыфячсмитьбюё)\-])+\.)+([a-zA-Z0-9(йцукенгшщзхъэждлорпавыфячсмитьбюё)]{2,4})+$/;
  if (!filter.test(email.toLowerCase())) {
    return false;
  }
  return true;
}