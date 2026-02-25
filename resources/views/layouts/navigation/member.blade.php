<a href="{{ route('member.dashboard') }}"
   class="{{ request()->routeIs('member.dashboard') ? $itemActive : $itemIdle }}">
    Dashboard Member
</a>
<a href="{{ route('member.colocations.index') }}"
   class="{{ request()->routeIs('member.colocations.*') ? $itemActive : $itemIdle }}">
    Mes Colocations
</a>
