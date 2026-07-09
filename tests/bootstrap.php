<?php

$elgg_root = getenv('ELGG_ROOT') ?: '/var/www/html';
if (!file_exists($elgg_root . '/vendor/autoload.php')) {
    throw new \RuntimeException("Elgg not found at $elgg_root. Run tests inside the Docker container.");
}
require_once $elgg_root . '/vendor/autoload.php';

// Elgg ships its test case base classes (\Elgg\UnitTestCase, \Elgg\IntegrationTestCase)
// under engine/tests/classes, which is outside every composer autoload map.
$engine_test_classes = $elgg_root . '/vendor/elgg/elgg/engine/tests/classes';
spl_autoload_register(function ($class) use ($engine_test_classes) {
    $file = $engine_test_classes . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// The plugin's own classes must be autoloaded from THIS checkout, not from the
// site's mod/ tree. Elgg only registers mod/*/classes as a PSR-0 fallback while
// booting the application, and the plugin's composer vendor/ is not installed
// when the test runner stages a bare source tree into the container. Without
// this loader every `hypeJunction\Interactions\*` reference is unresolvable.
$plugin_root = dirname(__DIR__);
spl_autoload_register(function ($class) use ($plugin_root) {
    $prefix = 'hypeJunction\\Interactions\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = $plugin_root . '/classes/hypeJunction/Interactions/' . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

$plugin_autoload = $plugin_root . '/vendor/autoload.php';
if (file_exists($plugin_autoload)) {
    require_once $plugin_autoload;
}

// Defines the ACCESS_* constants and the elgg_* function library. Elgg's own
// IntegrationTestCase hierarchy resolves ACCESS_PUBLIC at class-compile time, so
// this must run before any test file is loaded. Does not boot the application.
\Elgg\Application::loadCore();
