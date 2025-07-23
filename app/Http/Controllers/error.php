<?php 

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

echo json_encode([
    "status" => "error",
    "ok" => false,
    "message" => $error["message"] ?? "An unexpected error occurred.",
])

?>