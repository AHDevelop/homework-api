<?php

namespace App;

use Silex\Application;

class RoutesLoader
{
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->instantiateControllers();
    }

    private function instantiateControllers()
    {
        $this->app['users.controller'] = function() {
            return new Controllers\UsersController($this->app['users.service']);
        };

        $this->app['rooms.controller'] = function() {
            return new Controllers\RoomsController($this->app['rooms.service']);
        };

        $this->app['roomHomework.controller'] = function() {
            return new Controllers\RoomHomeworkController($this->app['roomHomework.service']);
        };

        $this->app['homeworkHist.controller'] = function() {
            return new Controllers\HomeworkHistController($this->app['homeworkHist.service']);
        };
    }

    public function bindRoutesToControllers()
    {
        $api = $this->app["controllers_factory"];

        /*
        * 部屋
        */
        // 部屋一覧取得
        $api->get('/rooms/user_id={id}', "rooms.controller:getAll");
        // 部屋設定更新
        $api->put('room/update.json', "rooms.controller:update");

        /*
        * ユーザー
        */
        // 全ユーザー取得
        //$api->get('/users', "users.controller:getAll");
        // key(gmailでuser_masterをチェックし、存在確認する)
        $api->get('/users/key={key}&authToken={authToken}', "users.controller:getOneByKey");
        // IDでユーザーを取得
        $api->get('/users/{id}', "users.controller:getOne");
        // 新規ユーザー登録
        $api->post('/users/update.json', "users.controller:insertUser");
        // ユーザー更新
        $api->put('/users/update.json', "users.controller:updateUser");

        // 部屋ユーザー一覧取得
        $api->get('/users/room_id={roomId}', "users.controller:getAllWithRoom");
        // 部屋ユーザー追加
        $api->post('/room/users/update.json', "users.controller:insertUserWithRoom");
        // 部屋ユーザー削除
        $api->delete('/room/users/update.json', "users.controller:deleteUserWithRoom");

        /*
        * 部屋別家事
        */
        // 家事一覧取得
        $api->get('/homework/{roomId}', "roomHomework.controller:getAll");
        // 部屋別家事登録
        $api->post('/room/homework/update.json', "roomHomework.controller:insert");
        // 部屋別家事更新
        $api->put('/room/homework/update.json', "roomHomework.controller:update");
        // 部屋別家事削除
        $api->delete('/room/homework/update.json', "roomHomework.controller:delete");

        /*
        * 家事履歴
        */
        // 家事履歴一覧取得
        $api->get('/homeworkhist/room_id={roomId}', "homeworkHist.controller:getAll");
        // 家事履歴登録
        $api->post('/homeworkhist/update.json', "homeworkHist.controller:insert");
        // 家事履歴更新
        $api->put('/homeworkhist/update.json', "homeworkHist.controller:update");
        // 家事履歴削除
        $api->delete('/homeworkhist/update.json', "homeworkHist.controller:delete");
        // ユーザー別家事集計取得
        $api->get('/homeworkhist/summary', "homeworkHist.controller:getSummary");

        $this->app->mount($this->app["api.endpoint"].'/'.$this->app["api.version"], $api);
      }
    }
