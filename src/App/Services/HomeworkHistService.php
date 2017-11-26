<?php

namespace App\Services;

class HomeworkHistService extends BaseService
{

    /*
    *　家事履歴を登録する
    */
    public function insert($Param, $responce)
    {
        // SQLステートメントを用意
        $st = $this->pdo->prepare('
          INSERT INTO home_work_hist
            (room_id, room_home_work_id, user_id, home_work_date, home_work_time_hh, is_deleted, created_by, created_at, updated_by, updated_at)
        	VALUES
            (:roomId, :roomHomeworkId, :userId, :homeworkDate, :homeworkTimeHH, false, :updateUserId, now(), :updateUserId, now());
        ');

        // 変数をバインド
        $st->bindParam(':roomId', $roomId, $this->pdo::PARAM_INT);
        $st->bindParam(':roomHomeworkId', $roomHomeworkId, $this->pdo::PARAM_INT);
        $st->bindParam(':userId', $userId, $this->pdo::PARAM_INT);
        $st->bindParam(':homeworkDate', $homeworkDate, $this->pdo::PARAM_STR);
        $st->bindParam(':homeworkTimeHH', $homeworkTimeHH, $this->pdo::PARAM_INT);
        $st->bindParam(':updateUserId', $updateUserId, $this->pdo::PARAM_STR);

        // 変数に実数を設定
        $roomId = $Param->request->get("room_id");
        $updateUserId = $Param->request->get("user_id");
        $rocord = $Param->request->get("record");

        foreach ($rocord as $id => $row) {

          $roomHomeworkId = $row["room_home_work_id"];
          $userId = $row["user_id"];
          $homeworkDate = $row["home_work_date"];
          $homeworkTimeHH = $row["home_work_time"];

          $st->execute();
          // SQLの実行結果を出力
          $this->monolog->debug(sprintf("SQL log is '%s'  "), $st->errorInfo());
        }

        return;
    }

    /*
    * 家事履歴削除
    */
    public function delete($Param)
    {

        // SQLステートメントを用意
        $st = $this->pdo->prepare('
          UPDATE
            home_work_hist
          SET
            is_deleted = true, updated_by = :updateUserId, updated_at = now()
          WHERE
            home_work_hist_id = :homeworkHistId;
        ');

        // 変数をバインド
        $st->bindParam(':homeworkHistId', $homeworkHistId, $this->pdo::PARAM_INT);
        $st->bindParam(':updateUserId', $updateUserId, $this->pdo::PARAM_STR);

        // 変数に実数を設定
        $updateUserId = $Param->request->get("user_id");
        $rocord = $Param->request->get("record");

        foreach ($rocord as $id => $row) {
          $homeworkHistId = $row["room_home_work_id"];

          // SQLを実行
          $st->execute();

          // SQLの実行結果を出力
          $this->monolog->debug(sprintf("SQL log is '%s'  "), $st->errorInfo());
        }

        return $this->pdo->lastInsertId();
    }

}
