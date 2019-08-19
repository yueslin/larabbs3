<?php

// 判断导航栏选中状态
// function category_nav_active()
// {
//
// }

function category_nav_active($category_id)
{
    return active_class((if_route('categories.show') && if_route_param('category', $category_id)));
}

function route_class()
{
    return str_replace('.', '-', Route::currentRouteName());
}


function make_excerpt($value,$length = 200)
{
    $excerpt = trim(preg_replace('/\r\n|\r|\n+/',' ',strip_tags($value)));
    return str_limit($excerpt,$length);
}

