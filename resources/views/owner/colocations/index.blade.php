<x-app-layout>
    <div class="py-10">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-4">
                <h1 class="text-2xl font-semibold text-slate-900">Mes colocations Owner</h1>
                <a href="{{ route('owner.dashboard') }}"
                    class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900">
                    Retour dashboard owner
                </a>
            </div>

            @if($colocations->isEmpty())
                <div class="mt-6 rounded-xl border border-slate-200 bg-white p-5 text-sm text-slate-600">
                    Aucune colocation trouvée pour le rôle owner.
                </div>
            @else
                <div class="mt-6 grid gap-5 md:grid-cols-2">
                    @foreach($colocations as $colocation)
                        <x-colocation-card
                            :colocation="$colocation"
                            :details-url="route('owner.colocations.show', $colocation)" />
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
