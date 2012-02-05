<?php
//load_class('c_handler');

load_class('c_xml');

interface i_handler {

  /**
   * Разбор параметров страницы
   *
   */
  function process();

  /**
   * Разбор параметров страницы для запуска ajax
   *
   */
  function ajax_process();

  /*Общая функция. Вызывается перед ajax_process() и process()*/
  function common_process();
}

abstract class a_handler implements i_handler {

	/**
	 * @var a_handler
	 */
	public $handler;//=$this - для совместимости с a_sub_handler
	public $sub_handlers=array();//список вызванных обработчиков. Ключ - класс обработчика, значение - объект обработчика a_sub_handler

  /**
   * db
   *
   * @var c_db
   */
  public $db;//основная БД
  public $params;//параметры запрошенной страницы
  public $handler_info='';//данные из _site.xml
  public $screen='';//данные из $o_global->curr_page['@screen']

  public $titles=array();//заголовоки страниц. Имплодятся в $PM_Title. По всем заголовкам строиятся ХК
  public $is_multi_title=true;//если =true то заголовки имплодим. Иначе выводим последний
  public $is_show_title=true;//показывать ли заголовок
  public $xsl;//используемый шаблон страницы
  public $xsl_params=array();//доп. параметры для XSL
  public $is_debug=false;//дебаг контекста
  public $h_data;//данные для подстановки в шаблон xsl. В самом конце результат преобразования заносится в h_result
  public $h_result=null;//результат для вывода в браузер. Если он не пуст, то все данные из h_data игнорируются
  public $root_node='res';//имя корневого узла XML, который передаётся в xsl

  public $crumbs_del=0;// сколько последних экранов убрить из ХК. Названия экртанов берутся из $this->titles или $this->crumbs
  public $is_show_crumbs=false;
  public $xsl_page_with_crumbs;//шаблон страницы с ХК
  public $crumbs_style='15%';//значение параметра margin_left для шаблона
  public $is_debug_crumbs=false;//дебаг ХК
  public $crumbs=array();//массив массивов ['label','link']

  public $messages=array();//сообщения пишутся сюда
  public $errors=array();//ошибки пишутся сюда

  /**
   *
   * @var c_pager
   */
  protected $pager=null;//пагинатор. Автоматически создаётся при необходимости. Методы pager_get_limit() и pager_get_pages()
  public $pager_xsl='common/pager.xsl';
  public $is_save_page=false;


  function __construct($db_id=false) {
    global $o_global,$o_db_man;

    $this->xsl_params=array();

    $this->params = $o_global->url_params;
    if ($db_id!==false) $this->db=$o_db_man->get_db($db_id);
    $this->h_data=array();

    $this->xsl_page_with_crumbs='common/page_with_crumbs.xsl';

    $this->handler=&$this;
  }

  function __destruct() {

  }

  /**
   * получить значение глобальной переменной
   *
   * @param $var
   * @param $legal_values допустимые значения
   * @param $def_value значение по умолчанию (не проверяется на допустимость)
   * @param $on_srceen
   */
  function get_global_value($var,$screen='',$legal_values=array(),$def_value=null){
    global $o_session;
    $tmp=$o_session->get_session($var, $screen);
    if (isset($_REQUEST[$var]))$tmp=trim($_REQUEST[$var]);
    if (!empty($legal_values) && is_array($legal_values) && !in_array($tmp,$legal_values)) {
      if (is_null($def_value)){
        $legal_values=array_values($legal_values);
        $tmp_def=$legal_values[0];
      }
      else $tmp_def=$def_value;
      $tmp=$tmp_def;
    }
    else {
      if (is_null($tmp) && !is_null($def_value)) {
        $tmp=$def_value;
      }
    }
    $this->set_global_value($var,$screen,$tmp);
    return $tmp;
  }

  /*обычно можно обойтись get_global_value*/
  function set_global_value($var,$screen='',$value){
    global $o_session;
    $o_session->set_session($var,$screen,$value);
  }

