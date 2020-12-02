<?php

namespace App\Http\Controllers\Api;


use App\Http\Requests\Api\AuthorizationRequest;
use App\Http\Requests\Api\SocialAuthorizationRequest;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Overtrue\LaravelSocialite\Socialite;
use Overtrue\Socialite\AccessToken;

/**
 * Class AuthorizationsController
 * @package App\Http\Controllers\Api
 */
class AuthorizationsController extends Controller
{
    /**
     * 第三方登陆接口
     * @param $type
     * @param SocialAuthorizationRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws AuthenticationException
     */
    public function socialStore($type, SocialAuthorizationRequest $request)
    {
        try {
            $driver = Socialite::driver($type);
            if ($code = $request->code) {
                $accessToken = $driver->getAccessToken($code);
            } else {
                $tokenData['access_token'] = $request->access_token;
                if ($type == 'wechat') {
                    $tokenData['openid'] = $request->openid;
                }
                $accessToken = new AccessToken($tokenData);
            }
            $oauthUser = $driver->user($accessToken);

            if (isset($oauthUser->getOriginal()['errcode'])) {
                throw new AuthenticationException('参数错误，未获取用户信息！');
            }

        } catch (\Exception $e) {
            throw new AuthenticationException('参数错误，未获取用户信息！');
        }

        switch ($type) {
            case 'wechat':
                $unionid = $oauthUser->getOriginal()['unionid'] ?? null;
                if ($unionid) {
                    $user = User::where('wechat_unionid', $unionid)->first();

                } else {
                    $user = User::where('wechat_openid', $oauthUser->getId())->first();
                }
                if (!$user) {
                    $user = User::create([
                        'wechat_openid'  => $oauthUser->getId(),
                        'name'           => $oauthUser->getNickname(),
                        'avatar'         => $oauthUser->getAvatar(),
                        'wechat_unionid' => $unionid,
                    ]);
                }
                break;
        }
        $token = auth('api')->login($user);
        return $this->apiResponse($this->respondToken($token));
    }

    /**
     * 手机号或邮箱登陆
     * @param AuthorizationRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws AuthenticationException
     */
    public function store(AuthorizationRequest $request)
    {
        $username = $request->username;

        filter_var($username, FILTER_VALIDATE_EMAIL) ?
            $credentials['email'] = $username :
            $credentials['phone'] = $username;

        $credentials['password'] = $request->password;

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            throw new AuthenticationException(trans('auth.failed'));
        }
        return $this->apiResponse($this->respondToken($token));
    }


    /**
     * 刷新token
     * @return \Illuminate\Http\JsonResponse
     */
    public function update()
    {
        $token=auth('api')->refresh();
        return $this->apiResponse($this->respondToken($token));
    }

    /**
     * 退出登陆
     * @return mixed
     */
    public function destroy()
    {
        auth('api')->logout();
        return $this->apiResponse(true);
    }

    /**
     * 返回token数据通用方法
     * @param $token
     * @return array
     */
    public function respondToken($token)
    {
        $data = [
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60,
        ];
        return $data;
    }
}
