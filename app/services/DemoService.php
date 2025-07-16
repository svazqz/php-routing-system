<?php

namespace Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Core\Providers\EloquentServiceProvider;
use Models\Blog;
use Models\Sample;
use Illuminate\Database\Capsule\Manager as Capsule;
use Carbon\Carbon;

class DemoService {
    private Client $client;
    private string $baseUrl = 'https://jsonplaceholder.typicode.com';

    public function __construct()
    {
        $this->client = new Client();
        $this->initializeInMemoryDatabase();
    }

    /**
     * Initialize in-memory SQLite database for demonstration
     */
    private function initializeInMemoryDatabase()
    {
        // Configure in-memory SQLite database
        $capsule = new Capsule;
        $capsule->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        
        // Create tables and seed data
        Blog::seedData();
        Sample::createTable();
    }

    public function getPosts()
    {
        try {
            $response = $this->client->get($this->baseUrl . '/posts');
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function getPost(int $id)
    {
        try {
            $response = $this->client->get($this->baseUrl . '/posts/' . $id);
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function getUsers()
    {
        try {
            $response = $this->client->get($this->baseUrl . '/users');
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function getUser(int $id)
    {
        try {
            $response = $this->client->get($this->baseUrl . '/users/' . $id);
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function getComments(?int $postId = null)
    {
        try {
            $url = $this->baseUrl . '/comments';
            if ($postId) {
                $url .= '?postId=' . $postId;
            }
            $response = $this->client->get($url);
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // ===== ELOQUENT MODEL METHODS =====

    /**
     * Get blog posts from Eloquent model (in-memory database)
     */
    public function getBlogPosts()
    {
        try {
            return Blog::published()
                      ->orderBy('published_at', 'desc')
                      ->get()
                      ->toArray();
        } catch (\Exception $e) {
            return ['error' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Get a single blog post by ID from Eloquent model
     */
    public function getBlogPost(int $id)
    {
        try {
            $post = Blog::published()->find($id);
            return $post ? $post->toArray() : null;
        } catch (\Exception $e) {
            return ['error' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Create a new blog post using Eloquent model
     */
    public function createBlogPost(array $data)
    {
        try {
            $post = Blog::create([
                'title' => $data['title'] ?? '',
                'content' => $data['content'] ?? '',
                'author' => $data['author'] ?? 'Anonymous',
                'published_at' => $data['published_at'] ?? Carbon::now()
            ]);
            return $post->toArray();
        } catch (\Exception $e) {
            return ['error' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Get recent blog posts (last 30 days)
     */
    public function getRecentBlogPosts()
    {
        try {
            return Blog::published()
                      ->recent(30)
                      ->orderBy('published_at', 'desc')
                      ->get()
                      ->toArray();
        } catch (\Exception $e) {
            return ['error' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Demonstrate Sample model usage
     */
    public function createSample(array $data)
    {
        try {
            $sample = Sample::create([
                'name' => $data['name'] ?? '',
                'email' => $data['email'] ?? '',
                'description' => $data['description'] ?? ''
            ]);
            return $sample->toArray();
        } catch (\Exception $e) {
            return ['error' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Get all samples
     */
    public function getSamples()
    {
        try {
            return Sample::all()->toArray();
        } catch (\Exception $e) {
            return ['error' => 'Database error: ' . $e->getMessage()];
        }
    }
}