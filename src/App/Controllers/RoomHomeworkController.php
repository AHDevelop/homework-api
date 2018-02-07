<?php

namespace App\Controllers;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class RoomHomeworkController extends BaseController
{

    protected $roomHomeworkService;

    public function __construct($service)
    {
        $this->roomHomeworkService = $service;
    }

    /*
    * 家事一覧取得
    */
    public function getAll($roomId)
    {
        $result = $this->roomHomeworkService->getAll($roomId, $responce);
        return $this->returnResult($result, $responce);
    }

    /*
    * 部屋別家事登録
    */
    public function insert(Request $request){

      $result = $this->roomHomeworkService->insert($request, $responce);
      return $this->returnResult($result, $responce);
    }

    /*
    * 部屋別家事更新
    */
    public function update(Request $request){

      $result = $this->roomHomeworkService->update($request, $responce);
      return $this->returnResult($result, $responce);
    }

    /*
    * 部屋別家事削除
    */
    public function delete(Request $request){

      $result = $this->roomHomeworkService->delete($request, $responce);
      return $this->returnResult($result, $responce);
    }

}
