<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\UserRequest;
use App\Models\User;
use App\Transformers\UserTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class UsersController extends Controller
{

    public function store(UserRequest $request)
    {
        $verifyData = Cache::get($request->verification_key);

        if (!$verifyData){
            return $this->response->error('验证码已失效',422);
        }

        //hash_equals 可防止时序攻击的字符串比较，不会因为比对字符串前几位不同而出现返回时间差
        if(!hash_equals($verifyData['code'],$request->verification_code)){
            // 返回401
            return $this->response->errorUnauthorized('验证码错误');
        }

        $user = User::create([
            'name' => $request->name,
            'phone' => $verifyData['phone'],
            'password' => bcrypt($request->password),
        ]);

        // 清除验证码缓存
        Cache::forget($request->verification_key);

        return $this->response->item($user,new UserTransformer())
            ->setMeta([
                'access_token' => Auth::guard('api')->fromUser($user),
                'token_type' => 'Bearer',
                'expires_in' => Auth::guard('api')->factory()->getTTL() * 60
            ])
            ->setStatusCode(201);
        // return $this->response->created();

    }

    public function me()
    {
        $res = new UserTransformer();
        // return $this->response->array($res->transform($this->user()));
        return $this->response->item($this->user(),new UserTransformer());
    }


}