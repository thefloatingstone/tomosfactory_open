<?php

class Database {
    
    public static function getPDO() {
        return new PDO("mysql:host=" . TOMOSFACTORY_DATABASE_HOST . ";dbname=" . TOMOSFACTORY_DATABASE_NAME, TOMOSFACTORY_DATABASE_USER, TOMOSFACTORY_DATABASE_PASSWORD);
    }
    
}