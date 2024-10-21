<?php
declare(strict_types=1);

namespace App\Middlewares;

use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class VerifyUserRole {
    public function __invoke(Request $request, RequestHandlerInterface $handler){
        $user_role = $request->getAttribute('user_identity');

        if ($user_role !== "admin") {
            // $response = new Response();
            // $response->getBody()->write(json_encode($request->getAttributes()));
            // return $response;
            throw new HttpUnauthorizedException($request, 'User is uanuthorized for this action');
        }
        $response = $handler->handle($request);
        return $response;
    }
}