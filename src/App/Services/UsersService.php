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
    * ユーザーを一件取得する（認証キーで取得）
    */
    public function getOneByKey($key)
    {
      $st = $this->pdo->prepare('SELECT user_id, email, user_name, auth_type, auth_id FROM user_master where auth_id = :authId');
      $st->bindValue(':authId', $key, $this->pdo::PARAM_INT);
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

      $userId = $this->pdo->lastInsertId();

      // 部屋の新規作成

      // SQLステートメントを用意
      $st2 = $this->pdo->prepare('
        INSERT INTO room
          (room_name, user_id, room_access_key, is_deleted, created_by, created_at, updated_by, updated_at)
        VALUES
          (:roomName, :userId, :roomAccessKey, false, :updateUserId, now(), :updateUserId, now());
      ');

      // 変数をバインド
      $st2->bindParam(':roomName', $roomName, $this->pdo::PARAM_STR);
      $st2->bindParam(':userId', $userId, $this->pdo::PARAM_INT);
      $st2->bindParam(':roomAccessKey', $roomAccessKey, $this->pdo::PARAM_STR);
      $st2->bindParam(':updateUserId', $updateUserId, $this->pdo::PARAM_STR);

      // 変数に実数を設定
      $roomName = "NEW ROOM"; // TODO 編集する画面がアプリにない
      $roomAccessKey = "hogehogehoge"; // TODO make SHA512 hash values
      $updateUserId = "system";

      $st2->execute();

      // SQLの実行結果を出力
      $this->monolog->debug(sprintf("SQL log is '%s'  "), $st2->errorInfo());
      $roomId = $this->pdo->lastInsertId();

      // 家事マスタをすべて取得
      $homeworkMasterList = $this->getAllHomeworkMaster();

      $homeworkMasterList[0]["room_access_key"];

      // 部屋家事の登録
      // SQLステートメントを用意
      $st3 = $this->pdo->prepare('
        INSERT INTO room_home_work
          (room_id, home_work_name, base_home_work_time_hh, is_visible, is_deleted, created_by, created_at, updated_by, updated_at)
        VALUES
          (:roomId, :homeWorkName, :baseHomeworkTimeHH, true, false, :updateUserId, now(), :updateUserId, now());
      ');

      // 変数をバインド
      $st3->bindParam(':roomId', $roomId, $this->pdo::PARAM_STR);
      $st3->bindParam(':homeWorkName', $homeWorkName, $this->pdo::PARAM_INT);
      $st3->bindParam(':baseHomeworkTimeHH', $baseHomeworkTimeHH, $this->pdo::PARAM_STR);
      $st3->bindParam(':updateUserId', $updateUserId, $this->pdo::PARAM_STR);

      // 変数に実数を設定
      $updateUserId = "system";

      foreach ($homeworkMasterList as $key => $homeworkMaster) {
        $homeWorkName = $homeworkMaster["home_work_name"];
        $baseHomeworkTimeHH = $homeworkMaster["base_home_work_time_hh"];
        $st3->execute();
        $this->monolog->debug(sprintf("SQL log is '%s'  "), $st3->errorInfo());
      }

      // 登録したユーザ情報を返却するためにSelect
      $userId = $this->pdo->lastInsertId();
      $st = $this->pdo->prepare('SELECT user_id, email, user_name, auth_type, auth_id FROM user_master where user_id = :userId');
      $st->bindValue(':userId', $userId, $this->pdo::PARAM_INT);
      $st->execute();

      $names = array();
      while ($row = $st->fetch($this->pdo::FETCH_ASSOC)) {
        $names[] = $row;
      }

      return $names;
    }

    /*
    * 家事マスタを全件取得する
    */
    private function getAllHomeworkMaster(){

      $st = $this->pdo->prepare('
        SELECT
          home_work_id, home_work_name, base_home_work_time_hh
        FROM
          home_work_master
        WHERE
          is_deleted = false;
      ');

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
    * ユーザー追加
    */
    public function insertUserWithRoom($Param)
    {

      // room_access_key からRoomIdを取得する
      $roomAccessKey = $Param->request->get("room_access_key");

      // $roomAccessKeyを_で分割
      $roomAccessKeySplite = explode('_', $roomAccessKey);
      $roomId = $roomAccessKeySplite[0];
      $accessKey = $roomAccessKeySplite[1];

      // 部屋とアクセスキーを取得
      $roomInfo = $this->getOneRoom($roomId);

      // マッチングチェック
      $trueRoomAccessKey = $roomInfo[0]["room_access_key"];

      if($trueRoomAccessKey != $accessKey){
        return "認証エラー";
      }

      // TODO 重複チェック

      // SQLステートメントを用意
      $st = $this->pdo->prepare('
        INSERT INTO room_user
          (room_id, user_id, is_deleted, created_by, created_at, updated_by, updated_at)
        VALUES
          (:roomId, :userId, false, :updateUserId, now(), :updateUserId, now());
      ');

      // 変数をバインド
      $st->bindParam(':roomId', $roomId, $this->pdo::PARAM_INT);
      $st->bindParam(':userId', $userId, $this->pdo::PARAM_INT);
      $st->bindParam(':updateUserId', $updateUserId, $this->pdo::PARAM_STR);

      // 変数に実数を設定
      $userId = $Param->request->get("user_id");
      $updateUserId = "system";

      $st->execute();
      // SQLの実行結果を出力
      $this->monolog->debug(sprintf("SQL log is '%s'  "), $st->errorInfo());

      return $this->pdo->lastInsertId();
    }

    /**
    * 部屋を取得する
    */
    private function getOneRoom($roomId)
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

    /*
    * 部屋ユーザー削除
    */
    public function deleteUserWithRoom($Param)
    {
        // TODO ユーザーの存在チェック

        // SQLステートメントを用意
        $st = $this->pdo->prepare('
          UPDATE room_user
            SET is_deleted = true, updated_by = :updateUserId, updated_at = now()
          WHERE
            room_id = :roomId and user_id = :removeUserId;
        ');

        // 変数をバインド
        $st->bindParam(':roomId', $roomId, $this->pdo::PARAM_INT);
        $st->bindParam(':removeUserId', $removeUserId, $this->pdo::PARAM_INT);
        $st->bindParam(':updateUserId', $updateUserId, $this->pdo::PARAM_STR);

        // 変数に実数を設定
        $removeUserId = $Param->request->get("remove_user_id");
        $roomId = $Param->request->get("room_id");
        $updateUserId = "system";

        $st->execute();

        // SQLの実行結果を出力
        $this->monolog->debug(sprintf("SQL log is '%s'  "), $st->errorInfo());

        return $this->pdo->lastInsertId();
    }

}
