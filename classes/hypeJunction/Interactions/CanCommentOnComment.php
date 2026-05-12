<?php

namespace hypeJunction\Interactions;

use Elgg\Event;

class CanCommentOnComment {

	public function __invoke(\Elgg\Event $event) {

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