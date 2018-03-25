<?php

namespace App\Controllers;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class BaseController
{

    /*
    * 結果を返却する
    */
    protected function returnResult($results, $response)
    {

        if ($response == null) {
          $response = array();
        }
        $response["results"] = $results;

        if($response["message"] == ""){
            //$response["message"] = "正常に完了しました。";
        };

        if($response["status"] == ""){
            $response["status"] = "200";
        };
        return new JsonResponse($response);
    }
}
