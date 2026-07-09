<?php

declare(strict_types=1);

namespace hypeJunction\Interactions\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Migration regression guard for plugin "hypeinteractions" — target Elgg 7.x.
 *
 * Derived from elgg-test-writer/templates/MigrationRegressionTest.php.template
 * and driven by skills/elgg-migrate/references/migration-failure-catalog.md.
 * Asserts that every statically-detectable failure class from the catalog is
 * ABSENT for the 7.x target. Static-only (no Elgg boot): most catalog classes
 * fatal at class-load / page-render, so the signature is caught in the source.
 *
 * TWO DELIBERATE DEVIATIONS FROM THE STOCK TEMPLATE (verified against Elgg 7 core):
 *   1. FC-6x7x-03 (menu ->add) check is OMITTED. In Elgg 7 the register-menu
 *      event value is a real \Elgg\Menu\MenuItems collection (Menu\Service::
 *      getUnpreparedMenu passes prepareMenuItems() as the value), and MenuItems
 *      extends \Elgg\Collections\Collection which HAS ->add(). This plugin's
 *      InteractionsMenu/SocialMenu correctly use $menu->add() — flagging it
 *      would be a false positive on already-correct code.
 *   2. FC-ALL-06 manifest string-literal check ALLOWS ::class. `Class::class`
 *      is a compile-time constant that never autoloads, so it is safe in the
 *      entities block; only real class constants (::SUBTYPE, ::TYPE) fatal
 *      because they force the not-yet-wired autoloader. Only ::CONST is flagged.
 */
final class MigrationRegressionTest extends TestCase
{
    /** Target Elgg major this suite guards. */
    private const TARGET_MAJOR = 7;

    /**
     * Global functions REMOVED at each major, keyed by the major that removed
     * them. Treated CUMULATIVELY. Mirror of removed-functions.json (call forms).
     *
     * @var array<int, list<string>>
     */
    private const REMOVED_FUNCTIONS = [
        3 => [
            'elgg_get_metastring_id',
            '_elgg_entities_get_metastrings_options',
        ],
        4 => [
            'elgg_register_css',
            'elgg_load_css',
            'elgg_format_attributes',
            'access_get_show_hidden_status',
            'create_metadata',
            'update_metadata',
            'elgg_flush_simplecache',
        ],
        5 => [
            'add_translation',
            'get_default_access',
            'get_current_language',
            'check_entity_relationship',
        ],
        6 => [
            'elgg_trigger_plugin_hook',
            'elgg_register_plugin_hook_handler',
            'elgg_unregister_plugin_hook_handler',
            'elgg_clear_plugin_hook_handlers',
            'register_error',
            'system_message',
            'forward',
            'elgg_redirect',
            'current_page_url',
            '_elgg_rmdir',
            'elgg_require_js',
            'elgg_load_js',
            'elgg_define_js',
            'elgg_unregister_css',
            'elgg_flush_caches',
            'elgg_get_version',
            'elgg_instanceof',
            'elgg_set_ignore_access',
            'elgg_view_menu_item',
            'elgg_pop_breadcrumb',
            'elgg_set_plugin_setting',
            'elgg_unset_plugin_setting',
            'elgg_set_plugin_user_setting',
            'elgg_unset_plugin_user_setting',
            'elgg_delete_metadata_by_id',
            'elgg_add_subscription',
            'elgg_group_gatekeeper',
            'elgg_get_group_tool_options',
            'elgg_get_file_simple_type',
            'elgg_get_registered_tag_metadata_names',
            'elgg_list_views',
            'elgg_register_admin_menu_item',
            'elgg_strrchr',
            'elgg_strripos',
            'elgg_set_view_location',
            'elgg_disable_annotations',
            'elgg_enable_annotations',
            '_elgg_html_decode',
            '_elgg_get_display_query',
            '_elgg_get_access_where_sql',
            '_elgg_admin_add_plugin_settings_menu',
            // interactions-specific removals proven at 7.x by migration fixes
            'elgg_get_total_comments',
            'elgg_get_total_likes',
        ],
        7 => [
            'elgg_is_admin_user',
            'elgg_get_logged_in_user',
            'elgg_new_entity',
            'elgg_get_entities_from_relationship',
            'elgg_is_registered_viewtype',
            'elgg_get_registered_entity_types',
            'elgg_geocode_location',
            'elgg_reset_system_cache',
        ],
    ];

