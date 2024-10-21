<?php 
declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use Firebase\JWT\JWT;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Valitron\Validator;

class AuthController {
    public function __construct(private Validator $validator, private AuthService $authService)
    {
    }
    public function register(Request $request, Response $response): Response {
        $request_body = $request->getParsedBody();
        $this->validator->mapFieldsRules([
            'email'=> ['required', 'email'],
            'password'=> ['required', ['lengthMin', 6]],
            'confirm_password'=> ['required', ['lengthMin', 6], ['equals', 'password']],
        ]);

        $this->validator = $this->validator->withData($request_body);

        if (!$this->validator->validate()) {
            $response->getBody()->write(json_encode($this->validator->errors()));
            return $response->withStatus(400);
        }

        /* validate password contains number & string
        if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d).+$/', $request_body['password'])) {
            $this->validator->error('password', 'Password must contain letters & numbers');
            $response->getBody()->write(json_encode($this->validator->errors()));
            return $response->withStatus(400);
        }
        */

        $request_body['password'] = password_hash($request_body['password'], PASSWORD_ARGON2I);
        $res = $this->authService->create($request_body);

        if(!$res) {
            $response->getBody()->write(json_encode(['message'=>"Unable to register user"]));
            return $response->withStatus(500);
        }

        $response->getBody()->write(json_encode(['message'=>'User registered']));
        return $response;
    }
    public function login(Request $request, Response $response): Response {
        $request_body = $request->getParsedBody();
        $this->validator->mapFieldsRules([
            'email'=> ['required', 'email'],
            'password'=> ['required', ['lengthMin', 6]]
        ]);

        $this->validator = $this->validator->withData($request_body);

        if(!$this->validator->validate()) {
            $response->getBody()->write(json_encode(['errors'=>$this->validator->errors(), 'data'=>$request_body]));
            return $response->withStatus(400);
        }

        $found_user = $this->authService->get_user('email', $request_body['email']);

        if (!$found_user) {
            $response->getBody()->write(json_encode(['message'=>"Invalid user identity"]));
            return $response->withStatus(400);
        }

        $password_valid = password_verify($request_body['password'], $found_user['password']);

        if(!$password_valid) {
            $response->getBody()->write(json_encode(['message'=>"Invalid credentials"]));
            return $response->withStatus(400);
        }

        $issued_at = time();
        $expires_at = $issued_at + 60 * 60;
        $payload = [
            'iat'=> $issued_at,
            'exp'=> $expires_at,
            'user_id'=> $found_user['user_id'],
            'user_identity'=> $found_user['identity']
        ];

        $jwt = JWT::encode($payload, $_ENV['SECRET_KEY'], 'HS256');

        $response->getBody()->write(json_encode(['token'=>$jwt]));
        return $response;
    }
}