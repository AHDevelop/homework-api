<?php

namespace App\Services;

class UsersService extends BaseService
{

    /**
    * ユーザーを一件取得する
    */
    public function getOne($id)
    {
      $st = $this->pdo->prepare('SELECT user_id, email, user_name, auth_type, auth_id FROM user_master where user_id = :userId');
      $st->bindValue(':userId', $id, $this->pdo::PARAM_INT);
      $st->execute();

      $names = array();
      while ($row = $st->fetch($this->pdo::FETCH_ASSOC)) {
        $names[] = $row;
      }

      return $names;
    }

    /**
    * ユーザー全件を取得する
    */
    public function getAll()
    {
        $st = $this->pdo->prepare('SELECT user_id, email, user_name, auth_type, auth_id FROM user_master');
        $st->execute();

        $names = array();
        while ($row = $st->fetch($this->pdo::FETCH_ASSOC)) {
          $names[] = $row;
        }

        return $names;
    }

    /*
    * 部屋ユーザー一覧取得
    */
    public function getAllWithRoom($roomId){

      $st = $this->pdo->prepare('
      SELECT
        user_master.*
      FROM
        room
        LEFT JOIN room_user
        ON room.room_id = room_user.room_id
        LEFT JOIN user_master
        ON room_user.user_id = user_master.user_id
      WHERE
        room.room_id = :roomId AND user_master.is_deleted = false
      ORDER BY
        user_id
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
