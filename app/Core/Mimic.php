<?php 

namespace Core;

use Views\JsonView;

class Mimic {
    public static function success($message = 'Success'): void {
        JsonView::success(null, $message);
    }
    
    /**
     * Generate mock post response for production environment
     */
    public static function generateMockPost(array $input): array {
        return [
            'id' => rand(1000, 9999),
            'title' => $input['title'] ?? 'Sample Post Title',
            'content' => $input['content'] ?? 'Sample post content for testing purposes.',
            'image' => $input['image'] ?? 'https://via.placeholder.com/600x400',
            'url' => toSlug($input['title'] ?? 'sample-post-title'),
            'status' => 'active',
            'categories' => self::parseCategoriesForMock($input['categories'] ?? ''),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Generate mock product response for production environment
     */
    public static function generateMockProduct(array $input): array {
        return [
            'id' => rand(1000, 9999),
            'title' => $input['title'] ?? 'Sample Product Title',
            'description' => $input['description'] ?? 'Sample product description for testing purposes.',
            'price' => $input['price'] ?? 99.99,
            'image' => $input['image'] ?? 'https://via.placeholder.com/600x400',
            'url' => toSlug($input['title'] ?? 'sample-product-title'),
            'status' => 'active',
            'categories' => self::parseCategoriesForMock($input['categories'] ?? ''),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Parse categories for mock response
     */
    private static function parseCategoriesForMock($categories): array {
        if (empty($categories)) {
            return [];
        }
        
        if (is_string($categories)) {
            return array_filter(array_map('trim', explode(',', $categories)));
        }
        
        if (is_array($categories)) {
            return array_filter($categories);
        }
        
        return [];
    }
}