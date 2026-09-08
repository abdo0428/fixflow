<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use Illuminate\Notifications\Notification;

class ServiceRequestStatusChangedNotification extends Notification
{
    public function __construct(
        private readonly ServiceRequest $serviceRequest,
        private readonly string $oldStatus,
        private readonly string $newStatus,
    ) {}

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
            'title' => 'Service request status changed',
            'message' => 'Your service request status changed from '.$this->oldStatus.' to '.$this->newStatus.'.',
            'service_request_id' => $this->serviceRequest->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'url' => route('service-requests.show', $this->serviceRequest, absolute: false),
        ];
    }
}
