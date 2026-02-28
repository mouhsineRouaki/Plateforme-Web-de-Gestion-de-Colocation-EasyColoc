<x-app-layout>
    <div class="py-10">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">Mes colocations (admin)</h1>
                    <p class="mt-1 text-sm text-slate-600">Colocations actives ou vous etes owner/member.</p>
                </div>
                <a href="{{ route('admin.dashboard') }}"
                   class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-50">
                    Retour dashboard admin
                </a>
            </div>

            <div class="mt-4">
                <button type="button"
                        onclick="window.location='{{ route('dashboard') }}';"
                        class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    Creer une colocation
                </button>
                <p class="mt-1 text-xs text-slate-500">Utilisez aussi le dashboard user pour rejoindre via invitation.</p>
            </div>

            @if($colocations->isEmpty())
                <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-600">
                    Aucune colocation active.
                </div>
            @else
                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    @foreach($colocations as $colocation)
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex items-center justify-between gap-2">
                                <h2 class="text-lg font-semibold text-slate-900">{{ $colocation->name }}</h2>
                                <span class="rounded-full border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-semibold text-slate-700">
                                    {{ $colocation->pivot->role_in_colocation }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-slate-600">{{ $colocation->members->count() }} membres actifs</p>

                            <div class="mt-4">
                                @if($colocation->pivot->role_in_colocation === 'OWNER')
                                    <a href="{{ route('owner.colocations.show', $colocation) }}"
                                       class="inline-flex rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                                        Details
                                    </a>
                                @else
                                    <a href="{{ route('member.colocations.show', $colocation) }}"
                                       class="inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                                        Details
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
