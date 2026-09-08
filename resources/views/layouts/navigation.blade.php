<nav x-data="{ open: false }" class="bg-white border-b border-gray-100" dir="rtl">
    @php($user = Auth::user())

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:me-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">الرئيسية</x-nav-link>

                    @if ($user->hasRole('super_admin'))
                        <x-nav-link :href="route('platform.dashboard')" :active="request()->routeIs('platform.dashboard')">المنصة</x-nav-link>
                    @endif

                    @if ($user->hasRole('company_admin'))
                        <x-nav-link :href="route('company.dashboard')" :active="request()->routeIs('company.dashboard')">الشركة</x-nav-link>
                    @endif

                    @if ($user->hasRole('dispatcher'))
                        <x-nav-link :href="route('dispatch.dashboard')" :active="request()->routeIs('dispatch.dashboard')">لوحة التوزيع</x-nav-link>
                        <x-nav-link :href="route('dispatch.service-requests.index')" :active="request()->routeIs('dispatch.service-requests.*')">طلبات الصيانة</x-nav-link>
                    @endif

                    @if ($user->hasRole('technician'))
                        <x-nav-link :href="route('technician.dashboard')" :active="request()->routeIs('technician.dashboard')">لوحة الفني</x-nav-link>
                        <x-nav-link :href="route('technician.visits.index')" :active="request()->routeIs('technician.visits.*')">زياراتي</x-nav-link>
                    @endif

                    @if ($user->hasRole('customer'))
                        <x-nav-link :href="route('customer.dashboard')" :active="request()->routeIs('customer.dashboard') || request()->routeIs('customer.portal')">بوابة العميل</x-nav-link>
                        <x-nav-link :href="route('customer.assets.index')" :active="request()->routeIs('customer.assets.*')">أجهزتي</x-nav-link>
                        <x-nav-link :href="route('customer.service-requests.index')" :active="request()->routeIs('customer.service-requests.*')">طلباتي</x-nav-link>
                    @endif
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:me-6">
                <x-dropdown align="left" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ $user->name }}</div>
                            <div class="me-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">الملف الشخصي</x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                تسجيل الخروج
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">الرئيسية</x-responsive-nav-link>

            @if ($user->hasRole('super_admin'))
                <x-responsive-nav-link :href="route('platform.dashboard')" :active="request()->routeIs('platform.dashboard')">المنصة</x-responsive-nav-link>
            @endif

            @if ($user->hasRole('company_admin'))
                <x-responsive-nav-link :href="route('company.dashboard')" :active="request()->routeIs('company.dashboard')">الشركة</x-responsive-nav-link>
            @endif

            @if ($user->hasRole('dispatcher'))
                <x-responsive-nav-link :href="route('dispatch.dashboard')" :active="request()->routeIs('dispatch.dashboard')">لوحة التوزيع</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('dispatch.service-requests.index')" :active="request()->routeIs('dispatch.service-requests.*')">طلبات الصيانة</x-responsive-nav-link>
            @endif

            @if ($user->hasRole('technician'))
                <x-responsive-nav-link :href="route('technician.dashboard')" :active="request()->routeIs('technician.dashboard')">لوحة الفني</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('technician.visits.index')" :active="request()->routeIs('technician.visits.*')">زياراتي</x-responsive-nav-link>
            @endif

            @if ($user->hasRole('customer'))
                <x-responsive-nav-link :href="route('customer.dashboard')" :active="request()->routeIs('customer.dashboard') || request()->routeIs('customer.portal')">بوابة العميل</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('customer.assets.index')" :active="request()->routeIs('customer.assets.*')">أجهزتي</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('customer.service-requests.index')" :active="request()->routeIs('customer.service-requests.*')">طلباتي</x-responsive-nav-link>
            @endif
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ $user->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ $user->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">الملف الشخصي</x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        تسجيل الخروج
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
