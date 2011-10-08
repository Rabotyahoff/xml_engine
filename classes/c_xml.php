<?php
  /**
   * Работа с XML
   * Реализует преобразования
   *   XML    ->  c_xml
   *   array  ->  c_xml
   *   c_xml ->  XML
   *   c_xml ->  array
   *   c_xml ->  String
   *   c_xml ->  dump
   * Реализует поиск по xPath
   */

load_lib('array');
load_lib('str');

class c_xml_node{

	/**
	 * =true когда загрузка успешна
	 * @var boolean
	 */
	public $isLoaded=false;
	public $is_normalize_upper=null;

	protected $name='';
	protected $value='';
	protected $attributes=array();

	/**
	 * @var $childNodes array of c_xml_node
	 */
	protected $childNodes=array();
	protected $childNodesByName=array();// $childNodesByName['name'][]
	/**
	 *
	 * @var $parentNode c_xml_node
	 */
	protected $parentNode=null;

	/**
	 *
	 * @param c_xml_node $parent_node Если =null, то это корневой узел
	 * @param String $name
	 * @param String $value
	 * @return c_xml_node
	 */
	function __construct($name='', $value='', $attributes=array()){
		$this->emptyNode();
		$this->parentNode=null;
		$this->setName($name);
		$this->setValue($value);
		$attributes=make_array($attributes);
		foreach ($attributes as $k=>$v) $this->setAttribute($k,$v);//так, т.к. ещё обрабатывается normalizeName
		return $this;
	}

	protected function normalizeName($name){
	  if ($this->is_normalize_upper===true) return mb_strtoupper(trim($name));
	  if ($this->is_normalize_upper===false) return mb_strtolower(trim($name));
	  return trim($name);
	}

	protected function normalizeNameArray($array){
		foreach ($array as $k=>$v) $array[$k]=$this->normalizeName($v);
		return $array;
	}

	/**
	 *
	 * @param String $name
	 * @return c_xml_node
	 */
	function setName($name){
		$this->name=$this->normalizeName($name);
		return $this;
	}

	function getName(){
		return $this->name;
	}

	/**
	 *
	 * @param String $value
	 * @return c_xml_node
	 */
	function setValue($value){
		$this->value=trim($value);
		return $this;
	}

	/**
	 *
   * @param boolean $is_for_xml если надо, то обернёт в <![CDATA[...]]>
	 * @param boolean $is_strong если у узла есть дочерние узлы, то при $is_strong=true вернётся пустое значение
	 */
	function getValue($is_for_xml=false,$is_strong=true){
		$res='';
		if (empty($this->childNodes) || !$is_strong) {
			$res=$this->value;
			if ($is_for_xml){
				$res=$this->valueToXML($res);
			}
		}
		return $res;
	}

	static function valueToXML($value){
	  if (mb_strpos($value,'<')!==false || mb_strpos($value,'&')!==false) {
			$value="<![CDATA[$value]]>";
		}
		return $value;
	}

	/**
	 *
	 * @param String $attributeName
	 * @param String $attributeValue
	 * @return c_xml_node
	 */
	function setAttribute($attributeName,$attributeValue=''){
		$this->attributes[$this->normalizeName($attributeName)]=$attributeValue;
		return $this;
	}

	/**
	 *
	 * @param String $attributeName
	 * @return c_xml_node
	 */
	function removeAttribute($attributeName){
		unset($this->attributes[$this->normalizeName($attributeName)]);
		return $this;
	}

	function getAttributeValue($attributeName){
		return $this->attributes[$this->normalizeName($attributeName)];
	}

	function getAttributes(){
		return $this->attributes;
	}

	/**
	 * @return c_xml_node
	 */
	function getParentNode(){
		return $this->parentNode;
	}

	function getCountChildNodes(){
		return count($this->childNodes);
	}

	/**
	 * Возвращает узел с номером $num, начиная с 0
	 * В случае неудачи возвращает false
	 *
	 * @param Integer $num
	 * @return c_xml_node
	 */
	function getNodeByNum($num){
		if ($num<0 || $num>=$this->getCountChildNodes()) return false;
		return $this->childNodes[$num];
	}

  /**
   *
   * @param String $name
   * @return array
   */
  function getNodesByName($name){
    return $this->childNodesByName[$name];
  }

