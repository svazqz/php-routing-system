<?php

namespace Controllers;

use Core\Controller;

class Health extends Controller
{
    public function main()
    {
        // Redirect to API health endpoint
        header('Content-Type: application/json');
        
        try {
            // Check database connection
            \Core\Providers\EloquentServiceProvider::initialize();
            $connection = \Core\Providers\EloquentServiceProvider::getConnection();
            
            // Simple query to test database
            $connection->getPdo()->query('SELECT 1');
            
            echo json_encode([
                'status' => 'ok',
                'timestamp' => date('Y-m-d H:i:s'),
                'database' => 'connected',
                'version' => '1.0.0'
            ]);
        } catch (\Exception $e) {
            http_response_code(503);
            echo json_encode([
                'status' => 'unhealthy',
                'timestamp' => date('Y-m-d H:i:s'),
                'database' => 'disconnected',
                'error' => $e->getMessage()
            ]);
        }
    }
}