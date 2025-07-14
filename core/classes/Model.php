<?php

namespace Core\Classes;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Core\Providers\EloquentServiceProvider;

abstract class Model extends EloquentModel
{
    /**
     * Ensure Eloquent is initialized before any model operations
     */
    public function __construct(array $attributes = [])
    {
        // Initialize Eloquent if not already done
        if (!EloquentServiceProvider::isInitialized()) {
            EloquentServiceProvider::initialize();
        }

        parent::__construct($attributes);
    }

    /**
     * Get the database connection for the model
     */
    public function getConnection()
    {
        // Ensure Eloquent is initialized
        if (!EloquentServiceProvider::isInitialized()) {
            EloquentServiceProvider::initialize();
        }

        return parent::getConnection();
    }

    /**
     * Framework-specific helper methods can be added here
     */

    /**
     * Get all records with optional pagination
     */
    public static function getAll($perPage = null, $page = 1)
    {
        if ($perPage) {
            return static::paginate($perPage, ['*'], 'page', $page);
        }

        return static::all();
    }

    /**
     * Find record by ID with error handling
     */
    public static function findById($id)
    {
        return static::find($id);
    }

    /**
     * Find record by ID or throw exception
     */
    public static function findByIdOrFail($id)
    {
        return static::findOrFail($id);
    }

    /**
     * Create a new record
     */
    public static function createRecord(array $data)
    {
        return static::create($data);
    }

    /**
     * Update record by ID
     */
    public static function updateRecord($id, array $data)
    {
        $record = static::findOrFail($id);
        $record->update($data);
        return $record;
    }

    /**
     * Delete record by ID
     */
    public static function deleteRecord($id)
    {
        $record = static::findOrFail($id);
        return $record->delete();
    }

    /**
     * Soft delete record by ID (if using SoftDeletes trait)
     */
    public static function softDeleteRecord($id)
    {
        $record = static::findOrFail($id);
        return $record->delete();
    }

    /**
     * Restore soft deleted record by ID (if using SoftDeletes trait)
     */
    public static function restoreRecord($id)
    {
        $record = static::withTrashed()->findOrFail($id);
        return $record->restore();
    }
}