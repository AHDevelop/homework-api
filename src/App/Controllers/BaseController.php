<?php

namespace App\Controllers;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class BaseController
{

    /*
    * 認証チェック
    */
    protected function isTrueAuth($request)
    {
        $authKey = $request->request->get("auth_key");

        // TODO 認証エラー時は処理終了
        // if(){
        //   return false;
        // }

        return true;
    }

    /*
    * 結果を返却する
    */
    protected function returnResult($results, $responce)
    {

        $responce["results"] = $results;

        if($responce["message"] == ""){
            //$responce["message"] = "正常に完了しました。";
        };

        if($responce["status"] == ""){
            $responce["status"] = "200";
        };

        return new JsonResponse($responce);
    }
}
