<?php

namespace Views;

class JsonView {
    
    /**
     * Send success response
     */
    public static function success($data = null, string $message = 'Success', int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        
        echo json_encode([
            'version' => '1.2.8',
            'status' => 'success',
            'ok' => true,
            'message' => $message,
            'data' => $data
        ]);
    }
    
    /**
     * Send error response  
     */
    public static function error(string $message = 'Error', int $statusCode = 500, array $errors = []): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        
        echo json_encode([
            'version' => '1.2.8',
            'status' => 'error',
            'ok' => false,
            'message' => $message,
            'errors' => $errors
        ]);
    }
    
    /**
     * Send validation error response
     */
    public static function validationError(array $errors, string $message = 'Validation failed'): void {
        self::error($message, 422, $errors);
    }
    
    /**
     * Send not found response
     */
    public static function notFound(string $message = 'Resource not found'): void {
        http_response_code(200);
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        
        echo json_encode([
            'version' => '1.2.8',
            'status' => 'success',
            'ok' => true,
            'message' => $message,
            'data' => null
        ]);
    }
    
    /**
     * Send success response with pagination
     */
    public static function successWithPagination($data, array $pagination = [], string $message = 'Success'): void {
        http_response_code(200);
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        
        echo json_encode([
            'version' => '1.2.8',
            'status' => 'success',
            'ok' => true,
            'message' => $message,
            'pagination' => $pagination,
            'data' => $data
        ]);
    }
}