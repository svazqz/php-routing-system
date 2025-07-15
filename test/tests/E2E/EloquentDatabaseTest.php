<?php

namespace Tests\E2E;

use TestCase;
use Core\Providers\EloquentServiceProvider;
use Illuminate\Database\Schema\Blueprint;

class EloquentDatabaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Initialize Eloquent with the E2E configuration
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
        $connection = EloquentServiceProvider::getConnection();
        
        // Clean up test data
        $connection->table('samples')->where('email', 'LIKE', '%test.e2e%')->delete();
    }

    public function testDatabaseConnectionIsWorking()
    {
        $connection = EloquentServiceProvider::getConnection();
        
        // Test basic connection
        $result = $connection->select('SELECT 1 as test');
        
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals(1, $result[0]->test);
    }

    public function testSampleTableExists()
    {
        $connection = EloquentServiceProvider::getConnection();
        $schema = $connection->getSchemaBuilder();
        
        $this->assertTrue($schema->hasTable('samples'));
        $this->assertTrue($schema->hasColumn('samples', 'id'));
        $this->assertTrue($schema->hasColumn('samples', 'name'));
        $this->assertTrue($schema->hasColumn('samples', 'email'));
        $this->assertTrue($schema->hasColumn('samples', 'description'));
        $this->assertTrue($schema->hasColumn('samples', 'created_at'));
        $this->assertTrue($schema->hasColumn('samples', 'updated_at'));
    }

    public function testCreateSampleWithRealDatabase()
    {
        $data = [
            'name' => 'E2E Test Sample',
            'email' => 'e2e.test@test.e2e',
            'description' => 'This is an E2E test sample with real database'
        ];
        
        $sample = \Sample::create($data);
        
        $this->assertInstanceOf(\Sample::class, $sample);
        $this->assertEquals('E2E Test Sample', $sample->name);
        $this->assertEquals('e2e.test@test.e2e', $sample->email);
        $this->assertNotNull($sample->id);
        $this->assertNotNull($sample->created_at);
        $this->assertNotNull($sample->updated_at);
        
        // Verify it's actually in the database
        $foundSample = \Sample::find($sample->id);
        $this->assertNotNull($foundSample);
        $this->assertEquals($sample->id, $foundSample->id);
    }

    public function testUpdateSampleWithRealDatabase()
    {
        // Create a sample
        $sample = \Sample::create([
            'name' => 'Original Name',
            'email' => 'original@test.e2e',
            'description' => 'Original description'
        ]);
        
        $originalUpdatedAt = $sample->updated_at;
        
        // Wait a moment to ensure timestamp difference
        sleep(1);
        
        // Update the sample
        $sample->update([
            'name' => 'Updated Name',
            'description' => 'Updated description'
        ]);
        
        // Refresh from database
        $sample->refresh();
        
        $this->assertEquals('Updated Name', $sample->name);
        $this->assertEquals('Updated description', $sample->description);
        $this->assertEquals('original@test.e2e', $sample->email); // Should remain unchanged
        $this->assertNotEquals($originalUpdatedAt, $sample->updated_at); // Should be updated
    }

    public function testDeleteSampleWithRealDatabase()
    {
        // Create a sample
        $sample = \Sample::create([
            'name' => 'To Be Deleted',
            'email' => 'delete@test.e2e',
            'description' => 'This will be deleted'
        ]);
        
        $sampleId = $sample->id;
        
        // Delete the sample
        $result = $sample->delete();
        
        $this->assertTrue($result);
        
        // Verify it's deleted from database
        $deletedSample = \Sample::find($sampleId);
        $this->assertNull($deletedSample);
    }

    public function testQueryBuilderWithRealDatabase()
    {
        // Create multiple samples
        \Sample::create(['name' => 'Query Test 1', 'email' => 'query1@test.e2e']);
        \Sample::create(['name' => 'Query Test 2', 'email' => 'query2@test.e2e']);
        \Sample::create(['name' => 'Different Name', 'email' => 'different@test.e2e']);
        
        // Test where clause
        $queryResults = \Sample::where('name', 'LIKE', 'Query Test%')->get();
        $this->assertGreaterThanOrEqual(2, $queryResults->count());
        
        // Test specific email search
        $specificSample = \Sample::where('email', 'query1@test.e2e')->first();
        $this->assertNotNull($specificSample);
        $this->assertEquals('Query Test 1', $specificSample->name);
        
        // Test count
        $count = \Sample::where('email', 'LIKE', '%@test.e2e')->count();
        $this->assertGreaterThanOrEqual(3, $count);
    }

    public function testTransactionWithRealDatabase()
    {
        $connection = EloquentServiceProvider::getConnection();
        
        try {
            $connection->beginTransaction();
            
            // Create a sample within transaction
            $sample = \Sample::create([
                'name' => 'Transaction Test',
                'email' => 'transaction@test.e2e',
                'description' => 'Testing transactions'
            ]);
            
            $this->assertNotNull($sample->id);
            
            // Rollback the transaction
            $connection->rollBack();
            
            // Verify the sample was not actually saved
            $foundSample = \Sample::find($sample->id);
            $this->assertNull($foundSample);
            
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    public function testBulkOperationsWithRealDatabase()
    {
        // Test bulk insert
        $samples = [
            ['name' => 'Bulk 1', 'email' => 'bulk1@test.e2e', 'description' => 'Bulk test 1'],
            ['name' => 'Bulk 2', 'email' => 'bulk2@test.e2e', 'description' => 'Bulk test 2'],
            ['name' => 'Bulk 3', 'email' => 'bulk3@test.e2e', 'description' => 'Bulk test 3']
        ];
        
        $connection = EloquentServiceProvider::getConnection();
        $connection->table('samples')->insert($samples);
        
        // Verify bulk insert worked
        $bulkSamples = \Sample::where('email', 'LIKE', 'bulk%@test.e2e')->get();
        $this->assertEquals(3, $bulkSamples->count());
        
        // Test bulk update
        \Sample::where('email', 'LIKE', 'bulk%@test.e2e')
               ->update(['description' => 'Updated in bulk']);
        
        // Verify bulk update
        $updatedSamples = \Sample::where('email', 'LIKE', 'bulk%@test.e2e')->get();
        foreach ($updatedSamples as $sample) {
            $this->assertEquals('Updated in bulk', $sample->description);
        }
        
        // Test bulk delete
        $deletedCount = \Sample::where('email', 'LIKE', 'bulk%@test.e2e')->delete();
        $this->assertEquals(3, $deletedCount);
    }

    public function testDatabasePerformanceWithRealDatabase()
    {
        $startTime = microtime(true);
        
        // Create 100 samples to test performance
        for ($i = 1; $i <= 100; $i++) {
            \Sample::create([
                'name' => "Performance Test {$i}",
                'email' => "perf{$i}@test.e2e",
                'description' => "Performance testing sample {$i}"
            ]);
        }
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Should complete within reasonable time (adjust as needed)
        $this->assertLessThan(10, $executionTime, 'Bulk creation took too long');
        
        // Verify all samples were created
        $count = \Sample::where('email', 'LIKE', 'perf%@test.e2e')->count();
        $this->assertEquals(100, $count);
        
        // Clean up performance test data
        \Sample::where('email', 'LIKE', 'perf%@test.e2e')->delete();
    }
}