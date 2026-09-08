<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('ui.auth.verify_email_notice') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ __('ui.auth.verification_link_sent') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('ui.auth.resend_verification_email') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-slate-600 hover:text-slate-950 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-600">
                {{ __('ui.actions.logout') }}
            </button>
        </form>
    </div>
</x-guest-layout>
