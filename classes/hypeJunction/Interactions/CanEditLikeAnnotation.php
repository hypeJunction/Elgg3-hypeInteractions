<?php
/**
 *
 */

namespace hypeJunction\Interactions;


use Elgg\Event;

class CanEditLikeAnnotation {

	public function __invoke(\Elgg\Event $event) {

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