  /**
   *
   * @param String $name
   * @param integer $num номер узла, который необходимо вернуть (с 0)
   * @return c_xml_node
   */
  function getNodeByNameNum($name,$num=0){
  	$tmp=$this->getNodesByName($name);
  	if (is_array($tmp)) return $tmp[$num];
  	return false;
  }

  /**
   * Получить номер узла. (с 0) False при неудаче
   *
   * @param c_xml_node $node
   * @return integer
   */
  function getNodeNum($node){
    $n=0;
    $cnt=$this->getCountChildNodes();
    while ($n<$cnt){
      if ($this->getNodeByNum($n)==$node){
        return $n;
      }
      $n++;
    }
    return false;
  }

  /**
   * Получить узел следующий за указаным. В случае неудачи false
   * Если задан null, то вернут первый узел
   * @param c_xml_node $node
   * @return c_xml_node
   */
  function getNodeNextByNode($node){
    if ($node==null) return $this->getNodeByNum(0);
    return $this->getNodeByNum($this->getNodeNum($node)+1);
  }

	/**
	 * Добавить узел в начало
	 *
	 * @param c_xml_node $node
	 * @return c_xml_node
	 */
	function insertNodeFirst($node){
		$node->parentNode=$this;
    $this->childNodesByName[$node->name]=make_array($this->childNodesByName[$node->name]);

		array_unshift($this->childNodesByName[$node->name],$node);
		array_unshift($this->childNodes,$node);
		return $this;
	}

  /**
   * Добавить узел в конец
   *
   * @param c_xml_node $node
   * @return c_xml_node
   */
  function insertNodeToEnd($node){
  	$node->parentNode=$this;
  	$this->childNodesByName[$node->name]=make_array($this->childNodesByName[$node->name]);

    array_push($this->childNodesByName[$node->name],$node);
    array_push($this->childNodes,$node);
    return $this;
  }

  /**
   * Создаёт и добавляет узел в дерево
   * Если $is_return_new_node=true, то возвращает созданный узел
   *
   * @param String $name
   * @param String $value
   * @param array $attributes
   * @param boolean $is_return_new_node
   * @return c_xml_node
   */
  function insertChildFirst($name='', $value='', $attributes=array(), $is_return_new_node=false){
    $new_xml_node=new c_xml_node($name, $value, $attributes);
    $this->insertNodeFirst($new_xml_node);
    if ($is_return_new_node) return $new_xml_node;
    return $this;
  }

  /**
   * Создаёт и добавляет узел в дерево
   * Если $is_return_new_node=true, то возвращает созданный узел
   *
   * @param String $name
   * @param String $value
   * @param array $attributes
   * @param boolean $is_return_new_node
   * @return c_xml_node
   */
  function insertChildToEnd($name='', $value='', $attributes=array(), $is_return_new_node=false){
  	$new_xml_node=new c_xml_node($name, $value, $attributes);
  	$this->insertNodeToEnd($new_xml_node);
  	if ($is_return_new_node) return $new_xml_node;
  	return $this;
  }

  /**
   *
   * @param c_xml_node $node
   * @param c_xml_node $beforeNode
   * @return c_xml_node
   */
  function insertNodeBefore($node,$beforeNode){
    if ($beforeNode==null) {
      $this->insertNodeToEnd($node);
      return $this;
    }

  	$node->parentNode=$this;
  	$this->childNodesByName[$node->name]=make_array($this->childNodesByName[$node->name]);

  	$num=$this->getNodeNum($beforeNode);
  	if ($num!==false){
  		array_insert($this->childNodes, $node, $num);
      $this->childNodesByName[$node->name][]=$node;//@TODO вставить в массив в нужное место
  	}
  	return $this;
  }

  /**
   *
   * @param c_xml_node $node
   * @param c_xml_node $beforeNode
   * @return c_xml_node
   */
  function insertNodeAfter($node,$afterNode){
  	if ($afterNode==null) {
  		$this->insertNodeFirst($node);
  		return $this;
  	}

  	$node->parentNode=$this;
  	$this->childNodesByName[$node->name]=make_array($this->childNodesByName[$node->name]);

    $num=$this->getNodeNum($afterNode);
    if ($num!==false){
      array_insert($this->childNodes, $node, $num+1);
      $this->childNodesByName[$node->name][]=$node;//@TODO вставить в массив в нужное место
    }

    return $this;
  }


