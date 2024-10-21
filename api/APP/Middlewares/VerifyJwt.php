<?php 
declare(strict_types=1);

namespace App\Middlewares;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Psr7\Factory\ResponseFactory;

class VerifyJWT {
    public function __construct(private ResponseFactory $factory) {}
    public function __invoke(Request $request, RequestHandlerInterface $handler): Response {

        $bearer_token = $request->getHeaderLine('Authorization') ?? $request->getHeaderLine('authorization');
        if (!$bearer_token) {
            $response = $this->factory->createResponse();
            $response->getBody()->write(json_encode(['message'=>"Bearer token missing"]));
            return $response->withStatus(401);
        }

        $auth_token = explode(' ', $bearer_token)[1];

        try {
            $decoded = (array) JWT::decode($auth_token, new Key($_ENV['SECRET_KEY'], 'HS256'));
        } catch (Exception $e) {
            $response = $this->factory->createResponse();
            $response->getBody()->write(json_encode(['message'=>"Invalid token", 'error'=>$e->getMessage()]));
            return $response->withStatus(401);
        }

        $request = $request->withAttribute('user_id', $decoded['user_id']);
        $request = $request->withAttribute('user_identity', $decoded['user_identity']);

        $response = $handler->handle($request);
        return $response;
    }
}