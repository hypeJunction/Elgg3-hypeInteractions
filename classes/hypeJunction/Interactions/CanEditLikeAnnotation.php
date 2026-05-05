<?php
/**
 *
 */

namespace hypeJunction\Interactions;


use Elgg\Event;

class CanEditLikeAnnotation {

	/**
	 * Fixes editing permissions on likes
	 *
	 * @elgg_event permissions_check annotation
	 *
	 * @param Event $event Event
	 *
	 * @return bool|null
	 */
	public function __invoke(Event $event) {

		$annotation = $event->getParam('annotation');
		$user = $event->getParam('user');

		if (!$user) {
			return null;
		}

		if ($annotation instanceof \ElggAnnotation && $annotation->name == 'likes') {
			// only owners of original annotation (or users who can edit these owners)
			$ann_owner = $annotation->getOwnerEntity();
			return ($ann_owner && $ann_owner->canEdit($user->guid));
		}
	}
}