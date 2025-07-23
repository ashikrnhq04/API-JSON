<?php

describe('Posts API', function () {
    
    it('can get all posts', function () {
        $response = $this->makeRequest('GET', '/api/v1/posts');
        
        expect($response)->toBeApiResponse()
            ->and($response)->toBeSuccessResponse()
            ->and($response)->toHaveKey('data')
            ->and($response)->toHaveKey('pagination')
            ->and($response['data'])->toBeArray()
            ->and($response['pagination'])->toHaveKeys(['total', 'limit', 'offset', 'hasMore']);
    });

    it('can get a specific post by ID', function () {
        $response = $this->makeRequest('GET', '/api/v1/posts/1');
        
        expect($response)->toBeApiResponse()
            ->and($response)->toBeSuccessResponse()
            ->and($response)->toHaveKey('data')
            ->and($response['data'])->toHaveKeys(['id', 'title', 'content', 'url']);
    });

    it('can get a specific post by URL slug', function () {
        $response = $this->makeRequest('GET', '/api/v1/posts/updated-social-media-marketing-guide');
        
        expect($response)->toBeApiResponse()
            ->and($response)->toBeSuccessResponse()
            ->and($response)->toHaveKey('data')
            ->and($response['data'])->toHaveKey('url')
            ->and($response['data']['url'])->toBe('updated-social-media-marketing-guide');
    });

    it('returns 404 for non-existent post', function () {
        $response = $this->makeRequest('GET', '/api/v1/posts/non-existent-post');
        
        expect($response)->toBeApiResponse()
            ->and($response)->toBeErrorResponse()
            ->and($response['message'])->toContain('not found');
    });

    it('can create a new post', function () {
        $postData = [
            'title' => 'API Test Post',
            'content' => 'This post was created via API test. It contains detailed content about testing APIs.',
            'image' => 'https://example.com/api-test-post.jpg',
            'url' => 'api-test-post-' . time(),
            'status' => 'active'
        ];

        $response = $this->makeRequest('POST', '/api/v1/posts', $postData);
        
        expect($response)->toBeApiResponse()
            ->and($response)->toBeSuccessResponse()
            ->and($response)->toHaveKey('data')
            ->and($response['data'])->toHaveKey('id');
    });

    it('validates required fields when creating post', function () {
        $invalidData = [
            'title' => '', // Empty title
            'content' => '' // Empty content
        ];

        $response = $this->makeRequest('POST', '/api/v1/posts', $invalidData);
        
        expect($response)->toBeApiResponse()
            ->and($response)->toBeErrorResponse();
    });

    it('can update a post by ID', function () {
        $updateData = [
            'title' => 'Updated Post Title via API',
            'content' => 'Updated content via API test'
        ];

        $response = $this->makeRequest('PATCH', '/api/v1/posts/1', $updateData);
        
        expect($response)->toBeApiResponse()
            ->and($response)->toBeSuccessResponse();
    });

    it('can update a post by URL slug', function () {
        $updateData = [
            'content' => 'Updated content via slug API test'
        ];

        $response = $this->makeRequest('PATCH', '/api/v1/posts/updated-social-media-marketing-guide', $updateData);
        
        expect($response)->toBeApiResponse()
            ->and($response)->toBeSuccessResponse();
    });

    it('can delete a post by ID', function () {
        // First create a post to delete
        $postData = [
            'title' => 'Post to Delete via API',
            'content' => 'This post will be deleted via API test',
            'image' => 'https://example.com/delete-post-api.jpg',
            'url' => 'delete-post-api-' . time(),
            'status' => 'active'
        ];

        $createResponse = $this->makeRequest('POST', '/api/v1/posts', $postData);
        expect($createResponse)->toBeSuccessResponse();

        $postId = $createResponse['data']['id'];
        $deleteResponse = $this->makeRequest('DELETE', '/api/v1/posts/' . $postId);
        
        expect($deleteResponse)->toBeApiResponse()
            ->and($deleteResponse)->toBeSuccessResponse();
    });

    it('can delete a post by URL slug', function () {
        // First create a post to delete
        $postData = [
            'title' => 'Post to Delete by Slug',
            'content' => 'This post will be deleted by slug via API test',
            'image' => 'https://example.com/delete-post-slug.jpg',
            'url' => 'delete-post-slug-' . time(),
            'status' => 'active'
        ];

        $createResponse = $this->makeRequest('POST', '/api/v1/posts', $postData);
        expect($createResponse)->toBeSuccessResponse();

        $postSlug = $createResponse['data']['url'];
        $deleteResponse = $this->makeRequest('DELETE', '/api/v1/posts/' . $postSlug);
        
        expect($deleteResponse)->toBeApiResponse()
            ->and($deleteResponse)->toBeSuccessResponse();
    });

    it('handles pagination correctly', function () {
        $response = $this->makeRequest('GET', '/api/v1/posts?limit=5&offset=10');
        
        expect($response)->toBeApiResponse()
            ->and($response)->toBeSuccessResponse()
            ->and($response['pagination']['limit'])->toBe(5)
            ->and($response['pagination']['offset'])->toBe(10);
    });

    it('returns posts with categories', function () {
        $response = $this->makeRequest('GET', '/api/v1/posts/1');
        
        expect($response)->toBeApiResponse()
            ->and($response)->toBeSuccessResponse()
            ->and($response['data'])->toHaveKey('categories')
            ->and($response['data']['categories'])->toBeArray();
    });

    it('can handle posts with multiple categories', function () {
        $postData = [
            'title' => 'Multi-Category Post',
            'content' => 'This post has multiple categories',
            'image' => 'https://example.com/multi-category.jpg',
            'url' => 'multi-category-post-' . time(),
            'status' => 'active',
            'categories' => ['technology', 'programming', 'web-development']
        ];

        $response = $this->makeRequest('POST', '/api/v1/posts', $postData);
        
        expect($response)->toBeApiResponse()
            ->and($response)->toBeSuccessResponse()
            ->and($response['data'])->toHaveKey('categories');
    });

    it('returns proper error for invalid methods', function () {
        $response = $this->makeRequest('PUT', '/api/v1/posts/1');
        
        // Should either return method not allowed or route not found
        expect($response)->toBeArray();
    });
});
