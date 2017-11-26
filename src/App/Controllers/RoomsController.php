<?php

namespace App\Controllers;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class RoomController extends BaseController
{

    protected $roomsService;

    public function __construct($service)
    {
        // var_dump($service);
        $this->$roomsService = $service;
    }

    /*
    * 部屋取得
    */
    public function getAll($userId)
    {
        // var_dump('test');
        return new JsonResponse($this->roomsService->getAll(1));
    }
}
