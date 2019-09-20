<?php


use App\Models\Topic;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Qcloud\Sms\SmsSingleSender;

Route::get('test',function (){
    phpinfo();
});

Route::get("send/qcloudsms",function (){

    // 需要发送短信的手机号码
    $phoneNumbers = ["18390757710"];
    // 短信模板ID，需要在短信应用中申请
    $templateId = 98678;  // NOTE: 这里的模板ID`7839`只是一个示例，真实的模板ID需要在短信控制台中申请
    $smsSign = "test"; // NOTE: 这里的签名只是示例，请使用真实的已申请的签名，签名参数使用的是`签名内容`，而不是`签名ID`
    $ssender = app("qcloudsms");
    $params = ["5678"];


    try {
        $result = $ssender->send(0, "86", $phoneNumbers[0],
            "【腾讯云】您的验证码是: 5678","", "");
        $rsp = json_decode($result);
        echo $result;
    } catch(\Exception $e) {
        echo var_dump($e);
    }
    // try {
    //     $result = $ssender->sendWithParam("86", $phoneNumbers[0], $templateId,$params, $smsSign);  // 签名参数未提供或者为空时，会使用默认签名发送短信
    //     dd(json_decode($result));
    //     echo $result;
    // } catch(\Exception $e) {
    //     dd($e);
    // }
});


Route::get("send/easysms",function (){
    $sms  =  app('easysms');
    try {
        $sms->send(18390757710, [
            'template' => 98678,
            'data' => [10000],
        ]);
    } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $exception) {
        $message = $exception->getException('qcloud')->getMessage();
        dd($message);
    }
});


Route::get('/', 'TopicsController@index')->name('root');

// 用户身份验证相关的路由
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

// 用户注册相关路由
Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('register', 'Auth\RegisterController@register');

// 密码重置相关路由
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');

// Email 认证相关路由
Route::get('email/verify', 'Auth\VerificationController@show')->name('verification.notice');
Route::get('email/verify/{id}', 'Auth\VerificationController@verify')->name('verification.verify');
Route::get('email/resend', 'Auth\VerificationController@resend')->name('verification.resend');

// 用户
//Route::resource('users', 'UsersController', ['only' => ['show', 'update', 'edit']]);
Route::get('/users/{user}', 'UsersController@show')->name('users.show');
Route::get('/users/{user}/edit', 'UsersController@edit')->name('users.edit');
Route::patch('/users/{user}', 'UsersController@update')->name('users.update');

Route::resource('topics', 'TopicsController', ['only' => ['index', 'create', 'store', 'update', 'edit', 'destroy']]);

Route::get('topics/{topic}/{slug?}', 'TopicsController@show')->name('topics.show');

Route::resource('categories', 'CategoriesController', ['only' => ['show']]);

//编辑器上传文件
Route::post('upload_image', 'TopicsController@uploadImage')->name('topics.upload_image');

Route::resource('replies', 'RepliesController', ['only' => ['store', 'destroy']]);

Route::resource('notifications', 'NotificationsController', ['only' => ['index']]);


//无权限提醒页面
Route::get('permission-denied', 'PagesController@permissionDenied')->name('permission-denied');
