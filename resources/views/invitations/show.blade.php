<x-app-layout>
    <div class="py-10">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="text-xs font-medium text-slate-500">Invitation</div>
                <h1 class="mt-2 text-xl font-semibold text-slate-900">
                    Rejoindre : {{ $invitation->colocation->name }}
                </h1>

                <p class="mt-2 text-sm text-slate-600">
                    Statut : <span class="font-semibold">{{ $invitation->status }}</span>
                </p>

                <p class="mt-2 text-sm text-slate-600">
                    Expire le : <span class="font-semibold">{{ $invitation->expires_at->format('Y-m-d H:i') }}</span>
                </p>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
                    <form method="POST" action="{{ route('invitations.refuse', $invitation->token) }}">
                        @csrf
                        <button class="w-full sm:w-auto rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-50">
                            Refuser
                        </button>
                    </form>

                    <form method="POST" action="{{ route('invitations.accept', $invitation->token) }}">
                        @csrf
                        <button class="w-full sm:w-auto rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-700">
                            Accepter
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>