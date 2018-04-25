<?php

namespace hypeJunction\Interactions;

use Elgg\Hook;

class CanCommentOnComment {

	/**
	 * Disallows commenting on comments once a certain depth has been reached
	 *
	 * @elgg_plugin_hook permissions_check:comment object
	 *
	 * @param Hook $hook Hook
	 *
	 * @return bool|null
	 */
	public function __invoke(Hook $hook) {

		$entity = $hook->getEntityParam();

		if (!$entity instanceof Comment) {
			return null;
		}

		$max_depth = (int) elgg_get_plugin_setting('max_comment_depth', 'hypeInteractions');

		if ($entity->getDepthToOriginalContainer() >= $max_depth) {
			return false;
		}
	}
}