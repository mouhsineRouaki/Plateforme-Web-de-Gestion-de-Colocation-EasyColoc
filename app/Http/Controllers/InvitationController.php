<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

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

        if (! $ownerColocation) {
            return back()->withErrors([
                'invited_email' => 'Vous devez etre owner d une colocation active.',
            ]);
        }

        if ($invitedEmail === strtolower($currentUser->email)) {
            return back()->withErrors([
                'invited_email' => 'Vous ne pouvez pas vous inviter vous meme.',
            ]);
        }

        $isAlreadyActiveMember = $ownerColocation->members()
            ->where('email', $invitedEmail)
            ->wherePivotNull('left_at')
            ->exists();

        if ($isAlreadyActiveMember) {
            return back()->withErrors([
                'invited_email' => 'Cet utilisateur est deja membre actif de votre colocation.',
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
                'invited_email' => 'Une invitation active existe deja pour cet email.',
            ]);
        }

        $token = Str::random(40);

        $invitation = Invitation::create([
            'colocation_id' => $ownerColocation->id,
            'invited_email' => $invitedEmail,
            'token' => $token,
            'status' => 'PENDING',
            'expires_at' => now()->addDays(3),
            'sent_by' => $currentUser->id,
        ]);

        $invitationLink = route('invitations.show', $token);

        try {
            Mail::raw(
                "Bonjour,\n\nVous avez recu une invitation pour rejoindre la colocation '{$ownerColocation->name}'.\n\nLien invitation:\n{$invitationLink}\n\nCe lien expire le {$invitation->expires_at->format('Y-m-d H:i')}.",
                function ($message) use ($invitedEmail, $ownerColocation) {
                    $message->to($invitedEmail)
                        ->subject("Invitation EasyColoc - {$ownerColocation->name}");
                }
            );

            return back()
                ->with('success', "Invitation envoyee a {$invitedEmail}. Un email a ete envoye.")
                ->with('invitation_link', $invitationLink);
        } catch (Throwable $e) {
            return back()
                ->with('success', "Invitation creee pour {$invitedEmail}, mais l'email n'a pas pu etre envoye.")
                ->with('invitation_link', $invitationLink)
                ->with('warning', 'Copiez le lien ci-dessous et partagez-le manuellement.');
        }
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
        $existingUser = User::where('email', $invitation->invited_email)->first();

        if (! $request->user() && ! $existingUser && $invitation->status === 'PENDING') {
            return redirect()->route('register', [
                'email' => $invitation->invited_email,
                'invitation_token' => $invitation->token,
            ]);
        }

        return view('invitations.show', [
            'invitation' => $invitation,
            'existingUser' => $existingUser,
            'authUser' => $request->user(),
        ]);
    }

    public function accept(Request $request, string $token)
    {
        $user = $request->user();
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if ($invitation->status !== 'PENDING') {
            return back()->withErrors(['invitation' => 'Invitation non valide (deja traitee).']);
        }

        if (now()->greaterThan($invitation->expires_at)) {
            $invitation->update(['status' => 'EXPIRED']);
            return back()->withErrors(['invitation' => 'Invitation expiree.']);
        }

        if ($invitation->invited_email !== $user->email) {
            return back()->withErrors(['invitation' => 'Cette invitation ne correspond pas a votre email.']);
        }

        $hasActive = $user->colocations()
            ->wherePivotNull('left_at')
            ->where('status', 'ACTIVE')
            ->exists();

        if ($hasActive) {
            return back()->withErrors(['invitation' => 'Vous avez deja une colocation active.']);
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

            return redirect()
                ->route('dashboard')
                ->with('success', 'Invitation acceptee avec succes.');
        });
    }

    public function refuse(Request $request, string $token)
    {
        $user = $request->user();
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if ($invitation->invited_email !== $user->email) {
            return back()->withErrors(['invitation' => 'Cette invitation ne correspond pas a votre email.']);
        }

        if ($invitation->status !== 'PENDING') {
            return back()->withErrors(['invitation' => 'Invitation non valide (deja traitee).']);
        }

        $invitation->update([
            'status' => 'REFUSED',
            'accepted_by' => $user->id,
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Invitation refusee.');
    }
}
