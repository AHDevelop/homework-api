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
        return new JsonResponse($this->homeworkHistService->getAll($roomId));
    }

    /*
    * 家事履歴登録登録
    */
    public function insert(Request $request){

      $this->homeworkHistService->insert($request, $responce);
      return $this->returnResult($responce);
    }

    /*
    * 部屋別家事更新
    */
    public function update(Request $request){

      return new JsonResponse(array("id" => $this->homeworkHistService->update($request)));
    }

    /*
    * 家事履歴削除
    */
    public function delete(Request $request){

      return $this->returnResult(array("id" => $this->homeworkHistService->delete($request)), "", "");
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

      if ($groupBy == "user") {
        return new JsonResponse($this->homeworkHistService->getSummaryUser($roomId, $from, $to));
      } else if ($groupBy == "homework") {
        return new JsonResponse($this->homeworkHistService->getSummaryHomework($roomId, $from, $to));
      }
      // TODO validate check
      return new JsonResponse("invalid group_by key (please input gruoup_by=user or gruoup_by=homework)");
    }

}
