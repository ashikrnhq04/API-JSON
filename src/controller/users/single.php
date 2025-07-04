<?php

use src\Core\App; 

$db = App::resolve("database");

$data = $db->query("select * from users where username = :username", [
    'username' => "ashik",
])->get();


viewsPath("users/single.view.php", [
    "data" => $data,
]);