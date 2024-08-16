<div class="relative flex min-h-screen shrink-0 justify-center md:px-12 lg:px-0">
    <div
        class="relative z-10 flex flex-1 flex-col bg-white px-4 py-10 shadow-2xl sm:justify-center md:flex-none md:px-28">
        <main class="mx-auto w-full max-w-md sm:px-4 md:w-96 md:max-w-sm md:px-0">
            <div class="flex">
                <a href="#" class="-m-1.5 p-1.5">
                    <span class="sr-only">{{ config('app.name') }}</span>
                    <span class="text-3xl font-bold xs:text-2xl">{{ config('app.name') }}</span>
                </a>
            </div>
            <h2 class="mt-20 text-lg font-semibold text-gray-900">
                {{ __('filament-panels::pages/auth/login.heading') }}
            </h2>

            @if (filament()->hasRegistration())
                {{ __('filament-panels::pages/auth/login.actions.register.before') }}

                {{ $this->registerAction }}
            @endif

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}
            <x-filament-panels::form id="form" wire:submit="loginWithFortify" class="mt-10">
                {{ $this->form }}

                <x-filament-panels::form.actions :actions="$this->getCachedFormActions()" :full-width="$this->hasFullWidthFormActions()" />
            </x-filament-panels::form>

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
        </main>
    </div>
    <div class="hidden sm:contents lg:relative lg:block lg:flex-1">
        <img class="absolute inset-0 h-full w-full object-cover" src="/img/background-auth.jpg" alt=""
            unoptimized />
    </div>
</div>
