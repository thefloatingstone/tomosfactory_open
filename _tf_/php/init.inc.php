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
require_once APPLICATION_DOCUMENT_ROOT.'php/libs/smarty/Smarty.class.php';

/**
 * Library instanciation
 */
$pdo    = new PDO("mysql:host=" . DATABASE_HOST . ";dbname=" . DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
$smarty = new Smarty;