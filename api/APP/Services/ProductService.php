<?php 
declare(strict_types=1);

namespace App\Services;

use App\Database;
use PDO;

class ProductService {
    protected $product_search = "SELECT products.id, products.product_name, products.product_description, products.price, products.image_url, categories.category_name, brands.brand_name FROM products JOIN brands ON products.brand_id = brands.id JOIN categories ON products.category_id = categories.id";

    public function __construct(private Database $database)    {    }
    public function create(array $data) {
        $pdo = $this->database->get_connection();
        $query = "INSERT INTO products (product_name, product_description, price, category_id, brand_id, image_url) VALUES (:product_name, :product_description, :price, :category_id, :brand_id, :image_url)";

        $statement = $pdo->prepare($query);

        $statement->bindValue(":product_name", $data['product_name']);
        $statement->bindValue(":product_description", $data['product_description'] ?? null);
        $statement->bindValue(":price", (float) $data['price']);
        $statement->bindValue(":category_id", (int) $data['category_id']);
        $statement->bindValue(":brand_id", (int) $data['brand_id']);
        $statement->bindValue(":image_url", (int) $data['image_url']);

        return $statement->execute();
    }

    public function update(int $product_id, array $data) {
        $pdo = $this->database->get_connection();
        $query = "UPDATE products SET product_name = :nproduct_ame, product_description = :product_description, price = :price, category_id = :category_id, brand_id = :brand_id WHERE id = :id";

        $statement = $pdo->prepare($query);
        $statement->bindValue(":id", $product_id);
        foreach(['product_name', 'product_description', 'price', 'category_id', 'brand_id'] as $key) {
            $statement->bindValue(":". $key, $data[$key]);
        }

        return $statement->execute();
    }

    public function get(string $key, $value) {
        $pdo = $this->database->get_connection();
        $query = "SELECT * FROM products WHERE $key = ?";

        $statement = $pdo->prepare($query);
        $statement->execute([$value]);

        return $statement->fetch();
    }

    public function all(?int $limit = null) {
        $pdo = $this->database->get_connection();
        $query = $this->product_search;
        // $query = "SELECT * FROM products";

        if (isset($limit)) {
            $query .= " LIMIT $limit";
        }

        $statement = $pdo->prepare($query);

        $statement->execute();

        return $statement->fetchAll();
    }

    public function delete(int $product_id) {
        $pdo = $this->database->get_connection();
        $query = "DELETE FROM products WHERE id = ?";

        $statement = $pdo->prepare($query);
        return $statement->execute([$product_id]);
    }

    public function latest(int $limit = 30) {
        $pdo = $this->database->get_connection();
        $query = $this->product_search . " ORDER BY products.updated_at DESC LIMIT $limit";
        // $query = "SELECT * FROM products";

        $statement = $pdo->prepare($query);

        $statement->execute();

        return $statement->fetchAll();
    }

    public function get_filtered(string $filter_by, int $value) {
        $pdo = $this->database->get_connection();

        if ($filter_by === "category") {
            
            $query = $this->product_search . " WHERE products.category_id = " . $value;
        } else {
            
            $query = $this->product_search . " WHERE products.brand_id = " . $value;
        }

        $statement = $pdo->prepare($query);

        $statement->execute();

        return $statement->fetchAll();
    }
}