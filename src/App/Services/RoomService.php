<?php

namespace App\Services;

class RoomService extends BaseService
{

    /**
    * 部屋を取得する
    */
    public function getOne($roomId)
    {
        $st = $this->pdo->prepare('
            SELECT
              room_id, room_name, user_id, room_access_key
            FROM
              room
            WHERE
              room_id = :roomId AND is_deleted = false
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

}
