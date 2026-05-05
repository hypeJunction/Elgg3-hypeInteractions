<?php

namespace hypeJunction\Interactions;

use ElggEntity;
use ElggGroup;
use ElggRiverItem;
use ElggUser;

class InteractionsService {

	/**
	 * Returns the service from DI container
	 *
	 * @return static
	 */
	public static function instance(): self {
		return elgg()->get(static::name());
	}

	/**
	 * Returns the DI service name
	 *
	 * @return string
	 */
	public static function name(): string {
		return 'interactions';
	}

	/**
	 * Creates an object associated with a river item for commenting and other purposes
	 * This is a workaround for river items that do not have an object or have an object that is group or user
	 *
	 * @param ElggRiverItem $river River item
	 *
	 * @return ElggEntity|null
	 */
	public function createActionableRiverObject(ElggRiverItem $river): ?ElggEntity {

		$object = $river->getObjectEntity();

		$views = $this->getActionableViews();

		if (!in_array($river->view, $views)) {
			return $object;
		}

		$access_id = $object ? $object->access_id : ACCESS_PUBLIC;
		if ($object instanceof ElggUser) {
			$acl = $object->getOwnedAccessCollection('friends');
			$access_id = $acl ? $acl->id : ACCESS_PUBLIC;
		} else if ($object instanceof ElggGroup) {
			$access_id = $object->group_acl;
		}

		return elgg_call(ELGG_IGNORE_ACCESS, function () use ($river, $access_id) {
			$river_object = new RiverObject();
			$river_object->owner_guid = $river->subject_guid;
			$river_object->container_guid = $river_object->guid;
			$river_object->access_id = $access_id;
			$river_object->river_id = $river->id;
			$river_object->save();

			return $river_object;
		});
	}

	/**
	 * Check if attachments are enabled
	 * @return bool
	 */
	public function canAttachFiles(): bool {
		if (!elgg_is_active_plugin('hypeAttachments')) {
			return false;
		}

		return (bool) elgg_get_plugin_setting('enable_attachments', 'hypeInteractions', true);
	}

	/**
	 * Get an actionable object associated with the river item
	 * This could be a river object entity or a special entity that was created for this river item
	 *
	 * @param ElggRiverItem $river                River item
	 * @param bool          $allow_default_object Allow river object
	 *
	 * @return ElggEntity|null
	 */
	public function getRiverObject(ElggRiverItem $river, bool $allow_default_object = true): ?ElggEntity {

		$object = null;
		if ($allow_default_object) {
			$object = $river->getObjectEntity();
		}

		$views = $this->getActionableViews();

		if (!in_array($river->view, $views)) {
			return $object;
		}

		// wrapping this in ignore access so that we do not accidentally create duplicate river objects
		$object = elgg_call(ELGG_IGNORE_ACCESS, function () use ($river) {
			$objects = elgg_get_entities([
				'types' => RiverObject::TYPE,
				'subtypes' => [RiverObject::SUBTYPE, 'hjstream'],
				'metadata_name_value_pairs' => [
					'name' => 'river_id',
					'value' => $river->id,
				],
				'limit' => 1,
			]);

			return $objects ? $objects[0] : null;
		});

		if (!$object) {
			$object = $this->createActionableRiverObject($river);
		}

		if ($object instanceof ElggEntity) {
			$object->setVolatileData('river_item', $river);
		}

		return has_access_to_entity($object) ? $object : null;
	}

	/**
	 * Get interaction statistics
	 *
	 * @param ElggEntity $entity Entity
	 *
	 * @return array
	 */
	public function getStats(ElggEntity $entity): array {

		$stats = [
			'comments' => [
				'count' => elgg_get_total_comments($entity),
			],
			'likes' => [
				'count' => elgg_get_total_likes($entity),
				'state' => $entity->getAnnotations([
					'annotation_names' => 'likes',
					'annotation_owner_guids' => (int) elgg_get_logged_in_user_guid(),
					'count' => true,
				]) ? 'after' : 'before',
			]
		];

		return elgg_trigger_event_results('get_stats', 'interactions', ['entity' => $entity], $stats);
	}

	/**
	 * Get configured comments order
	 * @return string
	 */
	public function getCommentsSort(): string {
		$sort = get_input('sort');
		if ($sort) {
			return $sort;
		}

		$user_setting = elgg_get_plugin_user_setting('comments_order', 0, 'hypeInteractions');
		$setting = $user_setting ?: elgg_get_plugin_setting('comments_order', 'hypeInteractions');

		if ($setting == 'asc') {
			$setting = 'time_created::asc';
		} else if ($setting == 'desc') {
			$setting = 'time_created::desc';
		}

		return (string) $setting;
	}

	/**
	 * Get configured loading style
	 * @return string
	 */
	public function getLoadStyle(): string {
		$user_setting = elgg_get_plugin_user_setting('comments_load_style', 0, 'hypeInteractions');

		return (string) ($user_setting ?: elgg_get_plugin_setting('comments_load_style', 'hypeInteractions'));
	}

	/**
	 * Get comment form position
	 * @return string
	 */
	public function getCommentsFormPosition(): string {
		$user_setting = elgg_get_plugin_user_setting('comment_form_position', 0, 'hypeInteractions');

		return (string) ($user_setting ?: elgg_get_plugin_setting('comment_form_position', 'hypeInteractions'));
	}

	/**
	 * Get number of comments to show
	 *
	 * @param bool $partial Partial or full view
	 *
	 * @return int
	 */
	public function getLimit(bool $partial = true): int {
		$limit = get_input('limit');
		if (isset($limit)) {
			return (int) $limit;
		}

		if ($partial) {
			$limit = elgg_get_plugin_setting('comments_limit', 'hypeInteractions');

			return (int) ($limit ?: 3);
		} else {
			$limit = elgg_get_plugin_setting('comments_load_limit', 'hypeInteractions');

			return min(max((int) $limit, 20), 200);
		}
	}

	/**
	 * Calculate offset till the page that contains the comment
	 *
	 * @param int     $count   Number of comments in the list
	 * @param int     $limit   Number of comments to display
	 * @param Comment $comment Comment entity
	 *
	 * @return int
	 */
	public function calculateOffset(int $count, int $limit, ?Comment $comment = null): int {

		$order = $this->getCommentsSort();
		$style = $this->getLoadStyle();

		if ($comment instanceof Comment) {
			$thread = new Thread($comment);
			$offset = $thread->getOffset($limit, $order);
		} else if (($order == 'time_created::asc' && $style == 'load_older') || ($order == 'time_created::desc' && $style == 'load_newer')) {
			// show last page
			$offset = $count - $limit;
			if ($offset < 0) {
				$offset = 0;
			}
		} else {
			// show first page
			$offset = 0;
		}

		return (int) $offset;
	}

	/**
	 * Get views, which custom threads should be created for
	 * @return array
	 */
	public function getActionableViews(): array {
		static $views;
		if (isset($views)) {
			return $views;
		}

		$views = [];

		$plugin = elgg_get_plugin_from_id('hypeInteractions');
		if (!$plugin) {
			return $views;
		}

		$settings = $plugin->getAllSettings();
		foreach ($settings as $key => $value) {
			if (!$value) {
				continue;
			}
			[$prefix, $view] = explode(':', $key);
			if ($prefix !== 'stream_object') {
				continue;
			}
			$views[] = $view;
		}

		return $views;
	}

}
