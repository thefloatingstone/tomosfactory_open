<?php

abstract class Tomos {
    
    private $id;
    private $name;

    public function __construct($id = null) {
        $this->id = $id;
    }

    abstract protected function create();

    abstract protected function edit();

    abstract protected function publish();

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

}