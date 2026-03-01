<a href="{{ route('admin.dashboard') }}"
   class="{{ request()->routeIs('admin.dashboard') ? $itemActive : $itemIdle }}">
    Dashboard Admin
</a>
<a href="{{ route('admin.users.index') }}"
   class="{{ request()->routeIs('admin.users.*') ? $itemActive : $itemIdle }}">
    Users
</a>
<a href="{{ route('admin.colocations.index') }}"
   class="{{ request()->routeIs('admin.colocations.*') ? $itemActive : $itemIdle }}">
    Mes colocations
</a>
<a href="{{ route('colocations.history') }}"
   class="{{ request()->routeIs('colocations.history') ? $itemActive : $itemIdle }}">
    Historique Colocations
</a>
<a href="{{ route('profile.edit') }}"
   class="{{ request()->routeIs('profile.*') ? $itemActive : $itemIdle }}">
    Mon Profil
</a>
