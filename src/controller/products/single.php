<?php

require_once 'classes/BaseProductController.php';

use src\Core\App; 
use src\Core\Database; 
use src\Core\Router;

$error = [];
$data = [];


class SingleProductController extends BaseProductController {

    protected string $slug;

    public function __construct() {
        
        parent::__construct();

        $this->slug = App::resolve(Router::class)->getSlug();        
        
    }

    public function single() {
        try {
            $this->data = $this->getProduct();
            if (empty($this->data)) {
                abort(404, [
                    "message" => "Product not found"
                ]);
            }

            viewsPath("products/single.view.php", [
                "data" => $this->data,
                "error" => $this->error
            ]);
        } catch (Exception $e) {
            abort(500, [
                "message" => "Failed to fetch product",
                "serverError" => $e
            ]);
        }
    }

    public function getProduct(): array {
        
        try {
            $column = ctype_digit($this->slug) ? "id" : "url";
            $sql = "SELECT p.*, GROUP_CONCAT(c.name SEPARATOR ', ') AS categories 
            FROM products p
            LEFT JOIN product_category pc ON p.id = pc.product_id
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
                "message" => "Failed to fetch product",
                "serverError" => $e
            ]);
        }
    }
}

$controller = new SingleProductController();
$controller->single();