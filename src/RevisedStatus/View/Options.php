<?php
namespace RevisedStatus\View;

use RevisedStatus\Controller\Main;

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
	 * Empty constructor. Nothing going on.
	 */
	public function __construct() {

	}

	/**
	 * Renders the option page for the plugin
	 */
	public function render_options_page() {
		?>
		<div class="wrap">
		<h2><?php _e( 'Publishing status tracking options', Main::slug ); ?></h2>

		<form action="options.php" method="post">
			<?php
			settings_fields( Main::settings_slug );
			do_settings_sections( Main::settings_slug );
			submit_button( __( 'Save', Main::slug ) );
			?>
		</form>
		<?php
	}


	/**
	 * Renders the main settings section for the plugin, including a warning in case there are
	 * hooked options
	 *
	 * @param $section
	 */
	public function render_section_posttypes( $section ) {
		$opt_controller = \RevisedStatus\Controller\Options::getInstance();

		$enabled  = $opt_controller->getEnabled();
		$disabled = $opt_controller->getDisabled();

		// If there are posttypes enabled by use of the appropriate hook display a notice.
		if ( ! empty( $enabled ) ) {
			$types = [ ];
			echo "<div id='message' class='updated'>" . __( 'Attention: Tracking for the following post-types has been enabled from a theme or a plugin:', Main::slug );
			foreach ( $enabled as $key => $val ) {

				if ( ( $post_type = get_post_type_object( $key ) ) !== null ) {
					$types[] = $post_type->labels->singular_name;
				}

			}
			echo ' ' . implode( ', ', $types );
			echo "</div>";
		}

		// If there are posttypes enabled by use of the appropriate hook display a warning.
		if ( ! empty( $disabled ) ) {

			echo "<div id='message' class='error'>" . __( 'Warning: Tracking for the following post-types has been disabled from a theme or a plugin:', Main::slug );
			$types = [ ];
			foreach ( $disabled as $key => $val ) {

				if ( ( $post_type = get_post_type_object( $key ) ) !== null ) {
					$types[] = $post_type->labels->singular_name;
				}
			}
			echo ' ' . implode( ', ', $types );
			echo "</div>";
		}
	}


	/**
	 * Generic checkbox for the posttypes enablers.
	 *
	 * @param $args
	 */
	public function render_checkbox( $args ) {
		if ( isset( $args['id'] ) ) {
			$id = $args['id'];
		} else {
			return;
		}

		$options = get_option( Main::settings_slug );

		$checked = checked( isset( $options[ $id ] ), true, false );

		echo "<input type='checkbox' id='" . Main::settings_slug . "' name='" . Main::settings_slug
		     . "[$id]' size='40' value='1' $checked />";
	}


}