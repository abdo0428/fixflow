<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use Illuminate\Notifications\Notification;

class NewCustomerServiceRequestNotification extends Notification
{
    public function __construct(private readonly ServiceRequest $serviceRequest) {}

    /**
     * Get the notification delivery channels.
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
            'title' => 'New customer service request',
            'message' => 'A customer opened a new service request: '.$this->serviceRequest->title,
            'service_request_id' => $this->serviceRequest->id,
            'status' => $this->serviceRequest->status,
            'url' => route('service-requests.show', $this->serviceRequest, absolute: false),
        ];
    }
}
