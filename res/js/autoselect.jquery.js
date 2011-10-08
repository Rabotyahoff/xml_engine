/*
 * RA
 * Autoselect
 * 
 * 24.11.2009 version 0.1
 * 13.09.2010 version 0.2 
 *   1) параметры теперь передаются в объекте
 *   2) можно передать параметр url, и тогда данные values и labels будут запрошены по данному url.
 *      url должен возвращать json с values и labels. Ответы кешируются
 *   3) можно "пересоздать" элемент, тогда данные будут обновлены (например запросить values&labels по новому url)
 *   4) пока идёт запрос по url, то полю ставится класс "load" 
 *   
 * 
 * DEMO
 * 
$(function() {
$("#id_field_id").autoselect(
  { selected:'val',
	  callback:"alert('seleceted')",
    values:['1','2'],
    labels:['one','two'],
    selectonly:'true',
    url:''
  }
 );
});
 * 
 */

 (function($) {
 	  var autoselect_cache_ajax=new Array();
	
    $.fn.autoselect = function(options) {
			/*Begin init options*/
			var selected_val=options.selected; selected_val=""+selected_val;//сделаем его строкой, чтобы jQuery.inArray рулил
			var values=options.values;
			var titles=options.labels;
			var selectonly=options.selectonly;
			if (selectonly || selectonly=='true') selectonly=true;
			else selectonly=false;
			var callback=options.callback;
			var url=options.url;
			/*End init options*/
			
			var element = $(this);
			/*Begin зададим нужные переменные*/
			var base_id=element.attr('id');
			var hidden_id='hide_'+base_id;
			var hidden_name=element.attr('name');
			var container_id='cont_'+base_id;
			var option_id_part='option_'+base_id;
			var tmp_nom=null;
			var tmp_val=null;
			/*End зададим нужные переменные*/			
			
			if (url!=null && url!=''){
				values=new Array();
				titles=new Array();				
				
				if (autoselect_cache_ajax[url]==null){
					element.attr('readonly','readonly').attr('dontshow','1').attr('value','').addClass('load');
					$.ajax({
					  url: url,
						async: true,
					  dataType: 'json',
					  success: function(data) {
							element.removeAttr('readonly').removeAttr('dontshow').removeClass('load');
							if (data!=null) autoselect_cache_ajax[url]=data;
							else  autoselect_cache_ajax[url]='';
							if (data!=null && data!='' && data!='error'){
								values=data.values;
							  titles=data.labels;
								
								start_it();
							}
						},
						error: function(){
							element.removeAttr('readonly').removeAttr('dontshow').removeClass('load');
						}
					});
					return element;
				}
				else {
					if (autoselect_cache_ajax[url]!=''){
						values=autoselect_cache_ajax[url].values;
					  titles=autoselect_cache_ajax[url].labels;						
					}					
				}
			}
			
			start_it();
			return element;
			
			//main function
			function start_it(){
				if (titles==null || titles=='') titles=values;
				tmp_nom=jQuery.inArray( selected_val, values);
				tmp_val=titles[tmp_nom];
				if (tmp_val==null){
					if (selectonly) tmp_val='';
					else tmp_val=selected_val;
				} 
				
				if (element.attr('maked')!='1'){
					style_main_element();
					$('body').bind('click',function(){$('#'+container_id).hide();});
					make_hidden_element();
					make_down_button();
					make_select_div();					
					attach_key_events();										
				}
				else {
					//всё уже создано. Надо пересоздать элементы выбора
          element.attr('value',tmp_val);										
					$('#'+container_id).empty();
					make_select_div(); 
				}
				element.attr('maked','1');				
			}
			
		  function remove_check(){
				$('#'+container_id+' > div.selected').removeClass('selected');
				$('#'+container_id+' > div > span.sel_ico').removeClass('ui-icon').removeClass('ui-icon-check');					
			}
		
		  function do_select(){
				remove_check();
				
				var selector_='#'+container_id+' > div.over';
				var selected_options=$(selector_);
				if (selected_options.length==0){
					//ручками ничего не выбрали. Возможно первый элемент = текущему значению
					var tmp_el=$($('#'+container_id+' > div')[0]);
					if ($.trim(element.attr('value')).toLowerCase()==tmp_el.attr('tit')){
						selected_options=tmp_el;
						selected_options.addClass('over');
					}						
				}
				
				if (selected_options.length > 0) {
					$('#' + hidden_id).attr('value', selected_options.attr('val'));
					element.attr('value', selected_options.attr('title'));
					selected_options.addClass('selected');
					$(selector_+' > span.sel_ico').addClass('ui-icon').addClass('ui-icon-check');
				}
				else {
					$('#' + hidden_id).attr('value', '');
				}
				$('#'+container_id).hide();
				if (callback!=null && callback!='') eval(callback);
			}//function do_select()
			
			function show_div_select(e){
        e.returnValue = false;
        e.cancelBubble = true;
        if (e.stopPropagation) e.stopPropagation();
				if (element.attr('dontshow')=='1') return;
				if ($('#'+container_id+' > div').length>0) $('#'+container_id).css('display','block');					
			}//function show_div_select(e)
			
			//стилизация основного элемента. У основного элемента name изменим
			function style_main_element(){
				element.css('float','left').width(1*element.width()-16+'px')
				  .attr('AUTOCOMPLETE','OFF')
				  .attr('name','auto_old_'+element.attr('name'))
				  .attr('value',tmp_val);									
				element.bind('click',function(e){show_div_select(e);});
				if (selectonly) element.attr('readonly','readonly').bind('focus',function(){element.blur();});
				else element.bind('blur',function(e){do_select();});
			}
			
			//сделаем скрытое поле с name таким же как у основного эленмента
			function make_hidden_element(){
				var el_hidden=$('<input/>')
				  .attr('type','hidden')
				  .attr('id',hidden_id)
				  .attr('name',hidden_name)
				  .attr('value',selected_val);				
				element.after(el_hidden);				
			}

      //слой с элементами выбора			
			function make_select_div(){
				var tmp_top=1*element.height();
				var tmp=element.css('padding-bottom');
				tmp_top+=1*tmp.substr(0,tmp.length-2);
				var tmp=element.css('padding-top');
				tmp_top+=1*tmp.substr(0,tmp.length-2);
				
				var el_container=$("<div/>")
				  .attr('id',container_id)
					.addClass('autoselect_container')
					.css('width',(1*element.innerWidth()+16-2)+'px')
					.css('left',1*element.position().left+'px')
					.css('margin-top',tmp_top+'px');
				var len=values.length;
				/*предполагаю, что высота одного элемента = 22px*/
				if (len<9){
					/*это значит, что высота < 200px*/
					if (len>0) el_container.css('height',(22*len+2)+'px');
					else  el_container.css('height','50px');
				}
				for (var n=0;n<len;n++){
					var trim_title=$.trim(titles[n]);
					var el=$("<div/>")
	          .attr('val',values[n])
						.attr('id',option_id_part+'_'+n)
						.attr('pos',n)
						.attr('tit',trim_title.toLocaleLowerCase())
						.attr('title',trim_title)
		 			  .html(trim_title)
						.addClass('option')
						.addClass('visible')
						.bind('click',do_select)
			  	  .hover(
						  function(){
								$('#'+container_id+' > div.over').removeClass('over');
								$(this).addClass('over');
							},
							function(){}
						);
						
				  var el_ico=$("<span/>").addClass('sel_ico');
					if (values[n]==selected_val){
						el_ico.addClass('ui-icon ui-icon-check');
						el.addClass('selected');
					}
					el_ico.prependTo(el);
					
					el.appendTo(el_container);
				}        
				element.after(el_container);
			}
			
			function make_down_button(){
        var el_button=$('<span/>')
          .addClass('ui-icon')
          .addClass('ui-icon-triangle-1-s')
          .addClass('ui-state-default')
          .addClass('field_add')
          .css('float','left')//right
          .css('padding-top',element.css('padding-top'))
          .css('padding-bottom',element.css('padding-bottom'))
          .css('display',element.css('display'))
          .height(element.height())
          .bind('click',function(e){
						 show_div_select(e);
           });				
        element.after(el_button);				
			}
			
			function attach_key_events(){
				if (!selectonly){
					element.bind('keypress', function(e) {
						var code = (e.charCode) ? e.charCode : ((e.keyCode) ? e.keyCode : ((e.which) ? e.which : 0));
						if (code==13) return false;
						});
					    
	        element.bind('keyup', function(e) {
						$('#'+container_id).css('display','block');					
	
	          var e = (!e) ? window.event : e;
	          var code = (e.charCode) ? e.charCode : ((e.keyCode) ? e.keyCode : ((e.which) ? e.which : 0));
	          if (e.type != "keyup") return;
	          e.cancelBubble = true;
	          e.returnValue = false;
						if (e.stopPropagation) e.stopPropagation();
						
						var all_options=$('#'+container_id+' > div.visible');
						var all_options_count=all_options.length;
					  var selected_options=$('#'+container_id+' > div.over');
						sel_position=jQuery.inArray( selected_options[0], all_options);
					  if (code!=13) selected_options.removeClass('over');
						
						var search_txt=$.trim(element.attr('value'));
							
					  switch (code){
							case 27:
								// ESC
			          element.attr('value',$('#'+container_id+' > div.selected').attr('title'));						
							  $('#'+container_id).hide();
							break;
					    case 13:
							  do_select();
								return false;
					    break;
					    case 40:
					      // if the down arrow is pressed we go to the next suggestion
					      if (selected_options.length==0) sel_position=0;
					      else {
	                sel_position++;
	                if (sel_position>=all_options_count) sel_position=0;
					      }
								
					      $(all_options[sel_position]).addClass('over');
					    break;
					    case 38:
					      // if the down arrow is pressed we go to the next suggestion
					      if (selected_options.length==0) sel_position=$(all_options).length-1;
					      else {								
									sel_position--;
									if (sel_position<0) sel_position=all_options_count-1;
					      }
					      $(all_options[sel_position]).addClass('over');
					    break;
					    default:
							  remove_check();
	              var selector_='#'+container_id+' > div';
							  
								if (search_txt.length > 0) {
									$(selector_).removeClass('visible');
							  	var selector_ = '#' + container_id + ' > [tit^="' + search_txt.toLocaleLowerCase() + '"]';
							  	$(selector_).addClass('visible');
				        }
								else {
									$(selector_).addClass('visible');
								}
								
								if ($('#'+container_id+' > div.visible').length==0) $('#'+container_id).hide();
					    break;
					  }//switch					
	        });
				}				
			}

    }
})(jQuery);
