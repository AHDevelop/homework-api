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

    public function getAll($roomId)
    {
        return new JsonResponse($this->roomHomeworkService->getAll($roomId));
    }

    /*
    * 部屋別家事登録
    */
    public function insert(Request $request){

      return new JsonResponse(array("id" => $this->roomHomeworkService->insert($request)));
    }

}
