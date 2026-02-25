<x-app-layout>
    <div class="mx-auto max-w-4xl px-4 py-8">
        <h1 class="text-2xl font-bold text-slate-900">Qui doit à qui</h1>

        @if($debts->isEmpty())
            <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6">
                <p class="text-slate-600">Aucune dette 🎉</p>
            </div>
        @else
            <div class="mt-6 space-y-3">
                @foreach($debts as $d)
                    <div class="rounded-xl border border-slate-200 bg-white p-4 flex items-center justify-between">
                        <div class="text-slate-800">
                            <span class="font-semibold">{{ $d->fromUser->name }}</span>
                            doit
                            <span class="font-semibold">{{ number_format($d->amount, 2) }} DH</span>
                            à
                            <span class="font-semibold">{{ $d->toUser->name }}</span>
                        </div>

                        {{-- bouton marquer payé (tu feras après) --}}
                        <form method="POST" action="#">
                            @csrf
                            <button class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white">
                                Marquer payé
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
