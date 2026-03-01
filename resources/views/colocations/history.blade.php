<x-app-layout>
    <div class="py-10">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">Historique des colocations</h1>
                    <p class="mt-1 text-sm text-slate-600">Toutes les colocations ou vous avez ete owner ou member.</p>
                </div>
                <a href="{{ route('dashboard') }}"
                   class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-50">
                    Retour dashboard
                </a>
            </div>

            <div class="mt-6 grid gap-4 lg:grid-cols-2">
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">Role Owner</h2>
                    @if($ownerHistory->isEmpty())
                        <p class="mt-2 text-sm text-slate-600">Aucune colocation en owner.</p>
                    @else
                        <div class="mt-4 grid gap-4">
                            @foreach($ownerHistory as $colocation)
                                <x-colocation-card
                                    :colocation="$colocation"
                                    :details-url="route('owner.colocations.show', $colocation)" />
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">Role Member</h2>
                    @if($memberHistory->isEmpty())
                        <p class="mt-2 text-sm text-slate-600">Aucune colocation en member.</p>
                    @else
                        <div class="mt-4 grid gap-4">
                            @foreach($memberHistory as $colocation)
                                <x-colocation-card
                                    :colocation="$colocation"
                                    :details-url="route('member.colocations.show', $colocation)" />
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
