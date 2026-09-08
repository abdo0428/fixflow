<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use App\Models\ServiceVisit;
use Illuminate\Notifications\Notification;

class TechnicianAssignedNotification extends Notification
{
    public function __construct(
        private readonly ServiceRequest $serviceRequest,
        private readonly ServiceVisit $serviceVisit,
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
            'title' => 'New assigned service visit',
            'message' => 'You have been assigned to service request: '.$this->serviceRequest->title,
            'service_request_id' => $this->serviceRequest->id,
            'service_visit_id' => $this->serviceVisit->id,
            'status' => $this->serviceRequest->status,
            'url' => route('service-requests.show', $this->serviceRequest, absolute: false),
        ];
    }
}
