<?php

use PHPUnit\Framework\TestCase as BaseTestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

abstract class TestCase extends BaseTestCase
{
    use MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear any existing sessions
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        // Reset global state
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        $_SERVER = $this->getDefaultServerVars();
        
        // Clear any static instances
        $this->clearStaticInstances();
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        $this->clearStaticInstances();
        
        parent::tearDown();
    }

    /**
     * Get default server variables for testing
     */
    protected function getDefaultServerVars(): array
    {
        return [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/',
            'HTTP_HOST' => 'localhost',
            'SERVER_NAME' => 'localhost',
            'SCRIPT_NAME' => '/index.php',
            'PHP_SELF' => '/index.php',
        ];
    }

    /**
     * Clear static instances to prevent test interference
     */
    protected function clearStaticInstances(): void
    {
        // Reset Config singleton
        $reflection = new ReflectionClass('Config');
        if ($reflection->hasProperty('instance')) {
            $property = $reflection->getProperty('instance');
            $property->setAccessible(true);
            $property->setValue(null, null);
        }
        
        // Reset View singleton
        $reflection = new ReflectionClass('View');
        if ($reflection->hasProperty('instance')) {
            $property = $reflection->getProperty('instance');
            $property->setAccessible(true);
            $property->setValue(null, null);
        }
    }

    /**
     * Create a mock HTTP request
     */
    protected function mockRequest(string $method = 'GET', string $uri = '/', array $data = []): void
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $uri;
        
        if ($method === 'GET') {
            $_GET = $data;
        } else {
            $_POST = $data;
        }
    }

    /**
     * Assert that a string contains a substring (case-insensitive)
     */
    protected function assertStringContainsStringCaseInsensitive(string $needle, string $haystack, string $message = ''): void
    {
        $this->assertStringContainsString(strtolower($needle), strtolower($haystack), $message);
    }

    /**
     * Create a temporary config file for testing
     */
    protected function createTempConfig(array $config): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_config_');
        $content = '';
        
        foreach ($config as $section => $values) {
            $content .= "[{$section}]\n";
            foreach ($values as $key => $value) {
                $content .= "{$key} = \"{$value}\"\n";
            }
            $content .= "\n";
        }
        
        file_put_contents($tempFile, $content);
        return $tempFile;
    }
}