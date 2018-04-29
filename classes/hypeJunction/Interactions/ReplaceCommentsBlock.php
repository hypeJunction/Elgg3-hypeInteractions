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

		$entity = $hook->getEntityParam();
		if (!$entity instanceof \ElggEntity) {
			return null;
		}

		$view = elgg_view('page/components/interactions', $params);
		$view = elgg_format_element('div', [
			'id' => 'comments',
		], $view);

		return $view;
	}
}