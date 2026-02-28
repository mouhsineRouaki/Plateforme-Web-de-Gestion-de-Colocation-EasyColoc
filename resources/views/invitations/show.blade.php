<x-guest-layout>
    <div class="mx-auto max-w-2xl px-4 py-10 sm:px-6 lg:px-8">
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

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="text-xs font-medium text-slate-500">Invitation</div>
            <h1 class="mt-2 text-xl font-semibold text-slate-900">
                Rejoindre : {{ $invitation->colocation->name }}
            </h1>

            <p class="mt-2 text-sm text-slate-600">
                Email invite : <span class="font-semibold">{{ $invitation->invited_email }}</span>
            </p>

            <p class="mt-2 text-sm text-slate-600">
                Statut : <span class="font-semibold">{{ $invitation->status }}</span>
            </p>

            <p class="mt-2 text-sm text-slate-600">
                Expire le : <span class="font-semibold">{{ $invitation->expires_at->format('Y-m-d H:i') }}</span>
            </p>

            <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4">
                <h2 class="text-sm font-semibold text-slate-900">Membres actifs, roles et reputation</h2>
                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                    @foreach($activeMembers as $member)
                        <div class="rounded-lg border border-slate-200 bg-white px-3 py-2">
                            <p class="text-sm font-semibold text-slate-900">{{ $member->name }}</p>
                            <p class="text-xs text-slate-600">{{ $member->pivot->role_in_colocation }} | Reputation: {{ $member->reputation_score }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            @if($invitation->status !== 'PENDING')
                <p class="mt-4 text-sm font-semibold text-slate-700">
                    Cette invitation est deja traitee.
                </p>
            @else
                @guest
                    @if($existingUser)
                        <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm text-slate-700">
                                Un compte existe deja pour cet email. Connectez-vous pour accepter ou refuser.
                            </p>
                            <a href="{{ route('login') }}"
                               class="mt-3 inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                                Se connecter
                            </a>
                        </div>
                    @else
                        <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <h2 class="text-sm font-semibold text-slate-900">Redirection vers l inscription...</h2>
                            <p class="mt-2 text-sm text-slate-600">
                                Si la redirection ne se fait pas, ouvrez la page d inscription de la plateforme.
                            </p>
                        </div>
                    @endif
                @else
                    @if($authUser && $authUser->email !== $invitation->invited_email)
                        <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                            Vous etes connecte avec {{ $authUser->email }}. Cette invitation est pour {{ $invitation->invited_email }}.
                        </div>
                    @else
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
                    @endif
                @endguest
            @endif
        </div>
    </div>
</x-guest-layout>
