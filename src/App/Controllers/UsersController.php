<?php

namespace App\Controllers;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class UsersController extends BaseController
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

    public function getOneByKey($key)
    {
        return new JsonResponse($this->usersService->getOneByKey($key));
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
    * 新規ユーザー登録
    */
    public function insertUser(Request $request)
    {
        return new JsonResponse($this->usersService->insertUser($request));
    }

    /*
    * ユーザー更新
    */
    public function updateUser(Request $request)
    {
        return new JsonResponse($this->usersService->updateUser($request));
    }

    /*
    * 部屋ユーザー追加
    */
    public function insertUserWithRoom(Request $request)
    {
        return new JsonResponse($this->usersService->insertUserWithRoom($request));
    }

    /*
    * 部屋ユーザー削除
    */
    public function deleteUserWithRoom(Request $request)
    {
        return new JsonResponse($this->usersService->deleteUserWithRoom($request));
    }

}
