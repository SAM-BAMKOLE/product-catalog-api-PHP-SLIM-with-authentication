<?php 
declare(strict_types=1);

namespace App\Services;

use App\Database;

class  SalesService {
    public function __construct(private Database $database){ }
    public function create(string $payment_method) {
        $pdo = $this->database->get_connection();
        $query = "INSERT INTO sales (sale_id, payment_method) VALUES (UUID(), ?)"; 

        $statement = $pdo->prepare($query);
        $statement->execute([$payment_method]);

        $auto_increment_id = $pdo->lastInsertId();

        $sale = $this->get("id", $auto_increment_id, "sale_id");
        
        return $sale['sale_id'];
    }

    public function update(string $sale_id, string $column, $value) {
        $pdo = $this->database->get_connection();
        $query = "UPDATE sales SET $column = :value where sale_id = :sale_id";

        $statement = $pdo->prepare($query);
        return $statement->execute([':value'=>$value, ":sale_id"=>$sale_id]);
    }

    public function get(string $key, $value, string $receive = "*") {
        $pdo = $this->database->get_connection();
        $query = "SELECT {$receive} FROM sales WHERE $key = ?";

        $statement = $pdo->prepare($query);
        $statement->execute([$value]);

        return $statement->fetch();
    }
}