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

    protected function handleError($result){
      if (!$result) {
        $this->monolog->debug($this->pdo->errorCode().':'.print_r($this->pdo->errorInfo(), true));
      }
    }

    protected function executeSql($st){
      if(!$st){
        $this->monolog->error('$st is invalid');
        return false;
      }

      if(!$st->execute()){
        $this->monolog->error('SQL ERROR: '.$st->errorCode().':'.$st->errorInfo()[2]);
      }
    }

}
