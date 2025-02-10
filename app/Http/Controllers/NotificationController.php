<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\PushNotification;
use DB;

class NotificationController extends Controller
{
    use PushNotification;

    public function sendPushNotification(Request $request)
    {
        // Sample device token and notification details
        $deviceToken = 'dxVPboLARUWD8iOQ-6MDES:APA91bHJil0jttnnbfNPnGIinMh8IwWzlmbIdClo2zl8xkbgADezaih1XGmUl9O4yEkI0rndZvVuNbIBKzUQAmDnhTp9VvkGSE-qywnDxR6CV8okP7-8m9k';  // Replace with actual device token
        $title = 'Info91';
        $body = 'You have a notification from Info91';
        $data = [
            'click_action' => 'FLUTTER_NOTIICATION_CLICK',
            'message' => 'hello akshay. vellom nadakuo',
            'group_id' => 'koratty police group',
        ];

        // Call the correct method from the trait (sendNotification instead of sendPushNotificationToDevice)
        $response = $this->sendNotification($deviceToken, $title, $body, $data);

        return response()->json([
            'message' => 'Notification sent successfully!',
            'response' => $response // No need to decode here since it's already an array
        ]);
    }

    public function sendMultiplePushNotifications(Request $request)
    {
        
        $fcm_token=$request->fcm_tokens;
        $title = 'Info91';
        $body = 'You have a multiple notification';
        $data = [
            'click_action' => 'FLUTTER_NOTIICATION_CLICK',
            'message' => $request->group_message ?? null,
            'group_id' => $request->group_id ?? null,
            'group_name' => $request->group_name ?? null,
            'type'=>$request->type ?? null,
            'time'=>$request->time ?? null
        ];
        
        foreach($fcm_token as $token){
            $response = $this->sendNotification($token, $title, $body, $data);
            $responses[] = $response;
        }
        return response()->json([
            'message' => 'Notifications sent successfully!',
            'responses' => $responses // Return all the responses from the notifications
        ]);
    } 
}