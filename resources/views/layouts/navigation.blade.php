<nav x-data="{ open: false }" class="border-b border-slate-200 bg-white">
    @php($user = Auth::user())

    <div class="ff-container">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <x-application-logo class="block h-8 w-8 fill-current text-cyan-700" />
                        <span class="hidden text-base font-bold text-slate-950 sm:inline">FixFlow</span>
                    </a>
                </div>

                <div class="hidden gap-8 sm:-my-px sm:me-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('ui.nav.home') }}</x-nav-link>

                    @if ($user->hasRole('super_admin'))
                        <x-nav-link :href="route('platform.dashboard')" :active="request()->routeIs('platform.dashboard')">{{ __('ui.nav.platform') }}</x-nav-link>
                    @endif

                    @if ($user->hasRole('company_admin'))
                        <x-nav-link :href="route('company.dashboard')" :active="request()->routeIs('company.dashboard')">{{ __('ui.nav.company') }}</x-nav-link>
                    @endif

                    @if ($user->hasRole('dispatcher'))
                        <x-nav-link :href="route('dispatch.dashboard')" :active="request()->routeIs('dispatch.dashboard')">{{ __('ui.nav.dispatch_dashboard') }}</x-nav-link>
                        <x-nav-link :href="route('dispatch.service-requests.index')" :active="request()->routeIs('dispatch.service-requests.*')">{{ __('ui.nav.service_requests') }}</x-nav-link>
                    @endif

                    @if ($user->hasRole('technician'))
                        <x-nav-link :href="route('technician.dashboard')" :active="request()->routeIs('technician.dashboard')">{{ __('ui.nav.technician_dashboard') }}</x-nav-link>
                        <x-nav-link :href="route('technician.visits.index')" :active="request()->routeIs('technician.visits.*')">{{ __('ui.nav.my_visits') }}</x-nav-link>
                    @endif

                    @if ($user->hasRole('customer'))
                        <x-nav-link :href="route('customer.dashboard')" :active="request()->routeIs('customer.dashboard') || request()->routeIs('customer.portal')">{{ __('ui.nav.customer_portal') }}</x-nav-link>
                        <x-nav-link :href="route('customer.assets.index')" :active="request()->routeIs('customer.assets.*')">{{ __('ui.nav.my_assets') }}</x-nav-link>
                        <x-nav-link :href="route('customer.service-requests.index')" :active="request()->routeIs('customer.service-requests.*')">{{ __('ui.nav.my_requests') }}</x-nav-link>
                    @endif
                </div>
            </div>

            <div class="hidden gap-4 sm:flex sm:items-center sm:me-6">
                <x-ui.locale-switcher compact />

                <x-dropdown align="left" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-slate-500 transition duration-150 ease-in-out hover:bg-slate-50 hover:text-slate-800 focus:outline-none">
                            <div>{{ $user->name }}</div>
                            <div class="me-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">{{ __('ui.nav.profile') }}</x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('ui.nav.logout') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center rounded-md p-2 text-slate-400 transition duration-150 ease-in-out hover:bg-slate-100 hover:text-slate-600 focus:bg-slate-100 focus:text-slate-600 focus:outline-none">
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
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('ui.nav.home') }}</x-responsive-nav-link>

            @if ($user->hasRole('super_admin'))
                <x-responsive-nav-link :href="route('platform.dashboard')" :active="request()->routeIs('platform.dashboard')">{{ __('ui.nav.platform') }}</x-responsive-nav-link>
            @endif

            @if ($user->hasRole('company_admin'))
                <x-responsive-nav-link :href="route('company.dashboard')" :active="request()->routeIs('company.dashboard')">{{ __('ui.nav.company') }}</x-responsive-nav-link>
            @endif

            @if ($user->hasRole('dispatcher'))
                <x-responsive-nav-link :href="route('dispatch.dashboard')" :active="request()->routeIs('dispatch.dashboard')">{{ __('ui.nav.dispatch_dashboard') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('dispatch.service-requests.index')" :active="request()->routeIs('dispatch.service-requests.*')">{{ __('ui.nav.service_requests') }}</x-responsive-nav-link>
            @endif

            @if ($user->hasRole('technician'))
                <x-responsive-nav-link :href="route('technician.dashboard')" :active="request()->routeIs('technician.dashboard')">{{ __('ui.nav.technician_dashboard') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('technician.visits.index')" :active="request()->routeIs('technician.visits.*')">{{ __('ui.nav.my_visits') }}</x-responsive-nav-link>
            @endif

            @if ($user->hasRole('customer'))
                <x-responsive-nav-link :href="route('customer.dashboard')" :active="request()->routeIs('customer.dashboard') || request()->routeIs('customer.portal')">{{ __('ui.nav.customer_portal') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('customer.assets.index')" :active="request()->routeIs('customer.assets.*')">{{ __('ui.nav.my_assets') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('customer.service-requests.index')" :active="request()->routeIs('customer.service-requests.*')">{{ __('ui.nav.my_requests') }}</x-responsive-nav-link>
            @endif
        </div>

        <div class="border-t border-slate-200 pb-1 pt-4">
            <div class="px-4">
                <div class="text-base font-medium text-slate-800">{{ $user->name }}</div>
                <div class="text-sm font-medium text-slate-500">{{ $user->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <div class="px-4 py-2">
                    <x-ui.locale-switcher compact />
                </div>

                <x-responsive-nav-link :href="route('profile.edit')">{{ __('ui.nav.profile') }}</x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('ui.nav.logout') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
