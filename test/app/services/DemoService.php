<?php

namespace Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class DemoService {
    private Client $client;
    private string $baseUrl = 'https://jsonplaceholder.typicode.com';

    public function __construct()
    {
        $this->client = new Client();
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
}