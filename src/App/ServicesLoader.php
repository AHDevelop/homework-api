<?php

namespace App;

use Silex\Application;

class ServicesLoader
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function bindServicesIntoContainer()
    {
        $this->app['notes.service'] = function() {
            return new Services\NotesService($this->app["db"], $this->app["monolog"]);
        };

        // ユーザマスタ
        $this->app['users.service'] = function() {
            return new Services\UsersService($this->app["pdo"], $this->app["monolog"]);
        };

        // 部屋家事
        $this->app['roomHomework.service'] = function() {
            return new Services\RoomHomeworkService($this->app["pdo"], $this->app["monolog"]);
        };

        // 家事履歴
        $this->app['homeworkHist.service'] = function() {
            return new Services\HomeworkHistService($this->app["pdo"], $this->app["monolog"]);
        };

    }
}
