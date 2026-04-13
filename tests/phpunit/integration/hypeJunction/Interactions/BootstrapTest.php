<?php

namespace hypeJunction\Interactions;

use Elgg\IntegrationTestCase;

/**
 * Characterization suite for hypeinteractions on Elgg 4.x.
 *
 * Covers plugin lifecycle, entity subtype mapping (4 subtypes across 2
 * classes including 2 legacy subtypes that share classes with their
 * modern replacements), declarative action registration, and the large
 * block of Bootstrap::init hook/event wiring.
 */
class BootstrapTest extends IntegrationTestCase {

	public function getPluginID(): string {
		return 'hypeinteractions';
	}

	public function up() {}
	public function down() {}

	// --- plugin lifecycle ---

	public function testPluginIsRegistered() {
		$this->assertInstanceOf(\ElggPlugin::class, elgg_get_plugin_from_id('hypeinteractions'));
	}

	public function testPluginIsEnabled() {
		$this->assertTrue(elgg_get_plugin_from_id('hypeinteractions')->isEnabled());
	}

	public function testPluginIsActive() {
		$this->assertTrue(elgg_get_plugin_from_id('hypeinteractions')->isActive());
	}

	public function testHypeListsDepIsActive() {
		$p = elgg_get_plugin_from_id('hypelists');
		$this->assertNotNull($p);
		$this->assertTrue($p->isActive());
	}

	// --- class autoloading ---

	public function testBootstrapClassLoads() {
		$this->assertTrue(class_exists(Bootstrap::class));
	}

	public function testCommentClassLoads() {
		$this->assertTrue(class_exists(Comment::class));
	}

	public function testRiverObjectClassLoads() {
		$this->assertTrue(class_exists(RiverObject::class));
	}

	public function testCommentExtendsElggComment() {
		$r = new \ReflectionClass(Comment::class);
		$this->assertTrue($r->isSubclassOf(\ElggComment::class));
	}

	public function testRiverObjectExtendsElggObject() {
		$r = new \ReflectionClass(RiverObject::class);
		$this->assertTrue($r->isSubclassOf(\ElggObject::class));
	}

	// --- entity subtype mappings (4 subtypes, 2 classes) ---

	public function testModernCommentSubtypeMapped() {
		$this->assertSame(Comment::class, elgg_get_entity_class('object', 'comment'));
	}

	public function testModernRiverObjectSubtypeMapped() {
		$this->assertSame(RiverObject::class, elgg_get_entity_class('object', 'river_object'));
	}

	public function testLegacyHjcommentSubtypeMappedToCommentClass() {
		// elgg-plugin.php preserves the legacy 3.x subtype 'hjcomment' and
		// maps it to the modern Comment class so pre-existing rows keep
		// resolving. Pin the legacy mapping.
		$this->assertSame(Comment::class, elgg_get_entity_class('object', 'hjcomment'));
	}

	public function testLegacyHjstreamSubtypeMappedToRiverObjectClass() {
		$this->assertSame(RiverObject::class, elgg_get_entity_class('object', 'hjstream'));
	}

	public function testCommentSubtypeConstant() {
		$this->assertSame('comment', Comment::SUBTYPE);
	}

	public function testRiverObjectSubtypeConstant() {
		$this->assertSame('river_object', RiverObject::SUBTYPE);
	}

	// --- actions (all declarative controller form) ---

	public function testCommentSaveActionRegistered() {
		$this->assertTrue(_elgg_services()->actions->exists('comment/save'));
	}

	public function testStreamLikeActionRegistered() {
		$this->assertTrue(_elgg_services()->actions->exists('stream/like'));
	}

	public function testLikesAddActionRegistered() {
		$this->assertTrue(_elgg_services()->actions->exists('likes/add'));
	}

	public function testLikesDeleteActionRegistered() {
		$this->assertTrue(_elgg_services()->actions->exists('likes/delete'));
	}

	// --- hook / event wiring from Bootstrap::init ---

	public function testEntityUrlHookWired() {
		$handlers = _elgg_services()->hooks->getAllHandlers();
		$this->assertArrayHasKey('entity:url', $handlers);
		$this->assertArrayHasKey('object', $handlers['entity:url']);
	}

	public function testEntityIconUrlHookWired() {
		$handlers = _elgg_services()->hooks->getAllHandlers();
		$this->assertArrayHasKey('entity:icon:url', $handlers);
		$this->assertArrayHasKey('object', $handlers['entity:icon:url']);
	}

	public function testCommentsAllHookWired() {
		$handlers = _elgg_services()->hooks->getAllHandlers();
		$this->assertArrayHasKey('comments', $handlers);
		$this->assertArrayHasKey('all', $handlers['comments']);
	}

	public function testContainerLogicCheckHookWired() {
		$handlers = _elgg_services()->hooks->getAllHandlers();
		$this->assertArrayHasKey('container_logic_check', $handlers);
	}

	public function testAnnotationPermissionsCheckHookWired() {
		$handlers = _elgg_services()->hooks->getAllHandlers();
		$this->assertArrayHasKey('permissions_check', $handlers);
		$this->assertArrayHasKey('annotation', $handlers['permissions_check']);
	}

	public function testInteractionsMenuHookWired() {
		$handlers = _elgg_services()->hooks->getAllHandlers();
		$this->assertArrayHasKey('register', $handlers);
		$this->assertArrayHasKey('menu:interactions', $handlers['register']);
	}

	public function testRiverMenuHookWired() {
		$handlers = _elgg_services()->hooks->getAllHandlers();
		$this->assertArrayHasKey('register', $handlers);
		$this->assertArrayHasKey('menu:river', $handlers['register']);
	}

	public function testRiverCreatedEventWired() {
		$events = _elgg_services()->events->getAllHandlers();
		$this->assertArrayHasKey('created', $events);
		$this->assertArrayHasKey('river', $events['created']);
	}

	public function testRiverDeleteAfterEventWired() {
		$events = _elgg_services()->events->getAllHandlers();
		$this->assertArrayHasKey('delete:after', $events);
		$this->assertArrayHasKey('river', $events['delete:after']);
	}

	public function testRiverObjectIsLikableHookWired() {
		$handlers = _elgg_services()->hooks->getAllHandlers();
		$this->assertArrayHasKey('likes:is_likable', $handlers);
		$this->assertArrayHasKey('object:river_object', $handlers['likes:is_likable']);
	}
}
