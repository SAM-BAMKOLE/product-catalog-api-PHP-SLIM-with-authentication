<?php 
declare(strict_types=1);

namespace App\Middlewares;

use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class SetContentType {
    public function __invoke(Request $request, RequestHandlerInterface $handler): Response    {
        $response = $handler->handle($request);

        return $response->withHeader("Content-Type", "application/json");
    }
}