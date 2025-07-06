<?php

use src\Core\Requests; 

$toBeValidated = [
    "title" => "required|string|min:2",
    "description" => "required|string|min:5",
    "price" => "required|float",
    "image" => "required|url",
    "category" => "string",
];

$request = Requests::make()->validate($toBeValidated);

if($request->fails()) {
    abort(400, [
        "message" => $request->errors()
    ]);
}

require "save.php"; 