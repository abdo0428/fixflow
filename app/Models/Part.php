<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\PartFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['company_id', 'name', 'sku', 'unit_price', 'quantity', 'low_stock_threshold'])]
class Part extends Model
{
    /** @use HasFactory<PartFactory> */
    use BelongsToCompany, HasFactory;

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

    public function partsUsed(): HasMany
    {
        return $this->hasMany(PartUsed::class);
    }
}
