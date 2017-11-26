<?php

namespace App\Controllers;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class HomeworkHistController extends BaseController
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

      $this->homeworkHistService->insert($request, $responce);
      return $this->returnResult($responce);
    }

    /*
    * 部屋別家事削除
    */
    public function delete(Request $request){

      return $this->returnResult(array("id" => $this->homeworkHistService->delete($request)), "", "");
    }

}
