<?php

namespace App\Jobs;

use App\Services\NotificationService;
use App\Models\User;
use App\Models\Deck;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendLikeNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userName;
    protected $deckName;
    protected $deckId;
    protected $fcmToken;

    /**
     * Create a new job instance.
     */
    public function __construct($fcmToken, $userName, $deckName, $deckId)
    {
        $this->fcmToken = $fcmToken;
        $this->userName = $userName;
        $this->deckName = $deckName;
        $this->deckId = $deckId;
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService)
    {
        // Send the like notification to the deck owner
        $notificationService->sendLikeNotification($this->fcmToken, $this->userName, $this->deckName, $this->deckId);
    }
}
