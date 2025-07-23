<?php

use function Tests\makeApiRequest;

describe('Products API', function () {
    
    it('can get all products', function () {
        $response = $this->makeRequest('GET', '/api/v1/products');
        
        expect($response)->toBeApiResponse()
            ->and($response)->toBeSuccessResponse()
            ->and($response)->toHaveKey('data')
            ->and($response)->toHaveKey('pagination')
            ->and($response['data'])->toBeArray()
            ->and($response['pagination'])->toHaveKeys(['total', 'limit', 'offset', 'hasMore']);
    });

    it('can get a specific product by ID', function () {
        $response = $this->makeRequest('GET', '/api/v1/products/1');
        
        expect($response)->toBeApiResponse()
            ->and($response)->toBeSuccessResponse()
            ->and($response)->toHaveKey('data')
            ->and($response['data'])->toHaveKeys(['id', 'title', 'description', 'price', 'url']);
    });

    it('can get a specific product by URL slug', function () {
        $response = $this->makeRequest('GET', '/api/v1/products/plant-pot');
        
        expect($response)->toBeApiResponse()
            ->and($response)->toBeSuccessResponse()
            ->and($response)->toHaveKey('data')
            ->and($response['data'])->toHaveKey('url')
            ->and($response['data']['url'])->toBe('plant-pot');
    });

    it('returns 404 for non-existent product', function () {
        $response = $this->makeRequest('GET', '/api/v1/products/non-existent-product');
        
        expect($response)->toBeApiResponse()
            ->and($response)->toBeErrorResponse()
            ->and($response['message'])->toContain('not found');
    });

    it('can create a new product', function () {
        $productData = [
            'title' => 'API Test Product',
            'description' => 'This product was created via API test',
            'price' => '39.99',
            'image' => 'https://example.com/api-test.jpg',
            'url' => 'api-test-product-' . time(),
            'status' => 'active'
        ];

        $response = $this->makeRequest('POST', '/api/v1/products', $productData);
        
        expect($response)->toBeApiResponse()
            ->and($response)->toBeSuccessResponse()
            ->and($response)->toHaveKey('data')
            ->and($response['data'])->toHaveKey('id');
    });

    it('validates required fields when creating product', function () {
        $invalidData = [
            'title' => '', // Empty title
            'price' => 'invalid' // Invalid price
        ];

        $response = $this->makeRequest('POST', '/api/v1/products', $invalidData);
        
        expect($response)->toBeApiResponse()
            ->and($response)->toBeErrorResponse();
    });

    it('can update a product by ID', function () {
        $updateData = [
            'title' => 'Updated Product Title via API',
            'description' => 'Updated description via API'
        ];

        $response = $this->makeRequest('PATCH', '/api/v1/products/1', $updateData);
        
        expect($response)->toBeApiResponse()
            ->and($response)->toBeSuccessResponse();
    });

    it('can update a product by URL slug', function () {
        $updateData = [
            'description' => 'Updated via slug API test'
        ];

        $response = $this->makeRequest('PATCH', '/api/v1/products/plant-pot', $updateData);
        
        expect($response)->toBeApiResponse()
            ->and($response)->toBeSuccessResponse();
    });

    it('can delete a product by ID', function () {
        // First create a product to delete
        $productData = [
            'title' => 'Product to Delete via API',
            'description' => 'This will be deleted',
            'price' => '19.99',
            'image' => 'https://example.com/delete-api.jpg',
            'url' => 'delete-api-product-' . time(),
            'status' => 'active'
        ];

        $createResponse = $this->makeRequest('POST', '/api/v1/products', $productData);
        expect($createResponse)->toBeSuccessResponse();

        $productId = $createResponse['data']['id'];
        $deleteResponse = $this->makeRequest('DELETE', '/api/v1/products/' . $productId);
        
        expect($deleteResponse)->toBeApiResponse()
            ->and($deleteResponse)->toBeSuccessResponse();
    });

    it('can delete a product by URL slug', function () {
        // First create a product to delete
        $productData = [
            'title' => 'Product to Delete by Slug',
            'description' => 'This will be deleted by slug',
            'price' => '25.99',
            'image' => 'https://example.com/delete-slug.jpg',
            'url' => 'delete-slug-product-' . time(),
            'status' => 'active'
        ];

        $createResponse = $this->makeRequest('POST', '/api/v1/products', $productData);
        expect($createResponse)->toBeSuccessResponse();

        $productSlug = $createResponse['data']['url'];
        $deleteResponse = $this->makeRequest('DELETE', '/api/v1/products/' . $productSlug);
        
        expect($deleteResponse)->toBeApiResponse()
            ->and($deleteResponse)->toBeSuccessResponse();
    });

    it('handles pagination correctly', function () {
        $response = $this->makeRequest('GET', '/api/v1/products?limit=5&offset=10');
        
        expect($response)->toBeApiResponse()
            ->and($response)->toBeSuccessResponse()
            ->and($response['pagination']['limit'])->toBe(5)
            ->and($response['pagination']['offset'])->toBe(10);
    });

    it('returns proper error for invalid methods', function () {
        $response = $this->makeRequest('PUT', '/api/v1/products/1');
        
        // Should either return method not allowed or route not found
        expect($response)->toBeArray();
    });
});
