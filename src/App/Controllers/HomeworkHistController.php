<?php

namespace App\Controllers;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class HomeworkHistController extends BaseController
{

    protected $homeworkHistService;

    public function __construct($service)
    {
        $this->homeworkHistService = $service;
    }

    /*
    * 家事履歴一覧取得
    */
    public function getAll($roomId)
    {
        $result = $this->homeworkHistService->getAll($roomId, $responce);
        return $this->returnResult($result, $responce);
    }

    /*
    * 家事履歴登録
    */
    public function insert(Request $request){

        $result = $this->homeworkHistService->insert($request, $responce);
        return $this->returnResult($result, $responce);
    }

    /*
    * 家事履歴更新
    */
    public function update(Request $request){

        $result = $this->homeworkHistService->update($request, $responce);
        return $this->returnResult($result, $responce);
    }

    /*
    * 家事履歴削除
    */
    public function delete(Request $request){

        $result = $this->homeworkHistService->delete($request, $responce);
        return $this->returnResult($result, $responce);
    }

    /*
    * 家事履歴一括削除
    */
    public function bulkDelete(Request $request){

        $result = $this->homeworkHistService->bulkDelete($request, $responce);
        return $this->returnResult($result, $responce);
    }

    /*
    * ユーザー別家事集計取得
    */
    public function getSummary(Request $request)
    {
      $roomId = $request->get("room_id");
      $groupBy = $request->get("group_by");
      $from = $request->get("from");
      $to = $request->get("to");

      $result;
      if ($groupBy == "user") {
        $result = $this->homeworkHistService->getSummaryUser($roomId, $from, $to, $responce);
        return $this->returnResult($result, $responce);
      } else if ($groupBy == "homework") {

        $result = $this->homeworkHistService->getSummaryHomework($roomId, $from, $to, $responce);
        return $this->returnResult($result, $responce);
      }
      // TODO validate check
      $responce["message"] = "invalid group_by key (please input gruoup_by=user or gruoup_by=homework)";
      return $this->returnResult("", $responce);
    }

}
