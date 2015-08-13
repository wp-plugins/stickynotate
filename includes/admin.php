<?php

function frayd_edit_notes_admin_load_script() {
	wp_register_style( 'frayd_edit_notes_admin_style', FRAYD_EDIT_NOTES_URL.'assets/css/frayd-edit-notes-admin.css', false, '1.0' );
	wp_enqueue_style( 'frayd_edit_notes_admin_style' );

	wp_register_script( 'frayd_edit_notes_admin_script', FRAYD_EDIT_NOTES_URL.'assets/js/frayd-edit-notes-admin.js', false, '1.0' );
	wp_enqueue_script( 'frayd_edit_notes_admin_script' );
}
add_action( 'admin_enqueue_scripts', 'frayd_edit_notes_admin_load_script', 10 );

/* register menu item */
function frayd_edit_notes_admin_menu_setup() {
	// setup custom admin menu
	remove_submenu_page('edit.php?post_type=frayd_edit_notes', 'post-new.php?post_type=frayd_edit_notes');
}
add_action('admin_menu', 'frayd_edit_notes_admin_menu_setup'); //menu setup


// adjust columns for frayd_hero_slider post type
add_filter('manage_frayd_edit_notes_posts_columns', 'frayd_edit_notes_admin_columns');
function frayd_edit_notes_admin_columns( $columns ) {
	$new = array();
	foreach( $columns as $key => $title ) {
		$new[$key] = $title;

		if( $key == 'title' ) { // Put new column after Title column
			$new['note_url'] = 'Related Page';
			$new['priority'] = 'Priority';
		}
	}
	return $new;
}

add_action('manage_frayd_edit_notes_posts_custom_column', 'frayd_edit_notes_admin_show_columns');
function frayd_edit_notes_admin_show_columns($name) {
	global $post;

	$page_url = get_post_meta( $post->ID, '_frayd_edit_notes_page_url', true );

	switch ($name) {
		case 'note_url':
			echo get_bloginfo('url') . $page_url;
			echo ' <a href="' . $page_url . '#frayd_edit_notes_note_' . $post->ID . '" class="frayd_edit_notes_admin_view" title="View related page"></a>';
		break;

		case 'priority':
			$priority = get_post_meta( $post->ID, '_frayd_edit_notes_priority', true );
			echo '<div class="frayd_edit_notes_admin_priority ' . $priority . '" title="' . ucwords($priority) . '"></div>';
		break;
	}
}

// add actions to admin notes list
function frayd_edit_notes_row_actions( $actions, $post ) {
	if( is_frayd_edit_notes_post( $post ) ) {
		unset($actions['view']);
		unset($actions['inline hide-if-no-js']);
	}
	return $actions;
}
add_filter( 'post_row_actions', 'frayd_edit_notes_row_actions', 10, 2);
add_filter( 'page_row_actions', 'frayd_edit_notes_row_actions', 10, 2);

add_action( 'admin_head-edit.php', 'frayd_edit_notes_change_title_in_list');
function frayd_edit_notes_change_title_in_list() {
	global $post_type;

	if( 'frayd_edit_notes' != $post_type ) {
		return;
	}

	add_filter( 'the_title', 'frayd_edit_notes_construct_new_title', 100, 2 );
}
function frayd_edit_notes_construct_new_title( $title, $id ) {
	return str_replace("\n ", "<br>\n ", strip_tags( str_replace(array("<br />", "</div>"), "\n ", htmlspecialchars_decode($title)) ));
}


/**

	 AAA   JJJJJ   AAA   X   X
	A   A     J   A   A   X_X
	AAAAA     J   AAAAA   X X
	A   A  JJJ    A   A  X   X

*/
add_action( 'admin_action_frayd_edit_notes_ajax_new_note', 'frayd_edit_notes_ajax_new_note');
function frayd_edit_notes_ajax_new_note() {
	// create new note post
	$args = array(
		'post_type' => 'frayd_edit_notes',
		'post_title' => 'Note text here...',
		'post_content' => NULL,
		'post_author' => get_current_user_id(),
		'post_date_gmt' => date("Y-m-d H:i:s"),
		'post_status' => 'publish',
	);

	$note_id = wp_insert_post( $args );

	update_post_meta( $note_id, '_frayd_edit_notes_page_url', $_REQUEST['url'] );
	update_post_meta( $note_id, '_frayd_edit_notes_priority', 'med' );
	update_post_meta( $note_id, '_frayd_edit_notes_top_offset', '64' );
	update_post_meta( $note_id, '_frayd_edit_notes_center_offset', '0' );

	echo frayd_edit_notes_get_notes_html( array("note_id" => $note_id) );
}

add_action( 'admin_action_frayd_edit_notes_ajax_save_note', 'frayd_edit_notes_ajax_save_note');
function frayd_edit_notes_ajax_save_note() {
	$priority = array( 'low', 'med', 'high' );

	$note_id = $_REQUEST["note_id"];
	$args = array(
		'ID' => $note_id,
		'post_title' => $_REQUEST["note_text"]
	);

	wp_update_post( $args );

	update_post_meta( $note_id, '_frayd_edit_notes_top_offset', $_REQUEST["top_offset"] );
	update_post_meta( $note_id, '_frayd_edit_notes_center_offset', $_REQUEST["center_offset"] );
	update_post_meta( $note_id, '_frayd_edit_notes_priority', $priority[$_REQUEST["priority"]] );

	echo "success";

	exit;
}


/**

	M   M  EEEEE  TTTTT   AAA
	MM MM  E__      T    A   A
	M M M  E        T    AAAAA
	M   M  EEEEE    T    A   A

*/
add_action( 'add_meta_boxes', 'frayd_edit_notes_options_boxes' );
function frayd_edit_notes_options_boxes() {

	add_meta_box( 
		'frayd_edit_notes_options_box',
		__( '&nbsp;', 'frayd_edit_notes_textdomain' ),
		'frayd_edit_notes_options_box_content',
		'frayd_edit_notes',
		'normal',
		'core'
	);

}

add_filter('gettext', 'frayd_edit_notes_custom_enter_title');
function frayd_edit_notes_custom_enter_title( $input ) {
	global $post_type;

	if( is_admin() && 'Enter title here' == $input && 'frayd_edit_notes' == $post_type )
		return 'Note text...';

	return $input;
}

function frayd_edit_notes_options_box_content( $post ) {
	wp_nonce_field( plugin_basename( __FILE__ ), 'frayd_edit_notes_options_box_content_nonce' );

	$page_url = get_post_meta( $post->ID, '_frayd_edit_notes_page_url', true );

	echo '<table class="form-table">';  
	echo '	<tr>';
	echo '		<th><label for="frayd_edit_notes_page_url">Related Page</label></th>';
	echo '		<td><a href="' . $page_url . '">' . get_bloginfo('url') . $page_url . '</a></td>';
	echo '	</tr>';
	echo '</table>';
}


/**

	  SSS   AAA   V   V  EEEEE
	SS     A   A   V V   E__
	  SSS  AAAAA   V V   E
	SS     A   A    V    EEEEE

*/
add_action( 'save_post', 'frayd_edit_notes_options_box_save' );
function frayd_edit_notes_options_box_save( $post_id ) {
	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if( !isset($_POST['frayd_edit_notes_options_box_content_nonce']) || !wp_verify_nonce($_POST['frayd_edit_notes_options_box_content_nonce'], plugin_basename( __FILE__ )) ) { return; }
}

?>