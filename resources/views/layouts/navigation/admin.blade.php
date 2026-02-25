<a href="{{ route('admin.dashboard') }}"
   class="{{ request()->routeIs('admin.dashboard') ? $itemActive : $itemIdle }}">
    Dashboard Admin
</a>
<a href="{{ route('admin.users.index') }}"
   class="{{ request()->routeIs('admin.users.*') ? $itemActive : $itemIdle }}">
    Users
</a>
