<x-guest-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100">
        <div class="mx-auto max-w-6xl px-4 py-10 sm:py-14">
            <div class="grid items-stretch gap-8 lg:grid-cols-2">

                {{-- LEFT: Branding / pitch (même style que login) --}}
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
                            <p class="text-sm text-slate-600">Commencez en 2 minutes.</p>
                        </div>
                    </div>

                    <div class="mt-8 space-y-4">
                        <h2 class="text-3xl font-semibold tracking-tight text-slate-900">
                            Créez votre compte,
                            <span class="text-emerald-700">invitez</span> votre colocation
                            et suivez vos dépenses.
                        </h2>
                        <p class="text-slate-600">
                            Une fois inscrit, vous pourrez créer une colocation (Owner) ou rejoindre via invitation (Member).
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
                                <p class="font-medium text-slate-900">Rôles automatiques</p>
                                <p class="text-sm text-slate-600">Créateur = Owner. Premier inscrit = Global Admin.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4">
                            <div class="mt-0.5 text-emerald-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-slate-900">Une seule colocation active</p>
                                <p class="text-sm text-slate-600">Évite les conflits et garde tout clair.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4">
                            <div class="mt-0.5 text-emerald-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-slate-900">Réputation</p>
                                <p class="text-sm text-slate-600">Un score simple basé sur le comportement financier.</p>
                            </div>
                        </div>
                    </div>

                    <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-emerald-200/50 blur-3xl"></div>
                    <div class="pointer-events-none absolute -bottom-20 -left-20 h-56 w-56 rounded-full bg-sky-200/40 blur-3xl"></div>
                </div>

                {{-- RIGHT: Register card --}}
                <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm lg:p-10">
                    <div class="mb-6">
                        <h2 class="text-2xl font-semibold text-slate-900">Créer un compte</h2>
                        <p class="mt-1 text-sm text-slate-600">
                            Renseignez vos informations pour démarrer.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('register') }}" class="space-y-4">
                        @csrf

                        <!-- Name -->
                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input
                                id="name"
                                class="mt-1 block w-full"
                                type="text"
                                name="name"
                                :value="old('name')"
                                required
                                autofocus
                                autocomplete="name"
                            />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Email -->
                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input
                                id="email"
                                class="mt-1 block w-full"
                                type="email"
                                name="email"
                                :value="old('email')"
                                required
                                autocomplete="username"
                            />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Password -->
                        <div>
                            <x-input-label for="password" :value="__('Password')" />
                            <x-text-input
                                id="password"
                                class="mt-1 block w-full"
                                type="password"
                                name="password"
                                required
                                autocomplete="new-password"
                            />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                            <x-text-input
                                id="password_confirmation"
                                class="mt-1 block w-full"
                                type="password"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                            />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        <div class="pt-2">
                            <x-primary-button class="w-full justify-center">
                                {{ __('Register') }}
                            </x-primary-button>
                        </div>

                        <div class="pt-3 text-center text-sm text-slate-600">
                            Vous avez déjà un compte ?
                            <a href="{{ route('login') }}" class="font-medium text-emerald-700 hover:text-emerald-800">
                                Se connecter
                            </a>
                        </div>
                    </form>

                    <div class="mt-8 border-t border-slate-200 pt-6">
                        <p class="text-xs text-slate-500">
                            En créant un compte, vous pourrez créer une colocation ou rejoindre via une invitation.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-guest-layout>