<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\ServiceAssetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['company_id', 'customer_id', 'name', 'type', 'brand', 'model', 'serial_number', 'qr_code', 'purchase_date', 'warranty_start_date', 'warranty_end_date', 'notes', 'status'])]
class ServiceAsset extends Model
{
    /** @use HasFactory<ServiceAssetFactory> */
    use BelongsToCompany, HasFactory;

    protected static function booted(): void
    {
        static::creating(function (ServiceAsset $serviceAsset): void {
            if (! $serviceAsset->qr_code) {
                $serviceAsset->qr_code = self::generateQrCodeToken();
            }
        });
    }

    public static function generateQrCodeToken(): string
    {
        do {
            $token = 'asset_'.Str::lower((string) Str::ulid());
        } while (self::withoutGlobalScope('company')->where('qr_code', $token)->exists());

        return $token;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'warranty_start_date' => 'date',
            'warranty_end_date' => 'date',
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

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }
}
