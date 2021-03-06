<?php

namespace App\Controllers;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class RoomsController extends BaseController
{

    protected $roomsService;

    public function __construct($service)
    {
        $this->$roomsService = $service;
    }

    /*
    * 部屋取得
    */
    public function getAll($id)
    {
        $result = $this->$roomsService->getAll($id, $responce);
        return $this->returnResult($result, $responce);
    }

    /*
    * 部屋取得
    */
    public function getOne($roomId)
    {
        $result = $this->$roomsService->getOne($roomId, $responce);
        return $this->returnResult($result, $responce);
    }

    /*
    * 招待URL取得
    */
    public function getInviteUrl($roomId, $userId)
    {
        $result = $this->$roomsService->getInviteUrl($roomId, $userId, $responce);
        return $this->returnResult($result, $responce);
    }

    /*
    * 部屋設定更新
    */
    public function update(Request $request)
    {
        $result = $this->$roomsService->update($request, $responce);
        return $this->returnResult($result, $responce);
    }

}
