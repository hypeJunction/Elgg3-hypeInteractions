<?php

namespace hypeJunction\Interactions;

use Elgg\Event;

class RiverMenu {

	public function __invoke(\Elgg\Event $event) {

		$menu = $event->getValue();
		/* @var $menu \Elgg\Menu\MenuItems */

		$menu->remove('comment');
	}
}