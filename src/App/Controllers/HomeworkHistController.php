<?php

namespace App\Controllers;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class HomeworkHistController
{

    protected $homeworkHistService;

    public function __construct($service)
    {
        $this->homeworkHistService = $service;
    }

    /*
    * 部屋別家事登録
    */
    public function insert(Request $request){

      return new JsonResponse(array("id" => $this->homeworkHistService->insert($request)));
    }

    /*
    * 部屋別家事削除
    */
    public function delete(Request $request){

      return new JsonResponse(array("id" => $this->homeworkHistService->delete($request)));
    }

}
