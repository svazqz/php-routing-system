<?php

namespace Core\Providers;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Config;

class EloquentServiceProvider
{
    private static $capsule;
    private static $initialized = false;

    /**
     * Initialize Eloquent ORM
     */
    public static function initialize()
    {
        if (self::$initialized) {
            return self::$capsule;
        }

        self::$capsule = new Capsule;

        // Get database configuration
        $config = Config::get();
        $dbConfig = [
            'driver' => $config->getVar('database.driver', 'mysql'),
            'host' => $config->getVar('database.host', 'localhost'),
            'database' => $config->getVar('database.name', ''),
            'username' => $config->getVar('database.username', ''),
            'password' => $config->getVar('database.password', ''),
            'charset' => $config->getVar('database.charset', 'utf8mb4'),
            'collation' => $config->getVar('database.collation', 'utf8mb4_unicode_ci'),
            'prefix' => $config->getVar('database.prefix', ''),
        ];

        // Handle different database drivers
        switch ($dbConfig['driver']) {
            case 'sqlite':
                $dbConfig['database'] = $config->getVar('database.database', ':memory:');
                unset($dbConfig['host'], $dbConfig['username'], $dbConfig['password']);
                break;
            case 'pgsql':
                $dbConfig['port'] = $config->getVar('database.port', 5432);
                break;
            case 'mysql':
            default:
                $dbConfig['port'] = $config->getVar('database.port', 3306);
                break;
        }

        // Add database connection
        self::$capsule->addConnection($dbConfig);

        // Set the event dispatcher used by Eloquent models
        self::$capsule->setEventDispatcher(new Dispatcher(new Container));

        // Make this Capsule instance available globally via static methods
        self::$capsule->setAsGlobal();

        // Boot Eloquent
        self::$capsule->bootEloquent();

        self::$initialized = true;

        return self::$capsule;
    }

    /**
     * Get the Capsule instance
     */
    public static function getCapsule()
    {
        if (!self::$initialized) {
            self::initialize();
        }

        return self::$capsule;
    }

    /**
     * Get database connection
     */
    public static function getConnection($name = null)
    {
        return self::getCapsule()->getConnection($name);
    }

    /**
     * Check if Eloquent is initialized
     */
    public static function isInitialized()
    {
        return self::$initialized;
    }
}