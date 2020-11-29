<?php

namespace App\Http\Controllers\Api;


use App\Http\Requests\Api\TopicRequest;
use App\Http\Resources\TopicResource;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TopicsController extends Controller
{
    public function index(Topic $topic, Request $request)
    {
        /*$query = $topic->query();
        if ($categoryId = $request->catetory_id) {
            $query->where('category_id', $categoryId);
        }

        $topics = $query->with('user','category')->withOrder($request->order)->paginate();*/
        $topics=QueryBuilder::for(Topic::class)
            ->allowedIncludes('user','category')
            ->allowedFilters([
                'title',
                AllowedFilter::exact('category_id'),
                AllowedFilter::scope('withOrder')->default('recentReplied'),
                ])->paginate();
        return TopicResource::collection($topics);
    }

    public function store(TopicRequest $request, Topic $topic)
    {
        $topic = $topic->fill($request->all());
        $topic->user_id = $request->user()->id;
        $topic->save();
        return new TopicResource($topic);
    }

    public function update(Topic $topic, TopicRequest $request)
    {
        $this->authorize('update', $topic);
        $topic->update($request->all());
        return new TopicResource($topic);
    }

    public function destroy(Topic $topic)
    {
        $this->authorize('destroy', $topic);
        $topic->delete();
        return $this->apiResponse(true);
    }

    public function userIndex(User $user)
    {
        $query=$user->topics()->getQuery();
        $topics=QueryBuilder::for($query)
            ->allowedIncludes('user','category')
            ->allowedFilters([
                'title',
                AllowedFilter::exact('category_id'),
                AllowedFilter::scope('withOrder')->default('recent')
            ])->paginate();
        return TopicResource::collection($topics);
    }
}
