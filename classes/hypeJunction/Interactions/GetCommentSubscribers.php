<?php

namespace hypeJunction\Interactions;

use Elgg\Database\Select;
use Elgg\Hook;
use Elgg\Notifications\SubscriptionNotificationEvent;

class GetCommentSubscribers {

	/**
	 * Subscribe users to comments based on original entity
	 *
	 * @elgg_plugin_hook get subscriptions
	 *
	 * @param Hook $hook Hook
	 *
	 * @return array|null
	 */
	public function __invoke(Hook $hook) {

		$event = $hook->getParam('event');
		if (!$event instanceof SubscriptionNotificationEvent) {
			return null;
		}

		$object = $event->getObject();
		if (!$object instanceof Comment) {
			return null;
		}

		$return = $hook->getValue();

		$subscriptions = [];
		$actor_subscriptions = [];
		$group_subscriptions = [];

		$original_container = $object->getOriginalContainer();

		if ($original_container instanceof \ElggObject) {
			// Users subscribed to the original post in the thread
			$subscriptions = elgg_get_subscriptions_for_container($original_container->guid);
			$group = $original_container->getContainerEntity();
			if ($group instanceof \ElggGroup) {
				// Users subscribed to group notifications the thread was started in
				$group_subscriptions = elgg_get_subscriptions_for_container($group->guid);
			}
			// @todo: Do we need to notify users subscribed to a thread within user container?
			// 		  It doesn't seem that such notifications would make sense, because they are not performed by the user container
		} else if ($original_container instanceof \ElggGroup) {
			$group_subscriptions = elgg_get_subscriptions_for_container($original_container->guid);
		}

		$actor = $event->getActor();
		if ($actor instanceof \ElggUser) {
			$actor_subscriptions = elgg_get_subscriptions_for_container($actor->guid);
		}

		$all_subscriptions = $return + $subscriptions + $group_subscriptions + $actor_subscriptions;

		// Get user GUIDs that have subscribed to this entity via comment tracker
		$user_guids = elgg_get_entities([
			'type' => 'user',
			'relationship_guid' => $original_container->guid,
			'relationship' => 'comment_subscribe',
			'inverse_relationship' => true,
			'limit' => false,
			'callback' => function ($row) {
				return (int) $row->guid;
			},
		]);

		/* @var int[] $user_guids */

		if ($user_guids) {
			// Get a comma separated list of the subscribed users
			$user_guids_set = implode(',', $user_guids);

			$site_guid = elgg_get_site_entity()->guid;

			// Get relationships that are used to explicitly block specific notification methods

			$qb = Select::fromTable('entity_relationships');
			$qb->select('*')
				->where(
					$qb->merge([
						$qb->compare('relationship', 'like', 'block_comment_notify%', ELGG_VALUE_STRING),
						$qb->compare('guid_one', 'in', $user_guids_set, ELGG_VALUE_INTEGER),
						$qb->compare('guid_two', '=', $site_guid, ELGG_VALUE_INTEGER),
					])
				);

			$blocked_relationships = elgg()->db->getData($qb);

			// Get the methods from the relationship names
			$blocked_methods = [];
			foreach ($blocked_relationships as $row) {
				$method = str_replace('block_comment_notify', '', $row->relationship);
				$blocked_methods[$row->guid_one][] = $method;
			}

			$registered_methods = _elgg_services()->notifications->getMethods();

			foreach ($user_guids as $user_guid) {
				// All available notification methods on the site
				$methods = $registered_methods;

				// Remove the notification methods that user has explicitly blocked
				if (isset($blocked_methods[$user_guid])) {
					$methods = array_diff($methods, $blocked_methods[$user_guid]);
				}

				if ($methods) {
					$all_subscriptions[$user_guid] = $methods;
				}
			}
		}

		// Do not send any notifications, if user has explicitly unsubscribed
		foreach ($all_subscriptions as $guid => $methods) {
			if (check_entity_relationship($guid, 'comment_tracker_unsubscribed', $original_container->guid)) {
				unset($all_subscriptions[$guid]);
			}
		}

		// Do not notify the actor
		unset($all_subscriptions[$actor->guid]);

		return $all_subscriptions;
	}
}