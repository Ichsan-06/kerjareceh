<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTopUpRequest extends Notification
{
    use Queueable;

    public $topUp;
    public $user;

    /**
     * Create a new notification instance.
     */
    public function __construct($topUp, $user)
    {
        $this->topUp = $topUp;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'topup',
            'request_id' => $this->topUp->id,
            'amount' => $this->topUp->amount,
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'message' => "New Top Up request of {$this->topUp->amount} from {$this->user->name}",
            'link' => '/admin/topup', // Frontend link for navigation
        ];
    }
}
