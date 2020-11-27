<?php

namespace App\Http\Controllers\Api;


use App\Http\Requests\Api\SocialAuthorizationRequest;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Overtrue\LaravelSocialite\Socialite;
use Overtrue\Socialite\AccessToken;

class AuthorizationsController extends Controller
{
    public function socialStore($type,SocialAuthorizationRequest $request)
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

            if(isset($oauthUser->getOriginal()['errcode'])){
                throw new AuthenticationException('参数错误，未获取用户信息！');
            }

        } catch (\Exception $e) {
            throw new AuthenticationException('参数错误，未获取用户信息！');
        }

        switch ($type){
            case 'wechat':
                $unionid=$oauthUser->getOriginal()['unionid']??null;
                if ($unionid){
                    $user=User::where('wechat_unionid',$unionid)->first();

                }else{
                    $user=User::where('wechat_openid',$oauthUser->getId())->first();
                }
                if(!$user){
                    $user=User::create([
                        'wechat_openid'=>$oauthUser->getId(),
                        'name'=>$oauthUser->getNickname(),
                        'avatar'=>$oauthUser->getAvatar(),
                        'wechat_unionid'=>$unionid
                    ]);
                }
                break;
        }
        return $this->apiResponse(['token'=>$user->id]);
    }
}
