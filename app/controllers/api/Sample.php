<?php

namespace Controllers\API;

use Core\APIController;
use Core\Providers\EloquentServiceProvider;

class Sample extends APIController
{
    public function __construct()
    {
        // Ensure Eloquent is initialized
        EloquentServiceProvider::initialize();
        
        // Define custom routes
        $this->__ROUTES__ = array(
            "/api/sample/search" => "search",
            "/api/sample/stats" => "stats"
        );
    }

    /**
     * Get all samples or a specific sample by ID
     * GET /api/sample or GET /api/sample/{id}
     */
    public function show($id = null)
    {
        try {
            if ($id === null) {
                // Get all samples
                $samples = \Sample::all();
                
                return $this->jsonResponse($samples->toArray());
            } else {
                // Get specific sample
                $sample = \Sample::find($id);
                
                if (!$sample) {
                    return $this->jsonResponse([
                        'success' => false,
                        'error' => 'Sample not found'
                    ], 404);
                }
                
                return $this->jsonResponse([
                    'success' => true,
                    'data' => $sample->toArray()
                ]);
            }
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to retrieve sample(s)',
                'message' => $e->getMessage()
            ], 500);
        }
    }



    /**
     * Create a new sample
     * POST /api/sample
     */
    public function create()
    {
        try {
            $input = \Input::all();
            
            // Basic validation
            $errors = $this->validateSampleData($input);
            if (!empty($errors)) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $errors
                ], 422);
            }
            
            $sample = \Sample::create([
                'name' => $input['name'] ?? null,
                'email' => $input['email'] ?? null,
                'description' => $input['description'] ?? null
            ]);
            
            return $this->jsonResponse($sample->toArray(), 201);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to create sample',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing sample
     * PUT /api/sample/{id}
     */
    public function update()
    {
        try {
            // Get ID from URL path
            $pathParts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
            $id = end($pathParts);
            
            $sample = \Sample::find($id);
            
            if (!$sample) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'Sample not found'
                ], 404);
            }
            
            $input = \Input::all();
            
            // Basic validation
            $errors = $this->validateSampleData($input, true);
            if (!empty($errors)) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $errors
                ], 422);
            }
            
            $updateData = [];
            if (isset($input['name'])) {
                $updateData['name'] = $input['name'];
            }
            if (isset($input['email'])) {
                $updateData['email'] = $input['email'];
            }
            if (isset($input['description'])) {
                $updateData['description'] = $input['description'];
            }
            
            $sample->update($updateData);
            
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Sample updated successfully',
                'data' => $sample->fresh()->toArray()
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to update sample',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a sample
     * DELETE /api/sample/{id}
     */
    public function delete()
    {
        try {
            // Get ID from URL path
            $pathParts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
            $id = end($pathParts);
            
            $sample = \Sample::find($id);
            
            if (!$sample) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'Sample not found'
                ], 404);
            }
            
            $sample->delete();
            
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Sample deleted successfully'
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to delete sample',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search samples
     * GET /api/sample/search?q={query}
     */
    public function search()
    {
        try {
            $query = \Input::get('q', '');
            
            if (empty($query)) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'Search query is required'
                ], 400);
            }
            
            $samples = \Sample::where('name', 'LIKE', "%{$query}%")
                             ->orWhere('email', 'LIKE', "%{$query}%")
                             ->orWhere('description', 'LIKE', "%{$query}%")
                             ->get();
            
            return $this->jsonResponse([
                'success' => true,
                'data' => $samples->toArray(),
                'count' => $samples->count(),
                'query' => $query
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Search failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sample statistics
     * GET /api/samples/stats
     */
    public function stats()
    {
        try {
            $totalSamples = \Sample::count();
            $samplesWithEmail = \Sample::whereNotNull('email')->count();
            $samplesWithDescription = \Sample::whereNotNull('description')->count();
            $recentSamples = \Sample::where('created_at', '>=', date('Y-m-d H:i:s', strtotime('-7 days')))->count();
            
            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'total_samples' => $totalSamples,
                    'samples_with_email' => $samplesWithEmail,
                    'samples_with_description' => $samplesWithDescription,
                    'recent_samples' => $recentSamples,
                    'completion_rate' => $totalSamples > 0 ? round(($samplesWithEmail / $totalSamples) * 100, 2) : 0
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to retrieve statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate sample data
     */
    private function validateSampleData($data, $isUpdate = false)
    {
        $errors = [];
        
        // Name validation
        if (!$isUpdate || isset($data['name'])) {
            if (empty($data['name']) || trim($data['name']) === '') {
                $errors['name'] = 'Name is required';
            } elseif (strlen($data['name']) > 255) {
                $errors['name'] = 'Name must not exceed 255 characters';
            }
        }
        
        // Email validation
        if (!$isUpdate || isset($data['email'])) {
            if (!empty($data['email'])) {
                if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    $errors['email'] = 'Invalid email format';
                } elseif (strlen($data['email']) > 255) {
                    $errors['email'] = 'Email must not exceed 255 characters';
                }
                
                // Check for duplicate email (excluding current record for updates)
                $query = \Sample::where('email', $data['email']);
                if ($isUpdate && isset($data['id'])) {
                    $query->where('id', '!=', $data['id']);
                }
                if ($query->exists()) {
                    $errors['email'] = 'Email already exists';
                }
            }
        }
        
        // Description validation
        if (isset($data['description']) && strlen($data['description']) > 1000) {
            $errors['description'] = 'Description must not exceed 1000 characters';
        }
        
        return $errors;
    }
}