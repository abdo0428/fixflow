<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="$serviceRequest->title" :subtitle="__('ui.service_requests.details_subtitle', ['id' => $serviceRequest->id])">
            <x-slot name="actions">
                <x-ui.badge :status="$serviceRequest->status" />
                @can('update', $serviceRequest)
                    <x-ui.button :href="route('service-requests.edit', $serviceRequest)" variant="secondary" size="sm">{{ __('ui.actions.edit') }}</x-ui.button>
                @endcan
                <x-ui.button :href="route('service-requests.index')" size="sm">{{ __('ui.actions.back_to_list') }}</x-ui.button>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="ff-page">
        <div class="ff-container space-y-6">
            @if (session('status'))
                <div class="ff-alert-success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="ff-alert-danger">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 space-y-6">
                    <div class="ff-card p-6">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <div class="text-sm text-slate-500">{{ __('ui.service_requests.customer') }}</div>
                                <div class="mt-1 font-medium text-slate-950">{{ $serviceRequest->customer?->name }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-slate-500">{{ __('ui.service_requests.asset') }}</div>
                                <div class="mt-1 font-medium text-slate-950">{{ $serviceRequest->serviceAsset?->name ?? __('ui.common.general_request') }}</div>
                                @if ($assetQrUrl)
                                    <a href="{{ $assetQrUrl }}" class="mt-1 inline-flex text-sm font-medium text-cyan-700 hover:text-cyan-900">
                                        {{ __('ui.actions.open_qr') }}
                                    </a>
                                @endif
                            </div>
                            <div>
                                <div class="text-sm text-slate-500">{{ __('ui.service_requests.priority') }}</div>
                                <div class="mt-1"><x-ui.badge :status="$serviceRequest->priority" /></div>
                            </div>
                            <div>
                                <div class="text-sm text-slate-500">{{ __('ui.service_requests.preferred_date') }}</div>
                                <div class="mt-1 font-medium text-slate-950">{{ optional($serviceRequest->preferred_date)->format('Y-m-d') ?? __('ui.common.not_defined') }}</div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <div class="text-sm text-slate-500">{{ __('ui.service_requests.description') }}</div>
                            <p class="mt-2 text-slate-800 whitespace-pre-line">{{ $serviceRequest->description ?: __('ui.common.no_description') }}</p>
                        </div>
                    </div>

                    <div class="ff-card overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 font-semibold text-slate-950">{{ __('ui.service_requests.visits') }}</div>
                        <div class="divide-y divide-slate-100">
                            @forelse ($serviceRequest->visits as $visit)
                                <div class="px-6 py-4">
                                    <div class="flex items-center justify-between gap-4">
                                        <div class="font-medium text-slate-950">{{ $visit->technician?->name }}</div>
                                        <x-ui.badge :status="$visit->visit_status" />
                                    </div>
                                    <div class="mt-1 text-sm text-slate-500">
                                        {{ optional($visit->scheduled_at)->format('Y-m-d H:i') }} - {{ $visit->location_address }}
                                    </div>
                                    @if ($visit->technician_notes)
                                        <div class="mt-2 text-sm text-slate-700">{{ $visit->technician_notes }}</div>
                                    @endif
                                </div>
                            @empty
                                <div class="p-6">
                                    <x-ui.empty-state :title="__('ui.service_requests.no_visits')" />
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="ff-card overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 font-semibold text-slate-950">{{ __('ui.service_requests.used_parts') }}</div>
                        <div class="divide-y divide-slate-100">
                            @forelse ($serviceRequest->partsUsed as $partUsed)
                                <div class="px-6 py-4 flex items-center justify-between gap-4">
                                    <div>
                                        <div class="font-medium text-slate-950">{{ $partUsed->part?->name }}</div>
                                        <div class="text-sm text-slate-500">{{ $partUsed->quantity }} × {{ number_format((float) $partUsed->unit_price, 2) }}</div>
                                    </div>
                                    <div class="text-sm font-medium text-slate-950">{{ number_format($partUsed->quantity * (float) $partUsed->unit_price, 2) }}</div>
                                </div>
                            @empty
                                <div class="p-6">
                                    <x-ui.empty-state :title="__('ui.service_requests.no_parts')" />
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="ff-card overflow-hidden">
                        <div class="flex flex-col gap-3 px-6 py-4 border-b border-slate-100 sm:flex-row sm:items-center sm:justify-between">
                            <div class="font-semibold text-slate-950">{{ __('ui.reports.title') }}</div>
                            @if ($serviceRequest->report)
                                <div class="flex flex-wrap gap-2">
                                    @can('generateReportPdf', $serviceRequest)
                                        <form method="POST" action="{{ route('service-requests.report.pdf', $serviceRequest) }}">
                                            @csrf
                                            <x-ui.button type="submit" size="sm">{{ __('ui.actions.generate_pdf') }}</x-ui.button>
                                        </form>
                                    @endcan
                                    @if ($reportPdfUrl)
                                        <x-ui.button :href="$reportPdfUrl" target="_blank" variant="secondary" size="sm">{{ __('ui.actions.view_pdf') }}</x-ui.button>
                                    @endif
                                </div>
                            @endif
                        </div>

                        @if ($serviceRequest->report)
                            <div class="p-6 space-y-4">
                                <div>
                                    <div class="text-sm text-slate-500">{{ __('ui.reports.technician') }}</div>
                                    <div class="mt-1 font-medium text-slate-950">{{ $serviceRequest->report->technician?->name }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-slate-500">{{ __('ui.reports.diagnosis') }}</div>
                                    <p class="mt-1 text-slate-800 whitespace-pre-line">{{ $serviceRequest->report->diagnosis }}</p>
                                </div>
                                <div>
                                    <div class="text-sm text-slate-500">{{ __('ui.reports.solution') }}</div>
                                    <p class="mt-1 text-slate-800 whitespace-pre-line">{{ $serviceRequest->report->solution }}</p>
                                </div>
                                @if ($serviceRequest->report->customer_signature)
                                    <div>
                                        <div class="text-sm text-slate-500">{{ __('ui.reports.customer_signature') }}</div>
                                        <div class="mt-1 font-medium text-slate-950">{{ $serviceRequest->report->customer_signature }}</div>
                                    </div>
                                @endif
                            </div>
                        @else
                            @can('addReport', $serviceRequest)
                                <form method="POST" action="{{ route('service-requests.report.store', $serviceRequest) }}" class="p-6 space-y-4">
                                    @csrf
                                    @if (! Auth::user()->hasRole('technician'))
                                        <div>
                                            <label for="technician_id" class="ff-form-label">{{ __('ui.service_requests.technician') }}</label>
                                            <select id="technician_id" name="technician_id" class="ff-form-input">
                                                @foreach ($technicians as $technician)
                                                    <option value="{{ $technician->id }}">{{ $technician->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                    <div>
                                        <label for="diagnosis" class="ff-form-label">{{ __('ui.reports.diagnosis') }}</label>
                                        <textarea id="diagnosis" name="diagnosis" rows="4" class="ff-form-input" required>{{ old('diagnosis') }}</textarea>
                                    </div>
                                    <div>
                                        <label for="solution" class="ff-form-label">{{ __('ui.reports.solution') }}</label>
                                        <textarea id="solution" name="solution" rows="4" class="ff-form-input" required>{{ old('solution') }}</textarea>
                                    </div>
                                    <div>
                                        <label for="customer_signature" class="ff-form-label">{{ __('ui.reports.customer_signature') }}</label>
                                        <x-ui.input id="customer_signature" name="customer_signature" :value="old('customer_signature')" />
                                    </div>
                                    <x-ui.button type="submit" size="sm">{{ __('ui.actions.add_report') }}</x-ui.button>
                                </form>
                            @else
                                <div class="p-6">
                                    <x-ui.empty-state :title="__('ui.service_requests.no_report')" />
                                </div>
                            @endcan
                        @endif
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="ff-card p-6 space-y-4">
                        <div class="font-semibold text-slate-950">{{ __('ui.invoices.invoice') }}</div>
                        @if ($serviceRequest->invoice)
                            <dl class="space-y-3 text-sm">
                                <div class="flex justify-between gap-4">
                                    <dt class="text-slate-500">{{ __('ui.invoices.invoice_number') }}</dt>
                                    <dd class="font-medium text-slate-950">{{ $serviceRequest->invoice->invoice_number }}</dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-slate-500">{{ __('ui.invoices.total') }}</dt>
                                    <dd class="font-medium text-slate-950">{{ number_format((float) $serviceRequest->invoice->total, 2) }}</dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-slate-500">{{ __('ui.invoices.status') }}</dt>
                                    <dd><x-ui.badge :status="$serviceRequest->invoice->status" /></dd>
                                </div>
                            </dl>
                            <x-ui.button :href="route('invoices.show', $serviceRequest->invoice)" variant="secondary" size="sm">{{ __('ui.actions.view_invoice') }}</x-ui.button>
                        @else
                            @can('createInvoice', $serviceRequest)
                                <form method="POST" action="{{ route('service-requests.invoice.store', $serviceRequest) }}" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label for="service_cost" class="ff-form-label">{{ __('ui.invoices.service_cost') }}</label>
                                        <x-ui.input id="service_cost" name="service_cost" type="number" min="0" step="0.01" :value="old('service_cost', '0.00')" required />
                                    </div>
                                    <div>
                                        <div class="text-sm text-slate-500">{{ __('ui.service_requests.parts_cost') }}</div>
                                        <div class="mt-1 font-medium text-slate-950">{{ number_format($partsTotal, 2) }}</div>
                                    </div>
                                    <div>
                                        <label for="tax_rate" class="ff-form-label">{{ __('ui.invoices.tax_rate') }}</label>
                                        <x-ui.input id="tax_rate" name="tax_rate" type="number" min="0" max="100" step="0.01" :value="old('tax_rate', '15')" />
                                    </div>
                                    <div>
                                        <label for="invoice_status" class="ff-form-label">{{ __('ui.invoices.invoice_status') }}</label>
                                        <select id="invoice_status" name="status" class="ff-form-input">
                                            <option value="draft" @selected(old('status', 'draft') === 'draft')>draft</option>
                                            <option value="issued" @selected(old('status') === 'issued')>issued</option>
                                        </select>
                                    </div>
                                    <x-ui.button type="submit" size="sm">{{ __('ui.actions.create_invoice') }}</x-ui.button>
                                </form>
                            @else
                                <div class="text-sm text-slate-500">{{ __('ui.invoices.no_invoice') }}</div>
                            @endcan
                        @endif
                    </div>

                    @can('changeStatus', $serviceRequest)
                        <form method="POST" action="{{ route('service-requests.change-status', $serviceRequest) }}" class="ff-card p-6 space-y-4">
                            @csrf
                            @method('PATCH')
                            <div class="font-semibold text-slate-950">{{ __('ui.service_requests.change_status') }}</div>
                            @if (count($availableStatuses) > 0)
                                <div>
                                    <label for="status" class="ff-form-label">{{ __('ui.service_requests.new_status') }}</label>
                                    <select id="status" name="status" class="ff-form-input">
                                        @foreach ($availableStatuses as $status)
                                            <option value="{{ $status }}">{{ __('ui.statuses.'.$status) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="notes" class="ff-form-label">{{ __('ui.service_requests.note') }}</label>
                                    <textarea id="notes" name="notes" rows="3" class="ff-form-input">{{ old('notes') }}</textarea>
                                </div>
                                <x-ui.button type="submit" size="sm">{{ __('ui.service_requests.update_status') }}</x-ui.button>
                            @else
                                <div class="text-sm text-slate-500">{{ __('ui.service_requests.no_transitions') }}</div>
                            @endif
                        </form>
                    @endcan

                    @can('assignTechnicians', $serviceRequest)
                        <form method="POST" action="{{ route('service-requests.assign-technician', $serviceRequest) }}" class="ff-card p-6 space-y-4">
                            @csrf
                            <div class="font-semibold text-slate-950">{{ __('ui.service_requests.assign_technician') }}</div>
                            <div>
                                <label for="assign_technician_id" class="ff-form-label">{{ __('ui.service_requests.technician') }}</label>
                                <select id="assign_technician_id" name="technician_id" class="ff-form-input">
                                    @foreach ($technicians as $technician)
                                        <option value="{{ $technician->id }}">{{ $technician->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="scheduled_at" class="ff-form-label">{{ __('ui.service_requests.scheduled_at') }}</label>
                                <x-ui.input id="scheduled_at" name="scheduled_at" type="datetime-local" :value="old('scheduled_at')" required />
                            </div>
                            <div>
                                <label for="location_address" class="ff-form-label">{{ __('ui.service_requests.visit_address') }}</label>
                                <textarea id="location_address" name="location_address" rows="2" class="ff-form-input">{{ old('location_address', $serviceRequest->customer?->address) }}</textarea>
                            </div>
                            <div>
                                <label for="technician_notes" class="ff-form-label">{{ __('ui.service_requests.notes_for_technician') }}</label>
                                <textarea id="technician_notes" name="technician_notes" rows="3" class="ff-form-input">{{ old('technician_notes') }}</textarea>
                            </div>
                            <x-ui.button type="submit" size="sm">{{ __('ui.actions.assign') }}</x-ui.button>
                        </form>
                    @endcan

                    <x-ui.card :title="__('ui.service_requests.timeline')">
                        <x-ui.status-timeline :items="$timeline" />
                    </x-ui.card>

                    @can('delete', $serviceRequest)
                        <form method="POST" action="{{ route('service-requests.destroy', $serviceRequest) }}" class="ff-card p-6">
                            @csrf
                            @method('DELETE')
                            <x-ui.button type="submit" variant="danger" size="sm">{{ __('ui.service_requests.delete_request') }}</x-ui.button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
