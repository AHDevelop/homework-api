<?php

namespace App\Services;

class UsersService extends BaseService
{

    /**
    * ユーザーを一件取得する
    */
    public function getOne($id, &$responce)
    {
      $st = $this->pdo->prepare('SELECT user_id, email, user_name, auth_type, auth_id FROM user_master where user_id = :userId');
      $st->bindValue(':userId', $id, $this->pdo::PARAM_INT);
      $this->executeSql($st);

      $names = array();
      while ($row = $st->fetch($this->pdo::FETCH_ASSOC)) {
        $names[] = $row;
      }

      return $names;
    }

    /**
    * ユーザーを一件取得する（認証キーで取得）
    */
    public function getOneByKey($key, &$responce)
    {
      $st = $this->pdo->prepare('SELECT user_id, email, user_name, auth_type, auth_id FROM user_master where auth_id = :authId');
      $st->bindValue(':authId', $key, $this->pdo::PARAM_INT);
      $this->executeSql($st);

      $results = array();
      while ($row = $st->fetch($this->pdo::FETCH_ASSOC)) {
        $results[] = $row;
      }

      $this->monolog->debug($results);

      return $results;
    }

    /**
    * ユーザー全件を取得する
    */
    public function getAll(&$responce)
    {
        $st = $this->pdo->prepare('SELECT user_id, email, user_name, auth_type, auth_id FROM user_master');
        $this->executeSql($st);

        $names = array();
        while ($row = $st->fetch($this->pdo::FETCH_ASSOC)) {
          $names[] = $row;
        }

        return $names;
    }

    /*
    * 部屋ユーザー一覧取得
    */
    public function getAllWithRoom($roomId, &$responce)
    {
      $st = $this->pdo->prepare('
        SELECT
          owner_user.user_id,
          owner_user.email,
          owner_user.user_name,
          true as is_owner
        FROM
          room
        LEFT JOIN user_master owner_user
        ON room.user_id = owner_user.user_id
        WHERE
          room.room_id = :roomId AND owner_user.is_deleted = false
        UNION ALL
        SELECT
          member_user.user_id,
          member_user.email,
          member_user.user_name,
          false as is_owner
        FROM
          room_user
        LEFT JOIN user_master member_user
        ON room_user.user_id = member_user.user_id
        WHERE
          room_user.room_id = :roomId AND room_user.is_deleted = false
        ORDER BY
          is_owner desc, user_id
        ;'
      );

      $st->bindParam(':roomId', $roomId, $this->pdo::PARAM_INT);
      $st->execute();

      $results = array();
      while ($row = $st->fetch($this->pdo::FETCH_ASSOC)) {
        $results[] = $row;
      }

      return $results;
    }

    /*
    * 新規ユーザー登録
    */
    public function insertUser($Param, &$responce)
    {

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

      $this->executeSql($st);

      //結果返却用オブジェクト
      $userId = $this->pdo->lastInsertId();
      $results["userId"] = $userId;
      $results["email"] = $email;
      $results["userName"] = $userName;
      $results["authType"] = $authType;
      $results["authId"] = $authId;

      // 部屋の新規作成

      // SQLステートメントを用意
      $st2 = $this->pdo->prepare('
        INSERT INTO room
          (room_name, user_id, room_number, is_deleted, created_by, created_at, updated_by, updated_at)
        VALUES
          (:roomName, :userId, :roomNumber, false, :updateUserId, now(), :updateUserId, now());
      ');

      // 変数をバインド
      $st2->bindParam(':roomName', $roomName, $this->pdo::PARAM_STR);
      $st2->bindParam(':userId', $userId, $this->pdo::PARAM_INT);
      $st2->bindParam(':roomNumber', $roomNumber, $this->pdo::PARAM_STR);
      $st2->bindParam(':updateUserId', $updateUserId, $this->pdo::PARAM_STR);

      // 変数に実数を設定
      $roomName = $userName . "さんの部屋";
      $roomNumber = $this->makeRoomNumber();
      $updateUserId = "system";

      $this->executeSql($st2);

      $roomId = $this->pdo->lastInsertId();
      $results["roomId"] = $roomId;
      $results["roomName"] = $roomName;
      $results["roomNumber"] = $roomNumber;

      // 家事マスタをすべて取得
      $homeworkMasterList = $this->getAllHomeworkMaster();

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
        $this->executeSql($st3);
      }

      // 登録したユーザ情報を返却するためにSelect
      $this->monolog->debug($userId);
      $st = $this->pdo->prepare('SELECT user_id, email, user_name, auth_type, auth_id FROM user_master where user_id = :userId');
      $st->bindValue(':userId', $userId, $this->pdo::PARAM_INT);
      $this->executeSql($st);

      $usres = array();
      while ($row = $st->fetch($this->pdo::FETCH_ASSOC)) {
        $users[] = $row;
      }

      $newinfo = $users[0];
      $newinfo['room_id'] = $roomId;
      $newinfo['room_number'] = $roomNumber;
      $newinfo['room_name'] = $roomName;

      return $newinfo;
    }

