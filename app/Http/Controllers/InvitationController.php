<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    public function send(Request $request)
    {
        $data = $request->validate([
            'invited_email' => ['required', 'email', 'max:255'],
        ]);

        $currentUser = $request->user();
        $invitedEmail = strtolower(trim($data['invited_email']));

        $ownerColocation = $currentUser->colocations()
            ->wherePivotNull('left_at')
            ->wherePivot('role_in_colocation', 'OWNER')
            ->where('status', 'ACTIVE')
            ->first();

        if (!$ownerColocation) {
            return back()->withErrors([
                'invited_email' => 'Vous devez etre owner d\'une colocation active.',
            ]);
        }

        if ($invitedEmail === strtolower($currentUser->email)) {
            return back()->withErrors([
                'invited_email' => 'Vous ne pouvez pas vous inviter vous-même.',
            ]);
        }

        $isAlreadyActiveMember = $ownerColocation->members()
            ->where('email', $invitedEmail)
            ->wherePivotNull('left_at')
            ->exists();

        if ($isAlreadyActiveMember) {
            return back()->withErrors([
                'invited_email' => 'Cet utilisateur est déjà membre actif de votre colocation.',
            ]);
        }

        $alreadyHasPendingInvitation = Invitation::query()
            ->where('colocation_id', $ownerColocation->id)
            ->where('invited_email', $invitedEmail)
            ->where('status', 'PENDING')
            ->where('expires_at', '>', now())
            ->exists();

        if ($alreadyHasPendingInvitation) {
            return back()->withErrors([
                'invited_email' => 'Une invitation active existe déjà pour cet email.',
            ]);
        }

        $token = Str::random(40);

        Invitation::create([
            'colocation_id' => $ownerColocation->id,
            'invited_email' => $invitedEmail,
            'token' => $token,
            'status' => 'PENDING',
            'expires_at' => now()->addDays(3),
            'sent_by' => $currentUser->id,
        ]);

        $invitationLink = route('invitations.show', $token);

        return back()->with('success', "Invitation créée pour {$invitedEmail}. Lien: {$invitationLink}");
    }

    public function joinFromLink(Request $request)
    {
        $data = $request->validate([
            'invite_link' => ['required', 'string', 'max:500'],
        ]);

        $token = trim($data['invite_link']);

        if (filter_var($token, FILTER_VALIDATE_URL)) {
            $path = parse_url($token, PHP_URL_PATH);
            $token = trim(basename($path ?? ''), '/');
        }

        return redirect()->route('invitations.show', $token);
    }

    public function show(Request $request, string $token)
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        return view('invitations.show', [
            'invitation' => $invitation,
        ]);
    }

    public function accept(Request $request, string $token)
    {
        $user = $request->user();

        $invitation = Invitation::where('token', $token)->firstOrFail();

        if ($invitation->status !== 'PENDING') {
            return back()->withErrors(['invitation' => 'Invitation non valide (déjà traitée).']);
        }

        if (now()->greaterThan($invitation->expires_at)) {
            $invitation->update(['status' => 'EXPIRED']);
            return back()->withErrors(['invitation' => 'Invitation expirée.']);
        }

        if ($invitation->invited_email !== $user->email) {
            return back()->withErrors(['invitation' => 'Cette invitation ne correspond pas à votre email.']);
        }

        $hasActive = $user->colocations()
            ->wherePivotNull('left_at')
            ->where('status', 'ACTIVE')
            ->exists();

        if ($hasActive) {
            return back()->withErrors(['invitation' => 'Vous avez déjà une colocation active.']);
        }

        return DB::transaction(function () use ($invitation, $user) {
            $invitation->colocation->members()->attach($user->id, [
                'role_in_colocation' => 'MEMBER',
                'joined_at' => now(),
            ]);

            $invitation->update([
                'status' => 'ACCEPTED',
                'accepted_by' => $user->id,
            ]);

            return redirect()->route('dashboard')->with('success', 'Invitation acceptée.');
        });
    }

    public function refuse(Request $request, string $token)
    {
        $user = $request->user();
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if ($invitation->invited_email !== $user->email) {
            return back()->withErrors(['invitation' => 'Cette invitation ne correspond pas à votre email.']);
        }

        if ($invitation->status !== 'PENDING') {
            return back()->withErrors(['invitation' => 'Invitation non valide (déjà traitée).']);
        }

        $invitation->update([
            'status' => 'REFUSED',
            'accepted_by' => $user->id,
        ]);

        return redirect()->route('dashboard')->with('success', 'Invitation refusée.');
    }
}
