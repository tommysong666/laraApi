<?php

namespace App\Http\Controllers\Api;


use App\Http\Queries\TopicQuery;
use App\Http\Requests\Api\TopicRequest;
use App\Http\Resources\TopicResource;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * Class TopicsController
 * @package App\Http\Controllers\Api
 */
class TopicsController extends Controller
{
    /**
     * 查询所有话题
     * @param TopicQuery $query
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(TopicQuery $query, Request $request)
    {
        /*$query = $topic->query();
        if ($categoryId = $request->catetory_id) {
            $query->where('category_id', $categoryId);
        }

        $topics = $query->with('user','category')->withOrder($request->order)->paginate();*/
        $topics=$query->paginate();
        return TopicResource::collection($topics);
    }

    /**
     * 发布话题
     * @param TopicRequest $request
     * @param Topic $topic
     * @return TopicResource
     */
    public function store(TopicRequest $request, Topic $topic)
    {
        $topic = $topic->fill($request->all());
        $topic->user_id = $request->user()->id;
        $topic->save();
        return new TopicResource($topic);
    }

    /**
     * 修改话题
     * @param Topic $topic
     * @param TopicRequest $request
     * @return TopicResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Topic $topic, TopicRequest $request)
    {
        $this->authorize('update', $topic);
        $topic->update($request->all());
        return new TopicResource($topic);
    }

    /**
     * 删除话题
     * @param Topic $topic
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Topic $topic)
    {
        $this->authorize('destroy', $topic);
        $topic->delete();
        return response(null,204);
    }

    /**
     * 话题详情
     * @param $topicId
     * @param TopicQuery $query
     * @return TopicResource
     */
    public function show($topicId, TopicQuery $query)
    {
//        $topic->load('user','category');
        $topic=$query->findOrFail($topicId);
        return new TopicResource($topic);
    }

    /**
     * 查找某用户下的所有话题
     * @param TopicQuery $query
     * @param User $user
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function userIndex(TopicQuery $query, User $user)
    {
        /*$query=$user->topics()->getQuery();
        $topics=QueryBuilder::for($query)
            ->allowedIncludes('user','category')
            ->allowedFilters([
                'title',
                AllowedFilter::exact('category_id'),
                AllowedFilter::scope('withOrder')->default('recent')
            ])->paginate();*/
        $topics=$query->where('user_id',$user->id)->paginate();
        return TopicResource::collection($topics);
    }
}
