<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\ServiceReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['company_id', 'service_request_id', 'technician_id', 'diagnosis', 'solution', 'customer_signature', 'before_images', 'after_images', 'pdf_path'])]
class ServiceReport extends Model
{
    /** @use HasFactory<ServiceReportFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'before_images' => 'array',
            'after_images' => 'array',
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
