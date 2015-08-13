<?php

//if uninstall not called from WordPress exit
if( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

global $wp_post_types;
if( isset($wp_post_types[ 'frayd_edit_notes' ]) ) {
	unset($wp_post_types[ 'frayd_edit_notes' ]);
}

?>