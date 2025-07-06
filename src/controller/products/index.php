<?php 

use src\Core\App; 
use src\Core\Database; 

require_once 'classes/BaseProductController.php';

class ProductIndexController extends BaseProductController {
    
    public function index(): void {
        try {
            $this->data = $this->getAllProducts();
        } catch (Exception $e) {
            abort(500, [
                "message" => "Failed to fetch products",
                "serverError" => $e
            ]);
        }

        viewsPath("products/index.view.php", [
            "data" => $this->data,
            "error" => $this->error
        ]);
    }

    private function getAllProducts(): array {
        $sql = "SELECT p.*, GROUP_CONCAT(c.name SEPARATOR ', ') as categories 
                FROM products p
                LEFT JOIN product_category pc ON p.id = pc.product_id
                LEFT JOIN categories c ON c.id = pc.category_id 
                GROUP BY p.id
                ORDER BY p.created_at DESC"; 
        
        $results = $this->db->query($sql)->execute()->fetchAll();
        
        // Convert categories string to array
        foreach ($results as &$product) {
            $product['categories'] = !empty($product['categories']) 
                ? explode(', ', $product['categories']) 
                : [];
        }
        
        return $results;
    }
}

$controller = new ProductIndexController();
$controller->index();