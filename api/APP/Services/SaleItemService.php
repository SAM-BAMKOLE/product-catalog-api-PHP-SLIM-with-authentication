<?php 
declare(strict_types=1);

namespace App\Services;

use App\Database;

class SaleItemService {
    public function __construct(private Database $database){ }
    public function create(string $sale_id, array $data) {
        $pdo = $this->database->get_connection();
        $query = "INSERT INTO sale_items (sale_id, product_id, unit_price, quantity) VALUES (:sale_id, :product_id, :unit_price, :quantity)";

        $statement = $pdo->prepare($query);
        return $statement->execute([
            'sale_id'=>$sale_id,
            "product_id"=> $data['product_id'],
            "unit_price"=> $data['unit_price'],
            "quantity"=> $data['quantity']
        ]);
    }
    public function get_total(string $sale_id) {
        $pdo = $this->database->get_connection();
        $query = "SELECT sub_total from sale_items WHERE sale_id = ?";

        $statement = $pdo->prepare($query);
        $statement->execute([$sale_id]);

        return $statement->fetchAll();
    }
}