    /**
     * Bare global constants removed at a major (no `(`). Cumulative.
     *
     * @var array<int, list<string>>
     */
    private const REMOVED_CONSTANTS = [
        7 => ['ELGG_CACHE_PERSISTENT'],
    ];

    /**
     * Core types whose KIND changed so use/new/implements fatals at/beyond the
     * boundary. [needle regex, forbidden keyword regex, human message].
     *
     * @var array<int, list<array{0:string,1:string,2:string}>>
     */
    private const CHANGED_CONTRACTS = [
        4 => [
            ['Elgg\\\\Di\\\\ServiceFacade', 'use', 'Elgg\\Di\\ServiceFacade trait was removed in 4.x'],
            ['Elgg\\\\Notifications\\\\NotificationEvent', 'new', 'Elgg\\Notifications\\NotificationEvent is an interface in 4.x — instantiate SubscriptionNotificationEvent / InstantNotificationEvent'],
        ],
        6 => [
            ['Elgg\\\\Upgrade\\\\Batch', 'implements', 'Elgg\\Upgrade\\Batch became an abstract class in 6.x — extends \\Elgg\\Upgrade\\AsynchronousUpgrade'],
            ['Elgg\\\\Hook', 'use', 'Elgg\\Hook was removed in 6.x — use Elgg\\Event; type-hint \\Elgg\\Event $event'],
        ],
    ];

    /** Canonical Elgg core method signatures (param "type $name" list, defaults stripped). */
    private const CORE_SIG = [
        'canComment'          => 'int $user_guid',
        'canWriteToContainer' => 'int $user_guid, string $type, string $subtype',
        'canEdit'             => 'int $user_guid',
        'canDelete'           => 'int $user_guid',
        'canAnnotate'         => 'int $user_guid, string $annotation_name',
    ];

    /* ---------------------------------------------------------------- helpers */

    private static function pluginRoot(): string
    {
        $dir = __DIR__;
        for ($i = 0; $i < 6; $i++) {
            if (is_file($dir . '/elgg-plugin.php') || is_file($dir . '/manifest.xml') || is_file($dir . '/composer.json')) {
                return $dir;
            }
            $dir = \dirname($dir);
        }
        return \dirname(__DIR__);
    }

