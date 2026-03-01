<x-guest-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100">
        <div class="mx-auto max-w-6xl px-4 py-10 sm:py-14">
            <div class="grid items-stretch gap-8 lg:grid-cols-2">

                {{-- LEFT: Branding / pitch --}}
                <div class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-8 shadow-sm lg:p-10">
                    <div class="flex items-center gap-3">
                        <div class="grid h-11 w-11 place-items-center rounded-2xl bg-emerald-600 text-white shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8c-1.657 0-3 1.343-3 3v1h6v-1c0-1.657-1.343-3-3-3z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M7 12v6a2 2 0 002 2h6a2 2 0 002-2v-6"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5 12h14"/>
                            </svg>
                        </div>

                        <div>
                            <h1 class="text-xl font-semibold text-slate-900">EasyColoc</h1>
                            <p class="text-sm text-slate-600">Gérez les dépenses et remboursements en colocation.</p>
                        </div>
                    </div>

                    <div class="mt-8 space-y-4">
                        <h2 class="text-3xl font-semibold tracking-tight text-slate-900">
                            Une vision claire de
                            <span class="text-emerald-700">qui doit quoi à qui</span>.
                        </h2>
                        <p class="text-slate-600">
                            Ajoutez vos dépenses, suivez les soldes, et simplifiez les remboursements sans calculs manuels.
                        </p>
                    </div>

                    <div class="mt-8 grid gap-3">
                        <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4">
                            <div class="mt-0.5 text-emerald-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-slate-900">Soldes automatiques</p>
                                <p class="text-sm text-slate-600">Chaque dépense recalcule les balances des membres.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4">
                            <div class="mt-0.5 text-emerald-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-slate-900">Remboursements simplifiés</p>
                                <p class="text-sm text-slate-600">Une vue synthétique “qui doit à qui”.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4">
                            <div class="mt-0.5 text-emerald-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-slate-900">Invitations par lien</p>
                                <p class="text-sm text-slate-600">Ajoutez vos colocataires rapidement via token/email.</p>
                            </div>
                        </div>
                    </div>

                    <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-emerald-200/50 blur-3xl"></div>
                    <div class="pointer-events-none absolute -bottom-20 -left-20 h-56 w-56 rounded-full bg-sky-200/40 blur-3xl"></div>
                </div>

                {{-- RIGHT: Login card --}}
                <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm lg:p-10">
                    <div class="mb-6">
                        <h2 class="text-2xl font-semibold text-slate-900">Connexion</h2>
                        <p class="mt-1 text-sm text-slate-600">
                            Accédez à votre colocation et suivez les dépenses.
                        </p>
                    </div>

                    <!-- Session Status -->
                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}" class="space-y-4">
                        @csrf

                        <!-- Email Address -->
                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input
                                id="email"
                                class="mt-1 block w-full"
                                type="email"
                                name="email"
                                :value="old('email')"
                                required
                                autofocus
                                autocomplete="username"
                            />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Password -->
                        <div>
                            <div class="flex items-center justify-between">
                                <x-input-label for="password" :value="__('Password')" />
                                @if (Route::has('password.request'))
                                    <a
                                        href="{{ route('password.request') }}"
                                        class="text-sm font-medium text-emerald-700 hover:text-emerald-800"
                                    >
                                        {{ __('Forgot your password?') }}
                                    </a>
                                @endif
                            </div>

                            <x-text-input
                                id="password"
                                class="mt-1 block w-full"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                            />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center justify-between">
                            <label for="remember_me" class="inline-flex items-center">
                                <input
                                    id="remember_me"
                                    type="checkbox"
                                    class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500"
                                    name="remember"
                                >
                                <span class="ms-2 text-sm text-slate-600">{{ __('Remember me') }}</span>
                            </label>

                            <span class="text-xs text-slate-500">
                                Sécurisé • CSRF activé
                            </span>
                        </div>

                        <div class="pt-2">
                            <x-primary-button class="w-full justify-center">
                                {{ __('Log in') }}
                            </x-primary-button>
                        </div>

                        <div class="pt-3 text-center text-sm text-slate-600">
                            Vous n’avez pas de compte ?
                            <a href="{{ route('register') }}" class="font-medium text-emerald-700 hover:text-emerald-800">
                                Créer un compte
                            </a>
                        </div>
                    </form>

                    <div class="mt-8 border-t border-slate-200 pt-6">
                        <p class="text-xs text-slate-500">
                            Astuce : utilisez votre email d’invitation pour rejoindre votre colocation dès la première connexion.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-guest-layout>