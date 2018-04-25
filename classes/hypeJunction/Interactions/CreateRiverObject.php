<?php

namespace hypeJunction\Interactions;

use Elgg\Event;
use ElggRiverItem;

class CreateRiverObject {

	/**
	 * Creates a commentable object associated with river items whose object is not ElggObject
	 *
	 * @elgg_event created river
	 *
	 * @param Event $event Event
	 *
	 * @return void
	 */
	public function __invoke(Event $event) {
		$river = $event->getObject();
		if (!$river instanceof ElggRiverItem) {
			return;
		}

		InteractionsService::instance()->createActionableRiverObject($river);
	}
}