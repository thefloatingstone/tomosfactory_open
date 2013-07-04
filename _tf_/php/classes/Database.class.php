<?php

class Database {

    /**
     * @var Database
     * @access private
     * @static
     */
    private static $_instance = null;
    private $pdo;

    /**
     * Constructeur de la classe
     *
     * @param void
     * @return void
     */
    private function __construct() {
        $this->pdo = new PDO(DATABASE_DRIVER.":host=" . DATABASE_HOST . ";dbname=" . DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
    }

    /**
     * Méthode qui crée l'unique instance de la classe
     * si elle n'existe pas encore puis la retourne.
     *
     * @param void
     * @return Singleton
     */
    public static function getInstance() {

        if (is_null(self::$_instance)) {
            self::$_instance = new Database();
        }

        return self::$_instance;
    }

    public function getPDO() {
        return $this->pdo;
    }

}