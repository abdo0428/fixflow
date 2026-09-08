<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['company_id', 'service_request_id', 'invoice_number', 'service_cost', 'parts_total', 'tax_rate', 'subtotal', 'tax', 'total', 'status', 'issued_at', 'paid_at'])]
class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use BelongsToCompany, HasFactory;

    public const STATUSES = ['draft', 'issued', 'paid', 'cancelled'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'service_cost' => 'decimal:2',
            'parts_total' => 'decimal:2',
            'tax_rate' => 'decimal:4',
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'issued_at' => 'datetime',
            'paid_at' => 'datetime',
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
}
