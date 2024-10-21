<?php 
declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\BrandController;
use App\Controllers\CategoryController;
use App\Controllers\GeneralController;
use App\Controllers\ImageController;
use App\Controllers\ProductController;
use App\Controllers\SalesController;
use App\Middlewares\ImageUpload;
use App\Middlewares\SetContentType;
use App\Middlewares\VerifyJWT;
use App\Middlewares\VerifyUserRole;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Routing\RouteCollectorProxy;

// $app->get("/", function(Request $_, Response $response){ $response->getBody()->write(json_encode(['hello'=> "Hello world!"])); return $response; });

$app->group("/api/v1", function(RouteCollectorProxy $routes) {
    $routes->get('/hello/', function(Request $request, Response $response): Response {
        $response->getBody()->write(json_encode(["hello"=>"Hello world!"]));
        return $response;
    })->add(VerifyJWT::class);

    $routes->group('', function(RouteCollectorProxy $routes) {
        $routes->post("/brand", BrandController::class . ":add_brand");
        $routes->patch("/brand/{id:[0-9]+}", [BrandController::class, "update_brand"]);
        
        $routes->post("/category", CategoryController::class . ":add_category");
        $routes->patch("/category/{id:[0-9]+}/", [CategoryController::class, "update_category"]);

        $routes->post("/product", [ProductController::class, "create_product"])->add(ImageUpload::class);
        $routes->patch('/product/{id:[0-9]+}', [ProductController::class, "update_product"]);
        $routes->delete('/product/{id:[0-9]+}', [ProductController::class, "delete_product"]);

        $routes->post("/image/upload", [ImageController::class, "upload"]);

        $routes->post("/sales", SalesController::class . ":add_sales");
        
    })->add(VerifyUserRole::class)->add(VerifyJWT::class);
        
    
    $routes->get("/brand", [BrandController::class, "fetch_all"]);
    $routes->get("/brand/{id:[0-9]+}", [BrandController::class, "fetch_one"]);

    $routes->get("/category", [CategoryController::class, "fetch_all"]);
    $routes->get("/category/{id:[0-9]+}", [CategoryController::class, "fetch_one"]);

    $routes->get("/product/{id:[0-9]+}", [ProductController::class, "get_product"]);
    $routes->get("/product", [ProductController::class, "all_products"]);
    $routes->get("/product/latest", [ProductController::class, "latest_products"]);

    $routes->get("/product/search", [ProductController::class, "filter_product"]);

    $routes->get("/brand-category", GeneralController::class . ":get_brands_and_categories");
    
    $routes->post('/auth/register', [AuthController::class, "register"]);
    $routes->post('/auth/login', [AuthController::class, "login"]);

})->add(SetContentType::class);