<?php

namespace App\Http\Requests;

use App\Models\ServiceAsset;
use Illuminate\Validation\Rule;

class StoreCustomerServiceRequestRequest extends StoreServiceRequestRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'service_asset_id' => ['required', 'integer', Rule::exists(ServiceAsset::class, 'id')],
        ];
    }
}
