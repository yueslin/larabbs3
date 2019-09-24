<?php

namespace App\Providers;

use App\Models\Topic;
use App\Observers\TopicObserver;
use Dingo\Api\Facade\API;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        if (app()->isLocal()) {
            $this->app->register(ServiceProvider::class);
        }

        API::error(function  (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException  $exception)  {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(404,  '404 Not Found');
        });
        API::error(function (\Illuminate\Auth\Access\AuthorizationException $exception) {
            abort(403, $exception->getMessage());
        });

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
	{
        // 注册模型观察器
        \App\Models\User::observe(\App\Observers\UserObserver::class);
		\App\Models\Reply::observe(\App\Observers\ReplyObserver::class);
		\App\Models\Topic::observe(\App\Observers\TopicObserver::class);
		\App\Models\Link::observe(\App\Observers\LinkObserver::class);

        // 设置迁移文件 数据库字段默认长度
        Schema::defaultStringLength(250);

    }
}
