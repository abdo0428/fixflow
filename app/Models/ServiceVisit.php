<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\ServiceVisitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['company_id', 'service_request_id', 'technician_id', 'scheduled_at', 'started_at', 'finished_at', 'visit_status', 'location_address', 'technician_notes'])]
class ServiceVisit extends Model
{
    /** @use HasFactory<ServiceVisitFactory> */
    use BelongsToCompany, HasFactory;

    public const VISIT_STATUSES = [
        'scheduled',
        'on_the_way',
        'in_progress',
        'completed',
        'cancelled',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
