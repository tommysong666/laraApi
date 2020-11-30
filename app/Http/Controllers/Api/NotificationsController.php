<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->paginate();
        return NotificationResource::collection($notifications);
    }

    public function stats(Request $request)
    {
        return $this->apiResponse(['unread_count'=>$request->user()->notification_count]);
    }

    public function read(Request $request)
    {
        $request->user()->markAsRead();
        return $this->apiResponse(true);
    }
}
