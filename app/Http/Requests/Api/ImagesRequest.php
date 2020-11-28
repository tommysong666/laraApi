<?php

namespace App\Http\Requests\Api;


class ImagesRequest extends FormRequest
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
        $rules = ['type' => 'required|string|in:avatar,topic'];
        if ($this->type == 'avatar') {
            $rules['image'] = 'required|mimes:jpg,jpeg,bmp,png,gif|dimensions:min_width:200,max_width:200';
        } else {
            $rules['image'] = 'required|mimes:jpg,jpeg,bmp,png,gif';
        }
        return $rules;
    }

    public function messages()
    {
        return ['image.dimensions' => '头像清晰度不够，宽高必须200px以上'];
    }
}
