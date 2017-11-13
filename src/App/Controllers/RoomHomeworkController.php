<?php

namespace App\Controllers;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class RoomHomeworkController
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
        return new JsonResponse($this->roomHomeworkService->getAll($roomId));
    }
    // /*
    // * 家事一覧&家事別時間取得
    // */
    // public function getHomeworkWithTodayTime($roomId)
    // {
    //     return new JsonResponse($this->roomHomeworkService->getHomeworkWithTodayTime($roomId));
    // }
    /*
    * 部屋別家事登録
    */
    public function insert(Request $request){

      return new JsonResponse(array("id" => $this->roomHomeworkService->insert($request)));
    }

    /*
    * 部屋別家事更新
    */
    public function update(Request $request){

      return new JsonResponse(array("id" => $this->roomHomeworkService->update($request)));
    }

    /*
    * 部屋別家事削除
    */
    public function delete(Request $request){

      return new JsonResponse(array("id" => $this->roomHomeworkService->delete($request)));
    }

}