  static function use_https(){
  	//используется для определения нужен ли на этой странице https вооще или на http редируктнуть.
  	//см. function out в _run.php
  	define ('_USE_HTTPS',true);
  	if ($_SERVER['HTTPS']!='on'){
  	  global $o_global;
	    $uri = 'https://'.$_SERVER['HTTP_HOST'].implode('/',$o_global->url_params);
	    header("Location: ".$uri);
	    exit;
  	}
  }

  /**
   *
   * подсветка найденного текста
   * @param str $search_str
   * @param array $h_data_part
   * @param array $fields
   * @param bool $is_from_start_only =true когда поиск должен веститсь только по началу слова
   * @param str $start начальный маркер
   * @param str $end конечный маркер
   */
  static function highlight_h_data($search_str, &$h_data_part=array(), $fields=array(), $is_from_start_only=true, $start='<span class="highlight">', $end='</span>'){
    if (!is_array($h_data_part)) return;
    foreach ($h_data_part as $k=>$itm){
      if (is_array($itm)) a_handler::highlight_h_data($search_str, $h_data_part[$k], $fields);
      elseif (in_array($k, $fields)){
        $h_data_part[$k.'_hl']=$itm;
        $len=mb_strlen($search_str);
        if (!$is_from_start_only) {
          $h_data_part[$k.'_hl']=mb_str_ireplace($search_str, $start.mb_substr($itm, mb_stripos($itm, $search_str), $len).$end, $itm);
        }
        elseif (mb_stripos($itm, $search_str)===0){
          //найдено вначале
          $h_data_part[$k.'_hl']=$start.mb_substr($itm, 0, $len).$end.mb_substr($itm, $len, mb_strlen($itm));//  mb_str_replace($search_str, $start.$search_str.$end, $itm);
        }
      }
    }
  }

  protected function create_pager(){
	  global $o_global;
	  $page_param='p';
	  if ($o_global->is_ajax) $page_param='ap';
	  if ($this->is_save_page) $_REQUEST[$page_param]=$this->get_global_value($page_param,$this->screen);
    load_class('c_pager');
    $this->pager=new c_pager($page_param);
    $this->pager->xsl_file=$this->pager_xsl;
    if ($o_global->is_ajax) {
      $this->pager->params['script_name']='pager_get_page_'.$_REQUEST['scr'];
      $this->pager->params['scr']=$_REQUEST['scr'];
    }
  }

  /**
   * возвращаем массив с данными для подстановки в LIMIT
   *
   * @param int $count_line_on_page количество строк на странице
   * @param boolean $is_force_crate_new_pager принудительно создать новый объект пагинатора
   */
  function pager_get_limit($count_line_on_page,$is_force_crate_new_pager=false) {
  	if (is_null($this->pager) || $is_force_crate_new_pager){
  	  $this->create_pager();
  	}
    return $this->pager->get_limit($count_line_on_page);
  }

  /**
   * сгенерённые страницы пагинатора
   * $this->handler->h_data['pager']=$this->handler->pager_get_pages($o_artists->count,$this->handler->count_line_on_page);
   * <xsl:value-of select="pager/pages" disable-output-escaping="yes"/>
   *
   * @param int $count_total общее количество строк
   * @param int $count_line_on_page количество строк на странице
   * @param boolean $is_force_crate_new_pager принудительно создать новый объект пагинатора
   */
  function pager_get_pages($count_total, $count_line_on_page,$is_full_information=false,$is_force_crate_new_pager=false) {
    if (is_null($this->pager) || $is_force_crate_new_pager){
  	  $this->create_pager();
    }
    $res=$this->pager->get_pages($count_total,$count_line_on_page);
    if (!$is_full_information){
      $res_tmp=$res;
      $res=array();
      $res['html']=$res_tmp['html'];
    }
    return $res;
  }

  function add_crumbs($label, $link){
    $this->crumbs[]=array('label'=>$label, 'link'=>$link);
  }

