<div class="relative flex min-h-screen shrink-0 justify-center md:px-12 lg:px-0">
    <div
        class="relative z-10 flex flex-1 flex-col bg-white px-4 py-10 shadow-2xl sm:justify-center md:flex-none md:px-28">
        <main class="mx-auto w-full max-w-md sm:px-4 md:w-96 md:max-w-sm md:px-0">
            <div class="flex justify-center">
                <a href="#" class="-m-1.5 p-1.5">
                    <span class="sr-only">{{ config('app.name') }}</span>
                    <span class="text-3xl font-bold xs:text-2xl">{{ config('app.name') }}</span>
                </a>
            </div>
            <h2 class="mt-20 text-lg font-semibold text-gray-900 text-center">
                {{ __('filament-two-factor-auth::Authenticate with your Authentication code') }}
            </h2>
            {{ $this->resend() }}

            <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-8">

                @csrf
                {{ $this->form }}

                <div class="flex items-center justify-between">
                    <x-filament::button color="secondary" class="w-full" type="button" tag="a" outlined
                        href="{{ route('login') }}">
                        {{ __('filament-two-factor-auth::Cancel') }}
                    </x-filament::button>
                    <x-filament::button type="submit" class="w-full" color="info">
                        {{ __('filament-two-factor-auth::Login') }}
                    </x-filament::button>
                </div>
            </form>

        </main>
    </div>
    <div class="hidden sm:contents lg:relative lg:block lg:flex-1">
        <img class="absolute inset-0 h-full w-full object-cover" src="/img/background-auth.jpg" alt=""
            unoptimized />
    </div>
</div>
