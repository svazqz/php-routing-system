<?php

namespace Tests\Unit\App\Models;

use TestCase;
use Core\Providers\EloquentServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Models\Sample;

class SampleTest extends TestCase
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
        
        // Ensure Eloquent is initialized
        EloquentServiceProvider::initialize();
        
        // Create the samples table for testing
        $connection = EloquentServiceProvider::getConnection();
        $schema = $connection->getSchemaBuilder();
        
        if (!$schema->hasTable('samples')) {
            $schema->create('samples', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('email')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }
    }

    protected function tearDown(): void
    {
        // Clean up the test table
        $connection = EloquentServiceProvider::getConnection();
        $schema = $connection->getSchemaBuilder();
        
        if ($schema->hasTable('samples')) {
            $schema->drop('samples');
        }
        
        // Clean up test config file
        if (file_exists('config.ini')) {
            unlink('config.ini');
        }
        
        parent::tearDown();
    }

    public function testSampleModelExtendsEloquentModel()
    {
        $sample = new Sample();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Model::class, $sample);
    }

    public function testSampleModelHasCorrectTableName()
    {
        $sample = new Sample();
        
        $this->assertEquals('samples', $sample->getTable());
    }

    public function testSampleModelHasCorrectPrimaryKey()
    {
        $sample = new Sample();
        
        $this->assertEquals('id', $sample->getKeyName());
    }

    public function testSampleModelHasTimestamps()
    {
        $sample = new Sample();
        
        $this->assertTrue($sample->usesTimestamps());
    }

    public function testCreateSampleRecord()
    {
        $data = [
            'name' => 'Test Sample',
            'email' => 'test@example.com',
            'description' => 'This is a test sample'
        ];
        
        $sample = Sample::create($data);
        
        $this->assertInstanceOf(Sample::class, $sample);
        $this->assertEquals('Test Sample', $sample->name);
        $this->assertEquals('test@example.com', $sample->email);
        $this->assertEquals('This is a test sample', $sample->description);
        $this->assertNotNull($sample->id);
    }

    public function testFindSampleById()
    {
        // Create a sample first
        $sample = Sample::create([
            'name' => 'Find Test',
            'email' => 'find@example.com'
        ]);
        
        // Find it by ID
        $foundSample = Sample::find($sample->id);
        
        $this->assertInstanceOf(Sample::class, $foundSample);
        $this->assertEquals($sample->id, $foundSample->id);
        $this->assertEquals('Find Test', $foundSample->name);
    }

    public function testUpdateSampleRecord()
    {
        // Create a sample first
        $sample = Sample::create([
            'name' => 'Update Test',
            'email' => 'update@example.com'
        ]);
        
        // Update it
        $sample->update([
            'name' => 'Updated Name',
            'description' => 'Updated description'
        ]);
        
        $updatedSample = $sample->fresh();
        
        $this->assertEquals('Updated Name', $updatedSample->name);
        $this->assertEquals('Updated description', $updatedSample->description);
        $this->assertEquals('update@example.com', $updatedSample->email); // Should remain unchanged
    }

    public function testDeleteSampleRecord()
    {
        // Create a sample first
        $sample = Sample::create([
            'name' => 'Delete Test',
            'email' => 'delete@example.com'
        ]);
        
        $sampleId = $sample->id;
        
        // Delete it
        $result = $sample->delete();
        
        $this->assertTrue($result);
        
        // Verify it's deleted
        $deletedSample = Sample::find($sampleId);
        $this->assertNull($deletedSample);
    }

    public function testGetAllSamples()
    {
        // Create multiple samples
        Sample::create(['name' => 'Sample 1', 'email' => 'sample1@example.com']);
        Sample::create(['name' => 'Sample 2', 'email' => 'sample2@example.com']);
        Sample::create(['name' => 'Sample 3', 'email' => 'sample3@example.com']);
        
        $allSamples = Sample::all();
        
        $this->assertGreaterThanOrEqual(3, $allSamples->count());
    }
}