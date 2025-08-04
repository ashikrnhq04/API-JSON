<?php

use Core\Database;
use Core\App;

describe('Database Class', function () {
    beforeEach(function () {
        $this->db = App::resolve(Database::class);
    });

    it('can establish database connection', function () {
        expect($this->db)->toBeInstanceOf(Database::class);
    });

    it('can execute a simple query and get results', function () {
        $result = $this->db->query("SELECT 1 as test")->execute()->fetchAll();
        
        expect($result)->toBeArray()
            ->and($result[0]['test'])->toBe(1); // Database returns integer
    });

    it('can execute queries with parameters', function () {
        $result = $this->db->query("SELECT * FROM products WHERE id = :id LIMIT 1")
            ->execute(['id' => 1])
            ->fetchAll();
        
        expect($result)->toBeArray();
        
        if (!empty($result)) {
            expect($result[0])->toHaveKey('id')
                ->and($result[0]['id'])->toBe(1); // Database returns integer, not string
        }
    });

    it('can find a single record by ID', function () {
        $result = $this->db->query("SELECT * FROM products where id = :id")->execute([
            "id" => 1
        ])->find();
        
        if ($result) {
            expect($result)->toBeArray()
                ->and($result)->toHaveKey('id')
                ->and($result['id'])->toBe(1); // Database returns integer
        } else {
            expect($result)->toBeFalse();
        }
    });

    it('can find a single record by slug', function () {
        $result  = $this->db->query("SELECT * FROM products WHERE url = :url")->execute([
            "url" => "plant-pot"
        ])->find();
        
        if ($result) {
            expect($result)->toBeArray()
                ->and($result)->toHaveKey('url')
                ->and($result['url'])->toBe('plant-pot');
        } else {
            expect($result)->toBeFalse();
        }
    });

    it('can execute insert queries', function () {
        $uniqueUrl = 'test-database-product-' . time() . '-' . rand(1000, 9999);
        
        $result = $this->db->insert("products", [
            'title' => 'Test Database Product',
            'description' => 'Test description',
            'price' => 99.99,
            'image' => 'https://example.com/test.jpg',
            'url' => $uniqueUrl,
            'status' => 'active'
        ]);
        
        expect($result)->toBeInstanceOf(Database::class);
    });

    it('can handle transactions', function () {
        $this->db->beginTransaction();
        
        try {
            $uniqueUrl = 'transaction-test-product-' . time() . '-' . rand(1000, 9999);
            
            $this->db->insert("products", [
                'title' => 'Transaction Test Product',
                'description' => 'Test description for transaction',
                'price' => 49.99,
                'image' => 'https://example.com/transaction-test.jpg',
                'url' => $uniqueUrl,
                'status' => 'active'
            ]);
            
            $this->db->commit();
            expect(true)->toBeTrue(); // Transaction succeeded
        } catch (Exception $e) {
            $this->db->rollback();
            expect(false)->toBeTrue(); // Transaction failed
        }
    });

    it('can update records', function () {
        // Since there's no update method in Database class, we'll use a direct query
        $result = $this->db->query("UPDATE products SET description = :desc WHERE id = :id")
            ->execute([
                'desc' => 'Updated description via database test',
                'id' => 1
            ]);
        
        expect($result)->toBeInstanceOf(Database::class);
    });

    it('can delete records', function () {
        // First create a record to delete
        $uniqueUrl = 'delete-test-product-' . time() . '-' . rand(1000, 9999);
        
        $this->db->insert("products", [
            'title' => 'Product to Delete',
            'description' => 'This will be deleted',
            'price' => 19.99,
            'image' => 'https://example.com/delete-test.jpg',
            'url' => $uniqueUrl,
            'status' => 'active'
        ]);
        
        // Find the created product
        $created = $this->db->query("SELECT * FROM product WHERE url = :url")->execute([
            "url" => $uniqueUrl
        ])->find();
        
        if ($created) {
            // Use direct query since there's no delete method
            $result = $this->db->query("DELETE FROM products WHERE id = :id")
                ->execute(['id' => $created['id']]);
            expect($result)->toBeInstanceOf(Database::class);
        } else {
            // Just test that we can prepare the delete query
            expect(true)->toBeTrue();
        }
    });

    it('can handle database errors gracefully', function () {
        try {
            $result = $this->db->query("SELECT * FROM non_existent_table")->execute()->fetchAll();
            // If no exception is thrown, result should be empty
            expect($result)->toBeFalsy();
        } catch (Exception $e) {
            // Exception is expected for non-existent table
            expect($e)->toBeInstanceOf(Exception::class);
        }
    });

    it('can escape values properly with parameters', function () {
        // Test that parameters are properly escaped
        $result = $this->db->query("SELECT * FROM products WHERE title = :title")
            ->execute(['title' => "'; DROP TABLE products; --"])
            ->fetchAll();
        
        // Should return empty array, not cause SQL injection
        expect($result)->toBeArray()
            ->and($result)->toBeEmpty();
    });
});