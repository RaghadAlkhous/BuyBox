<?php

namespace App\Services;

use Kreait\Firebase\Factory;

class FcmService
{
    protected $messaging;

    protected $userRepository;

    public function __construct()
    {
        $firebase = (new Factory())
            ->withServiceAccount(storage_path(env('FIREBASE_CREDENTIALS')));

        $this->messaging = $firebase->createMessaging();
    }

    public function sendNotification($deviceToken, $title, $body)
    {
        $notification = \Kreait\Firebase\Messaging\Notification::create($title, $body);

        $message = \Kreait\Firebase\Messaging\CloudMessage::withTarget('token', $deviceToken)
            ->withNotification($notification);

        return $this->messaging->send($message);
    }
    public function notifyUsers($status)
    {
        $user = request()->user();
        $title = 'Order State';
        $body = $status;

        if($user["fcm-token"]){
            $this->sendNotification($user["fcm-token"], $title, $body);
        }



    }
}
