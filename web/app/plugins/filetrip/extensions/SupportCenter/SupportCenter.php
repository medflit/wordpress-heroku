<?php
/**
 * Example Extension
 *      Example extension just like below
 *      Use Extension Name Unique as Possible, because same Name Will Be [Override Able]
 *
 * @method init() as initialization after active
 */
namespace iTechFlare\WP\iTechFlareExtension;

use iTechFlare\WP\Plugin\FileTrip\Core\Abstracts\FlareExtension;
use iTechFlare\WP\Plugin\FileTrip\Core\Helper\LoaderOnce;
use iTechFlare\WP\Plugin\FileTrip\iTechFlareCore;
// use iTechFlare\WP\iTechFlareExtension\SupportCenter\Patcher\Silphium_Patcher_Initiator;

/**
 * Class SupportCenter
 * @package iTechFlare\WP\iTechFlareExtension
 */
class SupportCenter extends FlareExtension
{
	/**
	 * @var string
	 */
	protected $extension_name = 'Filetrip Support Center';

	/**
	 * @var string
	 */
	protected $wp_update_json = 'http://api.wordpress.org/core/version-check/1.7/';

	/**
	 * @var string
	 */
	protected $git_feeds_json = 'http://api.github.com/feeds';

	/**
	 * @var bool
	 */
	protected $is_support_ssl = false;

	/**
	 * @var string
	 */
	protected $extension_uri = 'https://itechflare.com'; // with or without http://

	/**
	 * @var string
	 */
	protected $extension_author = 'iTechFlare';

	/**
	 * @var capability
	 */
	protected $capability = 'edit_posts';

	/**
	 * @var string
	 */
	protected $extension_author_uri = \Filetrip_Constants::ITF_WEBSITE_LINK;

	/**
	 * @var string
	 */
	protected $extension_version = '1.3';

	/**
	 * @var string
	 */
	protected $extension_description = 'We care about our customers :). Please read our documentation and utilize our support tools to get the perfect operational setup you need.';

	/**
	 * @var string
	 *      fill with full URL to Extension icon
	 *      please use Square recommendation is :
	 *      128px square max 256px
	 *      Extension must be jpg or jpeg
	 */
	protected $extension_icon; // fill with icon url

	/**
	 * @var array
	 */
	private $extension_tabs = array();

	/**
	 * @var Patcher
	 */
	protected $patcher;

	/**
	 * @return array
	 */
	public function getExtensionTabs()
	{
		return $this->extension_tabs;
	}

	/**
	 * Initials
	 */
	public function init()
	{
		if (current_user_can($this->capability)) {
			// include
			LoaderOnce::load( __DIR__ .'/Library/itf-patcher/autoloader.php');
			// Inject dependencies
			add_action( 'admin_enqueue_scripts', array($this, 'loadCss') );
			add_action( 'admin_enqueue_scripts', array($this, 'loadFooterJs') );

			$this->extension_tabs = array(
				'server_info' => array(
					'include' => $this->getViewFolderPath() . 'SupportCenter-system-info.php',
					'slug'    => 'server_info',
					'title'   => __('Server Info', 'filetrip-plugin'),
					'action'  => 'itechflare/extensions/support_pages/server_info'
				),
				'wp_environment' => array(
					'include' => $this->getViewFolderPath(). 'SupportCenter-wp-environment.php',
					'slug'    => 'wp_environment',
					'title'   => __('WordPress Environment', 'filetrip-plugin'),
					'action'  => 'itechflare/extensions/support_pages/wp_environment'
				)
			);

			// Fix Center
			/* Disable for now
			$this->patcher = new Silphium_Patcher_Initiator($this);
			$this->patcher->load();
			*/

			$this->afterInit();
			$this->addAjax();

		} else {
			$this->extension_description = __('Status system is active, but does not allowed to see it.', 'filetrip-plugin');
		}
	}

