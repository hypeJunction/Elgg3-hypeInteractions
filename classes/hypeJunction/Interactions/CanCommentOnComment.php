<?php

namespace hypeJunction\Interactions;

use Elgg\Event;

class CanCommentOnComment {

	/**
	 * Disallows commenting on comments once a certain depth has been reached
	 *
	 * @elgg_event container_logic_check object
	 *
	 * @param Event $event Event
	 *
	 * @return bool|null
	 */
	public function __invoke(Event $event) {

		$entity = $event->getParam('container');

		if (!$entity instanceof Comment) {
			return null;
		}

		$max_depth = (int) elgg_get_plugin_setting('max_comment_depth', 'hypeInteractions');

		if ($entity->getDepthToOriginalContainer() >= $max_depth) {
			return false;
		}
	}
}