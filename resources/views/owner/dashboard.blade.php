<x-app-layout>
    <div class="py-10">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-semibold text-slate-900">Dashboard Owner</h1>
            <p class="mt-1 text-sm text-slate-600">Gérez votre colocation active et vos invitations.</p>

            @if (session('success'))
                <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('warning'))
                <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    {{ session('warning') }}
                </div>
            @endif

            @if (session('invitation_link'))
                <div class="mt-4 rounded-xl border border-slate-200 bg-white p-4">
                    <p class="text-sm font-semibold text-slate-900">Lien invitation</p>
                    <p class="mt-2 break-all text-sm text-slate-700">{{ session('invitation_link') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (!$activeColocation)
                <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6">
                    <p class="text-slate-700">Aucune colocation active comme owner.</p>
                    <a href="{{ route('owner.colocations.index') }}"
                       class="mt-4 inline-flex rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900">
                        Voir mon historique owner
                    </a>
                </div>
            @else
                <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold text-emerald-700">Colocation active</p>
                            <h2 class="mt-1 text-xl font-semibold text-slate-900">{{ $activeColocation->name }}</h2>
                            <p class="mt-2 text-sm text-slate-600">{{ $activeColocation->description }}</p>
                        </div>
                        <a href="{{ route('owner.colocations.index') }}"
                           class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900">
                            Mes colocations
                        </a>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('owner.colocations.show', $activeColocation) }}"
                           class="inline-flex rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                            Voir details colocation active
                        </a>
                    </div>

                    <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($activeColocation->members as $member)
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="font-semibold text-slate-900">{{ $member->name }}</p>
                                <p class="text-xs text-slate-600">{{ $member->email }}</p>
                                <p class="mt-2 text-xs font-semibold text-slate-700">{{ $member->pivot->role_in_colocation }}</p>
                            </div>
                        @endforeach
                    </div>

                    <form method="POST" action="{{ route('invitations.send') }}" class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        <label for="invited_email" class="text-sm font-semibold text-slate-700">Inviter un membre (email)</label>
                        <div class="mt-2 flex flex-col gap-3 sm:flex-row">
                            <input id="invited_email" name="invited_email" type="email" required
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm"
                                placeholder="exemple@email.com" />
                            <button type="submit"
                                class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                                Envoyer invitation
                            </button>
                        </div>
                    </form>

                    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-4">
                        <h3 class="text-sm font-semibold text-slate-900">Dettes de la colocation</h3>

                        @if($debts->isEmpty())
                            <p class="mt-2 text-sm text-slate-600">Aucune dette en cours.</p>
                        @else
                            <div class="mt-3 space-y-2">
                                @foreach($debts as $debt)
                                    <div class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm">
                                        <div class="text-slate-700">
                                            <span class="font-semibold">{{ $debt->fromUser->name }}</span>
                                            doit
                                            <span class="font-semibold">{{ number_format($debt->amount, 2) }} DH</span>
                                            a
                                            <span class="font-semibold">{{ $debt->toUser->name }}</span>
                                        </div>

                                        @if((int) $debt->from_user_id === (int) $user->id)
                                            <form method="POST" action="{{ route('debts.markPaid', $debt) }}">
                                                @csrf
                                                <button type="submit"
                                                    class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">
                                                    Mark as paye
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
