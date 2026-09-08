<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="__('Dashboard')" subtitle="تم تسجيل الدخول، لكن هذا الحساب لا يملك لوحة تشغيل مخصصة بعد." dir="rtl" />
    </x-slot>

    <div class="ff-page" dir="rtl">
        <div class="ff-container">
            <x-ui.empty-state title="تم تسجيل الدخول بنجاح." message="لم يتم تعيين دور تشغيلي لهذا الحساب بعد." />
        </div>
    </div>
</x-app-layout>
