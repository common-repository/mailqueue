<?php

namespace mailqueue;

class Emails {

	private $option_name;

	public function __construct() {
		$this->option_name = Info::OPTION_NAME;
	}

	public function send_email( $data ) {
		$settings = get_option( $this->option_name );
		if ( ! empty( $settings ) && ! empty( $data ) ) {
			$ssl_settings = [];
			if ( $settings['disable-ssl-validation'] ) {
				$ssl_settings = [
					'allow_self_signed' => true,
					'verify_peer'       => false,
					'verify_peer_name'  => false
				];
			}

			$transport = ( new \Swift_SmtpTransport( $settings['smtp-server'], $settings['smtp-port'], $settings['smtp-encryption'] ) )
				->setUsername( $settings['smtp-login'] )
				->setPassword( $settings['smtp-password'] )
				->setStreamOptions( array( 'ssl' => $ssl_settings ) );

			$mailer = new \Swift_Mailer( $transport );

			$message = ( new \Swift_Message( $data['subject'] ) )
				->setFrom( [ $settings['smtp-login'] => $settings['smtp-username'] ] )
				->setTo( $data['to'] )
				->setSubject( $data['subject'] )
				->setBody( $data['post_content'] );

			foreach ( $data['cc'] as $cc ) {
				if ( ! empty( $cc[0]['email'] ) ) {
					if ( $cc[0]['name'] !== '' ) {
						$message->addCc( $cc[0]['email'], $cc[0]['name'] );
					} else {
						$message->addCc( $cc[0]['email'] );
					}
				}
			}

			foreach ( $data['bcc'] as $bcc ) {
				if ( ! empty( $bcc[0]['email'] ) ) {
					if ( ! empty( $bcc[0]['name'] ) ) {
						$message->addBcc( $bcc[0]['email'], $bcc[0]['name'] );
					} else {
						$message->addBcc( $bcc[0]['email'] );
					}
				}
			}

			foreach ( $data['attachments'] as $attachment ) {
				$message->attach( \Swift_Attachment::fromPath( $attachment ) );
			}

			$message->setContentType( 'text/html' );

			$result = $mailer->send( $message );
			if ( $result ) {
				$postarr = [
					'ID'          => $data['post']['ID'],
					'post_status' => 'sent'
				];
				wp_update_post( $postarr );
			} else {
				$postarr = [
					'ID'          => $data['post']['ID'],
					'post_status' => 'send_failed'
				];
				wp_update_post( $postarr );
			}
		}
	}
}