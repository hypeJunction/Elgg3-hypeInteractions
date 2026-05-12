<?php

namespace hypeJunction\Interactions;

class Router {

	public static function urlHandler(\Elgg\Event $event) {

		$entity = $event->getParam('entity');
		$url = $event->getValue();

		if ($entity instanceof Comment) {
			$container = $entity->getContainerEntity();
			if ($container instanceof Comment) {
				return $container->getURL();
			}
			return elgg_normalize_url(implode('/', [
				'stream',
				'comments',
				$entity->container_guid,
				$entity->guid,
			])) . "#elgg-object-$entity->guid";
		} else if ($entity instanceof RiverObject) {
			return elgg_normalize_url(implode('/', [
				'stream',
				'view',
				$entity->guid,
			]));
		}

		return $url;
	}

	public static function iconUrlHandler(\Elgg\Event $event) {

		$entity = $event->getParam('entity');
		$url = $event->getValue();

		if ($entity instanceof Comment) {
			$owner = $entity->getOwnerEntity();
			if (!$owner) {
				return;
			}
			return $owner->getIconURL($event->getParams());
		}

		return $url;
	}

}
