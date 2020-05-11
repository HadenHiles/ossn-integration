<?php
// exit if uninstall constant is not defined
if (!defined('WP_UNINSTALL_PLUGIN')) exit;

// delete plugin options
delete_option('ossn_options');
?>
