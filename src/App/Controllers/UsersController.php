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

    /*
    * GmailIDを元にユーザーを取得する
    * 取得できた場合にTokenの更新も合わせて行う
    */
    public function getOneByKey($key, $authToken)
    {

        $result = $this->usersService->getOneByKey($key, $responce);

        // UPDATE token
        if(0 < count($result) && $result[0]['user_id'] != undefined){
          $appToken = $this->usersService->updateUserToken($result[0]['user_id'], $authToken);
          $result[0]['app_token'] = $appToken;
        }

        return $this->returnResult($result, $responce);
    }

    /*
    * UUIDを元にユーザーを取得する
    * 取得できた場合にTokenの更新も合わせて行う
    */
    // public function getOneByUUID($key)
    // {
    //     $result = $this->usersService->getOneByUUID($key, $responce);
    //
    //     // UPDATE token
    //     if(0 < count($result) && $result[0]['user_id'] != undefined){
    //       $appToken = $this->usersService->updateUserToken($result[0]['user_id'], $key);
    //       $result[0]['app_token'] = $appToken;
    //     }
    //     return $this->returnResult($result, $responce);
    // }

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
        $user = $this->usersService->insertUser($request, $responce);

        // ユーザトークン登録
        $appToken = $this->usersService->insertUserToken($user['user_id'], $request ->request->get("auth_token"));
        $user['app_token'] = $appToken;

        return $this->returnResult($user, $responce);
    }

    /*
    * ほーむわーくユーザーの新規登録
    */
    public function insertOriginalUser(Request $request)
    {
        // ユーザ登録
        $user = $this->usersService->insertOriginalUser($request, $responce);

        // ユーザトークン登録
        $appToken = $this->usersService->insertUserToken($user['user_id'], $request ->request->get("auth_id"));
        $user['app_token'] = $appToken;

        return $this->returnResult($user, $responce);
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

    // /*
    // * 招待_部屋ユーザー追加
    // */
    // public function insertUserWithInviteRoom(Request $request)
    // {
    //     $result = $this->usersService->insertUserWithInviteRoom($request, $responce);
    //     return $this->returnResult($result, $responce);
    // }

    /*
    * 部屋ユーザー削除
    */
    public function deleteUserWithRoom(Request $request)
    {
        $result = $this->usersService->deleteUserWithRoom($request, $responce);
        return $this->returnResult($result, $responce);
    }

}
