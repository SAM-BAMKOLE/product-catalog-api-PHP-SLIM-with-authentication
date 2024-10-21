<?php 
declare(strict_types=1);

use App\Database;

return [
    Database::class => function() {
        return new Database($_ENV['DB_HOST'], $_ENV['DB_PORT'], dbname: $_ENV['DB_NAME'], username: $_ENV['DB_USER'], password: $_ENV['DB_PASSWORD']);
    }
];