<?php 
declare(strict_types=1);

namespace App;

use PDO;

class Database {
    public function __construct(private string $host, private string $port, private string $dbname, private string $username="root", private string $password="")    {}
    public function get_connection(): PDO {
        $dsn = "mysql:host=$this->host;port=$this->port;dbname=$this->dbname;charset=utf8";
        
        $pdo = new PDO($dsn, $this->username, $this->password,
        [PDO::ATTR_ERRMODE=> PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);

        return $pdo;
    }
}