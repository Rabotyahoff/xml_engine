<?php
load_class('c_handler');

class h_static extends a_handler {
  function process(){
    global $o_global;
    $this->xsl='static/'.$this->handler_info['@xsl'];
    $this->h_data['site']=$o_global->settings_array['site'];
    $this->h_data['emails']=$o_global->settings_array['emails'];
  }
}

