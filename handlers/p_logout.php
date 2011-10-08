<?php

//не наследуем от c_handler, т.к. надо убить сесию

global $o_cur_user, $o_session;
$o_cur_user->do_logout();
$_SESSION = array();
redirect_to('/');

