<x-app-layout>
    <div class="py-10">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="flex items-start justify-between gap-6">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">Bienvenue, {{ $user->name }}</h1>
                    <p class="mt-1 text-sm text-slate-600">
                        Suivez vos depenses, equilibrez les dettes, et gardez une vue claire.
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                    <div class="text-xs text-slate-500">Reputation</div>
                    <div class="text-lg font-semibold text-slate-900">{{ $user->reputation_score }}</div>
                </div>
            </div>

            <div class="mt-8" x-data="{ openCreate:false, openJoin:false }">
                @if (!$activeColocation || ($isGlobalAdmin ?? false))
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                            <div>
                                @if(($isGlobalAdmin ?? false))
                                    <h2 class="text-lg font-semibold text-slate-900">Mode Global Admin</h2>
                                    <p class="mt-1 text-sm text-slate-600">
                                        Vous pouvez creer et rejoindre plusieurs colocations actives.
                                    </p>
                                @else
                                    <h2 class="text-lg font-semibold text-slate-900">Aucune colocation active</h2>
                                    <p class="mt-1 text-sm text-slate-600">
                                        Creez une colocation ou rejoignez-en une via une invitation.
                                    </p>
                                @endif
                            </div>

                            <div class="flex flex-col gap-3 sm:flex-row">
                                <button type="button"
                                        @click="openCreate=true"
                                        class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                                    Creer une colocation
                                </button>

                                <button type="button"
                                        @click="openJoin=true"
                                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-50">
                                    Rejoindre via invitation
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                @if(($isGlobalAdmin ?? false))
                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-900">Mes colocations actives</h2>
                        @if($activeColocations->isEmpty())
                            <p class="mt-2 text-sm text-slate-600">Aucune colocation active pour le moment.</p>
                        @else
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                @foreach($activeColocations as $colocation)
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                        <div class="flex items-center justify-between">
                                            <p class="font-semibold text-slate-900">{{ $colocation->name }}</p>
                                            <span class="rounded-full border border-slate-200 bg-white px-2 py-1 text-xs font-semibold text-slate-700">
                                                {{ $colocation->pivot->role_in_colocation }}
                                            </span>
                                        </div>
                                        <p class="mt-1 text-xs text-slate-600">{{ $colocation->members->count() }} membres actifs</p>
                                        <div class="mt-3">
                                            @if($colocation->pivot->role_in_colocation === 'OWNER')
                                                <a href="{{ route('owner.colocations.show', $colocation) }}"
                                                   class="inline-flex rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">
                                                    Ouvrir en owner
                                                </a>
                                            @else
                                                <a href="{{ route('member.colocations.show', $colocation) }}"
                                                   class="inline-flex rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800">
                                                    Ouvrir en member
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @elseif($activeColocation)
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-start justify-between gap-6">
                            <div>
                                <div class="text-xs font-medium text-emerald-700">Colocation active</div>
                                <h2 class="mt-1 text-xl font-semibold text-slate-900">{{ $activeColocation->name }}</h2>
                                @if($activeColocation->description)
                                    <p class="mt-2 text-sm text-slate-600">{{ $activeColocation->description }}</p>
                                @endif
                            </div>

                            <div class="text-right">
                                <div class="text-xs text-slate-500">Membres</div>
                                <div class="text-lg font-semibold text-slate-900">
                                    {{ $activeColocation->members->count() }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <div class="text-sm font-semibold text-slate-900">Liste des membres</div>
                            <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($activeColocation->members as $m)
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                        <div class="flex items-center justify-between">
                                            <div class="font-medium text-slate-900">{{ $m->name }}</div>
                                            <div class="text-xs rounded-full bg-white px-2 py-1 border border-slate-200">
                                                {{ $m->pivot->role_in_colocation }}
                                            </div>
                                        </div>
                                        <div class="mt-1 text-xs text-slate-600">{{ $m->email }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <div x-show="openCreate" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/40" @click="openCreate=false"></div>

                    <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-xl border border-slate-200">
                        <div class="p-6 border-b border-slate-200">
                            <h3 class="text-lg font-semibold text-slate-900">Creer une colocation</h3>
                            <p class="mt-1 text-sm text-slate-600">Donnez un nom et une description (optionnel).</p>
                        </div>

                        <form method="POST" action="{{ route('colocations.store') }}" class="p-6">
                            @csrf

                            <div>
                                <label class="text-sm font-medium text-slate-700">Nom</label>
                                <input name="name" required
                                       class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-emerald-500"
                                       placeholder="Ex: Coloc Centre Ville" />
                            </div>

                            <div class="mt-4">
                                <label class="text-sm font-medium text-slate-700">Description</label>
                                <textarea name="description" rows="3"
                                          class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-emerald-500"
                                          placeholder="Ex: Depenses partagees + regles..."></textarea>
                            </div>

                            <div class="mt-6 flex items-center justify-end gap-3">
                                <button type="button"
                                        @click="openCreate=false"
                                        class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 hover:bg-slate-50">
                                    Annuler
                                </button>
                                <button type="submit"
                                        class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                                    Creer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div x-show="openJoin" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/40" @click="openJoin=false"></div>

                    <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-xl border border-slate-200">
                        <div class="p-6 border-b border-slate-200">
                            <h3 class="text-lg font-semibold text-slate-900">Rejoindre via invitation</h3>
                            <p class="mt-1 text-sm text-slate-600">Collez le lien ou le token d invitation.</p>
                        </div>

                        <form method="POST" action="{{ route('invitations.join.link') }}" class="p-6">
                            @csrf

                            <div>
                                <label class="text-sm font-medium text-slate-700">Lien / Token</label>
                                <input name="invite_link" required
                                       class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-emerald-500"
                                       placeholder="Ex: http://127.0.0.1:8000/invitations/XXXX ou XXXX" />
                            </div>

                            <div class="mt-6 flex items-center justify-end gap-3">
                                <button type="button"
                                        @click="openJoin=false"
                                        class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 hover:bg-slate-50">
                                    Annuler
                                </button>
                                <button type="submit"
                                        class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                                    Continuer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
