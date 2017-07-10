<?php

/**
 * Webhook notifications for backups
 *
 * @extends FILETRIP_BKP_Service
 */
abstract class FILETRIP_BKP_Webhooks_Service extends FILETRIP_BKP_Service {

	/**
	 * Human readable name for this service
	 * @var string
	 */
	public $name = 'Webhook';

	public function __construct( $schedule ) {
		parent::__construct( $schedule );
	}

	/**
	 * @return string
	 */
	abstract protected function get_secret_key();

	/**
	 * @return string
	 */
	abstract protected function get_url();

	/**
	 * Fire the webhook notification on the filetrip_bkp_backup_complete
	 *
	 * @see  FILETRIP_Backup::do_action
	 * @param  string $action The action received from the backup
	 * @return void
	 */
	public function action( $action ) {

		if ( 'filetrip_bkp_backup_complete' !== $action || ! $this->is_service_active() )
			return;

		$webhook_url = $this->get_url();
		$file        = $this->schedule->get_archive_filepath();
		$download    = esc_url(add_query_arg( 'filetrip_bkp_download', base64_encode( $file ), FILETRIP_BKP_ADMIN_URL ));
		$domain      = parse_url( home_url(), PHP_URL_HOST ) . parse_url( home_url(), PHP_URL_PATH );

		// The backup failed, send a message saying as much
		if ( ! file_exists( $file ) && ( $errors = array_merge( $this->schedule->get_errors(), $this->schedule->get_warnings() ) ) ) {

			$error_message = '';

			foreach ( $errors as $error_set )
				$error_message .= implode( "\n - ", $error_set );

			if ( $error_message )
				$error_message = ' - ' . $error_message;

			$subject = sprintf( __( 'Backup of %s Failed', 'filetrip-plugin' ), $domain );

			$body = array (
				'type'         => 'backup.error',
				'site_url'     => site_url(),
				'backup'      => array(
					'id'           => 'backup_' . pathinfo( $file, PATHINFO_FILENAME ),
					'start'        => '0',
					'end'          => '0',
					'download_url' => '',
					'type'         => $this->schedule->get_type(),
					'status'       => array(
						'message'      => $subject . ' - ' . $error_message,
						'success'      => '0'
					)
				)
			);

		} else {

			$body = array (
				'type'         => 'backup.success',
				'site_url'     => site_url(),
				'backup'      => array(
					'id'           => 'backup_' . $this->schedule->get_id(),
					'start'        => '0',
					'end'          => '0',
					'download_url' => $download,
					'type'         => $this->schedule->get_type(),
					'status'       => array(
						'message'      => 'Backup complete',
						'success'      => '1'
					)
				)
			);

		}

		$signature    = hash_hmac( 'sha1', serialize( $body ), $this->get_secret_key() );
		$webhook_args = array( 'headers' => array( 'X-BWP-Signature' => $signature ), 'body' => $body );

		$ret = wp_remote_post( $webhook_url, $webhook_args );

		if ( is_wp_error( $ret ) )
			$this->schedule->error( 'Webhook', sprintf( __( 'Error: %s', 'filetrip-plugin' ), $ret->get_error_message() ) );

	}

	public static function intercom_data() { return array(); }

	public static function intercom_data_html() {}
}
