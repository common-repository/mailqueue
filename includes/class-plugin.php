<?php

namespace mailqueue;

/**
 * The main plugin class.
 */
class Plugin {

	private $loader;
	private $plugin_slug;
	private $version;
	private $option_name;
	private $class_emails;

	public function __construct() {
		$this->plugin_slug = Info::SLUG;
		$this->version     = Info::VERSION;
		$this->option_name = Info::OPTION_NAME;
		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->register_post_type_and_statuses();
		$this->override_wp_mail();
		$this->class_cron_tasks();
	}

	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-post-types.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-override-wp-mail.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cron-tasks.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-emails.php';
		$this->loader       = new Loader();
		$this->class_emails = ( new Emails() );
		$this->loader->add_action( 'wp_ajax_send_test_email', $this, 'send_test_email' );
		$this->loader->add_action( 'wp_ajax_nopriv_send_test_email', $this, 'send_test_email' );
		$this->loader->add_action( 'admin_action_send_email', $this, 'post_row_action_send_email_handler' );
		$this->loader->add_filter( 'bulk_actions-edit-queued_mail', $this, 'register_new_bulk_actions' );
		$this->loader->add_filter( 'handle_bulk_actions-edit-queued_mail', $this, 'new_bulk_actions_handler', 10, 3 );
		$this->loader->add_filter( 'post_row_actions', $this, 'add_send_email_link', 10, 2 );
	}

	function clear_recipients( $phpmailer ) {
		$phpmailer->ClearAllRecipients();
	}

	private function define_admin_hooks() {
		$plugin_admin = new Admin( $this->plugin_slug, $this->version, $this->option_name );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'assets' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_menus' );
	}

	private function register_post_type_and_statuses() {
		$post_types = new PostTypes();
		$this->loader->add_action( 'init', $post_types, 'queued_mail', 0 );
		$this->loader->add_action( 'init', $post_types, 'register_post_statuses' );
		$this->loader->add_action( 'admin_footer-post.php', $post_types, 'custom_statuses_in_post_page' );
		$this->loader->add_action( 'admin_footer-post-new.php', $post_types, 'custom_statuses_in_post_page' );
		$this->loader->add_action( 'admin_footer-edit.php', $post_types, 'custom_statuses_in_quick_edit' );
		$this->loader->add_filter( 'manage_queued_mail_posts_columns', $post_types, 'set_custom_queued_mail_columns' );
		$this->loader->add_action( 'manage_queued_mail_posts_custom_column', $post_types, 'custom_queued_mail_column', 10, 2 );
		$this->loader->add_action( 'post_submitbox_misc_actions', $post_types, 'set_status_to_send' );
		$this->loader->add_action( 'post_submitbox_misc_actions', $post_types, 'set_status_mail_draft' );
		$this->loader->add_action( 'admin_action_set_status_to_send', $post_types, 'set_post_status' );
		$this->loader->add_action( 'admin_action_set_status_mail_draft', $post_types, 'set_post_status' );
	}

	private function override_wp_mail() {
		$override_wp_mail = new OverrideWpMail();
		$this->loader->add_filter( 'wp_mail', $override_wp_mail, 'filter_mail' );
	}

	private function class_cron_tasks() {
		$class_cron_tasks = new CronTasks();
		$this->loader->add_action( 'remove_old_emails', $class_cron_tasks, 'remove_old_emails_function' );
		$this->loader->add_action( 'automatic_send_emails', $class_cron_tasks, 'automatic_send_emails_function' );
		$this->loader->add_filter( 'cron_schedules', $class_cron_tasks, 'mailqueue_add_intervals' );
	}

	function send_test_email() {
		$email   = sanitize_email( $_POST['email'] );
		$subject = sanitize_text_field( $_POST['subject'] );
		$message = sanitize_textarea_field( $_POST['message'] );
		$headers = [];

		wp_mail( $email, $subject, $message, $headers );
		echo json_encode( true );
		wp_die();
	}


	function register_new_bulk_actions( $bulk_actions ) {
		$bulk_actions['send_emails'] = __( 'Send emails', 'mailqueue' );

		return $bulk_actions;
	}

	function new_bulk_actions_handler( $redirect_to, $doaction, $post_ids ) {
		if ( $doaction !== 'send_emails' ) {
			return $redirect_to;
		}
		foreach ( $post_ids as $post_id ) {
			$post_status = get_post_status( $post_id );
			if ( $post_status == 'to_send' || $post_status == 'send_failed' ) {
				$data = $this->prepare_data_for_email( $post_id );
				$this->class_emails->send_email( $data );
			}
		}

		return $redirect_to;
	}

	public function add_send_email_link( $actions, $id ) {
		global $post, $current_screen, $mode;

		$post_type_object = get_post_type_object( $post->post_type );
		if ( $post_type_object->name !== 'queued_mail' ) {
			return $actions;
		}

		$actions = array_merge( $actions, array(
			'send_email' => sprintf( '<a href="%s">' . __( 'Send email', 'mailqueue' ) . '</a>',
				wp_nonce_url( sprintf( 'edit.php?post_type=queued_mail&action=send_email&post_id=%d', $post->ID ),
					'send_email' ) )
		) );

		return $actions;
	}

	function post_row_action_send_email_handler() {
		global $typenow;

		if ( 'queued_mail' != $typenow ) {
			wp_redirect( $_SERVER['HTTP_REFERER'] );
			exit();
		}
		if ( isset( $_GET['action'] ) && $_GET['action'] == 'send_email' ) {
			$post_id = $_GET['post_id'];
			$data    = $this->prepare_data_for_email( $post_id );
			$this->class_emails->send_email( $data );
		}

		wp_redirect( $_SERVER['HTTP_REFERER'] );
		exit();
	}

	function prepare_data_for_email( $post_id ) {
		$post = get_post( $post_id, ARRAY_A );
		$data = [
			'post'         => $post,
			'to'           => get_post_meta( $post_id, 'to', true ),
			'subject'      => get_post_meta( $post_id, 'subject', true ),
			'cc'           => json_decode( get_post_meta( $post_id, 'cc', true ), true ),
			'bcc'          => json_decode( get_post_meta( $post_id, 'bcc', true ), true ),
			'content_type' => get_post_meta( $post_id, 'content_type', true ),
			'attachments'  => json_decode( get_post_meta( $post_id, 'attachments', true ), true ),
			'post_content' => get_post_meta( $post_id, 'post_content', true ),
		];

		return $data;
	}


	public function run() {
		$this->loader->run();
	}
}
