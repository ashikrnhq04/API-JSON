<?php 


function dd($value) {
    echo "<pre>";
    var_dump($value);
    echo "</pre>";
    die();

}



function viewsPath($filePath, $args = []) {

    extract($args);
    
    require(BASE_PATH . "src/views/{$filePath}");
}

function controllerPath($filePath,  $args = []) {

    extract($args);

    require BASE_PATH . "src/controller/{$filePath}";
}

function schemaPath($filePath) {

    require BASE_PATH . "src/schema/{$filePath}";
    
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

function toSlug($str): string {
    $str = strtolower($str);
    $str = preg_replace("#[^a-z0-9\s-]#", "", $str);
    $str = preg_replace("#[\s-]+#", "-", $str);
    // $str = preg_replace("#-+#", "-", $str);
    $str = trim($str, "-");
    return $str; 
}


function abort($statusCode = 500, array $error) {
    http_response_code($statusCode);
    // actual server side/script error
    if(isset($error["serverError"]) && $error["serverError"] instanceof \Throwable) {

        $serverError = $error["serverError"];

        error_log($serverError->getMessage() . "\n" . $serverError->getTraceAsString());
        unset($error["serverError"]);
        
    }

    controllerPath("error.php", [
        "error" => $error
    ]);
    exit;
}

function paramGuard($value, string $type, string $message): void
{
    if (empty($value)) {
        throw new \InvalidArgumentException($message);
    }

    if (gettype($value) !== $type) {
        throw new \InvalidArgumentException("Expected type {$type}, got " . gettype($value), 500);
    }
}

function dbCheck($db, string $message): void
{
    if (empty($db)) {
        throw new \RuntimeException($message);
    }
}