<?php 
declare(strict_types=1);

namespace App\Controllers;

use App\Services\CategoryService;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Valitron\Validator;

class CategoryController {
    public function __construct(private CategoryService $categoryService, private Validator $validator) {    }
    public function add_category(Request $request, Response $response): Response {
        $request_body = $request->getParsedBody();

        $this->validator->mapFieldsRules([
            'category_name'=> ['required']
        ]);
        $this->validator = $this->validator->withData($request_body);

        if(!$this->validator->validate()) {
            $response = new Response();
            $response->getBody()->write(json_encode($this->validator->errors()));
            return $response;
        }

        // validate that category doesn't exist yet
        $category_exists = $this->categoryService->get("category_name", $request_body['category_name']);
        if ($category_exists) throw new HttpBadRequestException($request, "Category already exists, all category names must be unique");

        $res = $this->categoryService->create($request_body);
        if ($res != false) $data = json_encode(['message'=>"Category Created"]); else $data = json_encode(['message'=>"Unable to create category"]);
        $response->getBody()->write($data);
        return $response->withStatus($res ? 201 : 500);
    }
    public function update_category(Request $request, Response $response, string $id): Response {
        $request_body = $request->getParsedBody();

        $res = $this->categoryService->update($request_body, (int) $id);
        if (!$res) {
            $data = json_encode(['message'=>'Unable to update category']);
            $response->getBody()->write($data);
            return $response->withStatus(400);
        } else {
            $data = json_encode(['message'=>'Category updated']);
            $response->getBody()->write($data);
            return $response;
        }
    }
    public function fetch_all(Request $request, Response $response) {
        $data = $this->categoryService->all();
        
        $response->getBody()->write(json_encode($data));
        return $response;
    }
    public function fetch_one(Request $request, Response $response, string $id) {
        $data = $this->categoryService->get("id", (int) $id);

        if (!$data) {
            throw new HttpNotFoundException($request, "Category not found");
        }
        
        $response->getBody()->write(json_encode($data));
        return $response;
    }
}