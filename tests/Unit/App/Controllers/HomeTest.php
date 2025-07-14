<?php

use Controllers\Home;
use Services\DemoService;
use Views\Home as HomeView;

class HomeTest extends TestCase
{
    private Home $homeController;
    private DemoService $mockDemoService;
    private HomeView $mockHomeView;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create mock dependencies
        $this->mockDemoService = $this->createMock(DemoService::class);
        $this->mockHomeView = $this->createMock(HomeView::class);
        
        // Create controller with mocked dependencies
        $this->homeController = new Home($this->mockDemoService);
        
        // Mock the getView method to return our mock view
        $reflection = new ReflectionClass($this->homeController);
        $viewProperty = $reflection->getProperty('view');
        $viewProperty->setAccessible(true);
        $viewProperty->setValue($this->homeController, $this->mockHomeView);
    }

    public function testMainMethodCallsDemoServiceAndRendersView(): void
    {
        // Arrange
        $expectedPosts = [
            ['id' => 1, 'title' => 'Test Post 1', 'body' => 'Test body 1'],
            ['id' => 2, 'title' => 'Test Post 2', 'body' => 'Test body 2']
        ];
        
        $this->mockDemoService
            ->expects($this->once())
            ->method('getPosts')
            ->willReturn($expectedPosts);
            
        $this->mockHomeView
            ->expects($this->once())
            ->method('postsPage')
            ->with($expectedPosts);

        // Act
        $this->homeController->main();

        // Assert - expectations are verified automatically by PHPUnit
    }

    public function testMainMethodHandlesEmptyPostsArray(): void
    {
        // Arrange
        $emptyPosts = [];
        
        $this->mockDemoService
            ->expects($this->once())
            ->method('getPosts')
            ->willReturn($emptyPosts);
            
        $this->mockHomeView
            ->expects($this->once())
            ->method('postsPage')
            ->with($emptyPosts);

        // Act
        $this->homeController->main();

        // Assert - expectations are verified automatically by PHPUnit
    }

    public function testMainMethodHandlesServiceError(): void
    {
        // Arrange
        $errorResponse = ['error' => 'Service unavailable'];
        
        $this->mockDemoService
            ->expects($this->once())
            ->method('getPosts')
            ->willReturn($errorResponse);
            
        $this->mockHomeView
            ->expects($this->once())
            ->method('postsPage')
            ->with($errorResponse);

        // Act
        $this->homeController->main();

        // Assert - expectations are verified automatically by PHPUnit
    }

    public function testControllerImplementsIController(): void
    {
        // Assert
        $this->assertInstanceOf('Interfaces\IController', $this->homeController);
    }

    public function testControllerExtendsBaseController(): void
    {
        // Assert
        $this->assertInstanceOf('Core\Controller', $this->homeController);
    }

    public function testConstructorInjectsDependencies(): void
    {
        // Arrange & Act
        $controller = new Home($this->mockDemoService);
        
        // Assert - Use reflection to verify the dependency was injected
        $reflection = new ReflectionClass($controller);
        $demoServiceProperty = $reflection->getProperty('demoService');
        $demoServiceProperty->setAccessible(true);
        $injectedService = $demoServiceProperty->getValue($controller);
        
        $this->assertSame($this->mockDemoService, $injectedService);
    }

    public function testGetViewReturnsViewInstance(): void
    {
        // Arrange
        $controller = new Home($this->mockDemoService);
        
        // Act - Use reflection to call protected getView method
        $reflection = new ReflectionClass($controller);
        $getViewMethod = $reflection->getMethod('getView');
        $getViewMethod->setAccessible(true);
        $view = $getViewMethod->invoke($controller);
        
        // Assert
        $this->assertInstanceOf('Views\Home', $view);
    }
}