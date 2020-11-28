<?php

namespace App\Http\Requests\Api;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        switch ($this->method()) {
            case 'POST':
                return [
                    'name'              => 'required|between:3,25|regex:/^[A-Za-z0-9\-\_]+$/',
                    'password'          => 'required|min:6|string',
                    'verification_key'  => 'required|string',
                    'verification_code' => 'required|string',
                ];
                break;
            case 'PATCH':
                $userId = auth('api')->id();
                return [
                    'name'            => 'between:3,25|regex:/^[A-Za-z0-9\-\_]+$/',
                    'email'           => 'email|unique:users,email,' . $userId,
                    'introduction'    => 'max:80',
                    'avatar_image_id' => 'exists:images,id,type,avatar,user_id,' . $userId,
                ];
                break;
        }
    }

    public function attributes()
    {
        return [
            'verification_key'  => '短信验证码key',
            'verification_code' => '短信验证码',
        ];
    }
}
