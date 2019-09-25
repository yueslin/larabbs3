<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ReplyRequest;
use App\Models\Reply;
use App\Models\Topic;
use App\Transformers\ReplyTransformer;

class RepliesController extends Controller
{
    public function store(ReplyRequest $request, Topic $topic, Reply $reply)
    {
        $reply->content = $request->contents;
        $reply->topic()->associate($topic);
        $reply->user()->associate($this->user());
        $reply->save();
        return $this->response->item($reply,new ReplyTransformer())
            ->setStatusCode(201);

    }




}
