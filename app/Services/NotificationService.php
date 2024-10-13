<?php

namespace App\Services;

use Kreait\Firebase\Factory;

class NotificationService
{
    protected $firebase;
    protected $projectId;

    public function __construct()
    {
        // Load Firebase using the factory with the service account JSON file
        $factory = (new Factory)->withServiceAccount(config('firebase.credentials'));
        $this->firebase = $factory;
        
        // Retrieve the Project ID directly from the JSON file
        $serviceAccount = json_decode(file_get_contents(config('firebase.credentials')), true);
        $this->projectId = $serviceAccount['project_id'];
    }

    /**
     * Send a push notification using Firebase Cloud Messaging API V1.
     *
     * @param string $fcmToken
     * @param string $likerName
     * @param string $deckTitle
     * @return array
     */
    public function sendLikeNotification($fcmToken, $likerName, $deckTitle, $deckId)
    {
        $messaging = $this->firebase->createMessaging();

        $message = [
            'token' => $fcmToken,
            'notification' => [
                'title' => 'Flashy',
                'body' => "$likerName liked your deck: $deckTitle",
            ],
            'data' => [
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'notification_type' => 'deck_like',
                'deck_id' => $deckId,
            ],
        ];

        try {
            $messaging->send($message);
            return ['message' => 'Notification sent successfully', 'project_id' => $this->projectId];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
