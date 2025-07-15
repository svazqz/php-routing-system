<?php

namespace Tests\Unit\Core\Providers;

use TestCase;
use Core\Providers\EloquentServiceProvider;
use Illuminate\Database\Capsule\Manager as Capsule;

class EloquentServiceProviderTest extends TestCase
{
    protected function tearDown(): void
    {
        // Clean up test config file
        if (file_exists('config.ini')) {
            unlink('config.ini');
        }
        
        parent::tearDown();
    }
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test config.ini file
        $testConfig = [
            'database' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => ''
            ]
        ];
        
        $configContent = "";
        foreach ($testConfig as $section => $values) {
            $configContent .= "[{$section}]\n";
            foreach ($values as $key => $value) {
                $configContent .= "{$key} = \"{$value}\"\n";
            }
            $configContent .= "\n";
        }
        
        file_put_contents('config.ini', $configContent);
        
        // Reset the service provider state for each test
        $reflection = new \ReflectionClass(EloquentServiceProvider::class);
        $initializedProperty = $reflection->getProperty('initialized');
        $initializedProperty->setAccessible(true);
        $initializedProperty->setValue(null, false);
        
        $capsuleProperty = $reflection->getProperty('capsule');
        $capsuleProperty->setAccessible(true);
        $capsuleProperty->setValue(null, null);
    }

    public function testInitializeCreatesValidCapsuleInstance()
    {
        $capsule = EloquentServiceProvider::initialize();
        
        $this->assertInstanceOf(Capsule::class, $capsule);
        $this->assertTrue(EloquentServiceProvider::isInitialized());
    }

    public function testGetCapsuleReturnsValidInstance()
    {
        $capsule = EloquentServiceProvider::getCapsule();
        
        $this->assertInstanceOf(Capsule::class, $capsule);
        $this->assertTrue(EloquentServiceProvider::isInitialized());
    }

    public function testGetConnectionReturnsValidConnection()
    {
        $connection = EloquentServiceProvider::getConnection();
        
        $this->assertNotNull($connection);
        $this->assertTrue(method_exists($connection, 'table'));
        $this->assertTrue(method_exists($connection, 'select'));
    }

    public function testInitializeOnlyRunsOnce()
    {
        $capsule1 = EloquentServiceProvider::initialize();
        $capsule2 = EloquentServiceProvider::initialize();
        
        $this->assertSame($capsule1, $capsule2);
    }

    public function testDatabaseConnectionWorks()
    {
        $connection = EloquentServiceProvider::getConnection();
        
        // Test basic query execution
        $result = $connection->select('SELECT 1 as test');
        
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals(1, $result[0]->test);
    }

    public function testQueryBuilderWorks()
    {
        $connection = EloquentServiceProvider::getConnection();
        
        // Create a temporary table for testing
        $connection->statement('CREATE TEMPORARY TABLE test_table (id INTEGER, name TEXT)');
        $connection->statement('INSERT INTO test_table (id, name) VALUES (1, "test")');
        
        // Test query builder
        $result = $connection->table('test_table')->where('id', 1)->first();
        
        $this->assertNotNull($result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals('test', $result->name);
    }
}