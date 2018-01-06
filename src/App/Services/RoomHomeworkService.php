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
          rhw.room_home_work_id,
          rhw.room_id,
          rhw.home_work_name,
          rhw.base_home_work_time_hh,
          rhw.is_visible,
          hwh.home_work_time_hh
        FROM
          room_home_work rhw
          left join
            (
              select
                room_home_work_id,
                sum(home_work_time_hh) home_work_time_hh
              from
                home_work_hist
              where
                room_id = :roomId
                and is_deleted = false
                and home_work_date = current_date
              group by
                room_home_work_id
            ) hwh on rhw.room_home_work_id = hwh.room_home_work_id
        WHERE
          rhw.room_id = :roomId AND rhw.is_deleted = false
        ORDER BY
          rhw.room_home_work_id;
      ');

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

    // /**
    // * 部屋に紐づく家事を、今日の時間を含めて返す
    // */
    // public function getHomeworkWithTodayTime($roomId)
    // {
    //     $st = $this->pdo->prepare('
    //       SELECT
    //         rhw.room_home_work_id,
    //         rhw.room_id,
    //         rhw.home_work_name,
    //         rhw.is_visible,
    //         hwh.home_work_time_hh
    //       FROM
    //         room_home_work rhw
    //         left join
    //           (
    //             select
    //               room_home_work_id,
    //               sum(home_work_time_hh) home_work_time_hh
    //             from
    //               home_work_hist
    //             where
    //               room_id = :roomId
    //               and home_work_date = current_date
    //             group by
    //               room_home_work_id
    //           ) hwh on rhw.room_home_work_id = hwh.room_home_work_id
    //       WHERE
    //         rhw.room_id = :roomId AND rhw.is_deleted = false
    //       ORDER BY
    //         rhw.room_home_work_id
    //         );
    //     ');
    //     $st->bindParam(':roomId', $roomId, $this->pdo::PARAM_INT);
    //     $st->execute();

    //     // SQLエラーをログに出力
    //     $this->monolog->debug(sprintf("SQL log is '%s'  "), $st->errorInfo());

    //     $results = array();
    //     while ($row = $st->fetch($this->pdo::FETCH_ASSOC)) {
    //       $results[] = $row;
    //     }

    //     return $results;
    // }

    /*
    *　家事を登録する
    */
    public function insert($Param)
    {
        // SQLステートメントを用意
        $st = $this->pdo->prepare('
            INSERT INTO room_home_work
              (room_id, home_work_name, base_home_work_time_hh, is_visible, is_deleted, created_by, created_at, updated_by, updated_at)
            VALUES
              (:roomId, :homeWorkName, :baseHomeworkTimeHH, :isVisible, false, :updateUserId, now(), :updateUserId, now());
          ');

        // 変数をバインド
        $st->bindParam(':roomId', $roomId, $this->pdo::PARAM_INT);
        $st->bindParam(':homeWorkName', $homeWorkName, $this->pdo::PARAM_STR);
        $st->bindParam(':baseHomeworkTimeHH', $baseHomeworkTimeHH, $this->pdo::PARAM_INT);
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
          $baseHomeworkTimeHH = $row["base_home_work_time"];

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
          home_work_name = :homeworkName, base_home_work_time_hh = :baseHomeworkTimeHH, is_visible = :isVisible, created_by = :updateUserId, created_at = now(), updated_by = :updateUserId, updated_at = now()
        WHERE
          room_home_work_id = :roomHomeworkId;
        ');

        // 変数をバインド
        $st->bindParam(':roomHomeworkId', $roomHomeworkId, $this->pdo::PARAM_INT);
        $st->bindParam(':homeworkName', $homeworkName, $this->pdo::PARAM_STR);
        $st->bindParam(':baseHomeworkTimeHH', $baseHomeworkTimeHH, $this->pdo::PARAM_INT);
        $st->bindParam(':isVisible', $isVisible, $this->pdo::PARAM_BOOL);
        $st->bindParam(':updateUserId', $updateUserId, $this->pdo::PARAM_STR);

        // 変数に実数を設定
        $updateUserId = $Param->request->get("user_id");
        $rocord = $Param->request->get("record");
        foreach ($rocord as $id => $row) {

          $roomHomeworkId = $row["room_home_work_id"];
          $homeworkName = $row["home_work_name"];
          $baseHomeworkTimeHH = $row["base_home_work_time"];
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
