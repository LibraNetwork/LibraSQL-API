<?php

namespace sql\libra;

use PDO;
use PDOException;
use sql\libra\reconnect\MySQLReconnectable;

class MySQL extends DataProvider{

    /**
     * @param string $host
     * @param string $db
     * @param string $username
     * @param string $password
     * @param bool $reconnectable
     */
    public function __construct(string $host, string $db, string $username, string $password, bool $reconnectable = true){
        try{
            if ($reconnectable){
                new MySQLReconnectable($host, $db, $username, $password);
            }else{
                $database = new PDO("mysql:host=" . $host . ";dbname=" . $db, $username, $password);
                $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $database->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
                $this->setDatabase($database);
            }
        }catch (PDOException $exception){
            echo $exception->getMessage();
            $this->setDatabase();
        }
    }
}
