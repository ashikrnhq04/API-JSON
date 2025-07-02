<?php 


function dd($value) {
    echo "<pre>";
    var_dump($value);
    echo "</pre>";
    die();

}



function viewsPath($filePath, $args = []) {

    extract($args);
    
    require(BASE_PATH . "views/{$filePath}");
}

function controllerPath($filePath,  $args = []) {

    extract($args);

    require BASE_PATH . "controller/{$filePath}";
}

function extractDynamicURIPattern($uri) {
    
    // replace dynamic slug with actual url part
    $pattern = preg_replace('#:([\w]+)#', '([^/]+)', $uri);
    $pattern = "#^" . $pattern . "$#";

    return $pattern; 
}

function h($str) {
    return htmlspecialchars(trim($str ?? ""));
}

// trim item after exploding
function toArray($str) {
    foreach(explode(",", $str) as $item) {
        $data[] = h($item);
    }

    return $data; 
}