<?php

namespace hypeJunction\Interactions;

use Elgg\Event;

/**
 * Event handler that replaces the default comments block with the interactions version
 */
class ReplaceCommentsBlock {

	/**
	 * Replace core comments block
	 *
	 * @elgg_event comments all
	 *
	 * @param Event $event Event
	 *
	 * @return string
	 */
	public function __invoke(Event $event) {
		$params = $event->getParams();

		$entity = $event->getEntityParam();
		if (!$entity instanceof \ElggEntity) {
			return null;
		}

		$view = \elgg_view('page/components/interactions', $params);
		$view = \elgg_format_element('div', [
			'id' => 'comments',
		], $view);

		return $view;
	}
}
