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
                      user_id = :userId AND ru.room_id = r.room_id AND ru.is_deleted = false
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
        $results = self::getOneRoom($roomId);

        if(count($results) === 0){
          $responce["message"] = "部屋が存在しません。";
          return;
        }
        return $results;
    }

    /*
    * 部屋情報を一件取得するSQL処理のみ
    */
    private function getOneRoom($roomId){

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

      return $results;
    }

    /**
    * 部屋の設定情報を更新する
    */
    public function update($Param, &$responce)
    {
      // 変数に実数を設定
      $roomId = $Param->request->get("room_id");
      $roomName = $Param->request->get("room_name");
      $roomNumber = $Param->request->get("room_number");
      $updateUserId = $Param->request->get("user_id");

      // 同じ部屋名と部屋番号がすでに使用済みでないか確認する
      if(0 < count(self::isExistSameRoomName($roomId, $roomName, $roomNumber))){

        $roomInfoArr = self::getOneRoom($roomId);

        $results[room_id] = $roomInfoArr[0]["room_id"];
        $results[room_name] = $roomInfoArr[0]["room_name"];
        $results[room_number] = $roomInfoArr[0]["room_number"];

        $responce["message"] = "設定を更新できませんでした";

        return $results;
      }
      // 使用済みの場合に更新不可にする

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

    /*
    * 渡された部屋名と部屋番号の組み合わせがすでに存在するかチェックする
    */
    private function isExistSameRoomName($roomId ,$roomName, $roomNumber){

      $st = $this->pdo->prepare('
        SELECT
          room.*
        FROM
          room
        WHERE
          room_name = :roomName AND room_number = :roomNumber AND is_deleted = false AND room_id != :roomId
      ');

      // 変数をバインド
      $st->bindParam(':roomId', $roomId, $this->pdo::PARAM_INT);
      $st->bindParam(':roomName', $roomName, $this->pdo::PARAM_STR);
      $st->bindParam(':roomNumber', $roomNumber, $this->pdo::PARAM_STR);

      $this->executeSql($st);

      $results = array();
      while ($row = $st->fetch($this->pdo::FETCH_ASSOC)) {
        $results[] = $row;
      }

      return $results;
    }

    /*
    * 招待URL取得
    */
    public function getInviteUrl($roomId, $userId, &$responce){

      // 招待情報をDBに登録する
      // SQLステートメントを用意
      $st = $this->pdo->prepare('
        INSERT INTO invite_hist
          (room_id, user_id, invite_date, is_deleted, created_by, created_at, updated_by, updated_at)
        VALUES
          (:roomId, :userId, now(), false, :updateUserId, now(), :updateUserId, now());
      ');

      // 変数をバインド
      $st->bindParam(':roomId', $roomId, $this->pdo::PARAM_INT);
      $st->bindParam(':userId', $userId, $this->pdo::PARAM_STR);
      $st->bindParam(':updateUserId', $userId, $this->pdo::PARAM_STR);

      $this->executeSql($st);

      $results = array();
      while ($row = $st->fetch($this->pdo::FETCH_ASSOC)) {
        $results[] = $row;
      }

      // 招待SQLを生成する
      // "room_id" + "_" + "room_idと文字列{エンジョイほーむわーく}を結合した文字列のSHA512ハッシュ値"
      $encryptHash = hash(sha512, $roomId . $userId . "エンジョイほーむわーく");

      // Firebaseのダイナミックリンクを作成する
      $invite_url = "https://play.google.com/store/apps/details?id=com.hatakehirodev.homework&roomId=" . $roomId . "&userId=" . $userId . "&param=" . $encryptHash;

      $data = [];
      $data["dynamicLinkInfo"] = [];
      $data["dynamicLinkInfo"]["dynamicLinkDomain"] = "homework.page.link";
      $data["dynamicLinkInfo"]["link"] = $invite_url;
      $data["dynamicLinkInfo"]["androidInfo"] = [];
      $data["dynamicLinkInfo"]["androidInfo"]["androidPackageName"] = "com.hatakehirodev.homework";

      $header = [
          'Content-Type: application/json',
      ];
      $context = stream_context_create(array(
          'http' => array(
              'method' => 'POST',
              'header' => implode(PHP_EOL,$header),
              'content'=>  json_encode($data),
              'ignore_errors' => true
          )
      ));

      $url = 'https://firebasedynamiclinks.googleapis.com/v1/shortLinks?key=AIzaSyAUVaiMn91rcZfamAR06k5HJGbThU7vOy4';
      $response = file_get_contents($url, false, $context);

      $resObj = json_decode($response, true);
      $results[invite_url] = $resObj["shortLink"];

      // 招待URLを返却する
      return $results;
    }
}
