<?php

// Set the root directory
define('ROOT_DIR', dirname(__DIR__));

// Change to project root
chdir(ROOT_DIR);

// Load Composer autoloader
require ROOT_DIR . '/vendor/autoload.php';

// Load core utilities
require ROOT_DIR . '/core/utils.php';

// Load TestCase base class
require ROOT_DIR . '/tests/TestCase.php';

// Set up test environment
putenv('APP_ENV=testing');

// Initialize error handling for tests
$whoops = new Whoops\Run();
$whoops->pushHandler(new Whoops\Handler\PlainTextHandler());
$whoops->register();

// Create test configuration if it doesn't exist
if (!file_exists(ROOT_DIR . '/config.test.ini')) {
    $testConfig = <<<INI
[database]
type = "sqlite"
path = ":memory:"

[defaults]
controller = "Home"

[app]
debug = true
INI;
    file_put_contents(ROOT_DIR . '/config.test.ini', $testConfig);
}

// Override config file for tests
if (!defined('CONFIG_FILE')) {
    define('CONFIG_FILE', 'config.test.ini');
}

// Set up global test helpers
function createMockContainer(): Core\Container {
    return new Core\Container();
}

function createTestConfig(): stdClass {
    $config = new stdClass();
    $config->database = new stdClass();
    $config->database->type = 'sqlite';
    $config->database->path = ':memory:';
    return $config;
}