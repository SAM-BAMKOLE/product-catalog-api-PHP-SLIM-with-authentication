<?php 
declare(strict_types=1);

namespace App\Services;

use App\Database;

class CategoryService {
    public function __construct(private Database $database)  {    }
    public function create(array $data): bool {
        $pdo = $this->database->get_connection();
        $query = "INSERT INTO categories (category_name, category_description) VALUES(:category_name, :category_description)";

        $statement = $pdo->prepare($query);
        $statement->bindValue(":category_name", $data['category_name']);
        $statement->bindValue(":category_description", $data['category_description'] ?? null);
        $response = $statement->execute();

        return $response;
    }
    public function update(array $data, int $id): bool {
        $pdo = $this->database->get_connection();
        $query = "UPDATE categories SET category_name = :category_name, category_description = :category_description WHERE id=:id";

        $statement = $pdo->prepare($query);
        return $statement->execute([':category_name'=>$data['category_name'], ":category_description"=>$data['category_description'] ?? null, ":id"=>$id]);
    }
    public function all() {
        $pdo = $this->database->get_connection();
        $query = "SELECT * FROM categories";

        $statement = $pdo->prepare($query);
        
        $statement->execute();
        return $statement->fetchAll();
    }
    public function get(string $key, $value) {
        $pdo = $this->database->get_connection();
        $query = "SELECT * FROM categories WHERE $key = ?";

        $statement = $pdo->prepare($query);

        $res = $statement->execute([$value]);

        if (!$res) return $res;

        return $statement->fetch();
    }
}