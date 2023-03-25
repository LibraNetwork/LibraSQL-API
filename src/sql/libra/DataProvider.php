<?php

namespace sql\libra;

abstract class DataProvider{

    private $database = null;

    /**
     * @param $database
     * @return void
     */
    public function setDatabase($database = null): void{
        $this->database = $database;
    }

    /**
     * @param string $table
     * @param array $param
     * @return bool
     */
    public function execTable(string $table, array $param): bool{
        if ($this->database == null){
            return false;
        }
        return $this->database->exec("CREATE TABLE IF NOT EXISTS " . $table . "(" . implode(", ", $param) . ")");
    }

    /**
     * @param array $param
     * @param string $database
     * @param array $key
     * @param array $value
     * @return void
     */
    public function insert(array $param, string $database, array $key, array $value): void{
        if ($this->database == null){
            return;
        }
        $data = $this->database->prepare("INSERT INTO " . $database . "(" . implode(", ", $key) . ") VALUES(" . implode(", ", $value) . ")");
        $this->execute($data, $param);
    }

    /**
     * @param array $param
     * @param string $database
     * @param array $set
     * @param array $where
     * @return void
     */
    public function update(array $param, string $database, array $set, array $where = []): void{
        if ($this->database == null){
            return;
        }
        $keys = [];

        foreach ($set as $key){
            $keys[] = $key . " = :" . $key;
        }
        $query = "UPDATE " . $database . " SET " . implode(", ", $keys);

        if (!empty($where)){
            $whereKeys = [];

            foreach ($where as $key){
                $whereKeys[] = $key . " = :" . $key;
            }
            $data = $this->database->prepare($query . " WHERE " . implode(" AND ", $whereKeys));
        } else{
            $data = $this->database->prepare($query);
        }
        $this->execute($data, $param);
    }

    /**
     * @param array $param
     * @param string $database
     * @param array $where
     * @return void
     */
    public function delete(array $param, string $database, array $where = []): void{
        if ($this->database == null){
            return;
        }
        $whereKeys = [];

        foreach ($where as $key){
            $whereKeys[] = $key . " = :" . $key;
        }
        $data = empty($whereKeys) ? $this->database->prepare("DELETE FROM " . $database) : $this->database->prepare("DELETE FROM " . $database . " WHERE " . implode(" AND ", $whereKeys));
        $this->execute($data, $param);
    }

    /**
     * @param array $param
     * @param string $database
     * @param array $select
     * @param array $where
     * @param bool $array
     * @return array
     */
    public function get(array $param, string $database, array $select = [], array $where = [], bool $array = false): array{
        if ($this->database == null){
            return [];
        }
        $query = "SELECT * FROM " . $database;

        if (!empty($select)){
            $query = "SELECT " . implode(", ", $select) . " FROM " . $database;
        }
        if (!empty($where)){
            $whereKeys = [];

            foreach ($where as $key){
                $whereKeys[] = $key . " = :" . $key;
            }
            $query .= " WHERE " . implode(" AND ", $whereKeys);
        }
        $result = [];

        if ($this->database instanceof \PDO){
            $data = $this->database->prepare($query);
            $this->execute($data, $param);

            while ($row = $data->fetch()){
                if ($array){
                    $result[] = $row;
                }else{
                    $result = $row;
                }
            }
        }else{
            $data = $this->database->prepare($query);
            $execute = $this->execute($data, $param);

            while ($row = $execute->fetchArray(SQLITE3_ASSOC)){
                if ($array){
                    $result[] = $row;
                }else{
                    $result = $row;
                }
            }
        }
        return $result;
    }

    /**
     * @param array $param
     * @param string $database
     * @param array $where
     * @return bool
     */
    public function exists(array $param, string $database, array $where): bool{
        $result = $this->get($param, $database, [], $where);

        if (empty($result)){
            return false;
        }else{
            return true;
        }
    }

    /**
     * @param \SQLite3Stmt|\PDOStatement $data
     * @param array $param
     * @return bool|\SQLite3Result
     */
    private function execute(\SQLite3Stmt|\PDOStatement $data, array $param){
        if (!empty($param)){
            foreach ($param as $key => &$value){
                $data->bindParam($key, $value);
            }
        }
        return $data->execute();
    }
}
