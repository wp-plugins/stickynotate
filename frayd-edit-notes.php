<?php

/*
Plugin Name: StickyNotate
Description: Add sticky notes directly to website content.
Version: 1.0.20150806
Author: Kevin Freitas, Frayd Media
Author URI: http://frayd.us/
*/

define('FRAYD_EDIT_NOTES_DIR', plugin_dir_path(__FILE__)); // USAGE: FRAYD_EDIT_NOTES_DIR.'assets/img/image.jpg'
define('FRAYD_EDIT_NOTES_URL', plugin_dir_url(__FILE__));


// Load files
function frayd_edit_notes_load() {
	if( is_admin() ) { // load admin files only in admin
		require_once(FRAYD_EDIT_NOTES_DIR.'includes/admin.php');
	}

	require_once(FRAYD_EDIT_NOTES_DIR.'includes/core.php');
}
frayd_edit_notes_load();
// END Load files

?>