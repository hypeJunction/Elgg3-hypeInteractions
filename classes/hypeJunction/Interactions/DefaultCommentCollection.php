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
		return array_merge([
			'container_guids' => (int) $this->getTarget()->guid,
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
		return array_merge([
			'full_view' => true,
			'no_results' => elgg_echo('interactions:comments:no_results'),
			'pagination_type' => 'infinite',
			'list_class' => 'interactions-comments-list elgg-comments',
			'list_type' => 'list',
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