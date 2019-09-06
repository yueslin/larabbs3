<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmailContract
{
    use HasRoles;
    use MustVerifyEmailTrait;
    use Notifiable {
        notify as protected laravelNotify;
    }
    // 重新写 Notifiable notify 方法
    public function notify($instance)
    {
        // 如果要通知的人是当前用户，就不必通知了
        if ($this->id == Auth::id()){
            return;
        }
        // 只有数据库类型通知才需要提醒，直接发送 Email 或者其他的都 pass
        if (method_exists($instance,'toDatabase')){
            $this->increment('notification_count');
        }
        $this->laravelNotify($instance);
    }

    protected $fillable = [
        'name', 'email', 'password','introduction','avatar'
    ];
    protected $hidden = [
        'password', 'remember_token',
    ];
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function topics()
    {
        return $this->hasMany(Topic::class);
    }

    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    // 策略判断
    public function isAuthorOf($model)
    {
        return $this->id == $model->user_id;
    }

    // 刷新通知信息
    public function markAsRead()
    {
        $this->notification_count = 0;
        $this->save();
        // Notifiable -> HasDatabaseNotifications 里的方法，用于更新通知时间
        $this->unreadNotifications->markAsRead();
    }

    // 让即将保存的密码加密
    public function setPasswordAttribute($value)
    {
        // 如果值的长度等于 60，即认为是已经做过加密的情况
        if (strlen($value) != 60){
            // 不等于 60，做密码加密处理
            $this->attributes['password'] = bcrypt($value);
        }
    }

    public function setAvatarAttribute($path)
    {
        // 如果不是 'http' 子串开头，那就是从后台上传的，需要补全URL
        if (!starts_with($path,'http')){

            // 拼接完整的 URL
            $path = config('app.url') . "/uploads/images/avatars/$path";
        }

        $this->attributes['avatar'] = $path;

    }

}
