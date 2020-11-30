<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\PermissionResource;
use Illuminate\Http\Request;

class PermissionsController extends Controller
{
    //当前登陆用户所有权限
    public function index(Request $request)
    {
        $permissions=$request->user()->getAllPermissions();
        PermissionResource::wrap('data');
        return PermissionResource::collection($permissions);
    }
}
