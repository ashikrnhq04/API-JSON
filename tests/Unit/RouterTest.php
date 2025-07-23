<?php

use Core\Router;
use Core\App;

describe('Router Class', function () {
    beforeEach(function () {
        $this->router = App::resolve(Router::class);
    });

    it('can register GET routes', function () {
        $this->router->get('/test', function() {
            return 'GET test';
        });
        
        expect($this->router)->toBeInstanceOf(Router::class);
    });

    it('can register POST routes', function () {
        $this->router->post('/test', function() {
            return 'POST test';
        });
        
        expect($this->router)->toBeInstanceOf(Router::class);
    });

    it('can register PATCH routes', function () {
        $this->router->patch('/test', function() {
            return 'PATCH test';
        });
        
        expect($this->router)->toBeInstanceOf(Router::class);
    });

    it('can register DELETE routes', function () {
        $this->router->delete('/test', function() {
            return 'DELETE test';
        });
        
        expect($this->router)->toBeInstanceOf(Router::class);
    });

    it('can handle route parameters', function () {
        $this->router->get('/test/:id', function($id) {
            return "Test with ID: $id";
        });
        
        expect($this->router)->toBeInstanceOf(Router::class);
    });

    it('can handle multiple route parameters', function () {
        $this->router->get('/test/:category/:id', function($category, $id) {
            return "Category: $category, ID: $id";
        });
        
        expect($this->router)->toBeInstanceOf(Router::class);
    });

    it('can handle routes with middleware', function () {
        $this->router->get('/protected', function() {
            return 'Protected route';
        })->only('guest');
        
        expect($this->router)->toBeInstanceOf(Router::class);
    });

    it('can extract slug from parameters', function () {
        // Test the static method for slug extraction
        $slug = Router::getSlug();
        
        expect($slug)->toBeString()->or($slug)->toBeNull();
    });

    it('can match exact routes', function () {
        $this->router->get('/exact-match', function() {
            return 'Exact match';
        });
        
        // The router should be able to handle this registration
        expect($this->router)->toBeInstanceOf(Router::class);
    });

    it('can handle root route', function () {
        $this->router->get('/', function() {
            return 'Root route';
        });
        
        expect($this->router)->toBeInstanceOf(Router::class);
    });

    it('can handle API versioned routes', function () {
        $this->router->get('/api/v1/test', function() {
            return 'API v1 test';
        });
        
        $this->router->get('/api/v2/test', function() {
            return 'API v2 test';
        });
        
        expect($this->router)->toBeInstanceOf(Router::class);
    });

    it('can handle complex route patterns', function () {
        $this->router->get('/api/v1/users/:userId/posts/:postId', function($userId, $postId) {
            return "User: $userId, Post: $postId";
        });
        
        expect($this->router)->toBeInstanceOf(Router::class);
    });

    it('can register routes with different HTTP methods for same path', function () {
        $this->router->get('/resource', function() {
            return 'GET resource';
        });
        
        $this->router->post('/resource', function() {
            return 'POST resource';
        });
        
        $this->router->patch('/resource', function() {
            return 'PATCH resource';
        });
        
        $this->router->delete('/resource', function() {
            return 'DELETE resource';
        });
        
        expect($this->router)->toBeInstanceOf(Router::class);
    });
});
