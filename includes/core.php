<?php

function frayd_edit_notes_register_post_type() {
	// notes custom post type
	$labels = array(
		'name'               => _x( 'Notes', 'post type general name' ),
		'singular_name'      => _x( 'Note', 'post type singular name' ),
		'add_new'            => _x( 'Add New Note', 'frayd_edit_notes' ),
		'add_new_item'       => __( 'Add New Note' ),
		'edit_item'          => __( 'Edit Note' ),
		'new_item'           => __( 'New Note' ),
		'all_items'          => __( 'All Notes' ),
		'view_item'          => __( 'View Note' ),
		'search_items'       => __( 'Search Notes' ),
		'not_found'          => __( 'No note found' ),
		'not_found_in_trash' => __( 'No note found in the Trash' ), 
		'parent_item_colon'  => '',
		'menu_name'          => 'Notes'
	);
	$args = array(
		'labels'        => $labels,
		'description'   => 'Holds our notes and related data',
		'public'        => true,
		'menu_position' => 20,
		'supports'      => array( 'title' ),
		'hierarchical'  => false,
		'has_archive'   => false,
		'exclude_from_search' => true,
	);
	register_post_type( 'frayd_edit_notes', $args ); // post type name max 20 characters
	flush_rewrite_rules();
}
add_action( 'init', 'frayd_edit_notes_register_post_type' );


