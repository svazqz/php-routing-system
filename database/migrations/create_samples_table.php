<?php

/**
 * Example migration for creating the samples table
 * 
 * To run this migration, you can create a simple migration runner
 * or execute the SQL directly in your database.
 * 
 * This file demonstrates how to create tables using Eloquent's Schema Builder.
 */

use Illuminate\Database\Schema\Blueprint;
use Core\Providers\EloquentServiceProvider;

// Initialize Eloquent
EloquentServiceProvider::initialize();

// Get the schema builder
$schema = EloquentServiceProvider::getConnection()->getSchemaBuilder();

// Create the samples table
if (!$schema->hasTable('samples')) {
    $schema->create('samples', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();
        $table->string('email')->unique()->nullable();
        $table->text('description')->nullable();
        $table->boolean('active')->default(true);
        $table->timestamps();
        
        // Add indexes
        $table->index('active');
        $table->index('created_at');
    });
    
    echo "Samples table created successfully!\n";
} else {
    echo "Samples table already exists.\n";
}

// Example of creating another table with relationships
if (!$schema->hasTable('posts')) {
    $schema->create('posts', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('content');
        $table->unsignedBigInteger('user_id')->nullable();
        $table->boolean('published')->default(false);
        $table->timestamp('published_at')->nullable();
        $table->timestamps();
        
        // Foreign key constraint (if users table exists)
        // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        
        // Indexes
        $table->index('published');
        $table->index('user_id');
        $table->index('created_at');
    });
    
    echo "Posts table created successfully!\n";
} else {
    echo "Posts table already exists.\n";
}