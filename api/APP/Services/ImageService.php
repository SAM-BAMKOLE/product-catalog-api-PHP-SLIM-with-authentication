<?php 
declare(strict_types=1);

namespace App\Services;

use App\Database;


class ImageService {
    public function __construct(private Database $database){}
    public function create(string $image_url, array $data) {
        $pdo = $this->database->get_connection();
        $query = "INSERT INTO product_images (product_id, image_url, alt_text) VALUES (:product_id, :image_url, :alt_text)";

        $statement = $pdo->prepare($query);
        
        $statement->bindValue(':image_url', $image_url);

        foreach(['product_id', 'alt_text'] as $key) {
            $statement->bindValue(":".$key, $data[$key] ?? null);
        }

        $statement->execute();

        return $pdo->lastInsertId();
    }
}