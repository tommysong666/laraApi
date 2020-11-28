<?php

namespace App\Http\Controllers\Api;

use App\Handlers\ImageUploadHandler;
use App\Http\Requests\Api\ImagesRequest;
use App\Http\Resources\ImageResource;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ImagesController extends Controller
{
    public function store(ImagesRequest $request,ImageUploadHandler $uploader,Image $image)
    {
        $user=$request->user();
        $type=$request->type;
        $max_width=$request->type=='avatar'?416:1024;
        $result=$uploader->save($request->image,Str::plural($type),$user->id,$max_width);
        $image->user_id=$user->id;
        $image->type=$request->type;
        $image->path=$result['path'];
        $image->save();
        return $this->apiResponse(new ImageResource($image));
    }
}
