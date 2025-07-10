<?php 

require_once 'classes/BaseProductController.php';

class ProductDestroyController extends BaseProductController {

    public function destroy(string $slug): void {
        try {
            $column = ctype_digit($slug) ? "id" : "url";
            
            $product = $this->db->select("products", ["id"], [$column => $slug]);
            
            if (!$product) {
                abort(404, ["message" => "Product not found"]);
            }
            
            $productId = $product[0]["id"];

            // start transaction
            if (!$this->db->beginTransaction()) {
                throw new \Exception("Failed to start database transaction");
            }
            
            // Delete related category associations first
            $this->db->delete("product_category", ["product_id" => $productId]);
            
            // Delete the product
            $this->db->delete("products", ["id" => $productId]);

            // Commit transaction
            $this->db->commit();
            
            echo json_encode([
                "status" => "success",
                "message" => "Product deleted successfully",
            ]);
            
        } catch (Exception $e) {
            $this->db->rollBack();
            abort(500, [
                "message" => "Failed to delete product",
                "serverError" => $e
            ]);
        }
    }
}

$controller = new ProductDestroyController();
$controller->destroy($slug);