<?php

namespace App\Http\Controllers\Member;

use App\Models\Colocation;
use App\Models\Debt;
use App\Models\Payment;
use App\Models\ReputationEvent;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MemberColocationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $colocations = $user->colocations()
            ->wherePivot('role_in_colocation', 'MEMBER')
            ->with(['members' => function ($query) {
                $query->wherePivotNull('left_at');
            }])
            ->orderByDesc('colocations.created_at')
            ->get();

        return view('member.colocations.index', [
            'colocations' => $colocations,
        ]);
    }

    public function show(Request $request, Colocation $colocation)
    {
        $user = $request->user();

        $membership = $user->colocations()
            ->where('colocations.id', $colocation->id)
            ->wherePivot('role_in_colocation', 'MEMBER')
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
            'rolePrefix' => 'member',
            'colocation' => $colocation,
            'activeMembers' => $activeMembers,
            'debts' => $debts,
            'payments' => $payments,
        ]);
    }

    public function leave(Request $request, Colocation $colocation)
    {
        $member = $request->user();

        $memberMembership = $member->colocations()
            ->where('colocations.id', $colocation->id)
            ->wherePivot('role_in_colocation', 'MEMBER')
            ->wherePivotNull('left_at')
            ->exists();

        abort_if(! $memberMembership, 403);

        $activeOwner = $colocation->members()
            ->wherePivotNull('left_at')
            ->wherePivot('role_in_colocation', 'OWNER')
            ->first();

        if (! $activeOwner) {
            return back()->withErrors([
                'colocation' => 'Impossible de quitter: owner actif introuvable.',
            ]);
        }

        $hasDebtBeforeLeave = Debt::query()
            ->where('colocation_id', $colocation->id)
            ->where('from_user_id', $member->id)
            ->where('amount', '>', 0)
            ->exists();

        DB::transaction(function () use ($colocation, $member, $activeOwner, $hasDebtBeforeLeave) {
            $this->reassignMemberDebtsToReplacement($colocation->id, $member->id, $activeOwner->id);

            $colocation->members()->updateExistingPivot($member->id, [
                'left_at' => now(),
            ]);

            $this->applyLeaveReputation($member, $colocation->id, $hasDebtBeforeLeave);
        });

        return redirect()
            ->route('dashboard')
            ->with('success', 'Vous avez quitte la colocation.');
    }

    private function reassignMemberDebtsToReplacement(int $colocationId, int $leavingUserId, int $replacementUserId): void
    {
        $memberDebts = Debt::query()
            ->where('colocation_id', $colocationId)
            ->where('amount', '>', 0)
            ->where(function ($query) use ($leavingUserId) {
                $query->where('from_user_id', $leavingUserId)
                    ->orWhere('to_user_id', $leavingUserId);
            })
            ->get();

        foreach ($memberDebts as $debt) {
            $amount = (float) $debt->amount;
            if ($amount <= 0) {
                continue;
            }

            if ((int) $debt->from_user_id === $leavingUserId && (int) $debt->to_user_id !== $replacementUserId) {
                $this->addNetDebt($colocationId, $replacementUserId, (int) $debt->to_user_id, $amount);
            }

            if ((int) $debt->to_user_id === $leavingUserId && (int) $debt->from_user_id !== $replacementUserId) {
                $this->addNetDebt($colocationId, (int) $debt->from_user_id, $replacementUserId, $amount);
            }

            $debt->amount = 0;
            $debt->save();
        }
    }

    private function applyLeaveReputation(User $user, int $colocationId, bool $hasDebt): void
    {
        $delta = $hasDebt ? -1 : 1;

        $user->increment('reputation_score', $delta);

        ReputationEvent::create([
            'user_id' => $user->id,
            'colocation_id' => $colocationId,
            'type' => $hasDebt ? 'LEAVE_WITH_DEBT' : 'LEAVE_WITHOUT_DEBT',
            'delta' => $delta,
            'meta' => [
                'reason' => 'leave_colocation',
            ],
        ]);
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
