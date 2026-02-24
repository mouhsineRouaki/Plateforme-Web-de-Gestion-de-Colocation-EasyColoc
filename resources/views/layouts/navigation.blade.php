<header class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/80 backdrop-blur">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    {{-- Brand --}}
                    <div class="flex items-center gap-3">
                        <div
                            class="grid h-10 w-10 place-items-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100 4m0-4a2 2 0 110 4m0-4v-2m0 6v-2m6-10a2 2 0 100 4m0-4a2 2 0 110 4m0-4V6m0 6v-2m6 8a2 2 0 100 4m0-4a2 2 0 110 4m0-4v-2m0 6v-2" />
                            </svg>
                        </div>

                        <div class="leading-tight">
                            <div class="text-sm font-semibold text-slate-900">EasyColoc</div>
                            <div class="text-xs text-slate-500">Dashboard</div>
                        </div>
                    </div>

                    {{-- Search (desktop) --}}
                    <div class="hidden md:block w-[420px]">
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z" />
                                </svg>
                            </span>
                            <input type="text"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-10 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                                placeholder="Rechercher : colocations, dépenses, membres..." />
                        </div>
                    </div>

                    {{-- Right actions --}}
                    <div class="flex items-center gap-2">
                        {{-- Notification --}}
                        <button type="button"
                            class="relative inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50">
                            <span class="absolute right-2 top-2 h-2 w-2 rounded-full bg-emerald-500"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </button>

                        {{-- User chip --}}
                        <div
                            class="hidden sm:flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2">
                            <div
                                class="grid h-9 w-9 place-items-center rounded-xl bg-emerald-50 text-emerald-700 font-semibold">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div class="leading-tight">
                                <div class="text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</div>
                                <div class="text-xs text-slate-500">{{ auth()->user()->email }}</div>
                            </div>
                        </div>

                        {{-- Logout --}}
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="inline-flex h-10 items-center justify-center rounded-2xl bg-slate-900 px-4 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>