  /**
   *
   * @param Integer $num
   * @return c_xml_node
   */
  function deleteNodeByNum($num){
  	$serach_node=$this->getNodeByNum($num);
  	$serach_node_name=$serach_node->name;
  	$n=0;
  	$cnt=count($this->childNodesByName[$serach_node_name]);
  	while ($n<$cnt){
  		if ($this->childNodesByName[$serach_node_name][$n]==$serach_node){
  			unset($this->childNodesByName[$serach_node_name][$n]);
  			$this->childNodesByName[$serach_node_name]=array_values($this->childNodesByName[$serach_node_name]);
  			break;
  		}
  		$n++;
  	}
  	unset($this->childNodes[$num]);
  	$this->childNodes=array_values($this->childNodes);
  	return $this;
  }


  function getAttributesStr(){
    $res=array();
    foreach ($this->attributes as $k=>$v){
      $v=mb_str_replace('&','&amp;',$v);
      $v=mb_str_replace('"','&quot;',$v);
      $v=mb_str_replace('<','&lt;',$v);
      $res[]=$k.'='.'"'.$v.'"';
    }
    if (empty($res)) return '';
    return ' '.implode(' ',$res);
  }

  function getXMLOpenTag(){
  	return '<'.$this->name.$this->getAttributesStr().'>';
  }

  function getXMLOpenCloseTag(){
    return '<'.$this->name.$this->getAttributesStr().'/>';
  }

  function getXMLCloseTag(){
  	return '</'.$this->name.'>';
  }

  /**
   *
   * @param DOMNode $domNode
   */
  protected function fromDOMNode($domNode){
    $this->setName($domNode->nodeName);
    //echo 'set name='.$domNode->nodeName.'___ ';

    if (is_object($domNode->attributes)){
  	  foreach ($domNode->attributes as $attrName => $attrNode) {
  	  	$this->setAttribute($attrName, $attrNode->value);
  	    //echo $attrName; print_r($attrNode);
  	  }
    }

    if ($domNode->hasChildNodes()){
      foreach ($domNode->childNodes as $childNode){
        //echo ' '.$childNode->nodeName.'=';echo 'count='.count($childNode->childNodes).':type='.$childNode->nodeType.':';
      	switch ($childNode->nodeType) {
      		case XML_ELEMENT_NODE:
	          //echo 'node ';echo "\n\n";
	          $new_xml_node=new c_xml_node();
	          $new_xml_node->fromDOMNode($childNode);
	          $this->insertNodeToEnd($new_xml_node);
      		break;
          case XML_TEXT_NODE:
	          //echo 'text={'.$childNode->textContent.'} ';echo "\n\n";
	          $this->setValue($this->value.$childNode->textContent);
          break;
          case XML_COMMENT_NODE:
          	//без коментариев
          break;
          case XML_CDATA_SECTION_NODE:
            //echo 'CDATA={'.$childNode->textContent.'} ';echo "\n\n";
            $this->setValue($this->value.$childNode->textContent);
          break;
      	}
      }
    }
    $this->isLoaded=true;
  }

  /**
   * очищает текущий узел
   * @return c_xml_node
   */
  function emptyNode(){
  	$this->isLoaded=false;
    $this->name='';
    $this->value='';
    $this->attributes=array();

    $this->childNodes=array();
    $this->childNodesByName=array();
  	return $this;
  }

  /**
   *
   * @param String $xml
   * @return c_xml_node
   */
  protected function fromXML($xml){
  	$this->emptyNode();
    $dom = new DomDocument();
    $dom->substituteEntities = true;
    $res_load=@$dom->loadXML($xml);
    if (!$res_load) return false;

    //$rootNode=$dom->firstChild;
    foreach ($dom->childNodes as $mayBeRootNode){
      //print_r($mayBeRootNode->nodeName);echo '='.$mayBeRootNode->nodeType.'; ';
      if ($mayBeRootNode->nodeType==XML_ELEMENT_NODE){
        $this->fromDOMNode($mayBeRootNode);
        break;
      }
    }

    $this->isLoaded=true;
    return $this;
  }

