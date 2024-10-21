<?php 
declare(strict_types=1);

namespace App\Controllers;

use App\Services\BrandService;
use App\Services\CategoryService;
use App\Services\ProductService;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Valitron\Validator;

class ProductController {
    public function __construct(private Validator $validator, private ProductService $productService, private BrandService $brandService, private CategoryService $categoryService)
    {
        $this->validator->mapFieldsRules([
            "product_name"=> ["required", ["lengthMin", 3], ["lengthMax", 100]],
            "product_description"=> [["lengthMax", 5000]],
            "price"=> ["required", 'numeric'],
            'category_id'=> ['required', 'integer', ['min', 1]],
            'brand_id'=> ['required', 'integer', ['min', 1]],
        ]);
    }
    public function create_product (Request $request, Response $response) {
        $request_body = $request->getParsedBody();

        $this->validator = $this->validator->withData($request_body);

        if(!$this->validator->validate()) {
            $data = json_encode($this->validator->errors());

            $response->getBody()->write($data);
            return $response->withStatus(422);
        }
        // get category id from category name
        // $category = $this->categoryService->get("name", $request_body['category']);
        // $brand = $this->brandService->get('name', $request_body['brand']);

        // $request_body['category_id'] = $category['id'];
        // $request_body['brand_id'] = $brand['id'];

        // get image_url
        $image_url = $request->getAttribute('image_url');
        
        $request_body['image_url'] = $image_url;
        
        $res = $this->productService->create($request_body);

        if (!$res) {
            $response->getBody()->write(json_encode(['message'=>'Unable to create product']));
            return $response->withStatus(500);
        }
        
        $response->getBody()->write(json_encode(['message'=>'Product created']));
        return $response->withStatus(201);
    }
    public function update_product(Request $request, Response $response, string $id) {
        $request_body = $request->getParsedBody();

        $this->validator = $this->validator->withData($request_body);

        if(!$this->validator->validate()) {
            $response->getBody()->write(json_encode(['errors'=>$this->validator->errors()]));
            return $response->withStatus(400);
        }

        $product_exists =$this->productService->get("id", (int) $id);

        if (!$product_exists) {
            throw new HttpBadRequestException($request, "Product not found");
        }

        $res = $this->productService->update((int) $id, $request_body);

        if (!$res) {
            throw new HttpInternalServerErrorException($request, "Unable to update product");
        }

        $response->getBody()->write(json_encode(['message'=> "Product updated"]));
        return $response;
    }
    public function delete_product(Request $request, Response $response, string $id) {
        $id = (int) $id;

        $product_exists = $this->productService->get("id", $id);

        if (!$product_exists) {
            throw new HttpNotFoundException($request, "Product not found");
        }

        $res = $this->productService->delete($id);

        if (!$res) {
            throw new HttpInternalServerErrorException($request, "Unable to delete product");
        }

        $response->getBody()->write(json_encode(['message'=>"Product deleted"]));
        return $response;
    }
    public function all_products(Request $request, Response $response) {
        $query_params = $request->getQueryParams();

        if (isset($query_params['limit']) && (int) $query_params['limit']) $products = $this->productService->all((int) $query_params['limit']);
        else $products = $this->productService->all();

        $response->getBody()->write(json_encode([$products]));
        return $response;
    }
    public function latest_products(Request $request, Response $response) {
        $query_params = $request->getQueryParams();

        if (isset($query_params['limit']) && (int) $query_params['limit']) { $products = $this->productService->latest((int) $query_params['limit']); }
        else $products = $this->productService->latest();

        $response->getBody()->write(json_encode([$products]));
        return $response;
    }
    public function filter_product(Request $request, Response $response) {
        $query_params = $request->getQueryParams();

        $search_by = '';
        $search_value = '';

        if (isset($query_params['category'])) {
            $category = $this->categoryService->get("category_name", $query_params['category']);
            if ($category) {
                $search_by = "category";
                $search_value = $category['id'];
            }
        } else if (isset($query_params['brand'])) {
            $brand = $this->brandService->get("brand_name", $query_params['brand']);
            if ($brand) {
                $search_by = "brand";
                $search_value = $brand['id'];
            }
        }

        if (empty($search_by)) {
            $response->getBody()->write(json_encode([]));
            return $response;
        }

        $products = $this->productService->get_filtered($search_by, $search_value);
        
        $response->getBody()->write(json_encode($products));
        return $response;
    }
    public function get_product(Request $request, Response $response, string $id) {
        $product = $this->productService->get("id", (int) $id);

        if(!$product) {
            $response->getBody()->write(json_encode(['message'=> "Invalid product id"]));
            return $response->withStatus(404);
        }
        $response->getBody()->write(json_encode($product));
        return $response;
    }
}

/*
CREATE TABLE sales (
	sale_id INT unsigned auto_increment PRIMARY KEY,
	sale_date datetime NOT NULL,
	total_amount DECIMAL(10, 2),
	payment_method VARCHAR(50),
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE sales_details (
	detail_id INT unsigned AUTO_INCREMENT PRIMARY KEY,
	sale_id INT unsigned,
	product_id INT unsigned NOT NULL ,
	product_name VARCHAR(255) NOT NULL,
	quantity_sold INT NOT NULL,
	sale_price DECIMAL(10, 2) not NULL,
	total_price DECIMAL(10, 2) as (quantity_sold * sale_price) stored,
	
	FOREIGN KEY (sale_id) REFERENCES sales(sale_id) ON DELETE CASCADE
);
*/