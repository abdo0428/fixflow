<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            طلب صيانة جديد
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('service-requests.store') }}" class="bg-white rounded-lg shadow-sm p-6 space-y-5">
                @csrf
                @include('service-requests._form', ['serviceRequest' => null])
            </form>
        </div>
    </div>
</x-app-layout>
