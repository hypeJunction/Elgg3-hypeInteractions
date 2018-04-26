<?php

namespace hypeJunction\Interactions;

use hypeJunction\Lists\Collection;
use hypeJunction\Lists\Filters\All;
use hypeJunction\Lists\Filters\IsOwnedBy;
use hypeJunction\Lists\Filters\IsOwnedByFriendsOf;
use hypeJunction\Lists\SearchFields\CreatedBetween;
use hypeJunction\Lists\Sorters\LikesCount;
use hypeJunction\Lists\Sorters\TimeCreated;

class DefaultCommentCollection extends Collection {

	/**
	 * {@inheritdoc}
	 */
	public function getId() {
		return 'collection:object:comment';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayName() {
		return elgg_echo('collection:object:collection');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getType() {
		return 'object';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSubtypes() {
		return ['comment', 'hjcomment'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCollectionType() {
		return 'default';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getQueryOptions(array $options = []) {
		$entity = $this->getTarget();

		$full_view = elgg_extract('full_view', $this->params);

		$limit = InteractionsService::instance()->getLimit(!$full_view);

		$offset_key = "comments-{$entity->guid}";
		$offset = get_input($offset_key, null);

		$count = $entity->countComments();

		if (!isset($offset)) {
			$comment = elgg_extract('comment', $this->params);

			if ($comment->container_guid != $entity->guid) {
				// Comment serves as a pointer that allows us to access a specific comment within a tree
				// Sometimes the comment is erraneously passed to the view by the router,
				// e.g. when trying to a access a specific 2nd level reply in a thread,
				// we only need to calculate the offset if the comment we are trying to access is
				// a direct child of the object we are viewing
				$offset = InteractionsService::instance()->calculateOffset($count, $limit);
			} else {
				$offset = InteractionsService::instance()->calculateOffset($count, $limit, $comment);
			}
		}

		$options['offset_key'] = $offset_key;
		$options['offset'] = $offset;
		$options['limit'] = $limit;

		return array_merge([
			'container_guids' => (int) $this->target->guid,
			'preload_owners' => true,
			'preload_containers' => true,
			'distinct' => true,
		], $options);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getURL() {
		return elgg_generate_url($this->getId(), [
			'guid' => $this->getTarget()->guid,
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getListOptions(array $options = []) {
		$entity = $this->getTarget();

		return array_merge([
			'full_view' => true,
			'no_results' => elgg_echo('interactions:comments:no_results'),
			'pagination_type' => 'infinite',
			'list_class' => 'interactions-comments-list elgg-comments',
			'list_type' => 'list',
			'list_id' => "interactions-comments-{$entity->guid}",
			'lazy_load' => 0,
			'auto_refresh' => 90,
			'data-guid' => $entity->guid,
			'data-trait' => 'comments',
		], $options);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFilterOptions() {
		if (!elgg_is_logged_in()) {
			return [];
		}

		return [
			All::id() => All::class,
			IsOwnedBy::id() => IsOwnedBy::class,
			IsOwnedByFriendsOf::id() => IsOwnedByFriendsOf::class,
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSortOptions() {
		return [
			TimeCreated::id() => TimeCreated::class,
			LikesCount::id() => LikesCount::class,
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSearchOptions() {
		$fields = parent::getSearchOptions();

		$fields[] = CreatedBetween::class;

		return $fields;
	}
}