<?php

namespace sql\libra\reconnect;

use sql\libra\DataProvider;

class MySQLReconnectable extends DataProvider{

    /** @var bool */
    private $shouldReconnect = true;

    private $database;

    /** @var int */
    private const RETRY_ATTEMPTS = 3;

    /**
     * @param string $host
     * @param string $db
     * @param string $username
     * @param string $password
     * @param array $options
     */
    public function __construct(
        private string $host,
        private string $db,
        private string $username,
        private string $password,
        private array $options = []
    ){
        try{
            $this->connect();
        }catch (\PDOException $exception){
            echo $exception->getMessage();
            $this->setDatabase();
        }
    }

    /**
     * @param $method
     * @param $args
     * @return mixed|void
     */
    public function __call($method, $args){
        $has_gone_away = false;
        $retry_attempt = 0;
        try_again:

        try{
            if (is_callable(array($this->database, $method))){
                return call_user_func_array(array($this->database, $method), $args);
            }else{
                trigger_error("Call to undefined method '{$method}'");
            }
        }catch (\PDOException $exception){
            $exception_message = $exception->getMessage();

            if (($this->shouldReconnect) && str_contains($exception_message, 'server has gone away') && $retry_attempt <= self::RETRY_ATTEMPTS){
                $has_gone_away = true;
            }else{
                throw $exception;
            }
        }
        if ($has_gone_away){
            $retry_attempt++;
            $this->reconnect();
            goto try_again;
        }
    }

    private function connect(): void{
        $this->database = new \PDO("mysql:host=" . $this->host . ";dbname=" . $this->db, $this->username, $this->password);
        $this->database->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->setDatabase($this->database);
    }

    private function reconnect(): void{
        $this->database = null;
        $this->setDatabase();
        $this->connect();
    }
}