    /*
    * アプリ用のトークン作成
    */
    private function createAppToken($userId, $authToken){
      return hash('sha256', $userId . $authToken);
    }

    /*
    * ユーザトークン作成
    * 正常に作成した場合は、$appTokenを返します
    */
    public function insertUserToken($userId, $authToken){
      // SQLステートメントを用意
      $st = $this->pdo->prepare('
      INSERT INTO user_token
        (user_id, token, is_deleted, created_by, created_at, updated_by, updated_at)
      VALUES
        (:userId, :token, false, :updateUserId, now(), :updateUserId, now());
      ');

      // 変数をバインド
      $appToken = $this->createAppToken($userId, $authToken);
      $st->bindParam(':userId', $userId, $this->pdo::PARAM_STR);
      $st->bindParam(':token', $appToken, $this->pdo::PARAM_STR);
      $st->bindParam(':updateUserId', $userId, $this->pdo::PARAM_STR);
      $this->executeSql($st);
      return $appToken;
    }


    /*
    * ユーザトークン更新
    * 正常に作成した場合は、$appTokenを返します
    */
    public function updateUserToken($userId, $authToken){

        // SQLステートメントを用意
        $st = $this->pdo->prepare('
        UPDATE user_token
          SET token = :token, updated_by = :updateUserId, updated_at = now()
        WHERE
          user_id = :userId;
        ');

        // 変数をバインド
        $appToken = $this->createAppToken($userId, $authToken);

        $st->bindParam(':userId', $userId, $this->pdo::PARAM_STR);
        $st->bindParam(':token', $appToken, $this->pdo::PARAM_STR);
        $st->bindParam(':updateUserId', $userId, $this->pdo::PARAM_STR);

        $this->executeSql($st);
        return $appToken;
    }

   /*
    * ユーザトークンチェック
    */
    public function checkUserToken($token){
      // SQLステートメントを用意
      $st = $this->pdo->prepare('
        select count(*) from user_token where token = :token;
      ');

      // 変数をバインド
      $st->bindParam(':token', $token, $this->pdo::PARAM_STR);
      $this->executeSql($st);

      $count = $st->fetchColumn();
      // $this->monolog->debug("user token count:".$count);

      if ($count == 0) {
        return false;
      }
      return true;
    }

    /*
    * 新規の部屋番号を生成する
    */
    private function makeRoomNumber(){

      // 一意性は不要なためランダムに4桁の数字文字列を作り出す
      $newRoomNumber =  mt_rand(101, 9999);
      return sprintf('%04d', $newRoomNumber);
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

      $this->executeSql($st);

      $results = array();
      while ($row = $st->fetch($this->pdo::FETCH_ASSOC)) {
        $results[] = $row;
      }

      return $results;
    }

    /*
    * 指定されたユーザが指定された部屋のOwnerか判定する。
    */
    private function isOwner($roomId, $userId){

      $st = $this->pdo->prepare('
        SELECT
          count(*)
        FROM
          room
        WHERE
          room_id = :roomId AND user_id = :userId AND is_deleted = false
      ');

      // 変数をバインド
      $st->bindParam(':roomId', $roomId, $this->pdo::PARAM_INT);
      $st->bindParam(':userId', $userId, $this->pdo::PARAM_INT);
      $this->executeSql($st);

      $count = $st->fetchColumn();
      if ($count === 0) {
        return false;
      }
      return true;
    }

    /*
    * 特定のユーザがすでに部屋ユーザーに登録済みかどうかチェックする
    */
    private function getRoomUser($roomId, $userId){

      $st = $this->pdo->prepare('
        SELECT
          room_user.*
        FROM
          room_user
        WHERE
          room_id = :roomId AND user_id = :userId AND is_deleted = false
      ');

      // 変数をバインド
      $st->bindParam(':roomId', $roomId, $this->pdo::PARAM_INT);
      $st->bindParam(':userId', $userId, $this->pdo::PARAM_INT);

      $this->executeSql($st);

      $results = array();
      while ($row = $st->fetch($this->pdo::FETCH_ASSOC)) {
        $results[] = $row;
      }

      return $results;
    }

    /*
    * 特定のユーザが過去に部屋ユーザーに登録済みだったかどうかチェックする
    */
    private function getRoomUserIncludeDeleted($roomId, $userId){

      $st = $this->pdo->prepare('
        SELECT
          room_user.*
        FROM
          room_user
        WHERE
          room_id = :roomId AND user_id = :userId AND is_deleted = true
      ');

      // 変数をバインド
      $st->bindParam(':roomId', $roomId, $this->pdo::PARAM_INT);
      $st->bindParam(':userId', $userId, $this->pdo::PARAM_INT);

      $this->executeSql($st);

      $results = array();
      while ($row = $st->fetch($this->pdo::FETCH_ASSOC)) {
        $results[] = $row;
      }

      return $results;
    }

    /*
    * ユーザー更新
    */
    public function updateUser($Param, &$responce)
    {

      // SQLステートメントを用意
      $st = $this->pdo->prepare('
        UPDATE user_master
          SET user_name = :userName, updated_by = :updateUserId, updated_at = now()
        WHERE
          user_id = :userId;
      ');

      // 変数をバインド
      $st->bindParam(':userId', $userId, $this->pdo::PARAM_INT);
      $st->bindParam(':userName', $userName, $this->pdo::PARAM_STR);
      $st->bindParam(':updateUserId', $updateUserId, $this->pdo::PARAM_STR);

      // 変数に実数を設定
      $userId = $Param->request->get("user_id");
      $userName = $Param->request->get("user_name");
      $updateUserId = $Param->request->get("user_id");

      $this->executeSql($st);

      $result["user_id"] = $userId;
      $result["user_name"] = $userName;

      $responce["message"] = "更新しました。";

      return $result;
    }

    /*
    * ユーザー追加
    */
    public function insertUserWithRoom($Param, &$responce)
    {
      // 部屋名と部屋番号から部屋を特定する
      $st = $this->pdo->prepare('
        SELECT
          room.*
        FROM
          room
        WHERE
          room_name = :roomName AND room_number = :roomNumber
      ');

      // 変数をバインド
      $st->bindParam(':roomName', $roomName, $this->pdo::PARAM_STR);
      $st->bindParam(':roomNumber', $roomNumber, $this->pdo::PARAM_STR);

      // 変数に実数を設定
      $roomName = $Param->request->get("room_name");
      $roomNumber = $Param->request->get("room_no");

      $this->executeSql($st);

      $results = array();
      while ($row = $st->fetch($this->pdo::FETCH_ASSOC)) {
        $results[] = $row;
      }

      if(count($results) != 1){
        // 該当部屋が1件でない場合は認証エラー
        $responce["message"] = "部屋が存在しません。";
        return;
      }

      $roomId = $results[0]["room_id"];
      $userId = $Param->request->get("user_id");

      // $this->monolog->debug("room id is:".$roomId);
      // $this->monolog->debug("user id is:".$userId);

      if(0 < count(self::getRoomUser($roomId, $userId)) || self::isOwner($roomId, $userId)){
        $responce["message"] = "ユーザーは部屋に登録済みです。";
        return;
      }

      //　ユーザーが過去に部屋に登録されていたか
      if(0 < count(self::getRoomUserIncludeDeleted($roomId, $userId))){
        // 登録あり→　update
        // SQLステートメントを用意
        $st2 = $this->pdo->prepare('
          UPDATE room_user
            SET is_deleted = false, updated_by = :updateUserId, updated_at = now()
          WHERE
            room_id = :roomId AND userId = :userId;
        ');

        // 変数をバインド
        $st2->bindParam(':roomId', $roomId, $this->pdo::PARAM_INT);
        $st2->bindParam(':userId', $userId, $this->pdo::PARAM_INT);
        $st2->bindParam(':updateUserId', $updateUserId, $this->pdo::PARAM_STR);

        // 変数に実数を設定
        $roomId = $results[0]["room_id"];
        $userId = $Param->request->get("user_id");
        $updateUserId = "system";

        $this->executeSql($st2);

      } else {
        // 登録なし→　insert

        // SQLステートメントを用意
        $st2 = $this->pdo->prepare('
          INSERT INTO room_user
            (room_id, user_id, is_deleted, created_by, created_at, updated_by, updated_at)
          VALUES
            (:roomId, :userId, false, :updateUserId, now(), :updateUserId, now());
        ');

        // 変数をバインド
        $st2->bindParam(':roomId', $roomId, $this->pdo::PARAM_INT);
        $st2->bindParam(':userId', $userId, $this->pdo::PARAM_INT);
        $st2->bindParam(':updateUserId', $updateUserId, $this->pdo::PARAM_STR);

        // 変数に実数を設定
        $roomId = $results[0]["room_id"];
        $userId = $Param->request->get("user_id");
        $updateUserId = "system";

        $this->executeSql($st2);
      }

      $responce["message"] = "部屋を追加しました。";

      // 追加した先の部屋IDを返却する
      return $roomId;
    }

    /**
    * 部屋を取得する
    */
    private function getOneRoom($roomId, &$responce)
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
        $this->executeSql($st);

        $results = array();
        while ($row = $st->fetch($this->pdo::FETCH_ASSOC)) {
          $results[] = $row;
        }

        return $results;
    }

    /*
    * 部屋ユーザー削除
    */
    public function deleteUserWithRoom($Param, &$responce)
    {
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
        $updateUserId = $Param->request->get("user_id");

        $this->executeSql($st);

        $responce["message"] = "ユーザーを部屋から削除しました。";

        return $this->pdo->lastInsertId();
    }
}