 /**
 * Создание дерева из массива
 * Если элемент массива ничаниется с @, то он преобразовывается в аттрибут
 *
 * @param unknown_type $nodeName
 * @param unknown_type $inArray
 */
  function fromArray($nodeName,$inArray=array()){
  	$this->emptyNode();

  	if (!is_numeric($nodeName)) $this->setName($nodeName);
  	else $this->setName('item');

  	if (is_array($inArray)){
  		foreach ($inArray as $childKey=>$childValue){
  			if (!is_array($childValue) && ($childKey[0]=='@' || strcmp($childKey, '.')==0 || strcmp($childKey, '*')==0)){
  				if ($childKey[0]==='@') $this->setAttribute(mb_substr($childKey,1,mb_strlen($childKey)-1), $childValue);
  				if ($childKey==='.') $this->setValue($childValue);
  				if ($childKey==='*' && $this->name=='item') $this->setName($childValue);
  			}
  			else {
          $new_xml_node=new c_xml_node();
          $new_xml_node->is_upper_normalize=$this->is_upper_normalize;
          $new_xml_node->fromArray($childKey, $childValue);
          $this->insertNodeToEnd($new_xml_node);
  			}
  		}
  	}
  	else {
  		$this->setValue($inArray);
  	}

  	$this->isLoaded=true;
  	return $this;
  }

  /**
   * преобразуем массив в xml
   * @param str $nodeName
   * @param array $inArray
   * @return str
   */
  static function arrayToXML($nodeName,$inArray=array()){
    /*$tmp=$_REQUEST['debug_speed'];
    if (_PM_DEBUG===true && $tmp==1) $tmp_micro=microtime_float();
    $_REQUEST['debug_speed']=0;*/

    $result='';
  	if (!is_numeric($nodeName)) $item_name=$nodeName;
  	else $item_name='item';
  	$item_attributes=array();
  	$item_value='';
  	$item_inner=array();

  	if (is_array($inArray)){
  		foreach ($inArray as $childKey=>$childValue){
  		  if (!is_array($childValue) && ($childKey[0]=='@' || strcmp($childKey, '.')==0 || strcmp($childKey, '*')==0)){
  				if ($childKey[0]==='@') $item_attributes[substr($childKey,1,strlen($childKey)-1)]=$childValue;
  				if ($childKey==='.') $item_value=$childValue;
  				if ($childKey==='*' && $item_name=='item') $item_name=$childValue;
  			}
  			else {
  			  $item_inner[]=c_xml_node::arrayToXML($childKey, $childValue);
  			}
  		}
  	}
  	else {
  	  $item_value=$inArray;
  	}

  	$result='<'.$item_name;
  	foreach ($item_attributes as $k=>$v){
  	  $result.=' '.$k.'="'.$v.'"';
  	}
  	if (empty($item_inner) && $item_value===''){
  	  $result.='/>';
  	}
  	else {
    	$result.='>';
    	if (!empty($item_inner)){
    	  foreach ($item_inner as $v){
    	    $result.=$v;
    	  }
    	}
    	else {
    	  $result.=c_xml_node::valueToXML(trim($item_value));
    	}
    	$result.='</'.$item_name.'>';
  	}

  	/*if (_PM_DEBUG===true && $tmp==1) echo '<div> - pm_xml::arrayToXML. Time: <B>'.(microtime_float()-$tmp_micro).'</b></div>';
  	$_REQUEST['debug_speed']=$tmp;*/

  	return $result;
  }

  function toXML(){
  	$value=$this->getValue(true,true);
  	if ($value==='' && $this->getCountChildNodes()==0){
  		$res=$this->getXMLOpenCloseTag();
  	}
  	else {
  		$res=$this->getXMLOpenTag().$value;
  	  foreach ($this->childNodes as $node){
	      $res.=$node->toXML();
	    }
	    $res.=$this->getXMLCloseTag();
  	}
  	return $res;
  }

  function toStr(){
    $res=$this->getValue(false,true);
    foreach ($this->childNodes as $node){
      $res.=$node->toStr();
    }
    return $res;
  }

  static function is_system_key($key){
    return in_array(''.$key,array('.','*'));
  }

  static function is_item_key($key){
    return is_numeric($key);
  }

