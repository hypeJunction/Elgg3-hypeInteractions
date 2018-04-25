<?php

namespace hypeJunction\Interactions;

use Elgg\Database\QueryBuilder;
use Elgg\Event;

class SyncRiverObjectAccess {


	/**
	 * Update river object access to match that of the container
	 *
	 * @elgg_event update:after all
	 *
	 * @param Event $event Event
	 *
	 * @return void
	 */
	public function __invoke(Event $event) {
		$entity = $event->getObject();

		if (!$entity instanceof \ElggObject) {
			// keep user and group entries as is
			return;
		}

		// need to override access in case comments ended up with ACCESS_PRIVATE
		// and to ensure write permissions
		elgg_call(ELGG_IGNORE_ACCESS, function () use ($entity) {
			$batch = elgg_get_entities([
				'type' => 'object',
				'subtype' => RiverObject::class,
				'container_guid' => $entity->guid,
				'wheres' => function (QueryBuilder $qb) use ($entity) {
					return $qb->compare('e.access_id', '!=', $entity->access_id, ELGG_VALUE_INTEGER);
				},
				'limit' => 0,
				'batch' => true,
				'batch_inc_offset' => false,
			]);

			foreach ($batch as $river_object) {
				// Update comment access_id
				$river_object->access_id = $entity->access_id;
				$river_object->save();
			}
		});
	}
}