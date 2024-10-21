<?php 
declare(strict_types=1);

namespace App\Services;

use App\Database;

class BrandService {
    public function __construct(private Database $database)  {    }
    public function create(array $data): bool {
        $pdo = $this->database->get_connection();
        $query = "INSERT INTO brands (brand_name, brand_description) VALUES(:brand_name, :brand_description)";

        $statement = $pdo->prepare($query);
        $statement->bindValue(":brand_name", $data['brand_name']);
        $statement->bindValue(":brand_description", $data['brand_description'] ?? null);
        $response = $statement->execute();

        return $response;
    }
    public function update(array $data, int $id): bool {
        $pdo = $this->database->get_connection();
        $query = "UPDATE brands SET brand_name = :brand_name, brand_description = :brand_description WHERE id=:id";

        $statement = $pdo->prepare($query);
        return $statement->execute([':brand_name'=>$data['brand_name'], ":brand_description"=>$data['brand_description'] ?? null, ":id"=>$id]);
    }
    public function all() {
        $pdo = $this->database->get_connection();
        $query = "SELECT * FROM brands";

        $statement = $pdo->prepare($query);
        
        $statement->execute();

        return $statement->fetchAll();
    }
    public function get(string $key, $value) {
        $pdo = $this->database->get_connection();
        $query = "SELECT * FROM brands WHERE $key = ?";

        $statement = $pdo->prepare($query);

        $res = $statement->execute([$value]);

        if (!$res) return $res;
        
        return $statement->fetch();
    }
}