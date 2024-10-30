<?php

namespace mailqueue;

class CronTasks {
	private $class_emails;
	private $option_name;

	public function __construct() {
		$this->load_dependencies();
		$this->option_name = Info::OPTION_NAME;
	}

	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-emails.php';
		$this->class_emails = ( new Emails() );
	}

	function remove_old_emails_function() {
		global $wpdb;
		$sql = "SELECT * FROM {$wpdb->prefix}posts WHERE post_status = 'sent' AND post_type = 'queued_mail'";
		$sql .= " AND post_date < '" . date( 'Y-m-d H:i:s', strtotime( '-30 days' ) ) . "'";

		$posts = $wpdb->get_results( $sql, ARRAY_A );
		foreach ( $posts as $post ) {
			$postarr = [
				'ID'          => $post['ID'],
				'post_status' => 'trash'
			];
			wp_update_post( $postarr );
		}

		wp_reset_postdata();
	}

	function automatic_send_emails_function() {
		global $wpdb;
		$sql = "SELECT * FROM {$wpdb->prefix}posts WHERE post_status = 'to_send' AND post_type = 'queued_mail'";

		$settings = get_option( $this->option_name );
		if ( $settings['number-of-emails'] ) {
			$sql .= " LIMIT " . $settings['number-of-emails'];
		}

		$posts = $wpdb->get_results( $sql, ARRAY_A );
		foreach ( $posts as $post ) {
			$post_id = $post['ID'];
			$data    = [
				'post'         => $post,
				'to'           => get_post_meta( $post_id, 'to', true ),
				'subject'      => get_post_meta( $post_id, 'subject', true ),
				'cc'           => json_decode( get_post_meta( $post_id, 'cc', true ), true ),
				'bcc'          => json_decode( get_post_meta( $post_id, 'bcc', true ), true ),
				'content_type' => get_post_meta( $post_id, 'content_type', true ),
				'attachments'  => json_decode( get_post_meta( $post_id, 'attachments', true ), true ),
				'post_content' => get_post_meta( $post_id, 'post_content', true ),
			];
			$this->class_emails->send_email( $data );
		}
	}

	function mailqueue_add_intervals( $schedules ) {
		$schedules['15minutes'] = array(
			'interval' => 15 * 60,
			'display'  => __( '15 minutes' )
		);

		return $schedules;
	}
}