<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        return view('auth.register', [
            'prefilledEmail' => $request->query('email'),
            'invitationToken' => $request->query('invitation_token'),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $invitationToken = $request->input('invitation_token');

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        $firstUser = User::count() === 0;   

        $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => $firstUser ? 'GLOBAL_ADMIN' : 'USER',
    ]);


        event(new Registered($user));

        Auth::login($user);

        if ($user->role === 'GLOBAL_ADMIN') {
            return redirect()->route('admin.dashboard');
        }

        if ($invitationToken) {
            return redirect()
                ->route('invitations.show', $invitationToken)
                ->with('success', 'Compte cree. Vous pouvez maintenant accepter ou refuser l invitation.');
        }

        return redirect()->route('dashboard');
    }
}
