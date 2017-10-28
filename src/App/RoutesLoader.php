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

        $this->app['roomHomework.controller'] = function() {
            return new Controllers\RoomHomeworkController($this->app['roomHomework.service']);
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

        $api->get('/users', "users.controller:getAll");
        $api->get('/users/{id}', "users.controller:getOne");

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

        $this->app->mount($this->app["api.endpoint"].'/'.$this->app["api.version"], $api);
    }
}
