<?php

namespace hypeJunction\Interactions;

use Elgg\Hook;

class ReplaceCommentsBlock {

	/**
	 * Replace core comments block
	 *
	 * @elgg_plugin_hook comments all
	 *
	 * @param Hook $hook Hook
	 *
	 * @return string
	 */
	public function __invoke(Hook $hook) {
		$params = $hook->getParams();

		return elgg_view('page/components/interactions', $params);
	}
}