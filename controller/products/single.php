<?php

$data = [];

viewsPath("products/single.view.php", [
    "slug" => $slug ?? null,
    "data" => $data
]);