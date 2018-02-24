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
        $result = $this->usersService->getOne($id, $responce);
        return $this->returnResult($result, $responce);
    }

    public function getOneByKey($key)
    {
        $result = $this->usersService->getOneByKey($key, $responce);
        return $this->returnResult($result, $responce);
    }

    public function getAll()
    {
        $result = $this->usersService->getAll($responce);
        return $this->returnResult($result, $responce);
    }

    /*
    * 部屋ユーザー一覧取得
    */
    public function getAllWithRoom($roomId)
    {
        $result = $this->usersService->getAllWithRoom($roomId, $responce);
        return $this->returnResult($result, $responce);
    }

    /*
    * 新規ユーザー登録
    */
    public function insertUser(Request $request)
    {
        // ユーザ登録
        $user = $this->usersService->insertUser($request);

        // ユーザトークン登録
        $appToken = $this->usersService->insertUserToken($user['user_id'], $request ->request->get("auth_token"));
        $user['app_token'] = $appToken;
        
        return new JsonResponse($user);
    }

    /*
    * ユーザー更新
    */
    public function updateUser(Request $request)
    {
        $result = $this->usersService->updateUser($request, $responce);
        return $this->returnResult($result, $responce);
    }

    /*
    * 部屋ユーザー追加
    */
    public function insertUserWithRoom(Request $request)
    {
        $result = $this->usersService->insertUserWithRoom($request, $responce);
        return $this->returnResult($result, $responce);
    }

    /*
    * 部屋ユーザー削除
    */
    public function deleteUserWithRoom(Request $request)
    {
        $result = $this->usersService->deleteUserWithRoom($request, $responce);
        return $this->returnResult($result, $responce);
    }

}
