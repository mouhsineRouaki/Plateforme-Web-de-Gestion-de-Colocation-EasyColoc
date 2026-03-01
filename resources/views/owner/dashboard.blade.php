<x-app-layout>
    <div class="py-10">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-semibold text-slate-900">Dashboard Owner</h1>
            <p class="mt-1 text-sm text-slate-600">Gerez votre colocation active, vos membres et vos invitations.</p>

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
                @php
                    $imageUrl = $activeColocation->image ?: 'https://via.placeholder.com/1280x540?text=EasyColoc';
                @endphp

                <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="overflow-hidden rounded-2xl border border-slate-200">
                        <img src="{{ $imageUrl }}" alt="Image colocation" class="h-52 w-full object-cover sm:h-60">
                    </div>

                    <div class="mt-5 flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Colocation active</p>
                            <h2 class="mt-1 text-2xl font-semibold text-slate-900">{{ $activeColocation->name }}</h2>
                            @if($activeColocation->description)
                                <p class="mt-2 max-w-3xl text-sm text-slate-600">{{ $activeColocation->description }}</p>
                            @endif
                            <div class="mt-3 flex flex-wrap gap-2">
                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                    {{ $activeColocation->members->count() }} membres actifs
                                </span>
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                    Role: OWNER
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('owner.colocations.show', $activeColocation) }}"
                               class="inline-flex rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                                Voir details
                            </a>
                            <a href="{{ route('owner.colocations.index') }}"
                               class="inline-flex rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-50">
                                Mes colocations
                            </a>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($activeColocation->members as $member)
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="font-semibold text-slate-900">{{ $member->name }}</p>
                                <p class="text-xs text-slate-600">{{ $member->email }}</p>
                                <div class="mt-2 flex items-center justify-between">
                                    <p class="text-xs font-semibold text-slate-700">{{ $member->pivot->role_in_colocation }}</p>
                                    <p class="text-xs text-slate-600">Rep: {{ $member->reputation_score }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
