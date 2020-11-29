<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ReplyRequest;
use App\Http\Resources\ReplyResource;
use App\Models\Reply;
use App\Models\Topic;
use Illuminate\Http\Request;

class RepliesController extends Controller
{
    public function store(ReplyRequest $request, Topic $topic, Reply $reply)
    {
        $reply->content = $request->content;
        $reply->topic()->associate($topic);
        $reply->user()->associate($request->user());
        $reply->save();
        /*$reply->content=$request->content;
        $reply->topic_id=$topic->id;
        $reply->user_id=$request->user()->id;
        $reply->save();*/

        return new ReplyResource($reply);
    }

    public function destroy(Topic $topic,Reply $reply)
    {
        if($reply->topic_id!=$topic->id){
            abort(404,'话题不存在');
        }
        $this->authorize('destroy',$reply);
        $reply->delete();
        return $this->apiResponse(true);
    }
}
