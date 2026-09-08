<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    public const DEFAULT_TAX_RATE = 0.15;

    public function createFromServiceRequest(ServiceRequest $serviceRequest, User $actor, float $serviceCost, float $taxRate = self::DEFAULT_TAX_RATE, string $status = 'draft'): Invoice
    {
        if (! $actor->can('createInvoice', $serviceRequest)) {
            throw new AuthorizationException;
        }

        if ($serviceRequest->invoice()->exists()) {
            throw ValidationException::withMessages([
                'invoice' => 'This service request already has an invoice.',
            ]);
        }

        if (! in_array($status, ['draft', 'issued'], true)) {
            throw ValidationException::withMessages([
                'status' => 'New invoices can only be created as draft or issued.',
            ]);
        }

        return DB::transaction(function () use ($serviceRequest, $actor, $serviceCost, $taxRate, $status): Invoice {
            $partsTotal = $this->partsTotal($serviceRequest);
            $subtotal = round($serviceCost + $partsTotal, 2);
            $tax = round($subtotal * $taxRate, 2);
            $total = round($subtotal + $tax, 2);

            $invoice = Invoice::create([
                'company_id' => $serviceRequest->company_id,
                'service_request_id' => $serviceRequest->id,
                'invoice_number' => $this->nextInvoiceNumber($serviceRequest->company_id),
                'service_cost' => $serviceCost,
                'parts_total' => $partsTotal,
                'tax_rate' => $taxRate,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'status' => $status,
                'issued_at' => $status === 'issued' ? now() : null,
                'paid_at' => null,
            ]);

            $this->audit($serviceRequest, $actor, 'invoice_created', null, [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'service_cost' => $serviceCost,
                'parts_total' => $partsTotal,
                'tax' => $tax,
                'total' => $total,
                'status' => $status,
            ]);

            return $invoice;
        });
    }

    public function markAsPaid(Invoice $invoice, User $actor): Invoice
    {
        if (! $actor->can('update', $invoice)) {
            throw new AuthorizationException;
        }

        if ($invoice->status === 'cancelled') {
            throw ValidationException::withMessages([
                'status' => 'Cancelled invoices cannot be marked as paid.',
            ]);
        }

        $oldStatus = $invoice->status;

        $invoice->forceFill([
            'status' => 'paid',
            'issued_at' => $invoice->issued_at ?? now(),
            'paid_at' => now(),
        ])->save();

        $this->audit($invoice->serviceRequest()->firstOrFail(), $actor, 'invoice_paid', [
            'status' => $oldStatus,
        ], [
            'invoice_id' => $invoice->id,
            'status' => 'paid',
            'paid_at' => $invoice->paid_at?->toISOString(),
        ]);

        return $invoice->refresh();
    }

    private function partsTotal(ServiceRequest $serviceRequest): float
    {
        return (float) $serviceRequest->partsUsed()
            ->get()
            ->sum(fn ($partUsed): float => $partUsed->quantity * (float) $partUsed->unit_price);
    }

    private function nextInvoiceNumber(int $companyId): string
    {
        $year = now()->format('Y');
        $sequence = Invoice::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->whereYear('created_at', now()->year)
            ->count() + 1;

        do {
            $number = sprintf('INV-%s-%04d', $year, $sequence++);
        } while (Invoice::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->where('invoice_number', $number)
            ->exists());

        return $number;
    }

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    private function audit(ServiceRequest $serviceRequest, User $actor, string $action, ?array $oldValues, ?array $newValues): void
    {
        AuditLog::create([
            'company_id' => $serviceRequest->company_id,
            'user_id' => $actor->id,
            'action' => $action,
            'model_type' => ServiceRequest::class,
            'model_id' => $serviceRequest->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()?->ip(),
        ]);
    }
}
