<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    protected $fillable = [
      'name','description'
    ];

    public function categories()
    {
        if (Cache::missing("categories")){
            Cache::put("categories",$this->all(),1800);
        }
        return Cache::get("categories");
    }

}
