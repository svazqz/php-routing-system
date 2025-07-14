<?php

class RoutingIntegrationTest extends TestCase
{
    protected Core\Container $container;
    
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
            ],
            'defaults' => [
                'controller' => 'Home',
                'method' => 'main'
            ],
            'app' => [
                'debug' => 'true',
                'timezone' => 'UTC',
                'environment' => 'testing'
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
        
        // Set up a basic container for integration testing
        $this->container = new Core\Container();
        
        // Register real services for integration testing
        $this->container->set('Services\DemoService', function() {
            return new Services\DemoService();
        });
    }
    
    protected function tearDown(): void
    {
        // Clean up test config file
        if (file_exists('config.ini')) {
            unlink('config.ini');
        }
        
        parent::tearDown();
    }

    public function testParseURIAndComponentsWithDefaultController(): void
    {
        // Arrange
        $_SERVER['REQUEST_URI'] = '/';
        
        // Act
        $runnableData = parseURIAndComponents();
        
        // Assert
        $this->assertEquals('Home', $runnableData->controller);
        $this->assertEquals('Controllers\\', $runnableData->namespace);
        $this->assertEquals([], $runnableData->components);
        $this->assertEquals('/', $runnableData->originalURI);
    }

    public function testParseURIAndComponentsWithSpecificController(): void
    {
        // Arrange
        $_SERVER['REQUEST_URI'] = '/Home';
        
        // Act
        $runnableData = parseURIAndComponents();
        
        // Assert
        $this->assertEquals('Home', $runnableData->controller);
        $this->assertEquals('Controllers\\', $runnableData->namespace);
        $this->assertEquals([], $runnableData->components);
        $this->assertEquals('/Home', $runnableData->originalURI);
    }

    public function testParseURIAndComponentsWithControllerAndMethod(): void
    {
        // Arrange
        $_SERVER['REQUEST_URI'] = '/Home/index';
        
        // Act
        $runnableData = parseURIAndComponents();
        
        // Assert
        $this->assertEquals('Home', $runnableData->controller);
        $this->assertEquals('Controllers\\', $runnableData->namespace);
        $this->assertEquals(['index'], $runnableData->components);
        $this->assertEquals('/Home/index', $runnableData->originalURI);
    }

    public function testParseURIAndComponentsWithAPIController(): void
    {
        // Arrange
        $_SERVER['REQUEST_URI'] = '/api/Users';
        
        // Act
        $runnableData = parseURIAndComponents();
        
        // Assert
        $this->assertEquals('Users', $runnableData->controller);
        $this->assertEquals('Controllers\\API\\', $runnableData->namespace);
        $this->assertEquals([], $runnableData->components);
        $this->assertEquals('/api/Users', $runnableData->originalURI);
    }

    public function testParseURIAndComponentsWithAPIControllerAndMethod(): void
    {
        // Arrange
        $_SERVER['REQUEST_URI'] = '/api/Users/show/1';
        
        // Act
        $runnableData = parseURIAndComponents();
        
        // Assert
        $this->assertEquals('Users', $runnableData->controller);
        $this->assertEquals('Controllers\\API\\', $runnableData->namespace);
        $this->assertEquals(['show', '1'], $runnableData->components);
        $this->assertEquals('/api/Users/show/1', $runnableData->originalURI);
    }

    public function testParseURIAndComponentsWithMultipleParameters(): void
    {
        // Arrange
        $_SERVER['REQUEST_URI'] = '/Home/method/param1/param2/param3';
        
        // Act
        $runnableData = parseURIAndComponents();
        
        // Assert
        $this->assertEquals('Home', $runnableData->controller);
        $this->assertEquals('Controllers\\', $runnableData->namespace);
        $this->assertEquals(['method', 'param1', 'param2', 'param3'], $runnableData->components);
        $this->assertEquals('/Home/method/param1/param2/param3', $runnableData->originalURI);
    }

    public function testParseURIAndComponentsHandlesIndexPhp(): void
    {
        // Arrange
        $_SERVER['REQUEST_URI'] = '/index.php/Home';
        
        // Act
        $runnableData = parseURIAndComponents();
        
        // Assert
        $this->assertEquals('Home', $runnableData->controller);
        $this->assertEquals('Controllers\\', $runnableData->namespace);
        $this->assertEquals([], $runnableData->components);
        $this->assertEquals('/Home', $runnableData->originalURI);
    }

    public function testParseURIAndComponentsHandlesTrailingSlash(): void
    {
        // Arrange
        $_SERVER['REQUEST_URI'] = '/Home/';
        
        // Act
        $runnableData = parseURIAndComponents();
        
        // Assert
        $this->assertEquals('Home', $runnableData->controller);
        $this->assertEquals('Controllers\\', $runnableData->namespace);
        $this->assertEquals([], $runnableData->components);
        $this->assertEquals('/Home/', $runnableData->originalURI);
    }

    public function testContainerCanBuildHomeController(): void
    {
        // Act
        $controller = $this->container->build('Controllers\\Home');
        
        // Assert
        $this->assertInstanceOf('Controllers\\Home', $controller);
    }

    public function testHomeControllerHasRequiredMethods(): void
    {
        // Arrange
        $controller = $this->container->build('Controllers\\Home');
        
        // Assert
        $this->assertTrue(method_exists($controller, 'main'));
        $this->assertTrue(method_exists($controller, '__run'));
    }

    public function testControllerImplementsIController(): void
    {
        // Arrange
        $controller = $this->container->build('Controllers\\Home');
        
        // Assert
        $this->assertInstanceOf('Interfaces\\IController', $controller);
    }
}