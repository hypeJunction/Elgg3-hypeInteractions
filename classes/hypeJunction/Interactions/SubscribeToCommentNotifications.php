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

		if (check_entity_relationship($entity->owner_guid, 'comment_tracker_unsubscribed', $original_container->guid)) {
			// User unsubscribed from notifications about this container
			return;
		}

		add_entity_relationship($entity->owner_guid, 'comment_subscribe', $original_container->guid);
	}
}