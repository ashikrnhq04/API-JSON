<?php 


use src\Core\App; 


$db = App::resolve("database");


$data = $db->query("select * from users")->get();


viewsPath("products/index.view.php", [
    "data" => $data
]);