  /**
   * преобразовать в массив
   * атрибуты преобразуются в элементы массива вида @attribute
   * при $is_value_in_array=true (по-умолчанию):
   *   имя ноды преобразуется в элемент массива с ключом '*'
   *   значение преобразуется в элемент массива с ключом '.'
   *
   * для перебора item'ом поможет проверка ключа на системность is_system_key($key)
   * foreach ($res as $k=>$val){
   *   if (c_xml::is_system_key($k)) continue;
   * }
   *
   * @param $node_names_ass_int ноды для преобразования в числовые индентификаторы. Если=true, то все значения пишутся в массив по порядку
   */
  function toArray($node_names_ass_int=array('item'),$is_value_in_array=true){
  	$node_names_ass_int0=$node_names_ass_int;
  	$res_value=$this->getValue(false,true);

  	if ($res_value==='' || $is_value_in_array){
  		//если есть значение, то атрибутами придётся пожертвовать. Это единственный недостаток этого подхода
  		//поэтому для устранения этого недостатка ($is_value_in_array==true) значение переносим в ['.']
  		if ($is_value_in_array) {
  			$old_res_value=$res_value;
  			if (!is_array($res_value)) $res_value=array();
  			$res_value['*']=$this->name;
  			$res_value['.']=$old_res_value;
  		}

	  	foreach ($this->attributes as $attr=>$attr_val){
	  		if (!is_array($res_value)) $res_value=array();
	  		$res_value['@'.$attr]=$attr_val;
	  	}
  	}

    if ($this->getCountChildNodes()>0){
    	if (!is_array($res_value)) $res_value=array();
      foreach ($this->childNodes as $node){
      	$child_name=$node->getName();
      	if (isset($res_value[$child_name])){
      		$tmp=$res_value[$child_name];
      		unset($res_value[$child_name]);
      		$res_value[]=$tmp;
      		$node_names_ass_int[]=strtolower($child_name);
      	}

        if ($node_names_ass_int===true || in_array(strtolower($child_name), $node_names_ass_int)){
          $res_value[]=$node->toArray($node_names_ass_int0, $is_value_in_array);
        }
      	else {
      	  $res_value[$child_name]=$node->toArray($node_names_ass_int0, $is_value_in_array);
      	}
      }
    }

    return $res_value;
  }

  /**
   * Возвращает путь XPath к текущему узлу
   * @param boolean $is_whitoutRootNode без указания корневого узла
   */
  function getXPuthNode($is_whitoutRootNode=true){
  	if ($this->parentNode==null) return ($is_whitoutRootNode)?'':$this->getName();

  	$tmp=$this->getParentNode()->getPuthNode($is_whitoutRootNode);
  	if (!empty($tmp)) $tmp.='/';
  	return $tmp.$this->getName();
  }

  static function dump($xml, $nohead=false,$charset='UTF-8'){
    return c_xml_node::transform('xmlDump.xsl',$xml,null,false,$nohead,$charset);
  }

  static function transform($xsl_file, $xml, $params=null,$charset='UTF-8',$nohead=false){
    global $o_global;

    //if (strtolower(substr($xsl_file,-4))!='.xsl') $xsl_file.='.xsl';
    $ent = '<!DOCTYPE page [
        <!ENTITY nbsp   "&#160;">
        <!ENTITY copy   "&#169;">
        <!ENTITY reg    "&#174;">
        <!ENTITY trade  "&#8482;">
        <!ENTITY mdash  "&#8212;">
        <!ENTITY ldquo  "&#0171;">
        <!ENTITY rdquo  "&#0187;">
        <!ENTITY pound  "&#163;">
        <!ENTITY sum    "&#0216;">
        <!ENTITY yen    "&#165;">
        <!ENTITY euro   "&#8364;">
    ]>';

    if (!$nohead) $xml="<?xml version=\"1.0\" encoding=\"".$charset."\"?>\n".$ent.$xml;
    $browser=null;
    if(empty($xml)) $xml = '<empty_xml>Empty xml</empty_xml>';

    //$xml=iconv('cp1251','cp1251',$xml);
    if(!$nohead && $charset=='windows-1251') $xml=preg_replace("{[\x98]}i","",$xml);

    $xsl = new DomDocument();
    //$xsl->resolveExternals = true;
    $xsl->substituteEntities = true;

    if (file_exists($o_global->themes_site_root.$xsl_file)) $xsl->load($o_global->themes_site_root.$xsl_file);
    elseif (file_exists($o_global->themes_engine_root.$xsl_file)) $xsl->load($o_global->themes_engine_root.$xsl_file);
    elseif (_GL_DEBUG===true) return 'Function "transform". Error. File "'.$xsl_file.'" not found ('.$o_global->themes_site_root.$xsl_file.', '.$o_global->themes_engine_root.$xsl_file.')<BR/>'."\n";
    else return '';

