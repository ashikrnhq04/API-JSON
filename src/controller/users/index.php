<?php 


use src\Core\App; 

$db = App::resolve("database");


$data = $db->query("select * from users")->get();

viewsPath("users/index.view.php", [
    "data" => $data,
]);