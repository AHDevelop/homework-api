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
    * 家事履歴一覧取得
    */
    public function getAll($roomId)
    {
        return new JsonResponse($this->homeworkHistService->getAll($roomId));
    }

    /*
    * 家事履歴登録登録
    */
    public function insert(Request $request){

      $this->homeworkHistService->insert($request, $responce);
      return $this->returnResult($responce);
    }

    /*
    * 部屋別家事更新
    */
    public function update(Request $request){

      return new JsonResponse(array("id" => $this->homeworkHistService->update($request)));
    }

    /*
    * 家事履歴削除
    */
    public function delete(Request $request){

      return $this->returnResult(array("id" => $this->homeworkHistService->delete($request)), "", "");
    }

}
