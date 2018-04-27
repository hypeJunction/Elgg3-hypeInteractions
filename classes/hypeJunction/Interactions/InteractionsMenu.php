<?php

namespace hypeJunction\Interactions;

use Elgg\Hook;

class InteractionsMenu {

	/**
	 * Setups entity interactions menu
	 *
	 * @elgg_plugin_hook register menu:interactions
	 *
	 * @param Hook $hook Hook
	 *
	 * @return void
	 */
	public function __invoke(Hook $hook) {

		$entity = $hook->getEntityParam();

		if (!$entity instanceof \ElggEntity) {
			return;
		}

		$menu = $hook->getValue();
		/* @var $menu \Elgg\Menu\MenuItems */

		$active_tab = $hook->getParam('active_tab');

		// Commenting
		$comments_count = $entity->countComments();
		$can_comment = $entity->canComment();

		if ($can_comment) {
			$menu->add(\ElggMenuItem::factory([
				'name' => 'comments',
				'text' => ($entity instanceof Comment) ? elgg_echo('interactions:reply:create') : elgg_echo('interactions:comment:create'),
				'href' => "stream/comments/$entity->guid",
				'priority' => 200,
				'data-trait' => 'comments',
				'item_class' => 'interactions-action',
			]));
		}

		if ($can_comment || $comments_count) {
			$menu->add(\ElggMenuItem::factory([
				'name' => 'comments:badge',
				'text' => elgg_view('framework/interactions/elements/badge', [
					'entity' => $entity,
					'icon' => 'comments',
					'type' => 'comments',
					'count' => $comments_count,
				]),
				'href' => "stream/comments/$entity->guid",
				'selected' => ($active_tab == 'comments'),
				'priority' => 100,
				'data-trait' => 'comments',
				'item_class' => 'interactions-tab',
			]));
		}

		if (elgg_is_active_plugin('likes')) {
			// Liking and unliking
			$likes_count = $entity->countAnnotations('likes');
			$can_like = $entity->canAnnotate(0, 'likes');
			$does_like = elgg_annotation_exists($entity->guid, 'likes');

			if ($can_like) {

				$before_text = elgg_echo('interactions:likes:before');
				$after_text = elgg_echo('interactions:likes:after');

				$menu->add(\ElggMenuItem::factory([
					'name' => 'likes',
					'text' => ($does_like) ? $after_text : $before_text,
					'href' => "action/stream/like?guid=$entity->guid",
					'is_action' => true,
					'priority' => 400,
					'link_class' => 'interactions-state-toggler',
					'item_class' => 'interactions-action',
					// Attrs for JS toggle
					'data-guid' => $entity->guid,
					'data-trait' => 'likes',
					'data-state' => ($does_like) ? 'after' : 'before',
				]));
			}

			if ($can_like || $likes_count) {
				$menu->add(\ElggMenuItem::factory([
					'name' => 'likes:badge',
					'text' => elgg_view('framework/interactions/elements/badge', [
						'entity' => $entity,
						'icon' => 'likes',
						'type' => 'likes',
						'count' => $likes_count,
					]),
					'href' => "stream/likes/$entity->guid",
					'selected' => ($active_tab == 'likes'),
					'data-trait' => 'likes',
					'priority' => 300,
					'item_class' => 'interactions-tab',
				]));
			}
		}
	}
}