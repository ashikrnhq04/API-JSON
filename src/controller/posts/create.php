<?php

use src\Core\Requests; 
use src\Core\App; 
use src\Core\Database; 
use src\Core\SchemaManager;

require_once "classes/BasePostController.php";

class PostSaveController extends BasePostController {

    protected $validationRules = [
        "title" => "required|string|min:2",
        "content" => "required|string|min:5",
        "image" => "url",
        "categories" => "string",
    ];

    public function save(): void {
        $request = Requests::make()->validate($this->validationRules);
        if ($request->fails()) {
            abort(400, [
                "message" => $request->errors()
            ]);
        }

        $this->handleTableOperations();

        $input = $request->all();

        // mimic the create post operation
        if($_ENV["APP_ENV"] === "production") {
            echo json_encode([
                "status" => "success",
                "message" => "Post saved successfully",
            ]);
            return;
        } 

        try {

            // extract categories to insert to the DB seperately
            $categories = isset($input["categories"]) ? explode(",", $input["categories"]) : [];

            
            // catch and insert only the right data to posts table
            $postData = ["title", "content", "image", "url"];

            if (!$this->db->beginTransaction()) {
                throw new \Exception("Failed to start database transaction");
            }


            $this->db->insert("posts", array_intersect_key([...$input, 'url' => toSlug($input['title'])], array_flip($postData)));

            // get the last inserted post id
            $postId = $this->db->lastInsertId();

            if (empty($postId)) {
                abort(500, ["message" => "Failed to save the post"]);
            }

            
            // Handle categories
            $this->handleCategoryOperations($postId, $categories ?? []);

            // Commit transaction
            $this->db->commit();

            http_response_code(201);
            echo json_encode([
                "status" => "success",
                "message" => "Post saved successfully",
            ]);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            abort(500, [
                "message" => "Failed to save the post",
                "serverError" => $e
            ]);
        }
    }

}

$postSave = new PostSaveController();
$postSave->save();