<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use App\Policies\Concerns\HandlesTenantAuthorization;

class InvoicePolicy
{
    use HandlesTenantAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        return $this->isSuperAdmin($user) ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->company_id !== null
            && (
                $this->hasPermission($user, 'manage invoices')
                || $this->hasPermission($user, 'manage service requests')
                || $user->hasRole('customer')
            );
    }

    public function view(User $user, Invoice $invoice): bool
    {
        if (! $this->sameCompany($user, $invoice)) {
            return false;
        }

        if ($user->hasRole('customer')) {
            return (int) $invoice->serviceRequest?->customer?->user_id === (int) $user->id;
        }

        return $this->hasPermission($user, 'manage invoices')
            || $this->hasPermission($user, 'manage service requests');
    }

    public function create(User $user): bool
    {
        return $user->company_id !== null && $this->hasPermission($user, 'manage invoices');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $this->sameCompany($user, $invoice)
            && $this->hasPermission($user, 'manage invoices');
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $this->update($user, $invoice);
    }
}
