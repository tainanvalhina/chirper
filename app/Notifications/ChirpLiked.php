<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Models\Chirp;

class ChirpLiked extends Notification
{
    use Queueable;

    public function __construct(
        public User $user,
        public Chirp $chirp
    ) {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'chirp_id' => $this->chirp->id,
            'message' => $this->user->name . ' curtiu sua publicação!',
        ];
    }
}