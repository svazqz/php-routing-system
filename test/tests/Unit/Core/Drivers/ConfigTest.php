<?php

class ConfigTest extends TestCase
{
    private string $tempConfigFile;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test config.ini file
        $testConfig = [
            'database' => [
                'type' => 'mysql',
                'host' => 'localhost',
                'username' => 'test_user',
                'password' => 'test_pass',
                'database' => 'test_db'
            ],
            'defaults' => [
                'controller' => 'Home',
                'method' => 'main'
            ],
            'app' => [
                'debug' => 'true',
                'timezone' => 'UTC'
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
        
        // Create a temporary config file for testing
        $this->tempConfigFile = $this->createTempConfig([
            'database' => [
                'type' => 'mysql',
                'host' => 'localhost',
                'username' => 'test_user',
                'password' => 'test_pass',
                'database' => 'test_db'
            ],
            'defaults' => [
                'controller' => 'Home',
                'method' => 'main'
            ],
            'app' => [
                'debug' => 'true',
                'timezone' => 'UTC'
            ]
        ]);
    }

    protected function tearDown(): void
    {
        // Clean up temp file
        if (file_exists($this->tempConfigFile)) {
            unlink($this->tempConfigFile);
        }
        
        // Clean up test config file
        if (file_exists('config.ini')) {
            unlink('config.ini');
        }
        
        parent::tearDown();
    }

    public function testCanGetConfigInstance(): void
    {
        // Act
        $config = Config::get();

        // Assert
        $this->assertInstanceOf('Config', $config);
    }

    public function testSingletonPattern(): void
    {
        // Act
        $config1 = Config::get();
        $config2 = Config::get();

        // Assert
        $this->assertSame($config1, $config2);
    }

    public function testCanGetSimpleConfigVar(): void
    {
        // Arrange
        $config = $this->createConfigWithFile();

        // Act
        $result = $config->getVar('app.debug');

        // Assert
        $this->assertEquals('true', $result);
    }

    public function testCanGetNestedConfigVar(): void
    {
        // Arrange
        $config = $this->createConfigWithFile();

        // Act
        $result = $config->getVar('database.host');

        // Assert
        $this->assertEquals('localhost', $result);
    }

    public function testReturnsDefaultForMissingVar(): void
    {
        // Arrange
        $config = $this->createConfigWithFile();
        $defaultValue = 'default_value';

        // Act
        $result = $config->getVar('missing.var', $defaultValue);

        // Assert
        $this->assertEquals($defaultValue, $result);
    }

    public function testReturnsNullForMissingVarWithoutDefault(): void
    {
        // Arrange
        $config = $this->createConfigWithFile();

        // Act
        $result = $config->getVar('missing.var');

        // Assert
        $this->assertNull($result);
    }

    public function testCanGetTopLevelSection(): void
    {
        // Arrange
        $config = $this->createConfigWithFile();

        // Act
        $result = $config->getVar('defaults.controller');

        // Assert
        $this->assertEquals('Home', $result);
    }

    public function testCloneThrowsError(): void
    {
        // Arrange
        $config = Config::get();

        // Expect
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Clone no se permite.');

        // Act
        clone $config;
    }

    /**
     * Create a Config instance with our test file
     */
    private function createConfigWithFile(): Config
    {
        // We need to mock the config file reading since Config uses a hardcoded filename
        // For now, we'll test the public interface
        $config = Config::get();
        
        // Use reflection to set the config data directly for testing
        $reflection = new ReflectionClass($config);
        $configProperty = $reflection->getProperty('config');
        $configProperty->setAccessible(true);
        
        $testConfig = [
            'database' => [
                'type' => 'mysql',
                'host' => 'localhost',
                'username' => 'test_user',
                'password' => 'test_pass',
                'database' => 'test_db'
            ],
            'defaults' => [
                'controller' => 'Home',
                'method' => 'main'
            ],
            'app' => [
                'debug' => 'true',
                'timezone' => 'UTC'
            ]
        ];
        
        $configProperty->setValue($config, $testConfig);
        
        return $config;
    }
}