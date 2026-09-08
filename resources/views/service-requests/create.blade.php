<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="__('ui.service_requests.create_title')" :subtitle="__('ui.service_requests.create_subtitle')" />
    </x-slot>

    <div class="ff-page">
        <div class="ff-container max-w-3xl">
            <x-ui.card :title="__('ui.service_requests.form_title')">
                <form method="POST" action="{{ route('service-requests.store') }}" class="space-y-5">
                    @csrf
                    @include('service-requests._form', ['serviceRequest' => null])
                </form>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
