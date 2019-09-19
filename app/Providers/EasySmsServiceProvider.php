<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Overtrue\EasySms\EasySms;
use Qcloud\Sms\SmsSingleSender;

class EasySmsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // 腾讯云
        $this->app->singleton(SmsSingleSender::class,function ($app){
            $deploy = config('services.qcloudsms');
            return new SmsSingleSender($deploy['appid'],$deploy['appkey']);
        });
        //
        $this->app->alias(SmsSingleSender::class,'qcloudsms');

        // $this->app->singleton(EasySms::class,function ($app){
        //     return new EasySms(config('easysms'));
        // });
        // $this->app->alias(EasySms::class,'easysms');
    }
}
