<?php
namespace RevisedStatus\Controller;

/**
 * Class Options
 *
 * @method static Options getInstance
 *
 * @package RevisedStatus
 */
class Options {
	use \RevisedStatus\Base;

	/**
	 * Instance of Options
	 *
	 * @var \RevisedStatus\View\Options
	 */
	private $ov;

	/**
	 * Configuration for settings fields, to be used in the view methods
	 *
	 * @var array
	 */
	private $inputs;


	/**
	 * Array of hook-enabled posttypes
	 *
	 * @var array
	 */
	private $enabled;

	/**
	 * Array of hook-disabled posttypes
	 *
	 * @var array
	 */
	private $disabled;

	/**
	 * Basic constructor
	 *
	 */
	public function __construct() {
		$this->ov       = \RevisedStatus\View\Options::getInstance();
		$this->inputs   = [ ];
		$this->enabled  = [ ];
		$this->disabled = [ ];
	}

	/**
	 * Basic setup
	 */
	public function setup() {

		add_action( 'admin_menu', [ $this, 'register_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}


	/**
	 * Registers the menu if the user didn't disable the options page for the plugin
	 */
	public function register_menu() {
		/**
		 * Allow disabling the options page.
		 *
		 * @since 0.6.0
		 *
		 * @param boolean $var True to disable the options page.
		 */
		if ( apply_filters( WP_REVSTATUS_SLUG . '_disable-options', false ) ) {
			return;
		} else {
			add_options_page(
				__( 'Publish Status Revisions Options', WP_REVSTATUS_SLUG ),
				__( 'Published Status Revisions', WP_REVSTATUS_SLUG ),
				'manage_options',
				WP_REVSTATUS_SETTINGS,
				[ $this->ov, 'render_options_page' ] );
		}
	}


	/**
	 * Registers the settings fields if the user didn't disable the options page.
	 */
	public function register_settings() {

		if ( apply_filters( WP_REVSTATUS_SLUG . '_disable-options', false ) ) {
			return;
		}


		add_settings_section(
			$section_iter = 'revised_status_posttypes',
			__( 'Enable or disable publishing status revisions for your activated post types', WP_REVSTATUS_SLUG ),
			[ $this->ov, 'render_section_posttypes' ],
			WP_REVSTATUS_SETTINGS
		);

		register_setting(
			WP_REVSTATUS_SETTINGS,                        // option group
			WP_REVSTATUS_SETTINGS,                        // option id
			[ $this, 'sanitize_values' ]                 // sanitize callback
		);

		$args = [
			'public' => true,
		];

		foreach ( get_post_types( $args, 'objects' ) as $post_type ) {
			if ( ! post_type_supports( $post_type->name, 'revisions' ) ) {
				continue;
			}
			$this->add_settings_field(
				$field_id = 'revise_' . $post_type->name,
				__( 'Track', WP_REVSTATUS_SLUG ) . " {$post_type->label}",
				[ $this->ov, 'render_checkbox' ],
				'checkbox',
				[ 'id' => $field_id ],
				WP_REVSTATUS_SETTINGS,
				$section_iter
			);
		}

	}

	/**
	 * Very basic sanitizer.
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public function sanitize_values( $args ) {

		foreach ( $args as $key => $value ) {
			if ( isset( $this->inputs[ $key ] ) ) {
				switch ( $this->inputs[ $key ] ) {
					case 'checkbox':
						$args[ $key ] = $this->sanitize_checkbox( $value );
						break;
					default:
						$args[ $key ] = sanitize_text_field( $value );
				}
			} else {
				$args[ $key ] = sanitize_text_field( $value );
			}
		}

		return $args;
	}

	/**
	 * Specific and silly sanitizer for my checkboxes
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public function sanitize_checkbox( $value ) {
		if ( $value ) {
			return '1';
		}

		return '';
	}

	/**
	 * Helper function for add_settings_field, so we save the configuration for each input.
	 *
	 * @param        $setting_name
	 * @param        $setting_label
	 * @param        $render_callback
	 * @param string $type
	 * @param array $callback_args
	 * @param null $page
	 * @param null $section
	 */
	public function add_settings_field(
		$setting_name, $setting_label, $render_callback, $type = 'text', $callback_args = [ ], $page = null,
		$section = null
	) {
		if ( $page == null ) {
			$page = WP_REVSTATUS_SETTINGS;
		}
		if ( $section == null ) {
			$section = WP_REVSTATUS_SETTINGS;
		}

		add_settings_field(
			$setting_name,
			$setting_label,
			$render_callback,
			$page,
			$section,
			$callback_args
		);

		$this->inputs[ $setting_name ] = [ 'type' => $type ];

	}

	/**
	 * Gets the options for the plugin, after filtering them through whatever the user got enabled
	 * or disabled through the filter hooks.
	 *
	 * @return mixed|void
	 */
	public function get_options() {

		$option = get_option( WP_REVSTATUS_SETTINGS );
		$option = empty( $option ) ? [ ] : $option;

		$enabled_inputs  = $this->getEnabled();
		$disabled_inputs = $this->getDisabled();

		if ( ! empty( $enabled_inputs ) ) {
			foreach ( $enabled_inputs as $key => $val ) {
				$enabled_inputs[ 'revise_' . $key ] = 1;
				unset( $enabled_inputs[ $key ] );
			}
			$option = array_merge( $option, $enabled_inputs );
		}

		if ( ! empty( $disabled_inputs ) ) {
			foreach ( array_keys( $option ) as $key ) {
				$cleanKey = str_replace( 'revise_', '', $key );
				if ( $disabled_inputs[ $cleanKey ] ) {
					unset( $option[ $key ] );
				}
			}

		}

		return $option;
	}

	/**
	 * Gets the enabled array through the _tracked-posttypes filter
	 *
	 * @return mixed|void
	 */
	public function getEnabled() {
		return apply_filters( WP_REVSTATUS_SLUG . '_tracked-posttypes', $this->enabled );
	}

	/**
	 * Gets the disabled array through the _untracked-posttypes filter
	 * @return mixed|void
	 */
	public function getDisabled() {
		return apply_filters( WP_REVSTATUS_SLUG . '_untracked-posttypes', $this->disabled );
	}
}