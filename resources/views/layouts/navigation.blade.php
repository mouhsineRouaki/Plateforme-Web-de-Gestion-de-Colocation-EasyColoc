@php
    $user = auth()->user();
    $itemBase = 'rounded-xl px-3 py-2 text-sm font-semibold transition';
    $itemIdle = $itemBase . ' text-slate-700 hover:bg-slate-100';
    $itemActive = $itemBase . ' bg-emerald-100 text-emerald-800';

    $hasOwnerRole = $user->colocations()
        ->wherePivot('role_in_colocation', 'OWNER')
        ->wherePivotNull('left_at')
        ->where('status', 'ACTIVE')
        ->exists();

    $hasMemberRole = $user->colocations()
        ->wherePivot('role_in_colocation', 'MEMBER')
        ->wherePivotNull('left_at')
        ->where('status', 'ACTIVE')
        ->exists();

    if (request()->routeIs('admin.*')) {
        $menuKey = 'admin';
        $roleLabel = 'Admin';
    } elseif (request()->routeIs('owner.*')) {
        $menuKey = 'owner';
        $roleLabel = 'Owner';
    } elseif (request()->routeIs('member.*')) {
        $menuKey = 'member';
        $roleLabel = 'Member';
    } elseif ($user->role === 'GLOBAL_ADMIN') {
        $menuKey = 'admin';
        $roleLabel = 'Admin';
    } elseif ($hasOwnerRole) {
        $menuKey = 'owner';
        $roleLabel = 'Owner';
    } elseif ($hasMemberRole) {
        $menuKey = 'member';
        $roleLabel = 'Member';
    } else {
        $menuKey = 'user';
        $roleLabel = 'User';
    }
@endphp

<header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-6">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-3">
                <span class="grid h-10 w-10 place-items-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 11.5 12 4l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-8.5Z" />
                    </svg>
                </span>
                <span class="leading-tight">
                    <span class="block text-base font-extrabold tracking-tight text-slate-900">EasyColoc</span>
                    <span class="block text-xs font-medium text-slate-500">Gestion Colocation</span>
                </span>
            </a>

            <nav class="hidden items-center gap-2 md:flex">
                @include('layouts.navigation.' . $menuKey)
            </nav>
        </div>

        <div class="flex items-center gap-2">
            <span class="hidden rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700 sm:inline">
                {{ $roleLabel }}
            </span>
            <span class="hidden text-sm font-medium text-slate-700 sm:inline">{{ $user->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="rounded-xl bg-slate-900 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
                    Logout
                </button>
            </form>
        </div>
    </div>

    <div class="border-t border-slate-100 px-4 py-2 md:hidden">
        <nav class="flex flex-wrap items-center gap-2">
            @include('layouts.navigation.' . $menuKey)
        </nav>
    </div>
</header>
