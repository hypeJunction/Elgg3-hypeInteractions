<?php

namespace hypeJunction\Interactions;

class Router {

	/**
	 * Handles entity URLs
	 *
	 * @param string $hook   "entity:url"
	 * @param string $type   "object"
	 * @param string $url    Current URL
	 * @param array  $params Hook params
	 * @return string Filtered URL
	 */
	public static function urlHandler($hook, $type = null, $url = null, $params = null) {

		if ($hook instanceof \Elgg\Hook) {
			$type = $hook->getType();
			$url = $hook->getValue();
			$params = $hook->getParams();
		}

		$entity = \elgg_extract('entity', $params);
		/* @var ElggEntity $entity */

		if ($entity instanceof Comment) {
			$container = $entity->getContainerEntity();
			if ($container instanceof Comment) {
				return $container->getURL();
			}
			return \elgg_normalize_url(implode('/', array(
						'stream',
						'comments',
						$entity->container_guid,
						$entity->guid,
					))) . "#elgg-object-$entity->guid";
		} else if ($entity instanceof RiverObject) {
			return \elgg_normalize_url(implode('/', array(
				'stream',
				'view',
				$entity->guid
			)));
		}

		return $url;
	}

	/**
	 * Replaces comment icons
	 *
	 * @param string $hook   "entity:icon:url"
	 * @param string $type   "object"
	 * @param string $url    Current URL
	 * @param array  $params Hook params
	 * @return string
	 */
	public static function iconUrlHandler($hook, $type = null, $url = null, $params = null) {

		if ($hook instanceof \Elgg\Hook) {
			$type = $hook->getType();
			$url = $hook->getValue();
			$params = $hook->getParams();
		}

		$entity = \elgg_extract('entity', $params);
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
