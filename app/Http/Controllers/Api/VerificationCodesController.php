<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\VerificationCodeRequest;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\InvalidArgumentException;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;

class VerificationCodesController extends Controller
{
    public function store(VerificationCodeRequest $request,EasySms $sms)
    {

        $captchaData=Cache::get($request->captcha_key);
        if(!$captchaData){
            abort(403,'图片验证码失效');
        }
        if(!hash_equals(strtolower($captchaData['captcha']),strtolower($request->captcha_code))){
            Cache::forget($request->captcha_key);
            return new AuthenticationException('图片验证码错误');
        }
        $phone=$captchaData['phone'];

        if(!app()->environment('production')){
            $code=1234;
        }else{
            $code=str_pad(random_int(1,9999),4,0,STR_PAD_LEFT);
            try {
                $sms->send($phone, [
                    'template' => config('easysms.gateways.aliyun.templates.register'),
                    'data'     => [
                        'code' => $code,
                    ],
                ]);
            } catch (NoGatewayAvailableException $e) {
                $message=$e->getException('aliyun')->getMessage();
                abort(500,$message?:'发送短信异常');
            }
        }
        $key='verificationCode_'.Str::random(15);
        $expiredAt=now()->addMinutes(5);
        Cache::put($key,['phone'=>$phone,'code'=>$code],$expiredAt);
        $data=[
            'key'=>$key,
            'expired_at'=>$expiredAt->toDateTimeString()
        ];
        return $this->apiResponse($data);
    }
}
