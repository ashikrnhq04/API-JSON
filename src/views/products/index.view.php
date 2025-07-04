<?php

header("content-type: application/json");
header("Access-Control-Allow-Origin: *");

echo json_encode([
    "version" => "1.0.0",
    "status" => empty($error) ? "success" : "error",
    "ok" => empty($error) ? true : false,
    "data" => $data,
]);

exit;