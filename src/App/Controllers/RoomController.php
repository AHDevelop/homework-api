<?php

namespace App\Controllers;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class RoomController
{

    protected $roomService;

    public function __construct($service)
    {
        $this->$roomService = $service;
    }

    /*
    * 部屋取得
    */
    public function getOne($roomId)
    {
        return new JsonResponse($this->roomHomeworkService->getOne($roomId));
    }
}
