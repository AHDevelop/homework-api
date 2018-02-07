<?php

namespace App\Services;

class BaseService
{
    protected $pdo;
    protected $monolog;

    public function __construct($pdo, $monolog)
    {
        $this->pdo = $pdo;
        $this->monolog = $monolog;
    }

}
