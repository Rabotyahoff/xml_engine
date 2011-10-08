<?php
load_class('c_handler');

class h_menu extends a_handler {
  function process(){
    global $o_global, $o_cur_user;

    $this->xsl_params['cur_rights']=$o_cur_user->rights;
    $menus=$o_global->site_array['menus'][$this->handler_info['*']];
    $this->xsl='menu/'.$menus['xsl']['.'].'.xsl';
    $this->h_data['menu']=$menus;
  }
}

