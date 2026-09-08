<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\ServiceAsset;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CustomerPortalService
{
    public const OPEN_STATUSES = [
        'new',
        'under_review',
        'scheduled',
        'in_progress',
        'waiting_parts',
    ];

    public function customerFor(User $user): ?Customer
    {
        return Customer::with('user')
            ->where('user_id', $user->id)
            ->first();
    }

    public function requestsFor(Customer $customer): Builder
    {
        return ServiceRequest::query()
            ->where('customer_id', $customer->id);
    }

    /**
     * @return Collection<int, array{asset: ServiceAsset, warranty_label: string, warranty_tone: string}>
     */
    public function assetSummaries(Customer $customer): Collection
    {
        return ServiceAsset::query()
            ->where('customer_id', $customer->id)
            ->latest()
            ->get()
            ->map(fn (ServiceAsset $asset): array => [
                'asset' => $asset,
                'warranty_label' => $this->warrantyLabel($asset),
                'warranty_tone' => $this->warrantyTone($asset),
            ]);
    }

    /**
     * @param  Collection<int, int>  $requestIds
     * @return Collection<int, Collection<int, AuditLog>>
     */
    public function timelinesFor(Collection $requestIds): Collection
    {
        if ($requestIds->isEmpty()) {
            return collect();
        }

        return AuditLog::with('user')
            ->where('model_type', ServiceRequest::class)
            ->whereIn('model_id', $requestIds->all())
            ->oldest()
            ->get()
            ->groupBy('model_id');
    }

    public function warrantyLabel(ServiceAsset $asset): string
    {
        if (! $asset->warranty_start_date || ! $asset->warranty_end_date) {
            return 'غير موثق';
        }

        if (today()->lt($asset->warranty_start_date)) {
            return 'لم يبدأ بعد';
        }

        if (today()->gt($asset->warranty_end_date)) {
            return 'منتهي';
        }

        return 'ساري حتى '.$asset->warranty_end_date->format('Y-m-d');
    }

    public function warrantyTone(ServiceAsset $asset): string
    {
        if (! $asset->warranty_start_date || ! $asset->warranty_end_date) {
            return 'gray';
        }

        if (today()->betweenIncluded($asset->warranty_start_date, $asset->warranty_end_date)) {
            return 'green';
        }

        return 'red';
    }
}
