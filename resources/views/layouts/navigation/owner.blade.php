<a href="{{ route('owner.dashboard') }}"
   class="{{ request()->routeIs('owner.dashboard') ? $itemActive : $itemIdle }}">
    Dashboard Owner
</a>
<a href="{{ route('owner.colocations.index') }}"
   class="{{ request()->routeIs('owner.colocations.*') ? $itemActive : $itemIdle }}">
    Mes Colocations
</a>
