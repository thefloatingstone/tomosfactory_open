<?php

function __autoload($class_name) {
    if(file_exists('classes/'.$class_name . '.class.php')) {
        require_once('classes/'.$class_name . '.class.php');
    } else {
        throw new Exception("Unable to load $class_name.");
    }
}

/**
 * Requirements
 */
require_once 'config/database.conf.php';
require_once 'php/libs/smarty/Smarty.class.php';

/**
 * Library instanciation
 */
$pdo    = new PDO("mysql:host=" . TOMOSFACTORY_DATABASE_HOST . ";dbname=" . TOMOSFACTORY_DATABASE_NAME, TOMOSFACTORY_DATABASE_USER, TOMOSFACTORY_DATABASE_PASSWORD);
$smarty = new Smarty;