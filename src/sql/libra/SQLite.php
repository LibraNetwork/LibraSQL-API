<?php

namespace sql\libra;

class SQLite extends DataProvider{

    /**
     * @param string $file
     */
    public function __construct(string $file){
        $database = new \SQLite3($file);
        $this->setDatabase($database);
    }
}
