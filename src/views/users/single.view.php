<?php

header('content-type: application/json');
header('Access-Control-Allow-Origin: *');

$response = [
    "description" => "Single user data API endpoint",
    'version' => "1.0.0",
    "ok" => true,
    "status" => "success",
    "data" => $data,
];

echo json_encode(
    $response, JSON_PRETTY_PRINT
);