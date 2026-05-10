<?php

namespace hypeJunction\Interactions;

use Elgg\Database\Seeds\Seed;

/**
 * Seeds fake comment entities for development and testing.
 *
 * Targets the canonical 'comment' subtype owned by this plugin.
 * Comments are posted by random users onto other random users' profiles.
 */
class Seeder extends Seed {

	/**
	 * {@inheritdoc}
	 */
	public function seed() {
		$this->advance($this->getCount());

		while ($this->seedsCount() < $this->getCount()) {
			$owner = $this->getRandomUser();
			$container_owner = $this->getRandomUser([$owner->guid]);

			if (!$owner || !$container_owner) {
				break;
			}

			$comment = new Comment();
			$comment->owner_guid = $owner->guid;
			$comment->container_guid = $container_owner->guid;
			$comment->access_id = ACCESS_PUBLIC;
			$comment->description = $this->faker->paragraph();

			if (!$comment->save()) {
				continue;
			}

			$this->advance();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function unseed() {
		$entities = elgg_get_entities([
			'type' => 'object',
			'subtype' => 'comment',
			'metadata_name' => '__faker',
			'limit' => false,
			'batch' => true,
		]);

		foreach ($entities as $entity) {
			$entity->delete();
			$this->advance();
		}
	}

	/**
	 * Registers this seeder with the seeds:database event.
	 *
	 * @param \Elgg\Event $event seeds:database event
	 * @return array
	 */
	public static function addSeed(\Elgg\Event $event) {
		$seeds = $event->getValue();
		$seeds[] = self::class;
		return $seeds;
	}
}
