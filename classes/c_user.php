<?php
load_class('c_data');

/*
необходимая минимальная структура
вообще, для доп данных юзеров конкретного проекта лучше использовать доп таблицу

CREATE TABLE `sys_users` (
  `user_id` mediumint(8) unsigned NOT NULL auto_increment,
  `login` varchar(255) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `rights` varchar(20) NOT NULL,
  `is_blocked` enum('y','n') NOT NULL default 'n',
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

уточнить размер поля `pass`
 */

class c_user extends a_data{

  function init_from_db($wheres=array(),$is_calck_count=false,$limit='',$order='', $group=''){
    return $this->init_from_db_('*','sys_users', $wheres,$is_calck_count,$limit,$order, $group);
  }

}

class c_cur_user extends c_user{

	public $is_logined;

	public $user_id='';
  public $login='';
  public $rights='';
  public $is_blocked='';

  public $s_user_id="''";

  function __construct($db_id) {
    parent::__construct($db_id);
    $this->init_from_sesion();
  }

  protected function read_dta_to_fields(){
  	$this->user_id='';
    $this->login='';
    $this->rights='';
    $this->is_blocked='';

    $this->s_user_id="''";

    if ($this->is_logined){
    	$this->user_id=$this->res_first['user_id'];
      $this->login=$this->res_first['login'];
      $this->rights=$this->res_first['rights'];
      $this->is_blocked=$this->res_first['is_blocked'];

      $this->s_user_id=$this->db->tosql($this->user_id);
    }
  }

  function init_from_sesion(){
    global $o_session, $o_global;

    $this->enable=FALSE;
    $this->is_logined=FALSE;

    $user_id=$o_session->get_session('user_id','');
    if (is_numeric($user_id) && $user_id>=0){
      $wheres=array();
      $wheres[]='user_id='.$this->db->tosql($user_id);
      $wheres[]="is_blocked='n'";
      $this->init_from_db($wheres,false,1);
      $this->is_logined=$this->enable;
      $this->read_dta_to_fields();
    }
  }

  function do_login_by_id($user_id){
    global $o_session,$o_global;
    $o_session->set_session('user_id','', $user_id);
    $this->init_from_sesion();
  }

  function do_login($login,$pass, $do_redirect=true){
    global $o_session;

    $this->enable=false;
    $this->is_logined=false;
    $s_login=$this->db->tosql($login);
    $s_pass=$this->db->tosql($pass);

    $wheres=array();
    $wheres[]="login=$s_login";
    $wheres[]="pass=PASSWORD($s_pass)";
    $wheres[]="is_blocked='n'";
    $this->init_from_db($wheres,false,1);
    if ($this->enable){
      $this->is_logined=true;
      $this->read_dta_to_fields();
      $o_session->set_session('user_id','', $this->res_first['user_id']);
      if ($do_redirect){
        redirect_to($o_session->get_session('login_from', ''));
      }
      return true;
    }
    return false;
  }

  function do_register($login,$pass, $rights='user'){
    global $o_session;

    $this->enable=false;
    $this->is_logined=false;
    $s_login=$this->db->tosql($login);

    $sql="SELECT count(*) FROM sys_users WHERE login=$s_login";
    if ($this->db->get_db_value($sql,0)>0) {
      return false;
    }

    $s_pass=$this->db->tosql($pass);
    $s_rights=$this->db->tosql($rights);

    $sql="INSERT INTO sys_users
          (login, pass, rights, is_blocked)
          VALUES
          ($s_login, PASSWORD($s_pass), $s_rights, 'n')";
    $this->db->db_query($sql);

    return $this->db->last_id;
  }

  function do_logout(){
    global $o_session,$o_global;
    $o_session->unregister_session('user_id','');
    $this->enable=FALSE;
    $this->is_logined=FALSE;

    /*$o_session->destroy_session();
    $o_session=new c_session();
    $this->init_from_sesion();*/
  }

  function check_rights($rights){
    $cur_rights=$this->res_first['rights'];
    //if ($cur_rights=='root') $cur_rights='admin';//root'у можно всё
    if ($cur_rights=='root') return true;//root'у можно всё
    if (!isset($rights)) return true;
    if ($this->is_logined){
      if (is_array($rights)&& in_array($cur_rights,$rights) ||
          $cur_rights==$rights ||
          empty($rights)) return true;
    }
    else {
      if ($rights=='_unloggined_') return true;
    }
    return false;
  }
}

