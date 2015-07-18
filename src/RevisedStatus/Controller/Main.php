<?php

namespace RevisedStatus\Controller;

/**
 * Class Main
 *
 * @method static Main getInstance
 *
 * @todo Remove this class and move all this to Revision Manager.
 * @package RevisedStatus
 */
class Main {
	use \RevisedStatus\Base;

	const slug = 'wp-revised-status';
	const settings_slug = 'revised_status_options';

	/**
	 * @var Main
	 */
	static $instance;


	/**
	 * Instantiates RevisionManager and hooks the proper methods.
	 *
	 */
	public function setup() {

		if ( is_admin() ) {

			$rm = \RevisedStatus\Controller\RevisionManager::getInstance();

			add_filter( 'wp_save_post_revision_post_has_changed', [
				$rm,
				'compare_statuses'
			], 3, 20 );
			add_action( '_wp_put_post_revision', [ $rm, 'maybe_save_status_in_meta' ] );
			add_action( 'wp_restore_post_revision', [ $rm, 'restore_revision' ], 2, 20 );
		}


	}

}