<x-app-layout>
    <div class="py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Header --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">Dashboard Admin</h1>
                    <p class="mt-1 text-sm text-slate-600">
                        Statistiques globales de la plateforme EasyColoc.
                    </p>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('admin.users.index') }}"
                       class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                        Gérer les utilisateurs
                    </a>
                </div>
            </div>

            {{-- KPI Cards --}}
            <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium text-slate-500">Utilisateurs</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $usersCount }}</p>
                    <p class="mt-2 text-xs text-slate-500">Total inscrits</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium text-slate-500">Bannis</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $bannedCount }}</p>
                    <p class="mt-2 text-xs text-slate-500">Accès bloqués</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium text-slate-500">Colocations</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $colocationsCount }}</p>
                    <div class="mt-2 flex gap-2 text-xs">
                        <span class="rounded-full bg-emerald-50 px-2 py-1 font-medium text-emerald-700">
                            Actives: {{ $activeColocationsCount }}
                        </span>
                        <span class="rounded-full bg-rose-50 px-2 py-1 font-medium text-rose-700">
                            Annulées: {{ $cancelledColocationsCount }}
                        </span>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium text-slate-500">Dépenses</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $expensesCount }}</p>
                    <p class="mt-2 text-xs text-slate-500">
                        Total: <span class="font-semibold text-slate-800">{{ number_format($totalExpenses, 2) }}</span>
                    </p>
                </div>
            </div>

            {{-- Content Grid --}}
            <div class="mt-8 grid grid-cols-1 gap-4 lg:grid-cols-3">

                {{-- Top spenders --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-1">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-slate-900">Top payeurs</h2>
                        <span class="text-xs text-slate-500">Top 5</span>
                    </div>

                    <div class="mt-4 space-y-3">
                        @forelse ($topSpenders as $row)
                            <div class="flex items-center justify-between rounded-xl border border-slate-100 p-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900">
                                        {{ optional($row->payer)->name ?? 'Utilisateur supprimé' }}
                                    </p>
                                    <p class="text-xs text-slate-500">Somme payée</p>
                                </div>
                                <div class="text-sm font-semibold text-slate-900">
                                    {{ number_format($row->total, 2) }}
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-600">Aucune dépense trouvée.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Recent users --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-slate-900">Derniers utilisateurs</h2>
                        <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">
                            Voir tout
                        </a>
                    </div>

                    <div class="mt-4 overflow-hidden rounded-xl border border-slate-100">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-xs font-semibold text-slate-600">
                                <tr>
                                    <th class="px-4 py-3">Nom</th>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">Rôle</th>
                                    <th class="px-4 py-3">Statut</th>
                                    <th class="px-4 py-3">Créé</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach ($recentUsers as $u)
                                    <tr>
                                        <td class="px-4 py-3 font-semibold text-slate-900">{{ $u->name }}</td>
                                        <td class="px-4 py-3 text-slate-700">{{ $u->email }}</td>
                                        <td class="px-4 py-3">
                                            <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">
                                                {{ $u->role }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($u->is_banned)
                                                <span class="rounded-full bg-rose-50 px-2 py-1 text-xs font-semibold text-rose-700">Banni</span>
                                            @else
                                                <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">Actif</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">{{ $u->created_at->format('Y-m-d') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>