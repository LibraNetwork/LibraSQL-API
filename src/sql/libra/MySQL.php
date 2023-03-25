<?php

namespace sql\libra;

use PDO;
use PDOException;

class MySQL extends DataProvider{

    /**
     * @param string $host
     * @param string $db
     * @param string $username
     * @param string $password
     */
    public function __construct(string $host, string $db, string $username, string $password){
        try{
            $database = new PDO("mysql:host=" . $host . ";dbname=" . $db, $username, $password);
            $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $database->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
            $database->query("SET SESSION interactive_timeout = 28800;");
            $database->query("SET SESSION wait_timeout = 28800;");
            $this->setDatabase($database);
        }catch (PDOException $exception){
            echo $exception->getMessage();
            $this->setDatabase();
        }
    }
}
