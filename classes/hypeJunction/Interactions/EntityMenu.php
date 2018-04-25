<?php

namespace hypeJunction\Interactions;

use Elgg\Hook;

class EntityMenu {

	public function __invoke(Hook $hook) {
		$entity = $hook->getEntityParam();
		$menu = $hook->getValue();
		/* @var $menu \Elgg\Menu\MenuItems */

		if (!$entity instanceof Comment) {
			return null;
		}

		if ($menu->has('edit')) {
			$menu->get('edit')->setHref("stream/edit/$entity->guid");
		}

		if ($entity->canEdit()) {
			$menu[] = ElggMenuItem::factory(array(
				'name' => 'edit',
				'text' => elgg_echo('edit'),
				'href' => "stream/edit/$entity->guid",
				'priority' => 800,
				'item_class' => 'interactions-edit',
				'data' => [
					'icon' => 'pencil',
				]
			));
		}

		if ($entity->canDelete()) {
			$menu[] = ElggMenuItem::factory(array(
				'name' => 'delete',
				'text' => elgg_echo('delete'),
				'href' => "action/comment/delete?guid=$entity->guid",
				'is_action' => true,
				'priority' => 900,
				'confirm' => true,
				'item_class' => 'interactions-delete',
				'data' => [
					'icon' => 'delete',
				]
			));
		}

		return $menu;
	}
}