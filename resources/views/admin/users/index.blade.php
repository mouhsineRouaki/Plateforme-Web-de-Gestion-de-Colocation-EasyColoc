<x-app-layout>
    <div class="py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">Gestion des utilisateurs</h1>
                    <p class="mt-1 text-sm text-slate-600">
                        Rechercher, filtrer et bannir/débannir.
                    </p>
                </div>

                <a href="{{ route('admin.dashboard') }}"
                   class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-50">
                    ← Retour dashboard
                </a>
            </div>

            {{-- Flash --}}
            @if (session('success'))
                <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm font-medium text-rose-800">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Filters --}}
            <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <form method="GET" class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="sm:col-span-2">
                        <label class="text-xs font-semibold text-slate-600">Recherche</label>
                        <input type="text" name="q" value="{{ $q }}"
                               placeholder="Nom ou email..."
                               class="mt-1 w-full rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-600">Statut</label>
                        <select name="status"
                                class="mt-1 w-full rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="" {{ $status ? '' : 'selected' }}>Tous</option>
                            <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Actifs</option>
                            <option value="banned" {{ $status === 'banned' ? 'selected' : '' }}>Bannis</option>
                        </select>
                    </div>

                    <div class="sm:col-span-3 flex gap-2">
                        <button class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                            Filtrer
                        </button>
                        <a href="{{ route('admin.users.index') }}"
                           class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-50">
                            Réinitialiser
                        </a>
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold text-slate-600">
                        <tr>
                            <th class="px-4 py-3">Utilisateur</th>
                            <th class="px-4 py-3">Rôle</th>
                            <th class="px-4 py-3">Statut</th>
                            <th class="px-4 py-3">Créé</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($users as $u)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-900">{{ $u->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $u->email }}</div>
                                </td>

                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">
                                        {{ $u->role }}
                                    </span>
                                </td>

                                <td class="px-4 py-3">
                                    @if($u->is_banned)
                                        <span class="rounded-full bg-rose-50 px-2 py-1 text-xs font-semibold text-rose-700">
                                            Banni
                                        </span>
                                        <div class="mt-1 text-xs text-slate-500">
                                            {{ optional($u->banned_at)->format('Y-m-d H:i') }}
                                        </div>
                                    @else
                                        <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">
                                            Actif
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-slate-600">
                                    {{ $u->created_at->format('Y-m-d') }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    @if($u->is_banned)
                                        <form method="POST" action="{{ route('admin.users.unban', $u) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-900 hover:bg-slate-50">
                                                Débannir
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.users.ban', $u) }}" class="inline"
                                              onsubmit="return confirm('Bannir cet utilisateur ?');">
                                            @csrf
                                            @method('PATCH')
                                            <button class="rounded-xl bg-rose-600 px-3 py-2 text-xs font-semibold text-white hover:bg-rose-700">
                                                Bannir
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-slate-600">
                                    Aucun utilisateur trouvé.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $users->links() }}
            </div>

        </div>
    </div>
</x-app-layout>