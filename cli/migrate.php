#!/usr/bin/env php
<?php

/**
 * Simple Migration Runner for PHP-RoutingSystem
 * 
 * Usage:
 *   php migrate.php [migration_file]
 *   php migrate.php database/migrations/create_samples_table.php
 * 
 * If no migration file is specified, it will run all migrations in the database/migrations folder.
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/core/utils.php';

use Core\Providers\EloquentServiceProvider;

// Initialize Eloquent
try {
    EloquentServiceProvider::initialize();
    echo "✓ Eloquent ORM initialized successfully\n";
} catch (Exception $e) {
    echo "✗ Failed to initialize Eloquent: " . $e->getMessage() . "\n";
    echo "Please check your database configuration in config.ini\n";
    exit(1);
}

// Test database connection
try {
    $connection = EloquentServiceProvider::getConnection();
    $connection->select('SELECT 1');
    echo "✓ Database connection successful\n";
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    echo "Please check your database configuration and ensure the database server is running\n";
    exit(1);
}

echo "\n";

// Get migration file(s) to run
$migrationFile = $argv[1] ?? null;

if ($migrationFile) {
    // Run specific migration
    if (!file_exists($migrationFile)) {
        echo "✗ Migration file not found: {$migrationFile}\n";
        exit(1);
    }
    
    echo "Running migration: {$migrationFile}\n";
    echo str_repeat('-', 50) . "\n";
    
    try {
        require $migrationFile;
        echo "✓ Migration completed successfully\n";
    } catch (Exception $e) {
        echo "✗ Migration failed: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    // Run all migrations in the migrations folder
    $migrationsDir = __DIR__ . '/database/migrations';
    
    if (!is_dir($migrationsDir)) {
        echo "✗ Migrations directory not found: {$migrationsDir}\n";
        echo "Creating migrations directory...\n";
        mkdir($migrationsDir, 0755, true);
        echo "✓ Migrations directory created\n";
        echo "No migrations to run.\n";
        exit(0);
    }
    
    $migrationFiles = glob($migrationsDir . '/*.php');
    
    if (empty($migrationFiles)) {
        echo "No migration files found in {$migrationsDir}\n";
        exit(0);
    }
    
    echo "Found " . count($migrationFiles) . " migration(s)\n";
    echo str_repeat('=', 50) . "\n";
    
    foreach ($migrationFiles as $file) {
        $filename = basename($file);
        echo "\nRunning migration: {$filename}\n";
        echo str_repeat('-', 30) . "\n";
        
        try {
            require $file;
            echo "✓ {$filename} completed successfully\n";
        } catch (Exception $e) {
            echo "✗ {$filename} failed: " . $e->getMessage() . "\n";
            echo "Stopping migration process.\n";
            exit(1);
        }
    }
    
    echo "\n" . str_repeat('=', 50) . "\n";
    echo "✓ All migrations completed successfully!\n";
}

echo "\nMigration process finished.\n";