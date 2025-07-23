<?php

use Models\Product;
use Core\App;
use Core\Database;

describe('Product Model', function () {
    beforeEach(function () {
        $this->product = new Product();
    });

    it('can create a new product', function () {
        $productData = [
            'title' => 'Test Product',
            'description' => 'Test Description',
            'price' => '29.99',
            'image' => 'https://example.com/image.jpg',
            'url' => 'test-product',
            'status' => 'active'
        ];

        $result = $this->product->create($productData);
        
        expect($result)->toBeArray()
            ->and($result)->toHaveKey('success')
            ->and($result['success'])->toBeTrue();
    });

    it('can find a product by ID', function () {
        $product = $this->product->find(1);
        
        expect($product)->toBeArray()
            ->and($product)->toHaveKeys(['id', 'title', 'description', 'price', 'url']);
    });

    it('can find a product by URL slug', function () {
        $product = $this->product->findBySlug('plant-pot');
        
        expect($product)->toBeArray()
            ->and($product)->toHaveKeys(['id', 'title', 'description', 'price', 'url'])
            ->and($product['url'])->toBe('plant-pot');
    });

    it('can get all products with pagination', function () {
        $result = $this->product->getAll(1, 10);
        
        expect($result)->toBeArray()
            ->and($result)->toHaveKeys(['data', 'pagination'])
            ->and($result['data'])->toBeArray()
            ->and($result['pagination'])->toHaveKeys(['total', 'limit', 'offset', 'hasMore']);
    });

    it('can update a product by ID', function () {
        $updateData = [
            'title' => 'Updated Product Title',
            'description' => 'Updated description'
        ];

        $result = $this->product->update(1, $updateData);
        
        expect($result)->toBeArray()
            ->and($result)->toHaveKey('success')
            ->and($result['success'])->toBeTrue();
    });

    it('can delete a product by ID', function () {
        // First create a product to delete
        $productData = [
            'title' => 'Product to Delete',
            'description' => 'This will be deleted',
            'price' => '19.99',
            'image' => 'https://example.com/delete.jpg',
            'url' => 'product-to-delete',
            'status' => 'active'
        ];

        $createResult = $this->product->create($productData);
        expect($createResult['success'])->toBeTrue();

        $productId = $createResult['data']['id'];
        $deleteResult = $this->product->delete($productId);
        
        expect($deleteResult)->toBeArray()
            ->and($deleteResult)->toHaveKey('success')
            ->and($deleteResult['success'])->toBeTrue();
    });

    it('validates product data correctly', function () {
        $invalidData = [
            'title' => '', // Empty title should fail
            'price' => 'invalid-price' // Invalid price should fail
        ];

        $result = $this->product->create($invalidData);
        
        expect($result)->toBeArray()
            ->and($result)->toHaveKey('success')
            ->and($result['success'])->toBeFalse();
    });

    it('can search products by title', function () {
        $results = $this->product->search('Plant');
        
        expect($results)->toBeArray();
        
        if (!empty($results)) {
            expect($results[0])->toHaveKeys(['id', 'title', 'description']);
        }
    });
});
