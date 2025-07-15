<?php

namespace Controllers\API;

use Core\APIController;
use Core\Providers\EloquentServiceProvider;

class Health extends APIController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Health check endpoint
     * GET /api/health
     */
    public function show()
    {
        try {
            // Check database connection
            EloquentServiceProvider::initialize();
            $connection = EloquentServiceProvider::getConnection();
            
            // Simple query to test database
            $connection->getPdo()->query('SELECT 1');
            
            return $this->jsonResponse([
                'status' => 'ok',
                'timestamp' => date('Y-m-d H:i:s'),
                'database' => 'connected',
                'version' => '1.0.0'
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'status' => 'unhealthy',
                'timestamp' => date('Y-m-d H:i:s'),
                'database' => 'disconnected',
                'error' => $e->getMessage()
            ], 503);
        }
    }
}