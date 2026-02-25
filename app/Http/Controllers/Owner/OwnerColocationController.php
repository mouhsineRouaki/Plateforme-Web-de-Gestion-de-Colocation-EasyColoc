<?php

namespace App\Http\Controllers\Owner;

use App\Models\Colocation;
use App\Models\Debt;
use App\Models\Payment;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OwnerColocationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $colocations = $user->colocations()
            ->wherePivot('role_in_colocation', 'OWNER')
            ->with(['members' => function ($query) {
                $query->wherePivotNull('left_at');
            }])
            ->orderByDesc('colocations.created_at')
            ->get();

        return view('owner.colocations.index', [
            'colocations' => $colocations,
        ]);
    }

    public function show(Request $request, Colocation $colocation)
    {
        $user = $request->user();

        $membership = $user->colocations()
            ->where('colocations.id', $colocation->id)
            ->wherePivot('role_in_colocation', 'OWNER')
            ->first();

        abort_if(! $membership, 403);

        $colocation->load(['members', 'categories']);

        $debts = Debt::with(['fromUser', 'toUser'])
            ->where('colocation_id', $colocation->id)
            ->where('amount', '>', 0)
            ->orderByDesc('amount')
            ->get();

        $payments = Payment::with(['fromUser', 'toUser', 'createdBy'])
            ->where('colocation_id', $colocation->id)
            ->orderByDesc('paid_at')
            ->limit(20)
            ->get();

        $activeMembers = $colocation->members->filter(function ($member) {
            return $member->pivot->left_at === null;
        })->values();

        return view('colocations.show', [
            'rolePrefix' => 'owner',
            'colocation' => $colocation,
            'activeMembers' => $activeMembers,
            'debts' => $debts,
            'payments' => $payments,
        ]);
    }

    public function removeMember(Request $request, Colocation $colocation, User $user)
    {
        $owner = $request->user();

        $ownerMembership = $owner->colocations()
            ->where('colocations.id', $colocation->id)
            ->wherePivot('role_in_colocation', 'OWNER')
            ->wherePivotNull('left_at')
            ->exists();

        abort_if(! $ownerMembership, 403);

        $targetMembership = $user->colocations()
            ->where('colocations.id', $colocation->id)
            ->wherePivotNull('left_at')
            ->first();

        if (! $targetMembership) {
            return back()->withErrors(['member' => 'Membre introuvable dans cette colocation.']);
        }

        if ($user->id === $owner->id) {
            return back()->withErrors(['member' => 'Vous ne pouvez pas vous retirer vous-meme ici.']);
        }

        if ($targetMembership->pivot->role_in_colocation === 'OWNER') {
            return back()->withErrors(['member' => 'Vous ne pouvez pas retirer un owner.']);
        }

        DB::transaction(function () use ($colocation, $owner, $user) {
            $memberDebts = Debt::query()
                ->where('colocation_id', $colocation->id)
                ->where('amount', '>', 0)
                ->where(function ($query) use ($user) {
                    $query->where('from_user_id', $user->id)
                        ->orWhere('to_user_id', $user->id);
                })
                ->get();

            foreach ($memberDebts as $debt) {
                $amount = (float) $debt->amount;
                if ($amount <= 0) {
                    continue;
                }

                // dette: membre -> X  devient owner -> X
                if ((int) $debt->from_user_id === (int) $user->id && (int) $debt->to_user_id !== (int) $owner->id) {
                    $this->addNetDebt($colocation->id, $owner->id, (int) $debt->to_user_id, $amount);
                }

                // dette: X -> membre devient X -> owner
                if ((int) $debt->to_user_id === (int) $user->id && (int) $debt->from_user_id !== (int) $owner->id) {
                    $this->addNetDebt($colocation->id, (int) $debt->from_user_id, $owner->id, $amount);
                }

                $debt->amount = 0;
                $debt->save();
            }

            $colocation->members()->updateExistingPivot($user->id, [
                'left_at' => now(),
            ]);
        });

        return back()->with('success', 'Membre retire. Les dettes ont ete assignees a l owner.');
    }

    private function addNetDebt(int $colocationId, int $debtorId, int $creditorId, float $amount): void
    {
        if ($amount <= 0 || $debtorId === $creditorId) {
            return;
        }

        $sameDirection = Debt::firstOrCreate(
            [
                'colocation_id' => $colocationId,
                'from_user_id' => $debtorId,
                'to_user_id' => $creditorId,
            ],
            ['amount' => 0]
        );

        $reverseDirection = Debt::firstOrCreate(
            [
                'colocation_id' => $colocationId,
                'from_user_id' => $creditorId,
                'to_user_id' => $debtorId,
            ],
            ['amount' => 0]
        );

        $sameAmount = (float) $sameDirection->amount;
        $reverseAmount = (float) $reverseDirection->amount;

        if ($reverseAmount > 0) {
            if ($reverseAmount >= $amount) {
                $reverseDirection->amount = $reverseAmount - $amount;
                $sameDirection->amount = 0;
            } else {
                $remaining = $amount - $reverseAmount;
                $reverseDirection->amount = 0;
                $sameDirection->amount = $sameAmount + $remaining;
            }
        } else {
            $sameDirection->amount = $sameAmount + $amount;
        }

        $sameDirection->save();
        $reverseDirection->save();
    }
}
