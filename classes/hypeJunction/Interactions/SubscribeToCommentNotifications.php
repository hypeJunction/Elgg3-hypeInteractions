<?php

namespace hypeJunction\Interactions;

use Elgg\Event;

class SubscribeToCommentNotifications {

	/**
	 * Subscribe users to notifications about the thread
	 *
	 * @elgg_event create object
	 *
	 * @param Event $event Event
	 *
	 * @return void
	 */
	public function __invoke(Event $event) {

		$entity = $event->getObject();

		if (!$entity instanceof Comment) {
			return;
		}

		$original_container = $entity->getOriginalContainer();

		if (!$original_container instanceof \ElggObject) {
			// Let core subscriptions deal with it
			return;
		}

		$owner = $entity->getOwnerEntity();
		if (!$owner instanceof \ElggUser) {
			return;
		}

		if ($owner->getRelationship($original_container->guid, 'comment_tracker_unsubscribed')) {
			// User unsubscribed from notifications about this container
			return;
		}

		$owner->addRelationship($original_container->guid, 'comment_subscribe');
	}
}