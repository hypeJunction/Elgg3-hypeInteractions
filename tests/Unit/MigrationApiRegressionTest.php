<?php

namespace hypeJunction\Interactions\Tests\Unit;

use hypeJunction\Interactions\CanCommentOnComment;
use hypeJunction\Interactions\CanEditLikeAnnotation;
use hypeJunction\Interactions\GetCommentSubscribers;
use hypeJunction\Interactions\InteractionsMenu;
use hypeJunction\Interactions\Router;
use hypeJunction\Interactions\Seeder;
use hypeJunction\Interactions\SocialMenu;
use hypeJunction\Interactions\SubscribeToCommentNotifications;
use hypeJunction\Interactions\SyncRiverObjectAccess;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Static (no-boot) regressions for the Elgg 6.x/7.x migration fixes that can be
 * proven from the source shape alone. Runs in the Unit suite alongside
 * RiverObjectTest (plain PHPUnit\Framework\TestCase — no DB, no booted Elgg).
 */
class MigrationApiRegressionTest extends TestCase {

	/**
	 * Regression for 6134715: every event handler was ported from the removed
	 * \Elgg\Hook to \Elgg\Event. If any handler regressed to a legacy multi-arg
	 * hook signature (or re-imported \Elgg\Hook), its first parameter would no
	 * longer type-hint \Elgg\Event and this fails.
	 *
	 * @dataProvider eventHandlerProvider
	 */
	public function testEventHandlerFirstParamIsElggEvent(string $class, string $method) {
		$ref = new ReflectionMethod($class, $method);
		$params = $ref->getParameters();

		$this->assertNotEmpty($params, "$class::$method should accept the event object");

		$type = $params[0]->getType();
		$this->assertNotNull($type, "$class::$method first param must be type-hinted");
		$this->assertSame(
			'Elgg\\Event',
			$type->getName(),
			"$class::$method must receive \\Elgg\\Event (\\Elgg\\Hook was removed in 6.x)"
		);
	}

	public static function eventHandlerProvider(): array {
		return [
			'Router::urlHandler' => [Router::class, 'urlHandler'],
			'Router::iconUrlHandler' => [Router::class, 'iconUrlHandler'],
			'CanCommentOnComment' => [CanCommentOnComment::class, '__invoke'],
			'CanEditLikeAnnotation' => [CanEditLikeAnnotation::class, '__invoke'],
			'SubscribeToCommentNotifications' => [SubscribeToCommentNotifications::class, '__invoke'],
			'GetCommentSubscribers' => [GetCommentSubscribers::class, '__invoke'],
			'SyncRiverObjectAccess' => [SyncRiverObjectAccess::class, '__invoke'],
			'InteractionsMenu' => [InteractionsMenu::class, '__invoke'],
			'SocialMenu' => [SocialMenu::class, '__invoke'],
			'Seeder::addSeed' => [Seeder::class, 'addSeed'],
		];
	}

	/**
	 * Regression for 314a63c + FC-5x6x-03: entity-type-owning plugins must ship a
	 * concrete \Elgg\Database\Seeds\Seed subclass, and since 6.1 that base is
	 * abstract on getType()/getCountOptions() — a subclass missing either method
	 * fatals on every page load during handler validation.
	 */
	public function testSeederIsConcreteSeedSubclassForElgg7() {
		$this->assertTrue(
			is_subclass_of(Seeder::class, \Elgg\Database\Seeds\Seed::class),
			'Seeder must extend \\Elgg\\Database\\Seeds\\Seed'
		);

		// Both abstract-since-6.1 methods must be DECLARED on Seeder itself,
		// not merely inherited (inherited = still abstract = fatal).
		$getType = new ReflectionMethod(Seeder::class, 'getType');
		$getCount = new ReflectionMethod(Seeder::class, 'getCountOptions');
		$this->assertSame(Seeder::class, $getType->getDeclaringClass()->getName());
		$this->assertSame(Seeder::class, $getCount->getDeclaringClass()->getName());

		// The seeder owns the canonical 'comment' subtype.
		$this->assertSame('comment', Seeder::getType());
	}

	/**
	 * Regression for f166d90: composer autoload was converted from psr-0 to
	 * psr-4 for Elgg 7.x. Assert the psr-4 block is present, psr-0 is gone, and
	 * it maps the plugin namespace onto classes/.
	 */
	public function testComposerAutoloadUsesPsr4() {
		$composer = json_decode(file_get_contents(dirname(__DIR__, 2) . '/composer.json'), true);

		$this->assertArrayHasKey('autoload', $composer);
		$this->assertArrayHasKey('psr-4', $composer['autoload'], 'psr-4 autoload is required on 7.x');
		$this->assertArrayNotHasKey('psr-0', $composer['autoload'], 'psr-0 must be removed on 7.x');

		$paths = array_values($composer['autoload']['psr-4']);
		$this->assertNotEmpty($paths);
		$this->assertStringContainsString(
			'classes/hypeJunction/Interactions/',
			$paths[0],
			'psr-4 must map the plugin namespace onto its classes/ tree'
		);
	}

	/**
	 * Regression for 0177080: the plugin must declare a hard hypelists dependency
	 * in elgg-plugin.php so the fully-qualified elgg_register_collection() call in
	 * Bootstrap::init() resolves. A dropped dependency block re-breaks activation.
	 */
	public function testHypelistsDependencyDeclared() {
		$config = include dirname(__DIR__, 2) . '/elgg-plugin.php';

		$this->assertArrayHasKey('dependencies', $config['plugin']);
		$this->assertArrayHasKey('hypelists', $config['plugin']['dependencies']);
		$this->assertTrue(
			(bool) ($config['plugin']['dependencies']['hypelists']['must_be_active'] ?? false),
			'hypelists must be declared must_be_active'
		);
	}
}
