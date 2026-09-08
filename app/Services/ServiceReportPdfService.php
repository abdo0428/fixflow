<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\ServiceReport;
use App\Models\ServiceRequest;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ServiceReportPdfService
{
    public function generate(ServiceRequest $serviceRequest, User $actor): ServiceReport
    {
        if (! $actor->can('generateReportPdf', $serviceRequest)) {
            throw new AuthorizationException;
        }

        $serviceRequest->load([
            'company',
            'customer',
            'serviceAsset',
            'visits.technician',
            'partsUsed.part',
            'report.technician',
        ]);

        $report = $serviceRequest->report;

        if (! $report) {
            throw ValidationException::withMessages([
                'report' => 'A service report is required before generating a PDF.',
            ]);
        }

        $pdf = Pdf::loadView('pdf.service-report', [
            'serviceRequest' => $serviceRequest,
            'report' => $report,
            'visit' => $serviceRequest->visits->sortByDesc('scheduled_at')->first(),
            'partsTotal' => $serviceRequest->partsUsed->sum(
                fn ($partUsed): float => $partUsed->quantity * (float) $partUsed->unit_price,
            ),
        ])->setPaper('a4');

        $path = 'reports/service-requests/service-request-'.$serviceRequest->id.'.pdf';

        Storage::disk('public')->put($path, $pdf->output());

        $report->forceFill(['pdf_path' => $path])->save();

        AuditLog::create([
            'company_id' => $serviceRequest->company_id,
            'user_id' => $actor->id,
            'action' => 'service_report_pdf_generated',
            'model_type' => ServiceRequest::class,
            'model_id' => $serviceRequest->id,
            'old_values' => null,
            'new_values' => ['service_report_id' => $report->id, 'pdf_path' => $path],
            'ip_address' => request()?->ip(),
        ]);

        return $report->refresh();
    }
}