function frayd_edit_notes_updated_messages( $messages ) {
	global $post, $post_ID;
	$messages['frayd_edit_notes'] = array(
		0 => '', 
		1 => sprintf( __('Note updated. <a href="%s">View</a>'), esc_url( get_permalink($post_ID) ) ),
		2 => __('Custom field updated.'),
		3 => __('Custom field deleted.'),
		4 => __('Note updated.'),
		5 => isset($_GET['revision']) ? sprintf( __('Note restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __('Note published. <a href="%s">View</a>'), esc_url( get_permalink($post_ID) ) ),
		7 => __('Note saved.'),
		8 => sprintf( __('Note submitted. <a target="_blank" href="%s">Preview</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		9 => sprintf( __('Note scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview</a>'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
		10 => sprintf( __('Note draft updated. <a target="_blank" href="%s">Preview</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	);

	return $messages;
}
add_filter( 'post_updated_messages', 'frayd_edit_notes_updated_messages' );

function frayd_edit_notes_load_script() {
	wp_register_style( 'frayd_edit_notes_style', FRAYD_EDIT_NOTES_URL.'assets/css/frayd-edit-notes.css', false, '1.0' );
	wp_enqueue_style( 'frayd_edit_notes_style' );

	wp_register_script( 'frayd_edit_notes_script', FRAYD_EDIT_NOTES_URL.'assets/js/frayd-edit-notes.js', array( 'jquery', 'jquery-ui-draggable' ), '1.0' );
	wp_enqueue_script( 'frayd_edit_notes_script' );
}
add_action( 'wp_enqueue_scripts', 'frayd_edit_notes_load_script', 10 );

function frayd_edit_notes_adminbar_button( $wp_admin_bar ) {
	if( !is_admin() ) {
		$args = array(
			'id' => 'frayd-edit-notes-adminbar-button',
			'title' => 'Add Note',
			'href' => 'javascript:void(0);',
			'meta' => array(
				'class' => 'frayd_edit_notes_adminbar_button',
				'onclick' => 'us.frayd.editnotes.newNote( event );',
			)
		);
		$wp_admin_bar->add_node($args);
	}
}
add_action('admin_bar_menu', 'frayd_edit_notes_adminbar_button', 50);

function frayd_edit_notes_page_setup() {
	global $post, $cat;

	$show_public = get_option('frayd_edit_notes_show_public');
	$show_notes = $show_public == 1 ? true : ( is_user_logged_in() ? true : false );

	if( !is_admin() ) {
		if( $show_notes ) {
			echo '<div id="frayd_edit_notes" data-page-url="' . $_SERVER['REQUEST_URI'] . '">';
			echo frayd_edit_notes_get_notes_html( array('url' => $_SERVER['REQUEST_URI']) );
			echo '</div>';
		}
	}
}
add_action('wp_footer', 'frayd_edit_notes_page_setup', 100);


/**

	  SSS  EEEEE  TTTTT  TTTTT  IIIII  NN  N   GGGG    SSS
	SS     E__      T      T      I    N N N  G      SS
	  SSS  E        T      T      I    N  NN  G  GG    SSS
	SS     EEEEE    T      T    IIIII  N   N  GGGGG  SS

*/

function frayd_edit_notes_register_settings() {
	add_option( 'frayd_edit_notes_show_public', '0');

	register_setting( 'frayd_edit_notes_settings', 'frayd_edit_notes_show_public' ); 
} 
add_action( 'admin_init', 'frayd_edit_notes_register_settings' );

function frayd_edit_notes_register_options_page() {
	add_submenu_page('edit.php?post_type=frayd_edit_notes', 'Settings', 'Settings', 'manage_options', 'frayd_edit_notes-options', 'frayd_edit_notes_options_page');
}
add_action('admin_menu', 'frayd_edit_notes_register_options_page');

function frayd_edit_notes_options_page() {
	?>

	<div class="wrap">
		<h2>StickyNotate Settings</h2>

		<form method="post" action="options.php">
			<?php

			settings_fields( 'frayd_edit_notes_settings' );
			do_settings_sections( 'frayd_edit_notes_settings' );

			$show_public = get_option('frayd_edit_notes_show_public');

			?>

			<table class="form-table">
				<tr valign="top">
					<th scope="row">Show notes to public/non-signed in users?</th>
					<td>
						<input id="frayd_edit_notes_show_public_yes" type="radio" name="frayd_edit_notes_show_public" value="1"<?php if( $show_public == 1 ): ?> checked<?php endif; ?> /> <label for="frayd_edit_notes_show_public_yes">Yes</label>
						&nbsp;
						<input id="frayd_edit_notes_show_public_no" type="radio" name="frayd_edit_notes_show_public" value="0"<?php if( $show_public == 0 ): ?> checked<?php endif; ?> /> <label for="frayd_edit_notes_show_public_no">No</label>
					</td>
				</tr>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>

	<?php
}


/**

	DDDD    AAA   TTTTT   AAA
	D   D  A   A    T    A   A
	D   D  AAAAA    T    AAAAA
	DDDD   A   A    T    A   A

*/
function frayd_edit_notes_get_notes( $opts=array() ) {
	$args = array(
		'post_type' => 'frayd_edit_notes',
		'posts_per_page' => -1,
		'post_status' => 'publish',
	);
	if( isset($opts["note_id"]) && is_numeric($opts["note_id"]) ) {
		$args['p'] = $opts["note_id"];
	}
	if( isset($opts["url"]) ) {
		$args['meta_query'] = array(
			array(
				'key'     => '_frayd_edit_notes_page_url',
				'compare' => '=',
				'value'   => $opts["url"],
			),
		);
	}
	$query = new WP_Query( $args );

	if( $query->have_posts() ) {
		$notes = $query->get_posts();

		foreach( $notes AS $key => $note ) {
			$notes[$key]->priority = get_post_meta( $note->ID, '_frayd_edit_notes_priority', true );
			$notes[$key]->top_offset = get_post_meta( $note->ID, '_frayd_edit_notes_top_offset', true );
			$notes[$key]->center_offset = get_post_meta( $note->ID, '_frayd_edit_notes_center_offset', true );
		}

		return $notes;
	} else {
		return false;
	}
}

function frayd_edit_notes_get_notes_html( $opts=array() ) {
	$notes = frayd_edit_notes_get_notes( $opts );
	$html = '';
	$priority = array( 'low', 'med', 'high' );
	$user_is_admin = current_user_can( 'manage_options' );

	if( $notes ) {
		foreach( $notes AS $note ) {
			$priority_id = array_search($note->priority, $priority);

			$html .= '<div id="frayd_edit_notes_note_' . $note->ID . '" ';
			$html .= 'class="note ' . $note->priority . ( !$user_is_admin ? ' tight' : '' ) . '" ';
			$html .= 'style="left: 50%; margin-left: ' . ($note->center_offset-115) . 'px; top: ' . $note->top_offset . 'px;" ';
			if( $user_is_admin ) {
				$html .= 'data-note-id="' . $note->ID . '" ';
				$html .= 'data-center-offset="' . $note->center_offset . '" ';
				$html .= 'data-top-offset="' . $note->top_offset . '" ';
				$html .= 'data-priority-id="' . $priority_id . '" ';
			}
			$html .= '>';
			$html .= '<div class="rte"' . ( $user_is_admin ? ' contenteditable="true"' : '' ) . '>' . $note->post_title . '</div>';
			$html .= '<div class="icon move"></div>';
			if( $user_is_admin ) {
				$html .= '<div class="icon color" title="Change note priority/color"></div>';
				$html .= '<div class="icon save" title="Save note"></div>';
			}
			$html .= '</div>';
		}
	}

	return $html;
}


/**

	M   M  IIIII    SSS   CCCC
	MM MM    I    SS     C    
	M M M    I      SSS  C    
	M   M  IIIII  SS      CCCC

*/
function is_frayd_edit_notes_post( $post ) {
	return ( is_object($post) && isset($post->post_type) && $post->post_type === 'frayd_edit_notes' );
}

?>