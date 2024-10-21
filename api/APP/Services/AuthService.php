<?php 
declare(strict_types=1);

namespace App\Services;

use App\Database;

class AuthService {
    public function __construct(private Database $database)
    {    }
    public function create(array $data) {
        $pdo = $this->database->get_connection();
        $query = 'INSERT into users (email, password) VALUES (:email, :password)';

        $statement = $pdo->prepare($query);
        return $statement->execute([':email'=>$data['email'], ':password'=>$data['password']]);
    }
    public function get_user(string $key, $value) {
        $pdo = $this->database->get_connection();
        $query = "SELECT * FROM users WHERE $key = ?";

        $statement = $pdo->prepare($query);
        $statement->execute([$value]);

        return $statement->fetch();
    }
}