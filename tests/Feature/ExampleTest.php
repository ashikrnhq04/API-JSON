<?php

describe('Example Feature Test', function () {
    it('can test application features', function () {
        expect(true)->toBe(true);
    });

    it('can test API response structure', function () {
        $mockResponse = [
            'version' => '1.2.8',
            'status' => 'success',
            'ok' => true,
            'message' => 'Test message'
        ];
        
        expect($mockResponse)->toBeApiResponse()
            ->and($mockResponse)->toBeSuccessResponse();
    });

    it('can test error response structure', function () {
        $mockErrorResponse = [
            'version' => '1.2.8',
            'status' => 'error',
            'ok' => false,
            'message' => 'Test error message'
        ];
        
        expect($mockErrorResponse)->toBeApiResponse()
            ->and($mockErrorResponse)->toBeErrorResponse();
    });

    it('can test application constants', function () {
        expect(defined('BASE_PATH'))->toBeTrue();
        expect(BASE_PATH)->toContain('commercio');
    });
});
