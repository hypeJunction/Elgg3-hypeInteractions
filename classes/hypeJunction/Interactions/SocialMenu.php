<?php

namespace hypeJunction\Interactions;

use Elgg\Event;
use ElggMenuItem;

/**
 * Event handler that builds the social actions menu for river items
 */
class SocialMenu {

	/**
	 * Filters river menu
	 *
	 * @elgg_event register menu:social
	 *
	 * @param Event $event Event
	 *
	 * @return void
	 */
	public function __invoke(Event $event) {

		$item = $event->getParam('item');
		if ($item instanceof \ElggRiverItem) {
			return;
		}

		$entity = $event->getEntityParam();

		if (!$entity || $entity instanceof Comment) {
			return;
		}

		$menu = $event->getValue();
		/* @var $menu \Elgg\Menu\MenuItems */

		$url = $entity->getURL();

		$parts = parse_url($url);
		$parts['fragment'] = 'comments';

		$interactions_url = \elgg_http_build_url($parts, false);

		$uses_comments = \elgg_trigger_event_results(
			'uses:comments',
			"$entity->type:$entity->subtype",
			$event->getParams(),
			$entity instanceof \ElggObject && !$entity->disable_comments
		);

		$comments_count = $entity->countComments();

		if ($uses_comments && $comments_count) {
			$menu->add(ElggMenuItem::factory([
				'name' => 'comments',
				'href' => \elgg_http_add_url_query_elements($interactions_url, [
					'active_tab' => 'comments',
				]),
				'text' => false,
				'icon' => 'comments-o',
				'badge' => $comments_count,
			]));
		}

		$uses_likes = \elgg_is_active_plugin('likes') && \elgg_trigger_event_results(
				'likes:is_likable',
				"$entity->type:$entity->subtype",
				$event->getParams(),
				false
			);

		if ($uses_likes) {
			$menu->add(ElggMenuItem::factory([
				'name' => 'likes',
				'href' => \elgg_http_add_url_query_elements($interactions_url, [
					'active_tab' => 'likes',
				]),
				'text' => false,
				'icon' => 'thumbs-o-up',
				'badge' => (function_exists('elgg_get_total_likes') ? \elgg_get_total_likes($entity) : 0),
			]));
		}
	}
}
