<?php

namespace hypeJunction\Interactions;

use Elgg\Hook;

class RiverMenu {

	/**
	 * Filters river menu
	 *
	 * @elgg_plugin_hook register menu:river
	 *
	 * @param Hook $hook Hook
	 * @return void
	 */
	public function __invoke(Hook $hook) {

		$menu = $hook->getValue();
		/* @var $menu \Elgg\Menu\MenuItems */

		$menu->remove('comment');
	}
}