    //$inputdom = new DomDocument();
    //$inputdom->loadXML($xml);
    /* create the processor and import the stylesheet */
    $proc = new XsltProcessor();
    $proc->registerPhpFunctions();
    //$xsl = $proc->importStylesheet($xsl);
    if($params){
      foreach ($params as $key=>$value){
        $proc->setParameter(null, $key, $value);
      }
    }
    $inputdom = new DomDocument();
    $inputdom->substituteEntities = true;
    $inputdom->loadXML($xml);
    $proc->importStyleSheet($xsl);
    $res = $proc->transformToXML($inputdom);
    return $res;
  }

  /**
   * Провернка узла на пустоту. Пустой узел может быть возвращён из getNodeByXPuth
   *
   * @param c_xml_node $node
   */
  function isEmptyNode($node){
  	$value=$node->getValue();
  	$name=$node->getName();
  	return (empty($name) && empty($value) && $node->getCountChildNodes()==0);
  }

  /**
   *
   * @param c_xml_node $node
   * @param str $cond @attr1='val1'
   */
  private function node_like_cond($node, $cond){
  	if (strpos('=',$cond)){
  	  $conds=explode('=', $cond);
  	  $left=trim($conds[0]);
  	  $right=trim($conds[1]);
  	  if ($right[0]==='"' || $right[0]==="'") $right=substr($right,1,strlen($right)-2);
  	  //проверяем только атрибуты
  	  if ($left[0]!='@') return false;
  	  $left=substr($left,1,strlen($left)-1);
  	  return $node->getAttributeValue($left)==$right;
  	}
  	elseif (strpos('!=',$cond)){
      $conds=explode('!=', $cond);
      $left=trim($conds[0]);
      $right=trim($conds[1]);
      if ($right[0]==='"' || $right[0]==="'") $right=substr($right,1,strlen($right)-2);
      //проверяем только атрибуты
      if ($left[0]!='@') return false;
      $left=substr($left,1,strlen($left)-1);
      return $node->getAttributeValue($left)!=$right;
  	}
  }

  /**
   * Поиск узла по xPath. Если не найден, то возвращает пустой узел (см. isEmptyNode).
   * someNode/nextNode/item[@attr='val1']/label
   *
   * @param String $puth (String | Array) путь без указания текущего узла 'someNode/nextNode/item[5]/label' здесь номер итема начинается с 1, как в xPath
   * @return c_xml_node
   */
  function getNodeByXPuth($puth){
  	if (empty($puth)) return $this;

  	if (!is_array($puth)){
      $puth=explode('/',$puth);
      $puth=$this->normalizeNameArray($puth);
  	}

  	$search_node_name=array_shift($puth);//item[n]
  	$pos0=mb_strpos($search_node_name,'[');
  	$pos1=mb_strpos($search_node_name,']');
  	$search_node_cond=0;
  	if ($pos0!==false && $pos1!==false){
  		$search_node_cond=mb_substr($search_node_name,$pos0+1,$pos1-$pos0-1);
  		$search_node_name=mb_substr($search_node_name,0,$pos0);
  	}

  	$child_nodes=$this->getNodesByName($search_node_name);
  	if (is_array($child_nodes)) {
  		$child_node='';
  	  if (is_numeric($search_node_cond)){
  	  	$search_node_num=intval($search_node_cond)-1;
  	  	$child_node=$child_nodes[$search_node_cond];
      }
      else {
      	foreach ($child_nodes as $tmp_node){
      		if ($this->node_like_cond($tmp_node, $search_node_cond)){
      			$child_node=$tmp_node;
      			break;
      		}
      	}
      }

  		if (!empty($child_node)) return $child_node->getNodeByPuth($puth);
  	}
  	if (is_object($child_node)) {
  		//проверим $child_node на соответствие условию $search_node_cond
  		$is_ok=true;
  		if (is_numeric($search_node_cond)){
  			//если задано число, то оно должно=1, т.к. нода только одна
        $is_ok=(intval($search_node_cond)==1);
  		}
  		else {
  			$is_ok=$this->node_like_cond($child_node, $search_node_cond);
  		}
  		if ($is_ok) return $child_node->getNodeByPuth($puth);
  	}

  	$empty_node=new c_xml_node();
  	$empty_node->is_upper_normalize=$this->is_upper_normalize;
    return $empty_node;
  }

  /**
   * Количество узлов удовлетворяющих xPath
   *
   * @param String $puth (String | Array) путь без указания текущего узла 'someNode/nextNode/item[5]/label' здесь номер итема начинается с 1, как в xPath
   * @return c_xml_node
   */
  function getCountNodesByPuth($puth){
    if (empty($puth)) return 1;

    if (!is_array($puth)){
      $puth=explode('/',$puth);
      $puth=$this->normalizeNameArray($puth);
    }

    $search_node_name=array_shift($puth);//item[n]
    $pos0=mb_strpos($search_node_name,'[');
    $pos1=mb_strpos($search_node_name,']');
    $search_node_num=0;
    if ($pos0!==false && $pos1!==false){
      $search_node_num=mb_substr($search_node_name,$pos0+1,$pos1-$pos0-1);
      $search_node_num=intval($search_node_num)-1;
      $search_node_name=mb_substr($search_node_name,0,$pos0);
    }

    $child_nodes=$this->getNodesByName($search_node_name);
    $child_node='';
    if (is_array($child_nodes)) {
    	if (empty($puth)) return count($child_nodes);
    	$child_node=$child_nodes[$search_node_num];
    }

    if (is_object($child_node)) return $child_node->getCountNodesByPuth($puth);
    else {
      return 0;
    }
  }

  /**
   * объеденяем 2 xml
   * приоритет - добавление
   *
   * @param c_xml_node $xml1
   * @param c_xml_node $xml2
   * @return c_xml_node
   */
  /*static function mergeXML($xml1,$xml2){
    $tmp_xml=new c_xml_node();
    $arr1=$xml1->toArray(true);
    $arr2=$xml2->toArray(true);
    $res=array_merge($arr1,$arr2);
    $tmp_xml->fromArray($arr2['*'],$res);
    return $tmp_xml;
  }*/

  /**
   *
   * @param c_xml_node $xml
   * @return c_xml_node
   */
  function mergeWithXML($xml){
    $this->merge2nodes($this,$xml);
    $this->unsetMark($this);
    return $this;
  }

  /**
   *
   * @param c_xml_node $node
   */
  protected function unsetMark($node){
    unset($node->mark);

    $cnt=$node->getCountChildNodes();
    for ($n=0;$n<$cnt;$n++){
      $tmp_node=$node->getNodeByNum($n);
      $this->unsetMark($tmp_node);
    }
  }

  /**
   * Объеденение. Если одинаковые атрибуты по ключам и значениям, то внутреность заменяется.
   *
   * @param c_xml_node $node1
   * @param c_xml_node $node2
   * @return c_xml_node
   */
  protected function merge2nodes($node1,$node2){
    //$node1->attributes=$node2->attributes;

    $cnt=$node2->getCountChildNodes();
    if ($cnt>0){
      for ($n=0;$n<$cnt;$n++){
        $tmp_node2=$node2->getNodeByNum($n);
        $tmp_node2_name=$tmp_node2->getName();
        $tmp_node2_atribute_names=array_keys($tmp_node2->getAttributes());
        $tmp_node2_atribute_values=array_values($tmp_node2->getAttributes());

        $enable_node=false;

        $tmp_nodes1=$node1->getNodesByName($tmp_node2_name);
        if ($tmp_node2_name!='item' && is_array($tmp_nodes1)){
          foreach ($tmp_nodes1 as $k=>$tmp_node1){
            $tmp_node1_atribute_names=array_keys($tmp_node1->getAttributes());
            $tmp_node1_atribute_values=array_values($tmp_node1->getAttributes());
            $tmp_arr=array_diff($tmp_node2_atribute_names, $tmp_node1_atribute_names);
            $tmp_arr2=array_diff($tmp_node2_atribute_values, $tmp_node1_atribute_values);

            if (empty($tmp_arr) && empty($tmp_arr2) && $tmp_node1->mark!==true ){
              $enable_node=$tmp_node1;
              break;
            }
            else {
              /*if ($k==$n) */$tmp_node1->mark=true;
            }
          }//foreach
        }
        if ($enable_node!==false){
          //нашли такой же узел с такими же атрибутами. Ставим наши атрибуты
          $enable_node->mark=true;
          $this->merge2nodes($enable_node, $tmp_node2);
        }
        else {
          $tmp_node2->mark=true;
          $node1->insertNodeToEnd($tmp_node2);
        }
      }
    }
    else {
      $node1->childNodes=array();
      $node1->childNodesByName=array();
      $node1->setValue($node2->getValue());
    }
    return $node1;
  }

  /**
   * добавить в дерево item'ы из $xml
   *
   * @param c_xml_node $xml
   * @param bool $is_ignoreAttributes изнорировать атрибуты
   */
  function addItemsFromXML($xml,$is_ignoreAttributes=true){
    $this->addItemsFromNode($this,$xml,$is_ignoreAttributes);
    return $this;
  }

  /**
   *
   * @param c_xml_node $node1
   * @param c_xml_node $node2
   */
  protected function addItemsFromNode($node1,$node2,$is_ignoreAttributes){
    $cnt=$node2->getCountChildNodes();
    if ($cnt>0){
      for ($n=0;$n<$cnt;$n++){
        $child_node2=$node2->getNodeByNum($n);
        if ($child_node2->getName()==$child_node2->normalizeName('item')){
          $node1->insertNodeToEnd($child_node2);
        }
        else {
          $child_nodes1=$node1->getNodesByName($child_node2->getName());

          if (!$is_ignoreAttributes){
            //найдём ноду с такими же атрибутами
            $child_node2_attrNames=array_keys($child_node2->getAttributes());
            $child_node2_attrValues=array_values($child_node2->getAttributes());

            $finded_node1=false;
            if (is_array($child_nodes1)){
              foreach ($child_nodes1 as $child_node1){
                $child_node1_attrNames=array_keys($child_node1->getAttributes());
                $child_node1_attrValues=array_values($child_node1->getAttributes());

                $tmp1=array_diff($child_node1_attrValues,$child_node2_attrValues);
                $tmp2=array_diff($child_node1_attrNames,$child_node2_attrNames);

                if (empty($tmp1) && empty($tmp2)){
                  $finded_node1=$child_node1;
                  break;
                }
              }
            }//if
            if ($finded_node1!==false){
              $this->addItemsFromNode($finded_node1,$child_node2,$is_ignoreAttributes);
            }
          }
          else {
            //атрибуты игнорируются, поэтому добавляем во все узлы
            foreach ($child_nodes1 as $child_node1){
              $this->addItemsFromNode($child_node1,$child_node2,$is_ignoreAttributes);
            }
          }

        }
      }
    }
  }

}

