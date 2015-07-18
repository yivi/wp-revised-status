<?php

namespace RevisedStatus\Controller;

/**
 * Class RevisionManager
 *
 * @method static RevisionManager getInstance
 *
 * @package RevisedStatus
 */
class RevisionManager {
	use \RevisedStatus\Base;

	/**
	 * @var $self
	 */
	static $instance;

	/**
	 * Updates the status for post revision
	 *
	 * @param $revision_id
	 * @param $new_status
	 *
	 * @return bool|int
	 */
	public function update_status( $revision_id, $new_status ) {

		return update_metadata( 'post', $revision_id, REVISED_STATUS_METAKEY, $new_status, '' );

	}

	/**
	 * Gets the status por the post revision
	 *
	 * @param $revision_id
	 *
	 * @return mixed
	 */
	public function get_status( $revision_id ) {

		return get_metadata( 'post', $revision_id, REVISED_STATUS_METAKEY, true );
	}


	/**
	 * Compares two post statuses, and returns true for changed and false for not changed.
	 *
	 * @param $post_has_changed
	 * @param $last_revision
	 * @param $post
	 *
	 * @return bool
	 */
	public function compare_statuses( $post_has_changed, $last_revision, $post ) {
		$options = \RevisedStatus\Controller\Options::getInstance()->get_options();

		if ( isset( $options[ 'revise_' . $post->post_type ] ) ) {

			$last_status    = $this->get_status( $last_revision->ID );
			$current_status = $post->post_status;

			if ( $last_status !== $current_status ) {
				$post_has_changed = true;
			}
		}

		return $post_has_changed;
	}

	/**
	 * Saves the current posts status with the revision being saved, but only if the current post is
	 * belongs to one of the enabled posttypes.
	 *
	 * @param $revision_id
	 */
	public function maybe_save_status_in_meta( $revision_id ) {
		$post           = get_post();
		$current_status = $post->post_status;

		$options = \RevisedStatus\Controller\Options::getInstance()->get_options();


		if ( isset( $options[ 'revise_' . $post->post_type ] ) ) {

			$this->update_status( $revision_id, $current_status );
		}
	}


	/**
	 * Restores the revision publishing status when restoring the rest of the revision.
	 *
	 * @param $post_id
	 * @param $revision_id
	 */
	public function restore_revision( $post_id, $revision_id ) {

		$options = \RevisedStatus\Controller\Options::getInstance()->get_options();

		$post = get_post();

		if ( isset( $options[ 'revise_' . $post->post_type ] ) ) {

			$status = $this->get_status( $revision_id );
			if ( ! empty( $status ) ) {
				$post              = get_post( $post_id );
				$post->post_status = $status;
				wp_update_post( $post );

			}
		}

	}
}