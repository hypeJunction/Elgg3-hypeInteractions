<?php

namespace hypeJunction\Interactions;

/**
 * Handles URL and icon routing for interaction entities
 */
class Router {

	/**
	 * Handles entity URLs
	 *
	 * @param \Elgg\Event $event "entity:url" / "object"
	 * @return string Filtered URL
	 */
	public static function urlHandler(\Elgg\Event $event) {

		$url = $event->getValue();
		$entity = $event->getParam('entity');
		/* @var ElggEntity $entity */

		if ($entity instanceof Comment) {
			$container = $entity->getContainerEntity();
			if ($container instanceof Comment) {
				return $container->getURL();
			}

			return \elgg_normalize_url(implode('/', [
				'stream',
				'comments',
				$entity->container_guid,
				$entity->guid,
			])) . "#elgg-object-$entity->guid";
		} else if ($entity instanceof RiverObject) {
			return \elgg_normalize_url(implode('/', [
				'stream',
				'view',
				$entity->guid
			]));
		}

		return $url;
	}

	/**
	 * Replaces comment icons
	 *
	 * @param \Elgg\Event $event "entity:icon:url" / "object"
	 * @return string
	 */
	public static function iconUrlHandler(\Elgg\Event $event) {

		$url = $event->getValue();
		$params = $event->getParams();
		$entity = $event->getParam('entity');
		/* @var ElggEntity $entity */

		if ($entity instanceof Comment) {
			$owner = $entity->getOwnerEntity();
			if (!$owner) {
				return;
			}

			return $owner->getIconURL($params);
		}

		return $url;
	}
}
