<x-app-layout>
    <div class="py-10" x-data="{ openExpenseModal: false, openCategoryModal: false }">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">Details colocation</h1>
                    <p class="mt-1 text-sm text-slate-600">{{ $colocation->name }}</p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route($rolePrefix . '.colocations.index') }}"
                       class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-50">
                        Retour colocations
                    </a>
                    <form method="POST" action="{{ route($rolePrefix . '.colocations.leave', $colocation) }}">
                        @csrf
                        <button type="submit"
                                class="rounded-xl border border-rose-300 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-800 hover:bg-rose-100"
                                onclick="return confirm('Confirmer quitter la colocation ? Votre reputation sera mise a jour selon vos dettes.')">
                            Quitter la colocation
                        </button>
                    </form>
                    @if($rolePrefix === 'owner')
                        <form method="POST" action="{{ route('owner.colocations.cancel', $colocation) }}">
                            @csrf
                            <button type="submit"
                                    class="rounded-xl border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-100"
                                    onclick="return confirm('Annuler cette colocation ? Cette action ferme la colocation pour tous les membres.')">
                                Annuler colocation
                            </button>
                        </form>
                        <button type="button"
                                @click="openCategoryModal = true"
                                class="rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-100">
                            Ajouter categorie
                        </button>
                    @endif
                    <button type="button"
                            @click="openExpenseModal = true"
                            class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                        Ajouter depense
                    </button>
                </div>
            </div>

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

            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2">
                    <h2 class="text-lg font-semibold text-slate-900">Mon solde</h2>
                    <p class="mt-2 text-sm text-slate-600">Positif = vous devez recevoir. Negatif = vous devez payer.</p>
                    <p class="mt-3 text-2xl font-semibold {{ $userBalance >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                        {{ number_format($userBalance, 2) }} DH
                    </p>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">Membres de la colocation</h2>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach($colocation->members as $member)
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                <p class="font-semibold text-slate-900">{{ $member->name }}</p>
                                <p class="text-xs text-slate-600">{{ $member->email }}</p>
                                <p class="mt-1 text-xs font-semibold text-slate-700">{{ $member->pivot->role_in_colocation }}</p>
                                <p class="mt-1 text-xs text-slate-600">Reputation: {{ $member->reputation_score }}</p>
                                @if($member->pivot->left_at)
                                    <p class="mt-1 text-xs font-semibold text-rose-600">A quitte la colocation</p>
                                @endif

                                @if(
                                    $rolePrefix === 'owner' &&
                                    $member->pivot->left_at === null &&
                                    $member->pivot->role_in_colocation === 'MEMBER'
                                )
                                    <form method="POST" action="{{ route('owner.members.remove', [$colocation, $member]) }}" class="mt-3">
                                        @csrf
                                        <button type="submit"
                                                class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700"
                                                onclick="return confirm('Retirer ce membre ? Ses dettes seront assignees a l owner.')">
                                            Retirer membre
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">Dettes (qui doit a qui)</h2>
                    @if($debts->isEmpty())
                        <p class="mt-4 text-sm text-slate-600">Aucune dette.</p>
                    @else
                        <div class="mt-4 space-y-2">
                            @foreach($debts as $debt)
                                <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                                    <div>
                                        <span class="font-semibold">{{ $debt->fromUser->name }}</span>
                                        doit
                                        <span class="font-semibold">{{ number_format($debt->amount, 2) }} DH</span>
                                        a
                                        <span class="font-semibold">{{ $debt->toUser->name }}</span>
                                    </div>

                                    @if((int) $debt->from_user_id === (int) auth()->id())
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
                </section>
            </div>

            <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold text-slate-900">Depenses</h2>
                    <div class="flex items-center gap-2">
                        <a href="{{ route($rolePrefix . '.colocations.show', $colocation) }}"
                           class="rounded-lg px-3 py-1 text-xs font-semibold {{ ($expenseScope ?? 'all') === 'all' ? 'bg-emerald-600 text-white' : 'border border-slate-300 bg-white text-slate-700' }}">
                            Toutes
                        </a>
                        <a href="{{ route($rolePrefix . '.colocations.show', ['colocation' => $colocation, 'expense_scope' => 'mine', 'expense_month' => ($expenseMonth ?? '')]) }}"
                           class="rounded-lg px-3 py-1 text-xs font-semibold {{ ($expenseScope ?? 'all') === 'mine' ? 'bg-emerald-600 text-white' : 'border border-slate-300 bg-white text-slate-700' }}">
                            Mes depenses
                        </a>
                    </div>
                </div>

                <form method="GET" action="{{ route($rolePrefix . '.colocations.show', $colocation) }}" class="mt-3 flex items-center gap-2">
                    <input type="hidden" name="expense_scope" value="{{ $expenseScope ?? 'all' }}">
                    <label for="expense_month" class="text-xs font-semibold text-slate-700">Mois:</label>
                    <select id="expense_month"
                            name="expense_month"
                            class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs text-slate-700">
                        <option value="">Tous les mois</option>
                        @foreach(($availableExpenseMonths ?? collect()) as $month)
                            <option value="{{ $month }}" {{ ($expenseMonth ?? '') === $month ? 'selected' : '' }}>
                                {{ $month }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        Filtrer
                    </button>
                </form>

                @if(($expenses ?? collect())->isEmpty())
                    <p class="mt-4 text-sm text-slate-600">Aucune depense trouvee pour ce filtre.</p>
                @else
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="border-b border-slate-200 text-slate-600">
                                <tr>
                                    <th class="px-3 py-2">Date</th>
                                    <th class="px-3 py-2">Titre</th>
                                    <th class="px-3 py-2">Categorie</th>
                                    <th class="px-3 py-2">Payeur</th>
                                    <th class="px-3 py-2">Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expenses as $expense)
                                    <tr class="border-b border-slate-100">
                                        <td class="px-3 py-2">{{ \Illuminate\Support\Carbon::parse($expense->spent_at)->format('Y-m-d') }}</td>
                                        <td class="px-3 py-2">{{ $expense->title }}</td>
                                        <td class="px-3 py-2">{{ optional($expense->category)->name ?? '-' }}</td>
                                        <td class="px-3 py-2">{{ optional($expense->payer)->name ?? '-' }}</td>
                                        <td class="px-3 py-2 font-semibold">{{ number_format($expense->amount, 2) }} DH</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold text-slate-900">Categories</h2>
                    @if($rolePrefix === 'owner')
                        <button type="button"
                                @click="openCategoryModal = true"
                                class="rounded-lg border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                            + Nouvelle categorie
                        </button>
                    @endif
                </div>

                @if($colocation->categories->isEmpty())
                    <p class="mt-4 text-sm text-slate-600">Aucune categorie pour cette colocation.</p>
                @else
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($colocation->categories as $category)
                            <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700">
                                <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $category->color ?? '#10b981' }}"></span>
                                {{ $category->name }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </section>

            @if($rolePrefix === 'owner')
                <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">Inviter un membre</h2>
                    <p class="mt-1 text-sm text-slate-600">Envoyez une invitation par email pour cette colocation.</p>

                    <form method="POST" action="{{ route('invitations.send') }}" class="mt-4">
                        @csrf
                        <input type="hidden" name="colocation_id" value="{{ $colocation->id }}">
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <input id="invited_email_details"
                                   name="invited_email"
                                   type="email"
                                   required
                                   class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm"
                                   placeholder="exemple@email.com" />
                            <button type="submit"
                                    class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                                Envoyer invitation
                            </button>
                        </div>
                    </form>
                </section>
            @endif

            <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Paiements enregistres</h2>

                @if($payments->isEmpty())
                    <p class="mt-4 text-sm text-slate-600">Aucun paiement enregistre.</p>
                @else
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="border-b border-slate-200 text-slate-600">
                                <tr>
                                    <th class="px-3 py-2">Date</th>
                                    <th class="px-3 py-2">De</th>
                                    <th class="px-3 py-2">Vers</th>
                                    <th class="px-3 py-2">Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payments as $payment)
                                    <tr class="border-b border-slate-100">
                                        <td class="px-3 py-2">{{ \Illuminate\Support\Carbon::parse($payment->paid_at)->format('Y-m-d H:i') }}</td>
                                        <td class="px-3 py-2">{{ $payment->fromUser->name }}</td>
                                        <td class="px-3 py-2">{{ $payment->toUser->name }}</td>
                                        <td class="px-3 py-2 font-semibold">{{ number_format($payment->amount, 2) }} DH</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>

        @if($rolePrefix === 'owner')
            <div x-show="openCategoryModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/40" @click="openCategoryModal = false"></div>

                <div class="relative w-full max-w-lg rounded-2xl border border-slate-200 bg-white shadow-xl">
                    <div class="border-b border-slate-200 p-5">
                        <h3 class="text-lg font-semibold text-slate-900">Ajouter une categorie</h3>
                        <p class="mt-1 text-sm text-slate-600">Cette categorie sera disponible pour les depenses.</p>
                    </div>

                    <form method="POST" action="{{ route('owner.categories.store', $colocation) }}" class="p-5">
                        @csrf

                        <div>
                            <label class="text-sm font-medium text-slate-700">Nom categorie</label>
                            <input type="text" name="name" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
                        </div>

                        <div class="mt-4">
                            <label class="text-sm font-medium text-slate-700">Couleur</label>
                            <input type="color" name="color" value="#10b981" class="mt-1 h-10 w-20 rounded-lg border border-slate-200">
                        </div>

                        <div class="mt-5 flex items-center justify-end gap-2">
                            <button type="button"
                                    @click="openCategoryModal = false"
                                    class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-800">
                                Annuler
                            </button>
                            <button type="submit"
                                    class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                                Ajouter categorie
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <div x-show="openExpenseModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" @click="openExpenseModal = false"></div>

            <div class="relative w-full max-w-2xl rounded-2xl border border-slate-200 bg-white shadow-xl">
                <div class="border-b border-slate-200 p-5">
                    <h3 class="text-lg font-semibold text-slate-900">Ajouter une depense</h3>
                    <p class="mt-1 text-sm text-slate-600">Le montant est reparti automatiquement a parts egales entre tous les membres actifs.</p>
                </div>

                <form method="POST" action="{{ route('expenses.store') }}" class="p-5">
                    @csrf
                    <input type="hidden" name="colocation_id" value="{{ $colocation->id }}">

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="text-sm font-medium text-slate-700">Titre</label>
                            <input type="text" name="title" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Montant total</label>
                            <input id="expense_amount" type="number" step="0.01" min="0.01" name="amount" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Date</label>
                            <input type="date" name="spent_at" value="{{ now()->format('Y-m-d') }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="text-sm font-medium text-slate-700">Categorie</label>
                            <select name="category_id" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
                                <option value="">Choisir une categorie</option>
                                @foreach($colocation->categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-semibold text-slate-800">Repartition des parts</h4>
                            <span class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                Partage egal automatique
                            </span>
                        </div>

                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            @foreach($activeMembers as $member)
                                <div class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                                    <span class="font-semibold">{{ $member->name }}</span>
                                    <span class="ml-1 text-slate-500">(part egale)</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-5 flex items-center justify-end gap-2">
                        <button type="button"
                                @click="openExpenseModal = false"
                                class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-800">
                            Annuler
                        </button>
                        <button type="submit"
                                class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                            Enregistrer depense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
