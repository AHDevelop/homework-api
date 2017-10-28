<?php

namespace App\Services;

class RoomHomeworkService extends BaseService
{

    /**
    * 部屋に紐づく家事全件を取得する
    */
    public function getAll($roomId)
    {
        $st = $this->pdo->prepare('
            SELECT
              room_home_work_id, room_id, home_work_name, bese_home_work_time_hh, is_visible
            FROM
              room_home_work
            WHERE
              room_id = :roomId AND is_deleted = false
            ORDER BY
              room_home_work_id
            ;'
        );

        $st->bindParam(':roomId', $roomId, $this->pdo::PARAM_INT);
        $st->execute();

        // SQLエラーをログに出力
        $this->monolog->debug(sprintf("SQL log is '%s'  "), $st->errorInfo());

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

        $this->monolog->debug($Param);
        $this->monolog->debug($roomId);

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

    /*
    *　家事を更新する
    */
    public function update($Param)
    {

      // SQLステートメントを用意
      $st = $this->pdo->prepare('
        UPDATE
          room_home_work
	      SET
          home_work_name = :homeworkName, bese_home_work_time_hh = :beseHomeworkTimeHH, is_visible = :isVisible, created_by = :updateUserId, created_at = now(), updated_by = :updateUserId, updated_at = now()
	      WHERE
          room_home_work_id = :roomHomeworkId;
        ');

        // 変数をバインド
        $st->bindParam(':roomHomeworkId', $roomHomeworkId, $this->pdo::PARAM_INT);
        $st->bindParam(':homeworkName', $homeworkName, $this->pdo::PARAM_STR);
        $st->bindParam(':beseHomeworkTimeHH', $beseHomeworkTimeHH, $this->pdo::PARAM_INT);
        $st->bindParam(':isVisible', $isVisible, $this->pdo::PARAM_BOOL);
        $st->bindParam(':updateUserId', $updateUserId, $this->pdo::PARAM_STR);

        // 変数に実数を設定
        $updateUserId = $Param->request->get("user_id");
        $rocord = $Param->request->get("record");
        foreach ($rocord as $id => $row) {

          $roomHomeworkId = $row["room_home_work_id"];
          $homeworkName = $row["home_work_name"];
          $beseHomeworkTimeHH = $row["base_home_work_time"];
          $isVisible = $row["is_visible"];

          // SQLを実行
          $st->execute();

          // SQLの実行結果を出力
          $this->monolog->debug(sprintf("SQL log is '%s'  "), $st->errorInfo());
        }
    }

    /*
    * 部屋別家事削除
    */
    public function delete($Param)
    {

        // SQLステートメントを用意
        $st = $this->pdo->prepare('
          UPDATE
            room_home_work
          SET
            is_deleted = true, created_by = :updateUserId, created_at = now(), updated_by = :updateUserId, updated_at = now()
          WHERE
            room_home_work_id = :roomHomeworkId;
        ');

        // 変数をバインド
        $st->bindParam(':roomHomeworkId', $roomHomeworkId, $this->pdo::PARAM_INT);
        $st->bindParam(':updateUserId', $updateUserId, $this->pdo::PARAM_STR);

        // 変数に実数を設定
        $updateUserId = $Param->request->get("user_id");
        $rocord = $Param->request->get("record");
        foreach ($rocord as $id => $row) {

          $roomHomeworkId = $row["room_home_work_id"];

          // SQLを実行
          $st->execute();

          // SQLの実行結果を出力
          $this->monolog->debug(sprintf("SQL log is '%s'  "), $st->errorInfo());
        }
    }

}