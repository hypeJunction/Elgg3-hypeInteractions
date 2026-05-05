<?php

namespace hypeJunction\Interactions;

use Elgg\Event;

/**
 * Event handler that adds interaction items to the river menu
 */
class RiverMenu {

	/**
	 * Filters river menu
	 *
	 * @elgg_event register menu:river
	 *
	 * @param Event $event Event
	 * @return void
	 */
	public function __invoke(Event $event) {

		$menu = $event->getValue();
		/* @var $menu \Elgg\Menu\MenuItems */

		$menu->remove('comment');
	}
}
