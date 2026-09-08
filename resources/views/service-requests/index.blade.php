<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                طلبات الصيانة
            </h2>
            @can('create', App\Models\ServiceRequest::class)
                <a href="{{ route('service-requests.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    طلب جديد
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3">
                    {{ session('status') }}
                </div>
            @endif

            <form method="GET" action="{{ route('service-requests.index') }}" class="bg-white rounded-lg shadow-sm p-5">
                <div class="grid gap-4 md:grid-cols-4">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">الحالة</label>
                        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">كل الحالات</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700">الأولوية</label>
                        <select id="priority" name="priority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">كل الأولويات</option>
                            @foreach ($priorities as $priority)
                                <option value="{{ $priority }}" @selected($filters['priority'] === $priority)>{{ $priority }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="technician_id" class="block text-sm font-medium text-gray-700">الفني</label>
                        <select id="technician_id" name="technician_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">كل الفنيين</option>
                            @foreach ($technicians as $technician)
                                <option value="{{ $technician->id }}" @selected((string) $filters['technician_id'] === (string) $technician->id)>{{ $technician->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-gray-800 rounded-md text-xs font-semibold uppercase text-white hover:bg-gray-700">
                            فلترة
                        </button>
                        <a href="{{ route('service-requests.index') }}" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md text-xs font-semibold uppercase text-gray-700 hover:bg-gray-50">
                            مسح
                        </a>
                    </div>
                </div>
            </form>

            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">الطلب</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">العميل</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">الجهاز</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">الأولوية</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">الحالة</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">الفني</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($serviceRequests as $serviceRequest)
                                <tr>
                                    <td class="px-5 py-4">
                                        <div class="font-medium text-gray-900">{{ $serviceRequest->title }}</div>
                                        <div class="text-sm text-gray-500">#{{ $serviceRequest->id }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-700">{{ $serviceRequest->customer?->name }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-700">{{ $serviceRequest->serviceAsset?->name ?? 'طلب عام' }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-700">{{ $serviceRequest->priority }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-700">{{ $serviceRequest->status }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-700">
                                        {{ $serviceRequest->visits->pluck('technician.name')->filter()->unique()->join(', ') ?: 'غير معين' }}
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <a href="{{ route('service-requests.show', $serviceRequest) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">عرض</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-8 text-center text-gray-500">لا توجد طلبات مطابقة.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-4 border-t border-gray-100">
                    {{ $serviceRequests->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
