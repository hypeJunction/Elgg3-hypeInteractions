<?php

namespace hypeJunction\Interactions;

use Elgg\Hook;
use ElggMenuItem;

class SocialMenu {

	/**
	 * Filters river menu
	 *
	 * @elgg_plugin_hook register menu:social
	 *
	 * @param Hook $hook Hook
	 * @return void
	 */
	public function __invoke(Hook $hook) {

		$entity = $hook->getEntityParam();

		if (!$entity || $entity instanceof Comment) {
			return;
		}

		$menu = $hook->getValue();
		/* @var $menu \Elgg\Menu\MenuItems */

		$url = $entity->getURL();

		$parts = parse_url($url);
		$parts['fragment'] = "comments";

		$interactions_url = elgg_http_build_url($parts, false);

		$uses_comments = elgg_trigger_plugin_hook(
			'uses:comments',
			"$entity->type:$entity->subtype",
			$hook->getParams(),
			!$entity->disable_comments
		);

		if ($uses_comments) {

			$menu->add(ElggMenuItem::factory([
				'name' => 'comments',
				'href' => elgg_http_add_url_query_elements($interactions_url, [
					'active_tab' => 'comments',
				]),
				'text' => false,
				'icon' => 'comments-o',
				'badge' => $entity->countComments(),
			]));
		}

		$uses_likes = elgg_is_active_plugin('likes') && elgg_trigger_plugin_hook(
			'likes:is_likable',
			"$entity->type:$entity->subtype",
			$hook->getParams(),
			false
		);

		if ($uses_likes) {
			$menu->add(ElggMenuItem::factory([
				'name' => 'likes',
				'href' => elgg_http_add_url_query_elements($interactions_url, [
					'active_tab' => 'likes',
				]),
				'text' => false,
				'icon' => 'thumbs-o-up',
				'badge' => $entity->countAnnotations('likes'),
			]));
		}
	}
}