	/**
	 * Add Bundle tabs
	 *
	 * @param string $name
	 * @param array $containers
	 */
	public function addBundleTab($name, $containers)
	{
		if (!is_string($name) || !is_array($containers) || isset($this->extension_tabs[$name])) {
			return;
		}

		if (!isset($containers['include']) || !is_string($containers['include'])
			|| !is_file($containers['include'])
		) {
			return;
		}
		if (isset($containers['action']) && !is_string($containers['action'])) {
			return;
		}
		$this->extension_tabs[$name] = array(
			'include' => $containers['include'],
			'title'   => (isset($containers['title']) && is_string($containers['title']) ? $containers['title'] : $name),
			'action'  => isset($containers['action']) ? $containers['action'] : 'itf_extension_external_'.trim($name),
			'slug'    => $name,
		);
	}

	/**
	 * Return absolute path for views folder
	 */
	public function getViewFolderPath()
	{
		return dirname ( $this->extensionGetPath() ) . '/Screens/';
	}

	/**
	 * Adding Admin Ajax
	 *
	 * @return void
	 */
	protected function addAjax()
	{
		if (current_user_can($this->capability)) {
			add_action( 'wp_ajax_itf_wp_extension_status', array( $this, 'ajaxResponseTestConnect' ) );
		}
	}

	/**
	 * Ajax Response
	 */
	public function ajaxResponseTestConnect()
	{
		if (!current_user_can($this->capability)) {
			return;
		}

		$default = array(
			'success' => false,
			'error'   => 'Invalid Request',
			'message' => null
		);
		if (!headers_sent()) {
			header('Content-Type: application/json;charset=utf-8', 200);
		}
		if (isset($_POST['detail'])
		    && is_string($_POST['detail'])
		    && in_array($_POST['detail'], array('ssltest', 'wpupdate', 'github', 'wordpress','phpinfo'))
		) {
			$default['error'] = false;
			switch ($_POST['detail']) :
				case 'phpinfo':
					//only super admin can access php info
					if (is_super_admin()) {
						if ( function_exists( 'phpinfo' ) ) {
							//start phpinfo
							ob_start();
							phpinfo();

							$default['success'] = true;
							$default['error']   = null;
							$default['message'] = ob_get_clean();
						} else {
							$default['error'] = sprintf( __( 'function %1$s has been disabled by your server.', 'filetrip-plugin' ), 'phpinfo' );
						}
					}
					break;
				case "ssltest":
					if ( $this->is_support_ssl ) {
						$default['success'] = true;
						$default['error']   = null;
						$default['message'] = __( 'Your WordPress Installation Support SSL', 'filetrip-plugin' );
					} else {
						$default['error'] = __( 'Your WordPress Installation Does Not Support SSL Connection', 'filetrip-plugin' );
					}
					break;
				case "wpupdate":
				case "wordpress":
					$transient = get_transient( 'itf_wp_extension_transient_wp' );
					if ( ! is_array( $transient ) || ! isset( $transient['offers'] ) || ! is_array( $transient['offers'] ) ) {
						$raw_response = wp_remote_get( $this->wp_update_json );
						if ( is_wp_error( $raw_response ) ) {
							$default['error'] = sprintf( __( 'Expected Error. Could Not Connect to %s' ), $this->wp_update_json );
						} else if ( 200 != wp_remote_retrieve_response_code( $raw_response ) ) {
							$default['error'] = sprintf( __( 'Expected Error. There was an Error while connect to %s' ), $this->wp_update_json );
						} else {
							$response = wp_remote_retrieve_body( $raw_response );
							$response = json_decode( $response, true );
							if ( ! is_array( $response ) || ! Isset( $response['offers'] ) ) {
								$default['error'] = __( 'Expected Error. Invalid return data from WordPress API' );
							}
							// set transient
							set_transient( 'itf_wp_extension_transient_wp', $response, HOUR_IN_SECONDS );
							$transient = $response;
							unset( $response );
						}
					}

					if ( is_array( $transient ) && ! empty( $transient['offers'] ) ) {
						global $wp_version;
						$default['success'] = true;
						$offers             = reset( $transient['offers'] );
						$default['message'] = version_compare( $offers['current'], $wp_version, '>' )
							? sprintf( __( 'Your WordPress is out dated. WordPress version %1$s is Available' ), $offers['current'] )
							: __( 'Your WordPress is up to date', 'filetrip-plugin' );
					}

					break;
				case 'github':
					$transient = get_transient( 'itf_wp_extension_transient_git' );
					if ( ! is_array( $transient ) ) {
						$raw_response = wp_remote_get( $this->git_feeds_json );
						if ( is_wp_error( $raw_response ) ) {
							$default['error'] = sprintf( __( 'Expected Error. Could Not Connect to %s' ), 'api.github.com' );
						} else if ( 200 != wp_remote_retrieve_response_code( $raw_response ) ) {
							$default['error'] = sprintf(
								__( 'Expected Error. There was an Error while connect to %s' ),
								'api.github.com'
							);
						}
					}
					if ( is_array( $transient ) ) {
						$default['success'] = true;
						$default['message'] = __( 'Your server does not blocking github api.', 'filetrip-plugin' );
					}
					break;
			endswitch;
		}

		echo json_encode($default);
		wp_die();
	}

