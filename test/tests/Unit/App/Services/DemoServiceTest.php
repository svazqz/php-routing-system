<?php

use Services\DemoService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

class DemoServiceTest extends TestCase
{
    private DemoService $demoService;
    private MockHandler $mockHandler;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a mock handler for Guzzle
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        
        // Create a mock client with the handler
        $mockClient = new Client(['handler' => $handlerStack]);
        
        // Create DemoService and inject the mock client
        $this->demoService = new DemoService();
        
        // Use reflection to replace the client
        $reflection = new ReflectionClass($this->demoService);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($this->demoService, $mockClient);
    }

    public function testGetPostsReturnsArrayOfPosts(): void
    {
        // Arrange
        $expectedPosts = [
            ['id' => 1, 'title' => 'Test Post 1', 'body' => 'Test body 1'],
            ['id' => 2, 'title' => 'Test Post 2', 'body' => 'Test body 2']
        ];
        
        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedPosts))
        );

        // Act
        $result = $this->demoService->getPosts();

        // Assert
        $this->assertEquals($expectedPosts, $result);
    }

    public function testGetPostReturnsSpecificPost(): void
    {
        // Arrange
        $postId = 1;
        $expectedPost = ['id' => $postId, 'title' => 'Test Post', 'body' => 'Test body'];
        
        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedPost))
        );

        // Act
        $result = $this->demoService->getPost($postId);

        // Assert
        $this->assertEquals($expectedPost, $result);
    }

    public function testGetUsersReturnsArrayOfUsers(): void
    {
        // Arrange
        $expectedUsers = [
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
            ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com']
        ];
        
        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedUsers))
        );

        // Act
        $result = $this->demoService->getUsers();

        // Assert
        $this->assertEquals($expectedUsers, $result);
    }

    public function testGetUserReturnsSpecificUser(): void
    {
        // Arrange
        $userId = 1;
        $expectedUser = ['id' => $userId, 'name' => 'John Doe', 'email' => 'john@example.com'];
        
        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedUser))
        );

        // Act
        $result = $this->demoService->getUser($userId);

        // Assert
        $this->assertEquals($expectedUser, $result);
    }

    public function testGetCommentsWithoutPostIdReturnsAllComments(): void
    {
        // Arrange
        $expectedComments = [
            ['id' => 1, 'postId' => 1, 'name' => 'Comment 1', 'body' => 'Comment body 1'],
            ['id' => 2, 'postId' => 2, 'name' => 'Comment 2', 'body' => 'Comment body 2']
        ];
        
        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedComments))
        );

        // Act
        $result = $this->demoService->getComments();

        // Assert
        $this->assertEquals($expectedComments, $result);
    }

    public function testGetCommentsWithPostIdReturnsFilteredComments(): void
    {
        // Arrange
        $postId = 1;
        $expectedComments = [
            ['id' => 1, 'postId' => $postId, 'name' => 'Comment 1', 'body' => 'Comment body 1']
        ];
        
        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedComments))
        );

        // Act
        $result = $this->demoService->getComments($postId);

        // Assert
        $this->assertEquals($expectedComments, $result);
    }

    public function testGetPostsHandlesHttpException(): void
    {
        // Arrange
        $this->mockHandler->append(
            new RequestException('Error Communicating with Server', new Request('GET', 'test'))
        );

        // Act
        $result = $this->demoService->getPosts();

        // Assert
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Error Communicating with Server', $result['error']);
    }

    public function testGetUserHandlesHttpException(): void
    {
        // Arrange
        $this->mockHandler->append(
            new RequestException('User not found', new Request('GET', 'test'))
        );

        // Act
        $result = $this->demoService->getUser(999);

        // Assert
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('User not found', $result['error']);
    }

    public function testGetCommentsHandlesHttpException(): void
    {
        // Arrange
        $this->mockHandler->append(
            new RequestException('Comments not available', new Request('GET', 'test'))
        );

        // Act
        $result = $this->demoService->getComments(1);

        // Assert
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Comments not available', $result['error']);
    }
}