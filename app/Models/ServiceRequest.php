<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\ServiceRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['company_id', 'customer_id', 'service_asset_id', 'created_by', 'title', 'description', 'priority', 'status', 'preferred_date', 'completed_at'])]
class ServiceRequest extends Model
{
    /** @use HasFactory<ServiceRequestFactory> */
    use BelongsToCompany, HasFactory;

    public const PRIORITIES = ['low', 'medium', 'high', 'urgent'];

    public const STATUSES = [
        'new',
        'under_review',
        'scheduled',
        'in_progress',
        'waiting_parts',
        'completed',
        'cancelled',
        'rejected',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'preferred_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function serviceAsset(): BelongsTo
    {
        return $this->belongsTo(ServiceAsset::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(ServiceVisit::class);
    }

    public function partsUsed(): HasMany
    {
        return $this->hasMany(PartUsed::class);
    }

    public function report(): HasOne
    {
        return $this->hasOne(ServiceReport::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'model_id')
            ->where('model_type', self::class)
            ->oldest();
    }
}
