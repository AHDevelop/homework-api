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
        return new JsonResponse($this->$roomsService->getAll($id));
    }

    /*
    * 部屋設定更新
    */
    public function update(Request $request)
    {
        return new JsonResponse($this->$roomsService->update($request));
    }

}
