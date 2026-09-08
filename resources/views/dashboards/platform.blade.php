<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight" dir="rtl">
            لوحة المنصة
        </h2>
    </x-slot>

    <div class="py-8" dir="rtl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">عدد الشركات</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['companies']) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">الشركات النشطة</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['activeCompanies']) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">عدد المستخدمين</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['users']) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">طلبات الصيانة في النظام</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['serviceRequests']) }}</div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="font-semibold text-gray-900">طلبات الصيانة حسب الحالة</div>
                    <div class="mt-5 space-y-3">
                        @php($statusMax = max(1, $requestsByStatus->max()))
                        @foreach ($requestsByStatus as $status => $count)
                            <div>
                                <div class="mb-1 flex items-center justify-between text-sm">
                                    <span class="font-medium text-gray-700">{{ $status }}</span>
                                    <span class="text-gray-500">{{ number_format($count) }}</span>
                                </div>
                                <div class="h-2 rounded-full bg-gray-100">
                                    <div class="h-2 rounded-full bg-indigo-600" style="width: {{ ($count / $statusMax) * 100 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-900">أحدث الشركات</div>
                    <div class="divide-y divide-gray-100">
                        @forelse ($companies as $company)
                            <div class="px-5 py-4 flex items-center justify-between gap-4">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $company->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $company->email ?? 'لا يوجد بريد' }}</div>
                                </div>
                                <span class="text-sm font-medium text-gray-700">{{ $company->status }}</span>
                            </div>
                        @empty
                            <div class="px-5 py-10 text-center text-gray-500">لا توجد شركات بعد.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