    /** @return list<string> files with $ext under $sub (relative dir); vendor dirs skipped */
    private static function files(string $sub, string $ext): array
    {
        $base = self::pluginRoot() . '/' . ltrim($sub, '/');
        if (!is_dir($base)) {
            return [];
        }
        $out = [];
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f) {
            $path = $f->getPathname();
            if (preg_match('#/(vendor|vendors|bower_components|node_modules)/#', $path)) {
                continue;
            }
            if (str_ends_with($path, '.' . $ext)) {
                $out[] = $path;
            }
        }
        return $out;
    }

    /** @return list<string> cumulative removed names for the target major */
    private static function cumulative(array $byMajor): array
    {
        $out = [];
        foreach ($byMajor as $major => $names) {
            if ((int) $major <= self::TARGET_MAJOR) {
                $out = array_merge($out, $names);
            }
        }
        return $out;
    }

    private static function normParams(string $raw): string
    {
        $tokens = [];
        foreach (explode(',', $raw) as $p) {
            $p = preg_replace('/=.*$/', '', $p);
            $p = trim(preg_replace('/\s+/', ' ', $p));
            if ($p !== '') {
                $tokens[] = $p;
            }
        }
        return implode(', ', $tokens);
    }

    private static function manifestSource(): string
    {
        $manifest = self::pluginRoot() . '/elgg-plugin.php';
        return is_file($manifest) ? (string) file_get_contents($manifest) : '';
    }

    /* ---------------------------------------------------------------- removed symbols */

    public function testNoRemovedSymbols(): void
    {
        $removed = self::cumulative(self::REMOVED_FUNCTIONS);
        $alt = implode('|', array_map(static fn (string $n): string => preg_quote($n, '#'), $removed));
        $re = '#(?<![\w>$:\\\\])(' . $alt . ')\s*\(#';

        $violations = [];
        foreach (self::files('', 'php') as $file) {
            if (str_contains($file, '/tests/')) {
                continue;
            }
            foreach (explode("\n", (string) file_get_contents($file)) as $n => $line) {
                if (preg_match_all($re, $line, $m)) {
                    foreach ($m[1] as $sym) {
                        $violations[] = sprintf('%s:%d %s() — removed at/before Elgg %d.x', basename($file), $n + 1, $sym, self::TARGET_MAJOR);
                    }
                }
            }
        }
        $this->assertSame([], $violations, "Calls to removed core functions fatal on Elgg " . self::TARGET_MAJOR . ".x:\n" . implode("\n", $violations));
    }

    public function testNoRemovedConstants(): void
    {
        $removed = self::cumulative(self::REMOVED_CONSTANTS);
        if ($removed === []) {
            $this->markTestSkipped('no removed constants at this target');
        }
        $alt = implode('|', array_map(static fn (string $n): string => preg_quote($n, '#'), $removed));
        $re = '#(?<![\w\\\\])(' . $alt . ')\b#';

        $violations = [];
        foreach (self::files('', 'php') as $file) {
            if (str_contains($file, '/tests/')) {
                continue;
            }
            if (preg_match_all($re, (string) file_get_contents($file), $m)) {
                foreach (array_unique($m[1]) as $c) {
                    $violations[] = basename($file) . ": {$c} removed on Elgg " . self::TARGET_MAJOR . '.x';
                }
            }
        }
        $this->assertSame([], $violations, "Use of removed core constants:\n" . implode("\n", $violations));
    }

    public function testNoRemovedStaticMimeType(): void
    {
        $violations = [];
        foreach (self::files('', 'php') as $file) {
            if (str_contains($file, '/tests/')) {
                continue;
            }
            if (preg_match('/(ElggFile::|->)detectMimeType\s*\(/', (string) file_get_contents($file))) {
                $violations[] = basename($file) . ': detectMimeType() removed in 4.x — use mime_content_type($path) guarded by is_file()';
            }
        }
        $this->assertSame([], $violations, "Calls to removed ElggFile::detectMimeType:\n" . implode("\n", $violations));
    }

    /* ---------------------------------------------------------------- changed class contracts */

    public function testNoRemovedClassContracts(): void
    {
        $rows = [];
        foreach (self::CHANGED_CONTRACTS as $major => $entries) {
            if ((int) $major <= self::TARGET_MAJOR) {
                $rows = array_merge($rows, $entries);
            }
        }
        $violations = [];
        foreach (self::files('', 'php') as $file) {
            if (str_contains($file, '/tests/')) {
                continue;
            }
            foreach (explode("\n", (string) file_get_contents($file)) as $n => $line) {
                foreach ($rows as [$needle, $keyword, $msg]) {
                    if (preg_match('/' . $needle . '\b/', $line) && preg_match('/\b' . $keyword . '\b/', $line)) {
                        $violations[] = sprintf('%s:%d — %s', basename($file), $n + 1, $msg);
                    }
                }
            }
        }
        $this->assertSame([], $violations, "Changed core-type contracts fatal on boot:\n" . implode("\n", $violations));
    }

    /* ---------------------------------------------------------------- forbidden files */

    public function testNoForbiddenBootstrapFiles(): void
    {
        $root = self::pluginRoot();
        $violations = [];
        foreach (['start.php', 'activate.php', 'deactivate.php'] as $forbidden) {
            if (is_file($root . '/' . $forbidden)) {
                $violations[] = "{$forbidden} must be deleted for Elgg 4.x+ (plugin is rejected on activation)";
            }
        }
        $this->assertSame([], $violations, "Forbidden bootstrap files present:\n" . implode("\n", $violations));
    }

    /* ---------------------------------------------------------------- lowercase plugin id */

    public function testNoCamelCasePluginIdCallsites(): void
    {
        $violations = [];
        foreach (self::files('', 'php') as $file) {
            if (str_contains($file, '/tests/')) {
                continue;
            }
            $src = (string) file_get_contents($file);
            if (preg_match_all('/elgg_get_plugin_(?:from_id|setting|user_setting)\s*\([^;]*?[\'"]([A-Za-z0-9_]*[A-Z][A-Za-z0-9_]*)[\'"]/', $src, $m)) {
                foreach (array_unique($m[1]) as $id) {
                    $violations[] = basename($file) . ": plugin id '{$id}' has uppercase — 4.x lowercases ids, callsite silently returns false";
                }
            }
        }
        $this->assertSame([], $violations, "camelCase plugin-id callsites resolve to false on Elgg 4.x+:\n" . implode("\n", $violations));
    }

    /* ---------------------------------------------------------------- Seed / Batch shape */

    public function testSeedSubclassShape(): void
    {
        $violations = [];
        foreach (self::files('classes', 'php') as $file) {
            $src = (string) file_get_contents($file);
            if (!preg_match('/\bextends\s+\\\\?(?:Elgg\\\\Database\\\\Seeds\\\\)?Seed\b/', $src)) {
                continue;
            }
            $hasType = (bool) preg_match('/function\s+getType\s*\(/', $src);
            $hasCount = (bool) preg_match('/function\s+getCountOptions\s*\(/', $src);
            if (!$hasType || !$hasCount) {
                $missing = [];
                if (!$hasType) {
                    $missing[] = 'getType()';
                }
                if (!$hasCount) {
                    $missing[] = 'getCountOptions()';
                }
                $violations[] = basename($file) . ': Seed subclass missing ' . implode(' + ', $missing) . ' (abstract since 6.1 — fatals on every page load)';
            }
        }
        $this->assertSame([], $violations, "Incomplete Seed subclass shape on Elgg 6.x+:\n" . implode("\n", $violations));
    }

    public function testUpgradeBatchShape(): void
    {
        $violations = [];
        foreach (self::files('classes', 'php') as $file) {
            $src = (string) file_get_contents($file);
            if (preg_match('/\bimplements\b[^;{]*\bBatch\b/', $src)) {
                $violations[] = basename($file) . ': `implements Batch` fatals in 6.x — extends \\Elgg\\Upgrade\\AsynchronousUpgrade';
            }
        }
        $this->assertSame([], $violations, "Upgrade Batch shape wrong on Elgg 6.x+:\n" . implode("\n", $violations));
    }

    /* ---------------------------------------------------------------- language files */

    public function testNoLegacyLanguageFiles(): void
    {
        $violations = [];
        foreach (self::files('languages', 'php') as $file) {
            if (preg_match('/(^|[^A-Za-z_])add_translation\s*\(/m', (string) file_get_contents($file))) {
                $violations[] = basename($file) . ': add_translation() removed in 5.0 — return the array directly';
            }
        }
        $this->assertSame([], $violations, "Legacy language files fatal at boot on Elgg 5.x+:\n" . implode("\n", $violations));
    }

    /* ---------------------------------------------------------------- security (all versions) */

    public function testNoUnsafeUnserialize(): void
    {
        $violations = [];
        foreach (self::files('', 'php') as $file) {
            if (str_contains($file, '/tests/')) {
                continue;
            }
            foreach (explode("\n", (string) file_get_contents($file)) as $n => $line) {
                if (preg_match('/(?<![\w>$:\\\\])unserialize\s*\(/', $line) && !str_contains($line, 'allowed_classes')) {
                    $violations[] = basename($file) . ':' . ($n + 1) . ' unserialize() without allowed_classes => false (PHP object-injection RCE)';
                }
            }
        }
        $this->assertSame([], $violations, "Unsafe unserialize():\n" . implode("\n", $violations));
    }

    /* ---------------------------------------------------------------- boot timing / manifest */

    public function testNoRouteRewriteAtInit(): void
    {
        $src = self::manifestSource();
        $this->assertNotSame('', $src, 'elgg-plugin.php must exist');
        $this->assertDoesNotMatchRegularExpression(
            "/['\"]route:rewrite['\"]\s*=>/",
            $src,
            'route:rewrite registered in elgg-plugin.php fires at init (too late) — register it in Bootstrap::boot()',
        );
    }

    public function testManifestEntitiesUseSafeClassReferences(): void
    {
        // elgg-plugin.php is parsed before the classes/ autoloader is wired, so
        // MyClass::SUBTYPE (a real class constant) fatals. Class::class is a
        // compile-time constant that NEVER autoloads, so it is allowed here.
        $src = self::manifestSource();
        if ($src === '' || !preg_match("/['\"]entities['\"]\s*=>\s*\[(.*)\n\s*\],/sU", $src, $block)) {
            $this->markTestSkipped('no entities block in elgg-plugin.php');
        }
        $violations = [];
        if (preg_match('/[\'"](?:class|subtype)[\'"]\s*=>\s*[\\\\\w]+::[A-Z_]+\b/', $block[1])) {
            $violations[] = 'entities block references a class CONSTANT (::SUBTYPE/::TYPE) — use a string literal (autoloader not wired at manifest parse time)';
        }
        $this->assertSame([], $violations, "Non-literal class constant in elgg-plugin.php entities block:\n" . implode("\n", $violations));
    }

    /* ---------------------------------------------------------------- entity-subclass signatures */

    public function testNoIncompatibleCoreOverrides(): void
    {
        $violations = [];
        foreach (self::files('classes', 'php') as $file) {
            $src = (string) file_get_contents($file);
            if (!preg_match('/extends\s+\\\\?(ElggEntity|ElggObject|ElggComment|ElggGroup|ElggUser|ElggSite)\b/', $src)) {
                continue;
            }
            foreach (self::CORE_SIG as $method => $want) {
                if (!preg_match('/function\s+' . $method . '\s*\(([^)]*)\)/', $src, $m)) {
                    continue;
                }
                $got = self::normParams($m[1]);
                if ($got !== $want) {
                    $violations[] = sprintf('%s: %s(%s) — core wants %s(%s)', basename($file), $method, $got, $method, $want);
                }
            }
        }
        $this->assertSame([], $violations, "Incompatible core-method override(s) fatal at class load:\n" . implode("\n", $violations));
    }

    /* ---------------------------------------------------------------- css relocation (7.x) */

    public function testNoOrphanedCssElementViews(): void
    {
        $root = self::pluginRoot();
        $dir = $root . '/views/default/css/elements';
        if (!is_dir($dir)) {
            $this->assertDirectoryDoesNotExist($dir, 'no css/elements overrides to orphan on 7.x');
            return;
        }
        $allPhp = '';
        foreach (self::files('', 'php') as $f) {
            $allPhp .= (string) file_get_contents($f);
        }
        $violations = [];
        foreach (glob($dir . '/*.css') ?: [] as $css) {
            $base = basename($css);
            $stem = substr($base, 0, -4);
            if (is_file($root . '/views/default/elements/' . $base)) {
                continue;
            }
            if (str_contains($allPhp, 'css/elements/' . $stem)) {
                continue;
            }
            $violations[] = "css/elements/{$base} has no relocated twin and is not loaded explicitly → never loads on Elgg 7.x";
        }
        $this->assertSame([], $violations, "Orphaned css/elements overrides:\n" . implode("\n", $violations));
    }
}
