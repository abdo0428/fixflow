<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\PartUsedFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['company_id', 'service_request_id', 'part_id', 'quantity', 'unit_price'])]
class PartUsed extends Model
{
    /** @use HasFactory<PartUsedFactory> */
    use BelongsToCompany, HasFactory;

    protected $table = 'parts_used';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
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

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }
}
