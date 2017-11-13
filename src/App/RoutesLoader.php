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
        $this->app['notes.controller'] = function() {
            return new Controllers\NotesController($this->app['notes.service']);
        };

        $this->app['users.controller'] = function() {
            return new Controllers\UsersController($this->app['users.service']);
        };

        $this->app['room.controller'] = function() {
            return new Controllers\RoomController($this->app['room.service']);
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

        $api->get('/notes', "notes.controller:getAll");
        $api->get('/notes/{id}', "notes.controller:getOne");
        $api->post('/notes', "notes.controller:save");
        $api->put('/notes/{id}', "notes.controller:update");
        $api->delete('/notes/{id}', "notes.controller:delete");

        /*
        * ユーザー
        */
        // 全ユーザー取得
        $api->get('/users', "users.controller:getAll");
        // IDでユーザーを取得
        $api->get('/users/user_id={id}', "users.controller:getOne");
        // 新規ユーザー登録
        $api->post('/users/update.json', "users.controller:insertUser");

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
        // 家事一覧&家事別時間取得
        $api->get('/homework/room_id={roomId}', "roomHomework.controller:getHomeworkWithTodayTime");
        // 部屋別家事登録
        $api->post('/room/homework/update.json', "roomHomework.controller:insert");
        // 部屋別家事更新
        $api->put('/room/homework/update.json', "roomHomework.controller:update");
        // 部屋別家事削除
        $api->delete('/room/homework/update.json', "roomHomework.controller:delete");

        /*
        * 家事履歴
        */
        // 家事履歴登録
        $api->post('/homeworkhist/update.json', "homeworkHist.controller:insert");
        // 家事履歴削除
        $api->delete('/homeworkhist/update.json', "homeworkHist.controller:delete");

        $this->app->mount($this->app["api.endpoint"].'/'.$this->app["api.version"], $api);
    }
}
