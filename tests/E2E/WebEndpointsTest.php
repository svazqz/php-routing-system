<?php

namespace Tests\E2E;

use TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\Utils;
use Core\Providers\EloquentServiceProvider;

class WebEndpointsTest extends TestCase
{
    private $client;
    private $baseUrl = 'http://web-e2e-temp:8000';

    protected function setUp(): void
    {
        parent::setUp();
        
        // Initialize HTTP client
        $this->client = new Client([
            'timeout' => 30,
            'http_errors' => false // Don't throw exceptions on HTTP errors
        ]);
        
        // Initialize Eloquent for database operations
        EloquentServiceProvider::initialize();
        
        // Clean up any existing test data
        $this->cleanupTestData();
    }

    protected function tearDown(): void
    {
        // Clean up test data after each test
        $this->cleanupTestData();
        
        parent::tearDown();
    }

    private function cleanupTestData()
    {
        try {
            $connection = EloquentServiceProvider::getConnection();
            $connection->table('samples')->where('email', 'LIKE', '%e2e.web%')->delete();
        } catch (\Exception $e) {
            // Ignore cleanup errors
        }
    }

    public function testHomePageLoads()
    {
        $response = $this->client->get($this->baseUrl);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $body = $response->getBody()->getContents();
        $this->assertNotEmpty($body);
        $this->assertStringContainsString('Welcome', $body);
    }

    public function testApiEndpointWithDatabase()
    {
        // First, create some test data in the database
        $testSample = \Sample::create([
            'name' => 'API Test Sample',
            'email' => 'api.test@e2e.web',
            'description' => 'Sample for API testing'
        ]);
        
        // Test API endpoint (assuming you have an API endpoint)
        $response = $this->client->get($this->baseUrl . '/api/sample');
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);
        
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        
        // Check if our test sample is in the response
        $found = false;
        foreach ($data as $sample) {
            if ($sample['email'] === 'api.test@e2e.web') {
                $found = true;
                $this->assertEquals('API Test Sample', $sample['name']);
                break;
            }
        }
        
        $this->assertTrue($found, 'Test sample not found in API response');
    }

    public function testCreateSampleViaApi()
    {
        $newSampleData = [
            'name' => 'Created via API',
            'email' => 'created.api@e2e.web',
            'description' => 'This sample was created via API call'
        ];
        
        // Send POST request to create sample
        $response = $this->client->post($this->baseUrl . '/api/sample', [
            'json' => $newSampleData,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);
        
        // Should return 201 Created or 200 OK
        $this->assertContains($response->getStatusCode(), [200, 201]);
        
        $responseBody = $response->getBody()->getContents();
        $responseData = json_decode($responseBody, true);
        
        $this->assertIsArray($responseData);
        $this->assertEquals('Created via API', $responseData['name']);
        $this->assertEquals('created.api@e2e.web', $responseData['email']);
        
        // Verify it was actually saved to the database
        $savedSample = \Sample::where('email', 'created.api@e2e.web')->first();
        $this->assertNotNull($savedSample);
        $this->assertEquals('Created via API', $savedSample->name);
    }

    public function testUpdateSampleViaApi()
    {
        // Create a sample first
        $sample = \Sample::create([
            'name' => 'Original Name',
            'email' => 'update.test@e2e.web',
            'description' => 'Original description'
        ]);
        
        $updateData = [
            'name' => 'Updated via API',
            'description' => 'Updated description via API'
        ];
        
        // Send PUT request to update sample
        $response = $this->client->put($this->baseUrl . '/api/sample/' . $sample->id, [
            'json' => $updateData,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        // Verify the update in the database
        $updatedSample = \Sample::find($sample->id);
        $this->assertNotNull($updatedSample);
        $this->assertEquals('Updated via API', $updatedSample->name);
        $this->assertEquals('Updated description via API', $updatedSample->description);
        $this->assertEquals('update.test@e2e.web', $updatedSample->email); // Should remain unchanged
    }

    public function testDeleteSampleViaApi()
    {
        // Create a sample first
        $sample = \Sample::create([
            'name' => 'To be deleted',
            'email' => 'delete.test@e2e.web',
            'description' => 'This will be deleted via API'
        ]);
        
        $sampleId = $sample->id;
        
        // Send DELETE request
        $response = $this->client->delete($this->baseUrl . '/api/sample/' . $sampleId);
        
        $this->assertContains($response->getStatusCode(), [200, 204]);
        
        // Verify it was deleted from the database
        $deletedSample = \Sample::find($sampleId);
        $this->assertNull($deletedSample);
    }

    public function testErrorHandlingWithInvalidData()
    {
        // Test creating sample with invalid data
        $invalidData = [
            'name' => '', // Empty name
            'email' => 'invalid-email', // Invalid email format
            'description' => str_repeat('a', 1000) // Very long description
        ];
        
        $response = $this->client->post($this->baseUrl . '/api/sample', [
            'json' => $invalidData,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);
        
        // Should return validation error
        $this->assertContains($response->getStatusCode(), [400, 422]);
        
        $responseBody = $response->getBody()->getContents();
        $responseData = json_decode($responseBody, true);
        
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('errors', $responseData);
    }

    public function testConcurrentRequests()
    {
        $promises = [];
        
        // Create multiple concurrent requests
        for ($i = 1; $i <= 5; $i++) {
            $promises[] = $this->client->postAsync($this->baseUrl . '/api/sample', [
                'json' => [
                    'name' => "Concurrent Test {$i}",
                    'email' => "concurrent{$i}@e2e.web",
                    'description' => "Concurrent request test {$i}"
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ]);
        }
        
        // Wait for all requests to complete
        $responses = Utils::settle($promises)->wait();
        
        // Verify all requests succeeded
        foreach ($responses as $response) {
            $this->assertEquals('fulfilled', $response['state']);
            $this->assertContains($response['value']->getStatusCode(), [200, 201]);
        }
        
        // Verify all samples were created in the database
        $concurrentSamples = \Sample::where('email', 'LIKE', 'concurrent%@e2e.web')->get();
        $this->assertEquals(5, $concurrentSamples->count());
    }

    public function testDatabaseConnectionPersistence()
    {
        // Make multiple requests to ensure database connection persists
        for ($i = 1; $i <= 10; $i++) {
            $response = $this->client->get($this->baseUrl . '/api/sample');
            $this->assertEquals(200, $response->getStatusCode());
            
            // Small delay between requests
            usleep(100000); // 100ms
        }
        
        // Verify database is still accessible
        $count = \Sample::count();
        $this->assertIsInt($count);
    }

    public function testHealthCheck()
    {
        $response = $this->client->get($this->baseUrl . '/health');
        
        // Health check should return 200 or create one if it doesn't exist
        $this->assertContains($response->getStatusCode(), [200, 404]);
        
        if ($response->getStatusCode() === 200) {
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);
            
            $this->assertIsArray($data);
            $this->assertArrayHasKey('status', $data);
            $this->assertEquals('ok', $data['status']);
        }
    }
}