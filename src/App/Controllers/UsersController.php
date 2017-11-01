<?php

namespace App\Controllers;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class UsersController
{

    protected $usersService;

    public function __construct($service)
    {
        $this->usersService = $service;
    }

    public function getOne($id)
    {
        return new JsonResponse($this->usersService->getOne($id));
    }

    public function getAll()
    {
        return new JsonResponse($this->usersService->getAll());
    }

    /*
    * 部屋ユーザー一覧取得
    */
    public function getAllWithRoom($roomId)
    {
        return new JsonResponse($this->usersService->getAllWithRoom($roomId));
    }

    /*
    * 部屋ユーザー一覧取得
    */
    public function insertUser(Request $request)
    {
        return new JsonResponse($this->usersService->insertUser($request));
    }

    // public function getDataFromRequest(Request $request)
    // {
    //     return $users = array(
    //         "user_name" => $request->request->get("user_name")
    //     );
    // }
}
