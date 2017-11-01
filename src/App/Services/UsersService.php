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

    /*
    * 新規ユーザー登録
    */
    public function insertUser($Param){

      // ユーザーマスタに登録

      // SQLステートメントを用意
      $st = $this->pdo->prepare('
        INSERT INTO user_master
          (email, user_name, auth_type, auth_id, is_deleted, created_by, created_at, updated_by, updated_at)
        VALUES
          (:email, :userName, :authType, :authId, false, :updateUserId, now(), :updateUserId, now());
      ');

      // 変数をバインド
      $st->bindParam(':email', $email, $this->pdo::PARAM_INT);
      $st->bindParam(':userName', $userName, $this->pdo::PARAM_STR);
      $st->bindParam(':authType', $authType, $this->pdo::PARAM_INT);
      $st->bindParam(':authId', $authId, $this->pdo::PARAM_BOOL);
      $st->bindParam(':updateUserId', $updateUserId, $this->pdo::PARAM_STR);

      // 変数に実数を設定
      $email = $Param->request->get("email");
      $userName = $Param->request->get("user_name");
      $authType = $Param->request->get("auth_type");
      $authId = $Param->request->get("auth_id");
      $updateUserId = "system";

      $st->execute();
      // SQLの実行結果を出力
      $this->monolog->debug(sprintf("SQL log is '%s'  "), $st->errorInfo());

      // 部屋の新規作成

      // 部屋家事の登録

      return $this->pdo->lastInsertId();
    }

}
