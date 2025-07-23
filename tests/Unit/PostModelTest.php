<?php

use Models\Post;
use Core\App;
use Core\Database;

describe('Post Model', function () {
    beforeEach(function () {
        $this->post = new Post();
    });

    it('can create a new post', function () {
        $postData = [
            'title' => 'Test Post',
            'content' => 'Test Content for the post',
            'image' => 'https://example.com/post-image.jpg',
            'url' => 'test-post',
            'status' => 'active'
        ];

        $result = $this->post->create($postData);
        
        expect($result)->toBeArray()
            ->and($result)->toHaveKey('success')
            ->and($result['success'])->toBeTrue();
    });

    it('can find a post by ID', function () {
        $post = $this->post->find(1);
        
        expect($post)->toBeArray()
            ->and($post)->toHaveKeys(['id', 'title', 'content', 'url']);
    });

    it('can find a post by URL slug', function () {
        $post = $this->post->findBySlug('updated-social-media-marketing-guide');
        
        expect($post)->toBeArray()
            ->and($post)->toHaveKeys(['id', 'title', 'content', 'url'])
            ->and($post['url'])->toBe('updated-social-media-marketing-guide');
    });

    it('can get all posts with pagination', function () {
        $result = $this->post->getAll(1, 10);
        
        expect($result)->toBeArray()
            ->and($result)->toHaveKeys(['data', 'pagination'])
            ->and($result['data'])->toBeArray()
            ->and($result['pagination'])->toHaveKeys(['total', 'limit', 'offset', 'hasMore']);
    });

    it('can update a post by ID', function () {
        $updateData = [
            'title' => 'Updated Post Title',
            'content' => 'Updated post content'
        ];

        $result = $this->post->update(1, $updateData);
        
        expect($result)->toBeArray()
            ->and($result)->toHaveKey('success')
            ->and($result['success'])->toBeTrue();
    });

    it('can delete a post by ID', function () {
        // First create a post to delete
        $postData = [
            'title' => 'Post to Delete',
            'content' => 'This post will be deleted',
            'image' => 'https://example.com/delete-post.jpg',
            'url' => 'post-to-delete',
            'status' => 'active'
        ];

        $createResult = $this->post->create($postData);
        expect($createResult['success'])->toBeTrue();

        $postId = $createResult['data']['id'];
        $deleteResult = $this->post->delete($postId);
        
        expect($deleteResult)->toBeArray()
            ->and($deleteResult)->toHaveKey('success')
            ->and($deleteResult['success'])->toBeTrue();
    });

    it('validates post data correctly', function () {
        $invalidData = [
            'title' => '', // Empty title should fail
            'content' => '' // Empty content should fail
        ];

        $result = $this->post->create($invalidData);
        
        expect($result)->toBeArray()
            ->and($result)->toHaveKey('success')
            ->and($result['success'])->toBeFalse();
    });

    it('can search posts by title', function () {
        $results = $this->post->search('Social Media');
        
        expect($results)->toBeArray();
        
        if (!empty($results)) {
            expect($results[0])->toHaveKeys(['id', 'title', 'content']);
        }
    });

    it('can handle post categories', function () {
        $post = $this->post->find(1);
        
        expect($post)->toBeArray()
            ->and($post)->toHaveKey('categories')
            ->and($post['categories'])->toBeArray();
    });
});
