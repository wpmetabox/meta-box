<?php
/**
 * This class handles plugin settings, including adding settings page, show fields, save settings
 *
 * @package Meta Box
 */

/**
 * Meta Box Update Settings class
 *
 * @package Meta Box
 */
class RWMB_Update_Settings {
	/**
	 * Update option.
	 *
	 * @var string
	 */
	private $option = 'meta_box_updater';

	/**
	 * Settings page ID.
	 *
	 * @var string
	 */
	private $page_id = 'meta-box-updater';

	/**
	 * Settings page hook.
	 *
	 * @var string
	 */
	private $page_hook;

	/**
	 * The update checker object
	 *
	 * @var object
	 */
	private $checker;

	/**
	 * Constructor.
	 *
	 * @param object $checker Update checker object.
	 */
	public function __construct( $checker ) {
		$this->checker = $checker;
	}

	/**
	 * Add hooks to create the settings page and show admin notice.
	 */
	public function init() {
		// Whether to enable Meta Box menu. Priority 1 makes sure it runs before adding Meta Box menu.
		add_action( 'admin_menu', array( $this, 'enable_menu' ), 1 );

		// Add submenu. Priority 90 makes it the last sub-menu item.
		$admin_menu_hook = is_multisite() ? 'network_admin_menu' : 'admin_menu';
		add_action( $admin_menu_hook, array( $this, 'add_settings_page' ), 90 );
	}

	/**
	 * Enable Meta Box menu when a premium extension is installed.
	 */
	public function enable_menu() {
		if ( $this->checker->has_extensions() ) {
			add_filter( 'rwmb_admin_menu', '__return_true' );
		}
	}

	/**
	 * Add settings page.
	 */
	public function add_settings_page() {
		$parent          = is_multisite() ? 'settings.php' : 'meta-box';
		$capability      = is_multisite() ? 'manage_network_options' : 'manage_options';
		$title           = is_multisite() ? esc_html__( 'Meta Box Updater', 'meta-box-updater' ) : esc_html__( 'License', 'meta-box-updater' );
		$this->page_hook = add_submenu_page(
			$parent,
			$title,
			$title,
			$capability,
			$this->page_id,
			array( $this, 'render' )
		);
		add_action( "load-{$this->page_hook}", array( $this, 'save' ) );
	}

	/**
	 * Render the content of settings page.
	 */
	public function render() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Meta Box License' ); ?></h1>
			<p><?php esc_html_e( 'Please enter your license key to receive automatic updates for Meta Box extensions.', 'meta-box-updater' ); ?></p>
			<p>
				<?php
				printf(
					// Translators: %s - URL to MetaBox.io website.
					wp_kses_post( __( 'To get the license key, please visit your profile page at <a href="%s" target="_blank">metabox.io website</a>.', 'meta-box-updater' ) ),
					'https://metabox.io/my-account/'
				);
				?>
			</p>

			<form action="" method="post">
				<?php wp_nonce_field( 'meta-box-updater' ); ?>

				<?php
				$option = is_multisite() ? get_site_option( $this->option ) : get_option( $this->option );
				$key    = isset( $option['api_key'] ) ? $option['api_key'] : '';
				?>

				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'License Key', 'meta-box-updater' ); ?></th>
						<td><input required class="regular-text" name="<?php echo esc_attr( $this->option ); ?>[api_key]" value="<?php echo esc_attr( $key ); ?>" type="password"></td>
					</tr>
				</table>

				<?php submit_button( __( 'Save Changes', 'meta-box-updater' ) ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Save update settings.
	 */
	public function save() {
		static $message_shown = false;

		if ( empty( $_POST['submit'] ) ) {
			return;
		}
		check_admin_referer( 'meta-box-updater' );

		// @codingStandardsIgnoreLine
		$option           = isset( $_POST[ $this->option ] ) ? $_POST[ $this->option ] : array();
		$option           = (array) $option;
		$option['status'] = 'success';

		$args           = $option;
		$args['action'] = 'check_license';
		$message        = $this->checker->request( $args );

		if ( $message ) {
			add_settings_error( '', 'invalid', $message );
			$option['status'] = 'error';
		} else {
			add_settings_error( '', 'success', __( 'Settings saved.', 'meta-box-updater' ), 'updated' );
		}

		// Non-multisite auto shows update message. See wp-admin/options-head.php.
		if ( is_multisite() ) {
			add_action( 'network_admin_notices', array( $this, 'show_update_message' ) );
		}

		if ( is_multisite() ) {
			update_site_option( $this->option, $option );
		} else {
			update_option( $this->option, $option );
		}
	}

	/**
	 * Show update message.
	 */
	public function show_update_message() {
		settings_errors();
	}
}
