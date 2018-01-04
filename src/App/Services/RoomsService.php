<?php

namespace App\Services;

class RoomsService extends BaseService
{
    /**
    * 部屋を取得する
    */
    public function getAll($userId)
    {
        $st = $this->pdo->prepare('
          SELECT
            room_id, room_name, user_id, room_access_key, is_owned
          FROM
            (
              SELECT
                room_id, room_name, user_id, room_access_key, 1 as is_owned
              FROM
                room
              WHERE
                user_id = :userId AND is_deleted = false

              UNION ALL

              SELECT
                room_id, room_name, user_id, room_access_key, 0 as is_owned
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

}
