<?php

namespace App\Services;

class RoomHomeworkService extends BaseService
{

    /**
    * 部屋に紐づく家事全件を取得する
    */
    public function getAll($roomId)
    {
        $st = $this->pdo->prepare(
          '
            SELECT
              home_work_id, home_work_name, base_home_work_time_hh
            FROM
              home_work_master
            WHERE
              room_id = ? ;
          ',
          [(int) $roomId]
        );
        $st->execute();

        $results = array();
        while ($row = $st->fetch($this->pdo::FETCH_ASSOC)) {
          $results[] = $row;
        }

        return $results;
    }

    /*
    *　家事を登録する
    */
    public function insert($Param)
    {
        // SQLステートメントを用意
        $st = $this->pdo->prepare('
            INSERT INTO room_home_work
              (room_id, home_work_name, bese_home_work_time_hh, is_visible, is_deleted, created_by, created_at, updated_by, updated_at)
            VALUES
              (:roomId, :homeWorkName, :beseHomeworkTimeHH, :isVisible, false, :updateUserId, now(), :updateUserId, now());
          ');

        // 変数をバインド
        $st->bindParam(':roomId', $roomId, $this->pdo::PARAM_INT);
        $st->bindParam(':homeWorkName', $homeWorkName, $this->pdo::PARAM_STR);
        $st->bindParam(':beseHomeworkTimeHH', $beseHomeworkTimeHH, $this->pdo::PARAM_INT);
        $st->bindParam(':isVisible', $isVisible, $this->pdo::PARAM_BOOL);
        $st->bindParam(':updateUserId', $updateUserId, $this->pdo::PARAM_STR);

        // 変数に実数を設定
        $roomId = $Param->request->get("room_id");
        $updateUserId = $Param->request->get("user_id");
        $isVisible = "true";

        $rocord = $Param->request->get("record");
        foreach ($rocord as $id => $row) {

          $homeWorkName = $row["home_work_name"];
          $beseHomeworkTimeHH = $row["base_home_work_time"];

          $st->execute();
          // SQLの実行結果を出力
          $this->monolog->debug(sprintf("SQL log is '%s'  "), $st->errorInfo());
        }

        return $this->pdo->lastInsertId();
    }

}
