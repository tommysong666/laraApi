<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\Image;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UsersController extends Controller
{
    //
    public function store(UserRequest $request)
    {
        $verifyData = Cache::get($request->verification_key);
        if (!$verifyData) {
            abort('403', '验证码失效！');
        }
        if (!hash_equals((string)$verifyData['code'], $request->verification_code)) {
            throw new AuthenticationException('验证码错误');
        }
        $user=User::create([
            'name'  => $request->name,
            'phone' => $verifyData['phone'],
            'password'=>$request->password,
        ]);
        //清除验证码缓存
        Cache::forget($request->verification_key);
        return $this->apiResponse((new UserResource($user))->showSensitiveFields());
    }

    public function show(User $user)
    {
        return $this->apiResponse(new UserResource($user));
    }

    public function me(Request $request)
    {
        return $this->apiResponse((new UserResource($request->user()))->showSensitiveFields());
    }

    public function update(UserRequest $request)
    {
        $user=$request->user();
        $attributes=$request->only('name','email','introduction','registration_id');
        if($id=$request->avatar_image_id){
            $image=Image::find($id);
            $attributes['avatar']=$image->path;
        }
        $user->update($attributes);
        return $this->apiResponse((new UserResource($user))->showSensitiveFields());
    }

    public function activedIndex(User $user)
    {
        UserResource::wrap('data');
        return UserResource::collection($user->getActiveUsers());
    }
}
