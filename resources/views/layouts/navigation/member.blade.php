<a href="{{ route('member.dashboard') }}"
   class="{{ request()->routeIs('member.dashboard') ? $itemActive : $itemIdle }}">
    Dashboard Member
</a>
<a href="{{ route('member.colocations.index') }}"
   class="{{ request()->routeIs('member.colocations.*') ? $itemActive : $itemIdle }}">
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
