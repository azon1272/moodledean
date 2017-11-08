<?php
require_once('lib.php');
require_login();
// load_all_capabilities();
get_fast_modinfo(get_record("course", "id", SITEID));
redirect("{$_SERVER['HTTP_REFERER']}");
?>