<?php
use classes\Requests; 

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$request = Requests::make();

if ($request->hasError()) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "ok" => false,
        "message" => $request->errors()
    ], JSON_PRETTY_PRINT);
    exit;
}

$data = $request->all();

$response = [
    "version" => "1.0.0",
    "status" => "success",
    "ok" => true,
    "data" => $data,
];

echo json_encode($response, JSON_PRETTY_PRINT);

exit;