<?php 

use classes\Database; 

$dbconfig = require BASE_PATH . "config.php";

$db = new Database($dbconfig['database'], "root", "phpmyadmin");

$data = $db->query("select * from users")->get();

viewsPath("users/index.view.php", [
    "data" => $data,
]);