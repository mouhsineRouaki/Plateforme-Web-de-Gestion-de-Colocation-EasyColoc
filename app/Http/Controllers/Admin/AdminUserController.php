<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $q = trim( $request->query('q', ''));
        $status = $request->query('status'); 

        $users = User::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {$qq->where('name', 'ilike', "%{$q}%")->orWhere('email', 'ilike', "%{$q}%");});
            })
            ->when($status === 'banned', fn($query) => $query->where('is_banned', true))
            ->when($status === 'active', fn($query) => $query->where('is_banned', false))
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'q', 'status'));
    }

    public function ban(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', "Vous ne pouvez pas vous bannir vous-même.");
        }

        $user->update([
            'is_banned' => true,
            'banned_at' => now(),
        ]);

        return back()->with('success', "Utilisateur banni avec succès.");
    }

    public function unban(User $user)
    {
        $user->update([
            'is_banned' => false,
            'banned_at' => null,
        ]);

        return back()->with('success', "Utilisateur débanni avec succès.");
    }
}