  /**
   * вывод хлебных крошек
   */
  function get_crumbs(){
  	$dta['crumbs']=$this->crumbs;
  	$dta['content']['html']=$this->h_result;

  	$params['crumbs_style']=$this->crumbs_style;
  	$result=xsl_out($this->xsl_page_with_crumbs,'page_crumbs',$dta,$this->is_debug_crumbs,$params);
  	return $result;
  }

  function process(){}
  function ajax_process(){}
  function common_process(){}

  function xsl_out(){
    return xsl_out($this->xsl,$this->root_node,$this->h_data,$this->is_debug,$this->xsl_params);
  }

  function run(){
    global $o_global;
    $this->screen=$o_global->curr_page['@screen'];

    $this->common_process();

    /*Begin запустим страницу*/
    if ($o_global->is_ajax) {
      $this->pager_xsl='common/pager_ajax.xsl';
    	$this->is_show_crumbs=false;
    	$this->ajax_process();
    }
    else $this->process();
    /*End запустим страницу*/

    $this->h_data['current_url']=$o_global->current_url;
    $this->h_data['full_path']=$o_global->url_params;
    $this->h_data['errors']=$this->errors;
    $this->h_data['messages']=$this->messages;
    $this->h_data['titles']=$this->titles;

    if (is_null($this->h_result)){
      $this->h_result=$this->xsl_out();
    }

    if ($this->is_show_crumbs) $this->h_result=$this->get_crumbs();

    if ($this->is_show_title){
	    if (!empty($this->titles)) {
	    	if (!$this->is_multi_title) $tmp_title=$this->titles[count($this->titles)-1];
	    	else  $tmp_title=implode(' / ',$this->titles);
	    	$o_global->curr_page['title']=$tmp_title;
	    }
    }

    return $this->h_result;
  }

}


/**
 * Абстрактный класс подэкрана
 * @author ra
 *
 */
abstract class a_sub_handler implements i_handler{

	/**
	 * @var a_handler
	 */
	public $handler;
  /**
   * db
   *
   * @var c_db
   */
	public $db;

	public $xsl;//используемый шаблон страницы
	public $xsl_params=array();//доп. параметры для XSL
	public $is_debug=false;//дебаг контекста
	public $h_data;//данные для подстановки в шаблон xsl. В самом конце результат преобразования заносится в h_result
	public $h_result=null;//результат для вывода в браузер. Если он не пуст, то все данные из h_data игнорируются

	/**
	 *
	 * @param a_handler $handler
	 */
	function __construct($handler) {
		$this->handler=$handler;
		$this->db=$handler->db;

		$this->xsl=&$this->handler->xsl;
		$this->xsl_params=&$this->handler->xsl_params;
		$this->is_debug=&$this->handler->is_debug;
		$this->h_data=&$this->handler->h_data;
		$this->h_result=&$this->handler->h_result;
	}

	/**
	 * Загрузка подэкрана
	 * load($this, 'manage','artists');
	 *
	 * @param a_handler $page_object можно использовать как a_handler, так и a_sub_handler (всё благодаря $this->handler)
	 * @param str $path
	 * @param str $sub_handler
	 * @param bool $is_auto_process
	 *
	 * @return a_sub_handler
	 */
	static function load($page_object, $path, $sub_handler ,$is_auto_process=true){
	  global $o_global;
	  $sub_handler_class_name='s_'.$sub_handler;
	  load_handler($path.'/'.$sub_handler_class_name);

	  $sub_handler_object=new $sub_handler_class_name($page_object->handler);
	  $page_object->handler->sub_handlers[$sub_handler_class_name]=$sub_handler_object;
	  if ($is_auto_process){
	    $sub_handler_object->common_process();
	  	if ($o_global->is_ajax) $sub_handler_object->ajax_process();
	  	else $sub_handler_object->process();
	  }
	  return $sub_handler_object;
	}

	function process(){}
	function common_process(){}
	function ajax_process(){}
}

