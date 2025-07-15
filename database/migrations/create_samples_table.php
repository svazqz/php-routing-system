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
