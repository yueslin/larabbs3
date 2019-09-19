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

        $phone = $request->phone;

        if (!app()->environment('production')){
            $code = '1234';
        }else{
            // 生成4位随机数，左侧补零
            $code = str_pad(random_int(1,9999),4,0,STR_PAD_LEFT);

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
