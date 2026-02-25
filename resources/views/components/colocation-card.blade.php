@props(['colocation', 'detailsUrl' => null])

@php
    $isGreyed = $colocation->status !== 'ACTIVE' || $colocation->pivot->left_at !== null;
    $joinedAt = $colocation->pivot->joined_at
        ? \Illuminate\Support\Carbon::parse($colocation->pivot->joined_at)->format('Y-m-d H:i')
        : '-';
    $leftAt = $colocation->pivot->left_at
        ? \Illuminate\Support\Carbon::parse($colocation->pivot->left_at)->format('Y-m-d H:i')
        : 'Toujours actif';
    $imageUrl = $colocation->image ?: 'https://via.placeholder.com/640x360?text=EasyColoc';
@endphp

<article class="overflow-hidden rounded-2xl border border-slate-200 shadow-sm {{ $isGreyed ? 'bg-slate-100 opacity-60' : 'bg-white' }}">
    <img src="{{ $imageUrl }}" alt="Image colocation" class="h-44 w-full object-cover" />

    <div class="p-5">
        <div class="flex items-start justify-between gap-4">
            <h3 class="text-lg font-semibold text-slate-900">{{ $colocation->name }}</h3>
            <span class="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700">
                {{ $colocation->status }}
            </span>
        </div>

        @if($colocation->description)
            <p class="mt-2 text-sm text-slate-600">{{ $colocation->description }}</p>
        @endif

        <div class="mt-4 grid gap-2 text-sm text-slate-700 sm:grid-cols-2">
            <p><span class="font-semibold">Role:</span> {{ $colocation->pivot->role_in_colocation }}</p>
            <p><span class="font-semibold">Membres actifs:</span> {{ $colocation->members->where('pivot.left_at', null)->count() }}</p>
            <p><span class="font-semibold">Rejoint le:</span> {{ $joinedAt }}</p>
            <p><span class="font-semibold">Left at:</span> {{ $leftAt }}</p>
        </div>

        @if($isGreyed)
            <p class="mt-4 text-xs font-semibold text-slate-600">
                Carte grisee: colocation inactive ou vous avez quitte la colocation.
            </p>
        @elseif($detailsUrl)
            <div class="mt-4">
                <a href="{{ $detailsUrl }}"
                   class="inline-flex rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    Voir details colocation
                </a>
            </div>
        @endif
    </div>
</article>
