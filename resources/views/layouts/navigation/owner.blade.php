<a href="{{ route('owner.dashboard') }}"
   class="{{ request()->routeIs('owner.dashboard') ? $itemActive : $itemIdle }}">
    Dashboard Owner
</a>
<a href="{{ route('owner.colocations.index') }}"
   class="{{ request()->routeIs('owner.colocations.*') ? $itemActive : $itemIdle }}">
    Mes Colocations
</a>
<a href="{{ route('colocations.history') }}"
   class="{{ request()->routeIs('colocations.history') ? $itemActive : $itemIdle }}">
    Historique Colocations
</a>
<a href="{{ route('profile.edit') }}"
   class="{{ request()->routeIs('profile.*') ? $itemActive : $itemIdle }}">
    Mon Profil
</a>
