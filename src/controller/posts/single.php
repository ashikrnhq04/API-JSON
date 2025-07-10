<?php

require_once 'classes/BasePostController.php';

use src\Core\App; 
use src\Core\Database; 
use src\Core\Router;

$error = [];
$data = [];


class SinglePostController extends BasePostController {

    protected string $slug;

    public function __construct() {
        
        parent::__construct();

        $this->slug = App::resolve(Router::class)->getSlug();        
        
    }

    public function single() {
        try {
            $this->data = $this->getPost();

            viewsPath("posts/single.view.php", [
                "data" => $this->data,
                "error" => $this->error
            ]);
            
        } catch (Exception $e) {
            abort(500, [
                "message" => "Failed to fetch post",
                "serverError" => $e
            ]);
        }
    }

    public function getPost(): array {
        
        try {
            $column = ctype_digit($this->slug) ? "id" : "url";
            $sql = "SELECT p.*, GROUP_CONCAT(c.name SEPARATOR ', ') AS categories 
            FROM posts p
            LEFT JOIN post_category pc ON p.id = pc.post_id
            LEFT JOIN categories c ON c.id = pc.category_id 
            WHERE p.`{$column}` = :{$column}
            GROUP BY p.id LIMIT 1";

            $result = $this->db->query($sql)->execute([$column => $this->slug])->fetch();

            return $result ? array_merge($result, [
                'categories' => isset($result['categories']) && $result['categories'] !== null
                    ? array_map('trim', explode(',', $result['categories']))
                    : []
            ]) : [];
            
        } catch (\Exception $e) {
            abort(500, [
                "message" => "Failed to fetch post",
                "serverError" => $e
            ]);
        }
    }
}

$controller = new SinglePostController();
$controller->single();