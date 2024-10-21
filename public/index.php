<?php 
declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Handlers\Strategies\RequestResponseArgs;

use Dotenv\Dotenv;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

define('APP_ROOT', dirname(__DIR__));

require APP_ROOT . "/vendor/autoload.php";

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// create contaner builder and container for DI;
$builder = new ContainerBuilder();
$container = $builder->addDefinitions(APP_ROOT . "/config/definitions.php")->build();

// add DI container to app
AppFactory::setContainer($container);
// create app
$app = AppFactory::create();
// create route (route params) collector
$collector = $app->getRouteCollector();
$collector->setDefaultInvocationStrategy(new RequestResponseArgs);

$app->addBodyParsingMiddleware();

// create error_handlers to set app error responses to json (since this is an API)
$error_middleware = $app->addErrorMiddleware(true, true, true);
$error_handler = $error_middleware->getDefaultErrorHandler();
$error_handler->forceContentType("application/json");

require APP_ROOT . "/config/routes.php";

$app->add(function (Request $request, RequestHandlerInterface $handler): Response {
    $response = $handler->handle($request);

    return $response->withHeader('Access-Control-Allow-Origin', ['http://localhost:5173'])
    ->withHeader('Access-Control-Allow-Credentials', 'true')
    ->withHeader("Access-Control-Allow-Headers", "X-Requested-With, Content-Type, Accept, Origin, Authorization");
});

$app->run();

// CREATE TABLE products (
// id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
// name VARCHAR(100),
// description TEXT,
// price DECIMAL(10, 2) UNSIGNED,
// category_id INT UNSIGNED,
// brand_id INT UNSIGNED,
// created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
// updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
// FOREIGN KEY fk_product_category (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
// FOREIGN KEY fk_product_brand (brand_id) REFERENCES brands(id) ON DELETE CASCADE
// );