<?php 

use src\Core\App; 
use src\Core\Database; 

require_once 'classes/BasePostController.php';

class PostIndexController extends BasePostController {
    
    public function index(): void {
        try {
            $this->data = $this->getAllPosts();
        } catch (Exception $e) {
            abort(500, [
                "message" => "Failed to fetch posts",
                "serverError" => $e
            ]);
        }

        viewsPath("posts/index.view.php", [
            "data" => $this->data,
            "error" => $this->error
        ]);
    }

    private function getAllPosts(): array {
        $sql = "SELECT p.*, GROUP_CONCAT(c.name SEPARATOR ', ') as categories 
                FROM posts p
                LEFT JOIN post_category pc ON p.id = pc.post_id
                LEFT JOIN categories c ON c.id = pc.category_id 
                GROUP BY p.id"; 
        
        $results = $this->db->query($sql)->execute()->fetchAll();
        
        // Convert categories string to array
        foreach ($results as &$post) {
            $post['categories'] = !empty($post['categories']) 
                ? explode(', ', $post['categories']) 
                : [];
        }
        
        return $results;
    }
}

$controller = new PostIndexController();
$controller->index();