<?php

use Core\Container;

class ContainerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
    }

    public function testCanSetAndResolveBinding(): void
    {
        // Arrange
        $this->container->set('test', function() {
            return 'test_value';
        });

        // Act
        $result = $this->container->get('test');

        // Assert
        $this->assertEquals('test_value', $result);
    }

    public function testCanSetAndResolveClassBinding(): void
    {
        // Arrange
        $this->container->set('stdClass', function() {
            return new stdClass();
        });

        // Act
        $result = $this->container->get('stdClass');

        // Assert
        $this->assertInstanceOf(stdClass::class, $result);
    }

    public function testCanBuildClassWithDependencies(): void
    {
        // Arrange - Create a mock service
        $mockService = $this->createMock('Services\DemoService');
        $this->container->set('Services\DemoService', function() use ($mockService) {
            return $mockService;
        });

        // Act
        $controller = $this->container->build('Controllers\Home');

        // Assert
        $this->assertInstanceOf('Controllers\Home', $controller);
    }

    public function testThrowsExceptionForUnknownBinding(): void
    {
        // Expect
        $this->expectException(Exception::class);

        // Act
        $this->container->get('unknown_binding');
    }

    public function testCanOverrideExistingBinding(): void
    {
        // Arrange
        $this->container->set('test', function() {
            return 'original_value';
        });
        
        $this->container->set('test', function() {
            return 'new_value';
        });

        // Act
        $result = $this->container->get('test');

        // Assert
        $this->assertEquals('new_value', $result);
    }

    public function testBindingFactoryIsCalledEachTime(): void
    {
        // Arrange
        $callCount = 0;
        $this->container->set('counter', function() use (&$callCount) {
            return ++$callCount;
        });

        // Act
        $first = $this->container->get('counter');
        $second = $this->container->get('counter');

        // Assert
        $this->assertEquals(1, $first);
        $this->assertEquals(2, $second);
    }
}