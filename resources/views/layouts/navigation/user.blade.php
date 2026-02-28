<a href="{{ route('dashboard') }}"
   class="{{ request()->routeIs('dashboard') ? $itemActive : $itemIdle }}">
    Dashboard
</a>
<a href="{{ route('profile.edit') }}"
   class="{{ request()->routeIs('profile.*') ? $itemActive : $itemIdle }}">
    Mon Profil
</a>
<a href="{{ route('colocations.history') }}"
   class="{{ request()->routeIs('colocations.history') ? $itemActive : $itemIdle }}">
    Historique Colocations
</a>
