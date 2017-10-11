<?php

namespace App\Services;

class BaseService
{
    protected $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

}
