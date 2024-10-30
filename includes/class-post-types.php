<?php

namespace mailqueue;

class PostTypes {

	public function __construct() {
	}

	function queued_mail() {

		$labels = array(
			'name'                  => _x( 'Queued mails', 'Post Type General Name', 'mailqueue' ),
			'singular_name'         => _x( 'Queued mail', 'Post Type Singular Name', 'mailqueue' ),
			'menu_name'             => __( 'Queued mails', 'mailqueue' ),
			'name_admin_bar'        => __( 'Queued mail', 'mailqueue' ),
			'archives'              => __( 'Item Archives', 'mailqueue' ),
			'attributes'            => __( 'Item Attributes', 'mailqueue' ),
			'parent_item_colon'     => __( 'Parent Item:', 'mailqueue' ),
			'all_items'             => __( 'All Items', 'mailqueue' ),
			'add_new_item'          => __( 'Add New Item', 'mailqueue' ),
			'add_new'               => __( 'Add New', 'mailqueue' ),
			'new_item'              => __( 'New Item', 'mailqueue' ),
			'edit_item'             => __( 'Edit Item', 'mailqueue' ),
			'update_item'           => __( 'Update Item', 'mailqueue' ),
			'view_item'             => __( 'View Item', 'mailqueue' ),
			'view_items'            => __( 'View Items', 'mailqueue' ),
			'search_items'          => __( 'Search Item', 'mailqueue' ),
			'not_found'             => __( 'Not found', 'mailqueue' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'mailqueue' ),
			'featured_image'        => __( 'Featured Image', 'mailqueue' ),
			'set_featured_image'    => __( 'Set featured image', 'mailqueue' ),
			'remove_featured_image' => __( 'Remove featured image', 'mailqueue' ),
			'use_featured_image'    => __( 'Use as featured image', 'mailqueue' ),
			'insert_into_item'      => __( 'Insert into item', 'mailqueue' ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', 'mailqueue' ),
			'items_list'            => __( 'Items list', 'mailqueue' ),
			'items_list_navigation' => __( 'Items list navigation', 'mailqueue' ),
			'filter_items_list'     => __( 'Filter items list', 'mailqueue' ),
		);
		$args   = array(
			'label'               => __( 'Queued mail', 'mailqueue' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', 'custom-fields' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 5,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'capability_type'     => 'page',
			'show_in_rest'        => false,
		);
		register_post_type( 'queued_mail', $args );
	}

	function set_custom_queued_mail_columns( $columns ) {
		unset( $columns['date'] );
		$columns['status'] = __( 'Status', 'mailqueue' );
		$columns['date']   = __( 'Date', 'mailqueue' );

		return $columns;
	}

	function custom_queued_mail_column( $column, $post_id ) {
		switch ( $column ) {
			case 'status' :
				$post_status = get_post_status( $post_id );
				switch ( $post_status ) {
					case 'to_send':
						_e( 'To send', 'mailqueue' );
						break;
					case 'sent':
						_e( 'Sent', 'mailqueue' );
						break;
					case 'send_failed':
						_e( 'Send failed', 'mailqueue' );
						break;
					case 'draft':
						_e( 'Draft', 'mailqueue' );
						break;
					case 'mail_draft':
						_e( 'Mail draft', 'mailqueue' );
						break;
					default:
						_e( 'Unrecognized', 'mailqueue' );
				}
				break;

		}
	}

	function register_post_statuses() {
		register_post_status( 'to_send', array(
			'label'                     => _x( 'To send', 'mailqueue' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'To send <span class="count">(%s)</span>', 'To send <span class="count">(%s)</span>' ),
		) );
		register_post_status( 'sent', array(
			'label'                     => _x( 'Sent', 'mailqueue' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Sent <span class="count">(%s)</span>', 'Sent <span class="count">(%s)</span>' ),
		) );
		register_post_status( 'send_failed', array(
			'label'                     => _x( 'Send failed', 'mailqueue' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Send failed <span class="count">(%s)</span>', 'Send failed <span class="count">(%s)</span>' ),
		) );
		register_post_status( 'mail_draft', array(
			'label'                     => _x( 'Mail draft', 'mailqueue' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Mail draft <span class="count">(%s)</span>', 'Mail draft <span class="count">(%s)</span>' ),
		) );
	}

	function custom_statuses_in_post_page() {
		echo "<script>
        jQuery(document).ready( function() {  
            if (jQuery('body').hasClass('post-type-queued_mail')) {
            jQuery( 'select[name=\"post_status\"]' ).append( '<option value=\"to_send\">To send</option>' );
            jQuery( 'select[name=\"post_status\"]' ).append( '<option value=\"sent\">Sent</option>' );
            jQuery( 'select[name=\"post_status\"]' ).append( '<option value=\"send_failed\">Send failed</option>' );                
            jQuery( 'select[name=\"post_status\"]' ).append( '<option value=\"mail_draft\">Mail draft</option>' );                
            }
        });
        </script>";
	}

	function custom_statuses_in_quick_edit() {
		echo "<script>
        jQuery(document).ready( function() {  
            if (jQuery('body').hasClass('post-type-queued_mail')) {
            jQuery( 'select[name=\"_status\"]' ).append( '<option value=\"to_send\">To send</option>' );
            jQuery( 'select[name=\"_status\"]' ).append( '<option value=\"sent\">Sent</option>' );
            jQuery( 'select[name=\"_status\"]' ).append( '<option value=\"send_failed\">Send failed</option>' );                
            jQuery( 'select[name=\"_status\"]' ).append( '<option value=\"mail_draft\">Mail draft</option>' );                
            }
        });
        </script>";
	}

	function set_status_to_send( $post ) {
		$post_type = get_post_type( $post );
		if ( 'queued_mail' == $post_type ) {
			$html = '<div id="major-publishing-actions" style="overflow:hidden">';
			$html .= '<div id="publishing-action">';
			$html .= '<a type="button" href="post.php?post=' . $post->ID
			         . '&action=set_status_to_send" accesskey="p" tabindex="5" class="button-primary" id="custom" name="publish">' . __( "Set status `to send`", 'mailqueue' )
			         . '</a>';
			$html .= '</div>';
			$html .= '</div>';
			echo $html;
		}
	}

	function set_status_mail_draft( $post ) {
		$post_type = get_post_type( $post );
		if ( 'queued_mail' == $post_type ) {
			$html = '<div id="major-publishing-actions" style="overflow:hidden">';
			$html .= '<div id="publishing-action">';
			$html .= '<a type="button" href="post.php?post=' . $post->ID
			         . '&action=set_status_mail_draft" accesskey="p" tabindex="5" class="button-primary" id="custom" name="publish">' . __( "Set status `mail draft`", 'mailqueue' )
			         . '</a>';
			$html .= '</div>';
			$html .= '</div>';
			echo $html;
		}
	}

	function set_post_status() {
		global $typenow;
		if ( 'queued_mail' != $typenow ) {
			wp_redirect( $_SERVER['HTTP_REFERER'] );
			exit();
		}
		if ( isset( $_GET['action'] ) && $_GET['action'] == 'set_status_to_send' ) {
			$post_id = intval( $_GET['post'] );
			$postarr = [
				'ID'          => $post_id,
				'post_status' => 'to_send'
			];
			$success = wp_update_post( $postarr );
		}
		if ( isset( $_GET['action'] ) && $_GET['action'] == 'set_status_mail_draft' ) {
			$post_id = intval( $_GET['post'] );
			$postarr = [
				'ID'          => $post_id,
				'post_status' => 'mail_draft'
			];
			$success = wp_update_post( $postarr );
		}

		wp_redirect( $_SERVER['HTTP_REFERER'] );
		exit();
	}
}