	/**
	 * After Init
	 *
	 * @return void
	 */
	protected function afterInit()
	{
		$this->is_support_ssl = wp_http_supports( array( 'ssl' ) );
		if ($this->is_support_ssl) {
			$this->wp_update_json =  set_url_scheme( $this->wp_update_json, 'https' );
			$this->git_feeds_json =  set_url_scheme( $this->git_feeds_json, 'https' );
		}
		$this->addAdminMenu();
	}

	/**
	 * @return void
	 */
	public function loadTemplate()
	{
		if (current_user_can($this->capability)) {
			/** @noinspection PhpIncludeInspection */
			require_once $this->getViewFolderPath() . 'TemplateStatus.php';
		}
	}

	/**
	 * @return void
	 */
	public function loadCss()
	{
		if (current_user_can($this->capability)) {
			wp_register_style( 'itf_support_wp_admin_css', $this->extensionGetDirectoryUrl() . '/assets/css/style.css', false, $this->extension_version );
			wp_enqueue_style( 'itf_support_wp_admin_css' );
			wp_enqueue_style('wp-pointer');
		}
	}

	/**
	 * @return void
	 */
	public function loadFooterJs()
	{
		if (current_user_can($this->capability)) {
			wp_register_script( 'itf_support_admin_js', $this->extensionGetDirectoryUrl() . '/assets/js/support.js' , array('jquery'), $this->extension_version, true );
			wp_register_script( 'itf_super_admin_js', $this->extensionGetDirectoryUrl() . '/assets/js/super-admin.js' , array('jquery'), $this->extension_version, true );

			// Localize the script with new data
			$translation_array = array(
				'show_php' => __('Show PHP Info', 'filetrip-plugin'),
				'hide_php' => __('Hide PHP Info', 'filetrip-plugin'),
				'json_ok'  => __( 'Ok', 'filetrip-plugin' ),
				'check_update_fail'  => __( 'Could not check Update', 'filetrip-plugin' ),
				'error_notification' => __( 'There was error', 'filetrip-plugin' ),
			);

			wp_enqueue_script('wp-pointer');
			// Add translation array to javascript
			// make a change to unique
			wp_localize_script( 'itf_support_admin_js', 'itf_wp_translator', $translation_array );
			// delete duplicate
			// wp_localize_script( 'itf_super_admin_js', 'itf_wp_translator', $translation_array );

			// Enqueu main javascript file
			wp_enqueue_script( 'itf_support_admin_js' );

			// If super admin include special script
			// only super admin can acccess phpinfo
			if(is_super_admin()) {
				wp_enqueue_script( 'itf_super_admin_js' );
			}
		}
	}

	/**
	 * Add Admin Menu
	 */
	protected function addAdminMenu()
	{
		iTechFlareCore::addSubMenuPage(
			__( 'Filetrip Support Center', 'filetrip-plugin' ),
			__( 'Support', 'filetrip-plugin' ),
			'edit_posts',
			'flare_system_status',
			array($this, 'loadTemplate'),
			100
		);
	}
}

