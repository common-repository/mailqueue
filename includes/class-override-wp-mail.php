<?php

namespace mailqueue;

class OverrideWpMail {

	private $option_name;

	public function __construct() {
		$this->option_name = Info::OPTION_NAME;
	}

	function filter_mail( $args ) {
		$settings = get_option( $this->option_name );

		$subjects       = [];
		if ( ! empty( $settings ) && $settings['email-subjects'] ) {
			$email_subjects = $settings['email-subjects'];
			$email_subjects = str_replace( array( "\n", "\r" ), '', $email_subjects );
			$email_subjects = explode( ';', $email_subjects );
			foreach ( $email_subjects as $email_subject ) {
				if ( ! empty( $email_subject ) && ! in_array( $email_subject, $subjects ) ) {
					array_push( $subjects, $email_subject );
				}
			}
		}

		$added = false;
		foreach ( $subjects as $subject ) {
			if ( strpos( $args['subject'], $subject ) > - 1 && ! $added ) {
				if ( ! empty( $settings ) && $settings['automatic-processing'] ) {
					$post_status = 'to_send';
				} else {
					$post_status = 'mail_draft';
				}
				$postarr = [
					'post_title'   => $args['subject'] . ' To ' . $args['to'],
					'post_content' => '',
					'post_status'  => $post_status,
					'post_type'    => 'queued_mail',
				];

				$post_id = wp_insert_post( $postarr );

				$headers      = $args['headers'];
				$cc           = [];
				$bcc          = [];
				$content_type = '';
				if ( ! empty( $headers ) && is_array( $headers ) ) {
					foreach ( $headers as $header ) {
						if ( strpos( strtolower( $header ), 'bcc:' ) > - 1 ) {
							$bcc[] = $this->parseAddressList( substr( $header, 4 ) );
						} else if ( strpos( strtolower( $header ), 'cc:' ) > - 1 ) {
							$cc[] = $this->parseAddressList( substr( $header, 3 ) );
						} else if ( strpos( strtolower( $header ), 'content-type:' ) > - 1 ) {
							$content_type = substr( $header, 14 );
						}
					}
				}

				if ( $post_id ) {
					add_post_meta( $post_id, 'subject', $args['subject'], true );
					add_post_meta( $post_id, 'to', $args['to'], true );
					add_post_meta( $post_id, 'cc', json_encode( $cc ), true );
					add_post_meta( $post_id, 'bcc', json_encode( $bcc ), true );
					add_post_meta( $post_id, 'content_type', json_encode( $content_type ), true );
					add_post_meta( $post_id, 'headers', json_encode( $args['headers'] ), true );
					add_post_meta( $post_id, 'attachments', json_encode( $args['attachments'] ), true );
					add_post_meta( $post_id, 'post_content', $args['message'], true );
				}

				add_action( 'phpmailer_init', [ $this, 'phpmailer_clear_recipients' ] );
				unset( $args['to'] );
				$added = true;
			}
		}

		return $args;
	}

	function phpmailer_clear_recipients( $phpmailer ) {
		$phpmailer->ClearAllRecipients();

		remove_action( 'phpmailer_init', [ $this, 'phpmailer_clear_recipients' ] );
	}

	public static function parseAddressList( $addressList ) {
		$pattern = '/^(?:"?([^<"]+)"?\s)?<?([^>]+@[^>]+)>?$/';
		if ( preg_match( $pattern, $addressList, $matches ) ) {
			return array(
				array(
					'name'  => trim( stripcslashes( $matches[1] ) ),
					'email' => trim( $matches[2] )
				)
			);
		} else {
			$parts  = str_getcsv( $addressList );
			$result = array();
			foreach ( $parts as $part ) {
				if ( preg_match( $pattern, $part, $matches ) ) {
					$result[] = array(
						'name'  => trim( stripcslashes( $matches[1] ) ),
						'email' => trim( $matches[2] )
					);
				}
			}

			return $result;
		}
	}
}