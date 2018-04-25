<?php
/**
 *
 */

namespace hypeJunction\Interactions;


use Elgg\Hook;

class CanEditLikeAnnotation {

	/**
	 * Fixes editing permissions on likes
	 *
	 * @elgg_plugin_hook permissions_check annotation
	 *
	 * @param Hook $hook Hook
	 *
	 * @return bool
	 */
	public function __invoke(Hook $hook) {

		$annotation = $hook->getParam('annotation');
		$user = $hook->getParam('user');

		if ($annotation instanceof \ElggAnnotation && $annotation->name == 'likes') {
			// only owners of original annotation (or users who can edit these owners)
			$ann_owner = $annotation->getOwnerEntity();
			return ($ann_owner && $ann_owner->canEdit($user->guid));
		}
	}
}