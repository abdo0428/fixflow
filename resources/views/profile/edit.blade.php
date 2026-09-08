<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="__('Profile')" subtitle="إعدادات الحساب وكلمة المرور." dir="rtl" />
    </x-slot>

    <div class="ff-page" dir="rtl">
        <div class="ff-container space-y-6">
            <x-ui.card>
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </x-ui.card>

            <x-ui.card>
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </x-ui.card>

            <x-ui.card>
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
