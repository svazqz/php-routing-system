<?php

namespace Controllers;

use Core;
use Services\DemoService;

class ModelDemo extends Core\Controller {
    private $demoService;
    
    public function __construct(DemoService $demoService) {
        $this->demoService = $demoService;
    }

    /**
     * Display blog posts from Eloquent model
     */
    public function blogPosts() {
        $posts = $this->demoService->getBlogPosts();
        
        // Simple JSON output for demonstration
        header('Content-Type: application/json');
        echo json_encode([
            'source' => 'Eloquent Model (In-Memory SQLite)',
            'posts' => $posts
        ], JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Create a new blog post
     */
    public function createBlogPost() {
        $newPost = $this->demoService->createBlogPost([
            'title' => 'New Post from Controller',
            'content' => 'This post was created using the Eloquent model in an in-memory SQLite database.',
            'author' => 'Demo Controller',
            'published_at' => date('Y-m-d H:i:s')
        ]);
        
        header('Content-Type: application/json');
        echo json_encode([
            'message' => 'Blog post created successfully',
            'post' => $newPost
        ], JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Demonstrate Sample model usage
     */
    public function samples() {
        // Create a sample record
        $newSample = $this->demoService->createSample([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'description' => 'This is a sample record created using Eloquent model.'
        ]);
        
        // Get all samples
        $allSamples = $this->demoService->getSamples();
        
        header('Content-Type: application/json');
        echo json_encode([
            'message' => 'Sample operations completed',
            'new_sample' => $newSample,
            'all_samples' => $allSamples
        ], JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Compare API data vs Model data
     */
    public function comparison() {
        $apiPosts = $this->demoService->getPosts();
        $modelPosts = $this->demoService->getBlogPosts();
        
        header('Content-Type: application/json');
        echo json_encode([
            'api_posts' => [
                'source' => 'JSONPlaceholder API',
                'count' => count($apiPosts),
                'sample' => array_slice($apiPosts, 0, 2) // First 2 posts
            ],
            'model_posts' => [
                'source' => 'Eloquent Model (In-Memory SQLite)',
                'count' => count($modelPosts),
                'posts' => $modelPosts
            ]
        ], JSON_PRETTY_PRINT);
        exit;
    }
}