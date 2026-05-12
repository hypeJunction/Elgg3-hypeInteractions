<?php

namespace hypeJunction\Interactions;

use Elgg\Event;

class ReplaceCommentsBlock {

	public function __invoke(\Elgg\Event $event) {
		$params = $event->getParams();

		$entity = $event->getParam('entity');
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