<?php

header("Content-type: application/json");
header("Access-Control-Allow-Origin: *");

echo json_encode([
    "version" => $_ENV["APP_VERSION"] ?? "1.0.0",
    "status" => "success",
    "ok" => true,
    "data" => (object) $data,
]);

exit;