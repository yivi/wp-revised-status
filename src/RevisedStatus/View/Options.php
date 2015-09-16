<?php
namespace RevisedStatus\View;

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
			<h2><?php _e( 'Publishing status tracking options',
					WP_REVSTATUS_SLUG ); ?></h2>

			<form action="options.php" method="post">
				<?php
				settings_fields( WP_REVSTATUS_SETTINGS );
				do_settings_sections( WP_REVSTATUS_SETTINGS );
				submit_button( __( 'Save', WP_REVSTATUS_SLUG ) );
				?>
			</form>

		</div>
		<div class="wrap">
			<h3><?php _e( 'Filter Hooks available', WP_REVSTATUS_SLUG ); ?></h3>

			<p><?php _e( 'Options set via hooks supersede options chosen in this page', WP_REVSTATUS_SLUG ); ?></p>

			<dl>
				<dt><code>wp-revised-status_track-all</code></dt>
				<dd><?php _e( 'Sets up tracking for all available versioned post-types. Return <em>true</em> to activate.',
						WP_REVSTATUS_SLUG ); ?></dd>
				<dt><code>wp-revised-status_tracked-posttypes</code></dt>
				<dd>
					<p><?php _e( 'Return an associative array in this filter to activate tracking to your chosen post types. e.g:',
							WP_REVSTATUS_SLUG ); ?></p>

					<p>E.g.: <code>
							add_filter( 'wp-revised-status_tracked-posttypes', function ( $i ) {
							$i['post'] = 1;

							return $i;
							} );
						</code>
					</p>

					<p><?php _e( 'Redundant with the previous filter', WP_REVSTATUS_SLUG ); ?></p></dd>
				<dt><code>wp-revised-status_untracked-posttypes</code></dt>
				<dd>
					<p><?php _e( 'Return an associative array in this filter to disable tracking for your chosen post types. e.g:',
							WP_REVSTATUS_SLUG ); ?></p>

					<p>E.g.: </p>
						<pre><code>add_filter( 'wp-revised-status_tracked-posttypes', 'my_theme_disable_tracking' );
function my_theme_disable_tracking($i) {
	$i['post'] = 1;
	return $i;
}</code></pre>
					<p><?php _e( 'Trumps all other options or filters', WP_REVSTATUS_SLUG ); ?></p>
				</dd>
			</dl>


		</div>
		<?php
	}


	/**
	 * Renders the main settings section for the plugin, including a warning in case there are
	 * hooked options
	 *
	 * @param $section
	 */
	public function render_section_posttypes( $section ) {
		$control = \RevisedStatus\Controller\Options::getInstance();

		$enabled  = $control->getEnabled();
		$disabled = $control->getDisabled();

		// If there are posttypes enabled by use of the appropriate hook display a notice.
		if ( ! empty( $enabled ) ) {
			$types = [ ];
			echo "<div id='message' class='updated'>"
			     . __( 'Attention: Tracking for the following post-types has been enabled from a theme or a plugin:',
					WP_REVSTATUS_SLUG );
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

			echo "<div id='message' class='error'>"
			     . __( 'Warning: Tracking for the following post-types has been disabled from a theme or a plugin:',
					WP_REVSTATUS_SLUG );
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

		$control  = \RevisedStatus\Controller\Options::getInstance();
		$settings = get_option( WP_REVSTATUS_SETTINGS );

		$hook_enabled  = $control->getEnabled( $id );
		$hook_disabled = $control->getDisabled( $id );

		$is_hooked   = $hook_disabled || $hook_enabled;
		$all_options = $control->getTrackAll() && 'track_all_posttypes' === $id;

		$checked  = checked( isset( $settings[ $id ] ), true, false );
		$disabled = $is_hooked || $all_options ? 'disabled="disabled"' : '';

		$memo = '';
		if ( $hook_enabled || $all_options ) {
			$memo = __( 'Warning, this setting has been activated by a plugin hook.', WP_REVSTATUS_SLUG );
		}
		if ( $hook_disabled ) {
			$memo = __( "Warning, tracking of this post type has been disabled by a plugin hook.", WP_REVSTATUS_SLUG );
		}

		echo "<input type='checkbox' id='" . WP_REVSTATUS_SETTINGS . "' name='"
		     . WP_REVSTATUS_SETTINGS
		     . "[$id]' size='40' value='1' $checked $disabled /> $memo";

		if ( $checked && $disabled || $all_options ) {
			echo "<input type='hidden' value='1' name='" . WP_REVSTATUS_SETTINGS . "[$id]' >\n";
		}

	}


}