<?php

namespace hypeJunction\Interactions;

use Elgg\Di\ServiceFacade;
use Elgg\PluginHooksService;
use ElggEntity;
use ElggGroup;
use ElggRiverItem;
use ElggUser;

class InteractionsService {

	use ServiceFacade;

	/**
	 * @var PluginHooksService
	 */
	protected $hooks;

	/**
	 * Constructor
	 *
	 * @param PluginHooksService $hooks Hooks
	 */
	public function __construct(PluginHooksService $hooks) {
		$this->hooks = $hooks;
	}

	/**
	 * {@inheritdoc}
	 */
	public function name() {
		return 'interactions';
	}

	/**
	 * Creates an object associated with a river item for commenting and other purposes
	 * This is a workaround for river items that do not have an object or have an object that is group or user
	 *
	 * @param ElggRiverItem $river River item
	 *
	 * @return ElggEntity|false
	 */
	public function createActionableRiverObject(ElggRiverItem $river) {

		if (!$river instanceof ElggRiverItem) {
			return false;
		}

		$object = $river->getObjectEntity();

		$views = $this->getActionableViews();

		if (!in_array($river->view, $views)) {
			return $object;
		}

		$access_id = $object->access_id;
		if ($object instanceof ElggUser) {
			$access_id = $object->getOwnedAccessCollection('friends')->id;
		} else if ($object instanceof ElggGroup) {
			$access_id = $object->group_acl;
		}

		$object = elgg_call(ELGG_IGNORE_ACCESS, function () use ($river, $access_id) {
			$object = new RiverObject();
			$object->owner_guid = $river->subject_guid;
			$object->container_guid = $object->guid;
			$object->access_id = $access_id;
			$object->river_id = $river->id;
			$object->save();

			return $object;
		});

		return $object;
	}

	/**
	 * Check if attachments are enabled
	 * @return bool
	 */
	public function canAttachFiles() {
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
	 * @return ElggEntity|false
	 */
	public function getRiverObject(ElggRiverItem $river, $allow_default_object = true) {

		if (!$river instanceof ElggRiverItem) {
			return false;
		}

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

			return $objects ? $objects[0] : false;
		});

		if (!$object) {
			$object = $this->createActionableRiverObject($river);
		}

		if ($object instanceof ElggEntity) {
			$object->setVolatileData('river_item', $river);
		}

		return has_access_to_entity($object) ? $object : false;
	}

	/**
	 * Get interaction statistics
	 *
	 * @param ElggEntity $entity Entity
	 *
	 * @return array
	 */
	public function getStats(ElggEntity $entity) {

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

		return $this->hooks->trigger('get_stats', 'interactions', ['entity' => $entity], $stats);
	}

	/**
	 * Get configured comments order
	 * @return string
	 */
	public function getCommentsSort() {
		$sort = get_input('sort');
		if ($sort) {
			return $sort;
		}

		$user_setting = elgg_get_plugin_user_setting('comments_order', 0, 'hypeInteractions');
		$setting = $user_setting ? : elgg_get_plugin_setting('comments_order', 'hypeInteractions');

		if ($setting == 'asc') {
			$setting = 'time_created::asc';
		} else if ($setting == 'desc') {
			$setting = 'time_created::desc';
		}

		return $setting;
	}

	/**
	 * Get configured loading style
	 * @return string
	 */
	public function getLoadStyle() {
		$user_setting = elgg_get_plugin_user_setting('comments_load_style', 0, 'hypeInteractions');

		return $user_setting ? : elgg_get_plugin_setting('comments_load_style', 'hypeInteractions');
	}

	/**
	 * Get comment form position
	 * @return string
	 */
	public function getCommentsFormPosition() {
		$user_setting = elgg_get_plugin_user_setting('comment_form_position', 0, 'hypeInteractions');

		return $user_setting ? : elgg_get_plugin_setting('comment_form_position', 'hypeInteractions');
	}

	/**
	 * Get number of comments to show
	 *
	 * @param string $partial Partial or full view
	 *
	 * @return string
	 */
	public function getLimit($partial = true) {
		$limit = get_input('limit');
		if (isset($limit)) {
			return $limit;
		}

		if ($partial) {
			$limit = elgg_get_plugin_setting('comments_limit', 'hypeInteractions');

			return $limit ? : 3;
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
	public function calculateOffset($count, $limit, $comment = null) {

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
	public function getActionableViews() {
		static $views;
		if (isset($views)) {
			return $views;
		}

		$views = [];

		$plugin = elgg_get_plugin_from_id('hypeInteractions');
		$settings = $plugin->getAllSettings();
		foreach ($settings as $key => $value) {
			if (!$value) {
				continue;
			}
			list ($prefix, $view) = explode(':', $key);
			if ($prefix !== 'stream_object') {
				continue;
			}
			$views[] = $view;
		}

		return $views;
	}

}
