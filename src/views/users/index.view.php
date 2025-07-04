<?php 

header('content-type: application/json');
header('Access-Control-Allow-Origin: *');

$response = [
    "description" => "Users endpoint API to fetch all the users data.",
    "version" => "1.0.0",
    "data" => $data
];

echo json_encode($response, JSON_PRETTY_PRINT);
exit;


?>