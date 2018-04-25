<?php
/**
 *
 */

namespace hypeJunction\Interactions;


use Elgg\Event;
use ElggRiverItem;

class DeleteRiverObject {

	/**
	 * Deletes a commentable object associated with river items whose object is not ElggObject
	 *
	 * @elgg_event delete:after river
	 *
	 * @param Event $event Event
	 *
	 * @return void
	 */
	public function __invoke(Event $event) {
		$river = $event->getObject();
		if (!$river instanceof ElggRiverItem) {
			return;
		}

		elgg_call(ELGG_IGNORE_ACCESS, function () use ($river) {
			$objects = elgg_get_entities([
				'types' => RiverObject::TYPE,
				'subtypes' => [RiverObject::SUBTYPE, 'hjstream'],
				'metadata_name_value_pairs' => [
					'name' => 'river_id',
					'value' => $river->id,
				],
				'limit' => 0,
				'batch' => true,
			]);

			$objects->setIncrementOffset(false);

			foreach ($objects as $object) {
				$object->delete();
			}
		});
	}

}