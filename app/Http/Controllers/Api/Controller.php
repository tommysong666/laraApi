<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;

class Controller extends BaseController
{
    /**
     * @param $data
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function apiResponse($data,  $code = 0)
    {
        if($code == 0){
            return response()->json([
                'resultStatus'  => $code == 0 ? true : false,
                'errorCode'     => $code  !=0 ? $code : null,
                'errorMessage'  => null,
                'resultData'    => $data

            ]);
        }else{
            return response()->json([
                'resultStatus'  =>  false,
                'errorCode'     => $code['errorCode'],
                'errorMessage'  => $code['errorMessage'],
                'resultData'    => null

            ]);
        }

    }
}
