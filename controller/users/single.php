<?php

use classes\Database; 

$dbconfig = require(BASE_PATH . "config.php");

$db = new Database($dbconfig['database'], 'root', 'phpmyadmin');

dd($_SERVER['REQUEST_URI']);

$data = $db->query("select * from users where username = :username", [
    'username' => "ashik",
])->get();


viewsPath("users/single.view.php", [
    "data" => $data,
]);