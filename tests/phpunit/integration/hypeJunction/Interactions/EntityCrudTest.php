<?php

namespace hypeJunction\Interactions;

use Elgg\IntegrationTestCase;

/**
 * Lock in Comment + RiverObject entity CRUD on Elgg 4.x. Comment extends
 * ElggComment (built-in Elgg comment class), which changes some of the
 * CRUD semantics compared to vanilla ElggObject subclasses — notably
 * Comment requires a container entity that permits commenting.
 */
class EntityCrudTest extends IntegrationTestCase {

	public function up() {}
	public function down() {}

	public function getPluginID(): string {
		return 'hypeinteractions';
	}

	// --- RiverObject CRUD ---

	private function makeRiverObject(array $overrides = []): RiverObject {
		return elgg_call(ELGG_IGNORE_ACCESS, function () use ($overrides) {
			$user = $overrides['__user'] ?? $this->createUser();
			$o = new RiverObject();
			$o->owner_guid = $overrides['owner_guid'] ?? $user->guid;
			$o->container_guid = $overrides['container_guid'] ?? $user->guid;
			$o->access_id = $overrides['access_id'] ?? ACCESS_PUBLIC;
			if (isset($overrides['title'])) {
				$o->title = $overrides['title'];
			}
			if (isset($overrides['river_id'])) {
				$o->river_id = $overrides['river_id'];
			}
			$o->save();
			return $o;
		});
	}

	public function testRiverObjectInitializesAsRiverObjectSubtype(): void {
		$o = new RiverObject();
		$this->assertSame('river_object', $o->getSubtype());
	}

	public function testCreatedRiverObjectHasGuid(): void {
		$o = $this->makeRiverObject();
		$this->assertGreaterThan(0, $o->guid);
		$this->assertSame('object', $o->type);
		$this->assertSame('river_object', $o->getSubtype());
		$o->delete();
	}

	public function testLoadedRiverObjectIsRiverObjectInstance(): void {
		$o = $this->makeRiverObject();
		$guid = $o->guid;
		_elgg_services()->entityCache->delete($guid);
		$loaded = elgg_call(ELGG_IGNORE_ACCESS, fn() => get_entity($guid));
		$this->assertInstanceOf(RiverObject::class, $loaded);
		$o->delete();
	}

	public function testRiverObjectTitlePersists(): void {
		$o = $this->makeRiverObject(['title' => 'river event title']);
		_elgg_services()->entityCache->delete($o->guid);
		$loaded = elgg_call(ELGG_IGNORE_ACCESS, fn() => get_entity($o->guid));
		$this->assertSame('river event title', (string) $loaded->title);
		$o->delete();
	}

	public function testRiverObjectRiverIdMetadataPersists(): void {
		$o = $this->makeRiverObject(['river_id' => 42]);
		_elgg_services()->entityCache->delete($o->guid);
		$loaded = elgg_call(ELGG_IGNORE_ACCESS, fn() => get_entity($o->guid));
		$this->assertSame('42', (string) $loaded->river_id);
		$o->delete();
	}

	public function testRiverObjectDeleteReturnsTruthy(): void {
		$o = $this->makeRiverObject();
		$result = elgg_call(ELGG_IGNORE_ACCESS, fn() => $o->delete());
		$this->assertNotFalse($result);
	}

	public function testRiverObjectGetRiverItemReturnsFalseForMissing(): void {
		// No matching river entry → getRiverItem() returns false.
		$o = $this->makeRiverObject(['river_id' => 999999]);
		$this->assertFalse($o->getRiverItem());
		$o->delete();
	}

	// --- Comment initializeAttributes (no full save — ElggComment
	// requires a commentable container which is heavy to seed here) ---

	public function testCommentInitializesAsCommentSubtype(): void {
		$c = new Comment();
		$this->assertSame('comment', $c->getSubtype());
	}

	public function testCommentTypeConstantIsObject(): void {
		$this->assertSame('object', Comment::TYPE);
	}

	public function testCommentSubtypeConstant(): void {
		$this->assertSame('comment', Comment::SUBTYPE);
	}
}
