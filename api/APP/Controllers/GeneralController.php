<?php 
declare(strict_types=1);

namespace App\Controllers;

use App\Services\BrandService;
use App\Services\CategoryService;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class GeneralController {
    public function __construct(private BrandService $brandService, private CategoryService $categoryService){}
    public function get_brands_and_categories(Request $request, Response $response) {
        $brands = $this->brandService->all();
        $categories = $this->categoryService->all();

        $response->getBody()->write(json_encode(["brands"=>$brands,"categories"=>$categories]));
        return $response;
    }
}