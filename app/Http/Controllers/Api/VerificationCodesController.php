<?php

namespace App\Http\Controllers\Api;


use App\Http\Requests\Api\VerificationCodeRequest;
use Illuminate\Support\Facades\Cache;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
use Qcloud\Sms\SmsSingleSender;

class VerificationCodesController extends Controller
{

    public function store(VerificationCodeRequest $request,SmsSingleSender $smsSingleSender)
    {
        $captchaData = Cache::get($request->captcha_key);

        if (!$captchaData){
            return $this->response->error('图片验证码已失效',422);
        }

        //hash_equals 可防止时序攻击的字符串比较，不会因为比对字符串前几位不同而出现返回时间差
        if (!hash_equals($captchaData['code'],$request->captcha_code)){
            // 验证错误清除
            Cache::forget($request->captcha_key);
            return $this->response->errorUnauthorized('验证码错误');
        }


        $phone = $captchaData['phone'];

        if (!app()->environment('production')){
            $code = '1234';
        }else{
            // 生成4位随机数，左侧补零
            $code = str_pad(random_int(1,9999),4,0,STR_PAD_LEFT);

            if (!in_array($phone,['18390757710'])){
                return $this->response->error('手机号码不在测试名单内',422);
            }


            try{
                $result = $smsSingleSender->sendWithParam("86", $phone,98678,[$code]);  // 签名参数未提供或者为空时，会使用默认签名发送短信
            }catch (\Exception $exception){
                $message = $exception;
                return $this->response->errorInternal($message ?: '短信发送异常');

            }
        }


        $key = 'verificationCode_'.str_random(15);
        $expiredAt = now()->addMinutes(10);

        //缓存验证码 10分钟过期
        Cache::put($key,['phone'=>$phone,'code'=>$code],$expiredAt);

        return $this->response->array([
            'key' => $key,
            'expired_at' => $expiredAt->toDateTimeString()
        ])->setStatusCode(201);
    }

}
