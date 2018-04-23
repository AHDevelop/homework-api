<?php

namespace App\Services;

class RoomsService extends BaseService
{
    /**
    * 部屋一覧を取得する
    */
    public function getAll($userId, &$responce)
    {
        $st = $this->pdo->prepare('
          SELECT
            room_id, room_name, user_id, room_number, is_owned
          FROM
            (
              SELECT
                room_id, room_name, user_id, room_number, 1 as is_owned
              FROM
                room
              WHERE
                user_id = :userId AND is_deleted = false

              UNION ALL

              SELECT
                room_id, room_name, user_id, room_number, 0 as is_owned
              FROM
                room r
              where
                  EXISTS (
                    SELECT
                      *
                    FROM
                      room_user ru
                    WHERE
                      user_id = :userId AND ru.room_id = r.room_id
                  )
              AND user_id != :userId AND r.is_deleted = false
            ) list;
        ');

        $st->bindParam(':userId', $userId, $this->pdo::PARAM_INT);
        $st->execute();

        // SQLエラーをログに出力
        $this->monolog->debug(sprintf("SQL log is '%s'  "), $st->errorInfo());

        $results = array();
        while ($row = $st->fetch($this->pdo::FETCH_ASSOC)) {
          $results[] = $row;
        }

        return $results;
    }

    /**
    * 指定された部屋情報を取得する
    */
    public function getOne($roomId, &$responce)
    {
        $st = $this->pdo->prepare('
          SELECT
            room_id, room_name, user_id, room_number
             FROM
          room
          WHERE
            room_id = :roomId
          ;
        ');

        $st->bindParam(':roomId', $roomId, $this->pdo::PARAM_INT);
        $st->execute();

        // SQLエラーをログに出力
        $this->monolog->debug(sprintf("SQL log is '%s'  "), $st->errorInfo());

        $results = array();
        while ($row = $st->fetch($this->pdo::FETCH_ASSOC)) {
          $results[] = $row;
        }
        if(count($results) === 0){
          $responce["message"] = "部屋が存在しません。";
          return;
        }
        return $results;
    }
        
    /**
    * 部屋の設定情報を更新する
    */
    public function update($Param, &$responce)
    {

      // SQLステートメントを用意
      $st = $this->pdo->prepare('
        UPDATE
            room
        SET
            room_name = :roomName, room_number = :roomNumber, updated_by = :updateUserId, updated_at = now()
        WHERE
            room_id = :roomId
        ;
      ');

      // 変数をバインド
      $st->bindParam(':roomId', $roomId, $this->pdo::PARAM_INT);
      $st->bindParam(':roomName', $roomName, $this->pdo::PARAM_STR);
      $st->bindParam(':roomNumber', $roomNumber, $this->pdo::PARAM_STR);
      $st->bindParam(':updateUserId', $updateUserId, $this->pdo::PARAM_STR);

      // 変数に実数を設定
      $roomId = $Param->request->get("room_id");
      $roomName = $Param->request->get("room_name");
      $roomNumber = $Param->request->get("room_number");
      $updateUserId = $Param->request->get("user_id");

      $st->execute();
      // SQLの実行結果を出力
      $this->monolog->debug(sprintf("SQL log is '%s'  "), $st->errorInfo());

      $results[room_id] = $roomId;
      $results[room_name] = $roomName;
      $results[room_number] = $roomNumber;

      $responce["message"] = "設定を更新しました。";

      // 更新した部屋情報を返却する
      return $results;
    }
}
