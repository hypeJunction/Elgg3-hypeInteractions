<?php

namespace hypeJunction\Interactions\Tests\Integration;

use Elgg\IntegrationTestCase;
use hypeJunction\Interactions\Comment;
use hypeJunction\Interactions\RiverObject;
use hypeJunction\Interactions\Seeder;

/**
 * Verifies that everything declared in elgg-plugin.php actually registers on a
 * booted Elgg 7 site: entity-class mapping (incl. legacy subtypes), routes with
 * their paths, actions, and the seeds:database wiring.
 */
class RegistrationTest extends IntegrationTestCase {

	public function testEntityClassMappingRegistered() {
		// Canonical subtypes
		$this->assertSame(Comment::class, elgg_get_entity_class('object', 'comment'));
		$this->assertSame(RiverObject::class, elgg_get_entity_class('object', 'river_object'));

		// Legacy subtypes must resolve to the same classes so pre-migration data renders
		$this->assertSame(Comment::class, elgg_get_entity_class('object', 'hjcomment'));
		$this->assertSame(RiverObject::class, elgg_get_entity_class('object', 'hjstream'));
	}

	public function testRoutesRegisteredWithExpectedPaths() {
		$routes = _elgg_services()->routes;

		$expected = [
			'edit:object:comment' => 'stream/edit',
			'view:object:comment' => 'stream/view',
			'collection:object:comment' => 'stream/comments',
			'collection:annotation:likes' => 'stream/likes',
		];

		foreach ($expected as $name => $path_fragment) {
			$route = $routes->get($name);
			$this->assertNotNull($route, "Route '$name' must be registered");
			$this->assertStringContainsString(
				$path_fragment,
				$route->getPath(),
				"Route '$name' should serve '$path_fragment'"
			);
		}
	}

	public function testActionsRegistered() {
		$actions = _elgg_services()->actions;

		$this->assertTrue($actions->exists('comment/save'));
		$this->assertTrue($actions->exists('stream/like'));
		$this->assertTrue($actions->exists('likes/add'));
		$this->assertTrue($actions->exists('likes/delete'));
	}

	/**
	 * Regression for 314a63c: the Seeder subclass must be wired onto the
	 * seeds:database event so the site can seed the plugin's comment entities.
	 */
	public function testSeederRegisteredOnSeedsDatabaseEvent() {
		$seeds = elgg_trigger_event_results('seeds', 'database', [], []);
		$this->assertContains(Seeder::class, $seeds);
	}
}