class c_xml extends c_xml_node{

  /**
   *
   * @param String $name
   * @param String $value
   * @return c_xml_node
   */
  function __construct($xml=''){
    parent::__construct();
    if (!empty($xml)) $this->fromXML($xml);
  }

	/**
	 *
	 * @param String $xml
	 * @return c_xml
	 */
	function fromXML($xml){
		return parent::fromXML($xml);
	}

}

  /**
   *
   * обработка xml xsl'ем
   * @param str $xsl_file
   * @param str $root_node_name
   * @param array,str,c_xml $dta - можно передать массив (тогда обязательно задать $root_node_name), строку с xml-данными, c_xml
   * @param bool $is_debug
   * @param array $params
   * @param array $keys_as_params
   * @param bool $kill_doctype
   */
  function xsl_out($xsl_file,$root_node_name,$dta=array(),$is_debug=false,$params=array(),$keys_as_params=array(),$kill_doctype=true){
    global $o_global;
    if (!isset($params['res_site_url'])) $params['res_site_url']=$o_global->res_site_url;
    if (!isset($params['res_engine_url'])) $params['res_engine_url']=$o_global->res_engine_url;
    if (!isset($params['current_url'])) $params['current_url']=$o_global->current_url;

    if (is_object($dta)) $xml=$dta->toXML();
    elseif (is_array($dta)) $xml=c_xml::arrayToXML($root_node_name,$dta);
    else $xml=$dta;

    $res='';
    if ($is_debug || ($_REQUEST['debug_xsl']>0 && _GL_DEBUG===true) || ($_REQUEST['debug_xsl']===$xsl_file && _GL_DEBUG===true)){
      $_REQUEST['debug_xsl']=$_REQUEST['debug_xsl']-1;//можно дебажить до определённого уровня
      $res.='<B>File: "'.$xsl_file.'"</B><BR/>';
      if (!empty($params)){
        $res.='<U>Params</U>:<BR>';
        foreach ($params as $k=>$v){
          $res.="<B>$k</B>=$v<BR>";
        }
      }
      $res.=c_xml::dump($xml);

      $save_to=_PMP_ROOT.'/tmp/xml_dump.xml';
      $fp = @fopen($save_to, 'w');
      if ($fp!==false) @fwrite($fp, $xml);
      @fclose($fp);
    }
    $res.=c_xml::transform($xsl_file, $xml, $params);


    if ($kill_doctype) $res=preg_replace("/^\<!DOCTYPE.*\.dtd\"\>/i",'',$res,1);
    return $res;
  }

