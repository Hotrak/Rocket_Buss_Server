<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index($userId){
        $user = User::find($userId);

        $notifications = [];
        foreach ($user->unreadNotifications as $notification) {
//            if($notification->data)
            $notifications[] = [
                'id' => $notification->id,
                'title'=>$notification->data
            ];
        }

        return $notifications;
    }

    public function markAsRead($userId,$id){
        $user = User::find($userId);
        foreach ($user->unreadNotifications as $notification) {
            if($notification->id = $id){
                $notification->markAsRead();
                break;
            }
        }
    }
}
