<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            زياراتي
        </h2>
    </x-slot>

    <div class="py-8" dir="rtl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <form method="GET" action="{{ route('technician.visits.index') }}" class="bg-white rounded-lg shadow-sm p-4">
                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">الحالة</label>
                        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">كل الحالات</option>
                            @foreach ($visitStatuses as $status)
                                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>
                                    {{ $visitLabels[$status] ?? $status }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700">التاريخ</label>
                        <input id="date" name="date" type="date" value="{{ $filters['date'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">تطبيق الفلتر</button>
                        <a href="{{ route('technician.visits.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">إعادة ضبط</a>
                    </div>
                </div>
            </form>

            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-900">الزيارات المسندة لي</div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-5 py-3 text-right font-medium">الطلب</th>
                                <th class="px-5 py-3 text-right font-medium">العميل</th>
                                <th class="px-5 py-3 text-right font-medium">الجهاز</th>
                                <th class="px-5 py-3 text-right font-medium">الموعد</th>
                                <th class="px-5 py-3 text-right font-medium">الحالة</th>
                                <th class="px-5 py-3 text-right font-medium"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($visits as $visit)
                                <tr>
                                    <td class="px-5 py-4 font-medium text-gray-900">{{ $visit->serviceRequest?->title }}</td>
                                    <td class="px-5 py-4 text-gray-600">{{ $visit->serviceRequest?->customer?->name }}</td>
                                    <td class="px-5 py-4 text-gray-600">{{ $visit->serviceRequest?->serviceAsset?->name ?? 'غير محدد' }}</td>
                                    <td class="px-5 py-4 text-gray-600">{{ optional($visit->scheduled_at)->format('Y-m-d H:i') }}</td>
                                    <td class="px-5 py-4 text-gray-600">{{ $visitLabels[$visit->visit_status] ?? $visit->visit_status }}</td>
                                    <td class="px-5 py-4">
                                        <a href="{{ route('technician.visits.show', $visit) }}" class="font-medium text-indigo-700 hover:text-indigo-900">التفاصيل</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10 text-center text-gray-500">لا توجد زيارات مطابقة للفلتر الحالي.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-4">
                    {{ $visits->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

