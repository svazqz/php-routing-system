<?php

class UtilsTest extends TestCase
{
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
        
        // Reset $_GET and $_SERVER for each test
        $_GET = [];
        $_SERVER = [];
    }
    
    protected function tearDown(): void
    {
        // Clean up test config file
        if (file_exists('config.ini')) {
            unlink('config.ini');
        }
        
        parent::tearDown();
    }

    public function testParseURIAndComponentsWithRootPath(): void
    {
        // Arrange
        $_SERVER['REQUEST_URI'] = '/';
        
        // Act
        $result = parseURIAndComponents();
        
        // Assert
        $this->assertEquals('Home', $result->controller);
        $this->assertEquals('Controllers\\', $result->namespace);
        $this->assertEquals([], $result->components);
        $this->assertEquals('/', $result->originalURI);
    }

    public function testParseURIAndComponentsWithSpecificController(): void
    {
        // Arrange
        $_SERVER['REQUEST_URI'] = '/Blog';
        
        // Act
        $result = parseURIAndComponents();
        
        // Assert
        $this->assertEquals('Blog', $result->controller);
        $this->assertEquals('Controllers\\', $result->namespace);
        $this->assertEquals([], $result->components);
        $this->assertEquals('/Blog', $result->originalURI);
    }

    public function testParseURIAndComponentsWithControllerAndMethod(): void
    {
        // Arrange
        $_SERVER['REQUEST_URI'] = '/Blog/show';
        
        // Act
        $result = parseURIAndComponents();
        
        // Assert
        $this->assertEquals('Blog', $result->controller);
        $this->assertEquals('Controllers\\', $result->namespace);
        $this->assertEquals(['show'], $result->components);
        $this->assertEquals('/Blog/show', $result->originalURI);
    }

    public function testParseURIAndComponentsWithMethodOfDefaultController(): void
    {
        // Arrange
        $_SERVER['REQUEST_URI'] = '/main';
        
        // Mock the Home controller class to exist and have a 'main' method
        // This test assumes the Home controller exists and has a main method
        
        // Act
        $result = parseURIAndComponents();
        
        // Assert
        // When first segment is a method of default controller, it should keep default controller
        // and treat the segment as a method (component)
        $this->assertEquals('Home', $result->controller);
        $this->assertEquals('Controllers\\', $result->namespace);
        $this->assertEquals(['main'], $result->components);
        $this->assertEquals('/main', $result->originalURI);
    }

    public function testParseURIAndComponentsWithAPIController(): void
    {
        // Arrange
        $_SERVER['REQUEST_URI'] = '/api/Users';
        
        // Act
        $result = parseURIAndComponents();
        
        // Assert
        $this->assertEquals('Users', $result->controller);
        $this->assertEquals('Controllers\\API\\', $result->namespace);
        $this->assertEquals([], $result->components);
        $this->assertEquals('/api/Users', $result->originalURI);
    }

    public function testParseURIAndComponentsWithAPIControllerAndMethod(): void
    {
        // Arrange
        $_SERVER['REQUEST_URI'] = '/api/Users/show/1';
        
        // Act
        $result = parseURIAndComponents();
        
        // Assert
        $this->assertEquals('Users', $result->controller);
        $this->assertEquals('Controllers\\API\\', $result->namespace);
        $this->assertEquals(['show', '1'], $result->components);
        $this->assertEquals('/api/Users/show/1', $result->originalURI);
    }

    public function testParseURIAndComponentsWithMultipleParameters(): void
    {
        // Arrange
        $_SERVER['REQUEST_URI'] = '/Blog/show/123/comments/456';
        
        // Act
        $result = parseURIAndComponents();
        
        // Assert
        $this->assertEquals('Blog', $result->controller);
        $this->assertEquals('Controllers\\', $result->namespace);
        $this->assertEquals(['show', '123', 'comments', '456'], $result->components);
        $this->assertEquals('/Blog/show/123/comments/456', $result->originalURI);
    }

    public function testParseURIAndComponentsHandlesIndexPhp(): void
    {
        // Arrange
        $_SERVER['REQUEST_URI'] = '/index.php/Blog/show';
        
        // Act
        $result = parseURIAndComponents();
        
        // Assert
        $this->assertEquals('Blog', $result->controller);
        $this->assertEquals('Controllers\\', $result->namespace);
        $this->assertEquals(['show'], $result->components);
        $this->assertEquals('/Blog/show', $result->originalURI);
    }

    public function testParseURIAndComponentsHandlesIndexPhpAtEnd(): void
    {
        // Arrange
        $_SERVER['REQUEST_URI'] = '/Blog/index.php';
        
        // Act
        $result = parseURIAndComponents();
        
        // Assert
        $this->assertEquals('Blog', $result->controller);
        $this->assertEquals('Controllers\\', $result->namespace);
        $this->assertEquals([], $result->components);
        $this->assertEquals('/Blog', $result->originalURI);
    }

    public function testParseURIAndComponentsHandlesTrailingSlash(): void
    {
        // Arrange
        $_SERVER['REQUEST_URI'] = '/Blog/show/';
        
        // Act
        $result = parseURIAndComponents();
        
        // Assert
        $this->assertEquals('Blog', $result->controller);
        $this->assertEquals('Controllers\\', $result->namespace);
        $this->assertEquals(['show'], $result->components);
        $this->assertEquals('/Blog/show/', $result->originalURI);
    }

    public function testParseURIAndComponentsWithQueryString(): void
    {
        // Arrange
        $_SERVER['REQUEST_URI'] = '/Blog/show?id=123&category=tech';
        
        // Act
        $result = parseURIAndComponents();
        
        // Assert
        $this->assertEquals('Blog', $result->controller);
        $this->assertEquals('Controllers\\', $result->namespace);
        $this->assertEquals(['show'], $result->components);
        $this->assertEquals('/Blog/show', $result->originalURI);
        
        // Verify query string was parsed into $_GET
        $this->assertEquals('123', $_GET['id']);
        $this->assertEquals('tech', $_GET['category']);
    }

    public function testParseURIAndComponentsWithComplexQueryString(): void
    {
        // Arrange
        $_SERVER['REQUEST_URI'] = '/api/Posts?limit=10&offset=20&sort=date&order=desc';
        
        // Act
        $result = parseURIAndComponents();
        
        // Assert
        $this->assertEquals('Posts', $result->controller);
        $this->assertEquals('Controllers\\API\\', $result->namespace);
        $this->assertEquals([], $result->components);
        $this->assertEquals('/api/Posts', $result->originalURI);
        
        // Verify complex query string was parsed correctly
        $this->assertEquals('10', $_GET['limit']);
        $this->assertEquals('20', $_GET['offset']);
        $this->assertEquals('date', $_GET['sort']);
        $this->assertEquals('desc', $_GET['order']);
    }

    public function testParseURIAndComponentsWithEmptyPath(): void
    {
        // Arrange
        $_SERVER['REQUEST_URI'] = '/';
        
        // Act
        $result = parseURIAndComponents();
        
        // Assert
        $this->assertEquals('Home', $result->controller);
        $this->assertEquals('Controllers\\', $result->namespace);
        $this->assertEquals([], $result->components);
        $this->assertEquals('/', $result->originalURI);
    }

    public function testParseURIAndComponentsWithOnlyQueryString(): void
    {
        // Arrange
        $_SERVER['REQUEST_URI'] = '/?search=test&page=1';
        
        // Act
        $result = parseURIAndComponents();
        
        // Assert
        $this->assertEquals('Home', $result->controller);
        $this->assertEquals('Controllers\\', $result->namespace);
        $this->assertEquals([], $result->components);
        $this->assertEquals('/', $result->originalURI);
        
        // Verify query string was parsed
        $this->assertEquals('test', $_GET['search']);
        $this->assertEquals('1', $_GET['page']);
    }

    public function testParseURIAndComponentsReturnsStdClassObject(): void
    {
        // Arrange
        $_SERVER['REQUEST_URI'] = '/Blog';
        
        // Act
        $result = parseURIAndComponents();
        
        // Assert
        $this->assertInstanceOf('stdClass', $result);
        $this->assertTrue(property_exists($result, 'controller'));
        $this->assertTrue(property_exists($result, 'components'));
        $this->assertTrue(property_exists($result, 'namespace'));
        $this->assertTrue(property_exists($result, 'originalURI'));
    }

    public function testParseURIAndComponentsWithSpecialCharactersInQueryString(): void
    {
        // Arrange
        $_SERVER['REQUEST_URI'] = '/Blog/search?q=hello%20world&tags=php%2Cweb';
        
        // Act
        $result = parseURIAndComponents();
        
        // Assert
        $this->assertEquals('Blog', $result->controller);
        $this->assertEquals('Controllers\\', $result->namespace);
        $this->assertEquals(['search'], $result->components);
        $this->assertEquals('/Blog/search', $result->originalURI);
        
        // Verify URL-encoded query parameters are decoded
        $this->assertEquals('hello world', $_GET['q']);
        $this->assertEquals('php,web', $_GET['tags']);
    }

    public function testParseURIAndComponentsWithAPIAndNoController(): void
    {
        // Arrange
        $_SERVER['REQUEST_URI'] = '/api';
        
        // Act & Assert
        // This should throw an error because the function tries to access $components[1]
        // when only 'api' is provided without a controller
        $this->expectException(\Whoops\Exception\ErrorException::class);
        parseURIAndComponents();
    }
}