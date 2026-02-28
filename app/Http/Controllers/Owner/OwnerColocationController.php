<?php

namespace App\Http\Controllers\Owner;

use App\Models\Colocation;
use App\Models\Debt;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\ReputationEvent;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OwnerColocationController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('colocations.history');
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

        $expenseScope = $request->query('expense_scope', 'all');
        if (! in_array($expenseScope, ['all', 'mine'], true)) {
            $expenseScope = 'all';
        }

        $expenseMonth = (string) $request->query('expense_month', '');
        if ($expenseMonth !== '' && ! preg_match('/^\d{4}-\d{2}$/', $expenseMonth)) {
            $expenseMonth = '';
        }

        $expensesQuery = Expense::with([
            'payer:id,name',
            'category:id,name',
        ])
            ->where('colocation_id', $colocation->id)
            ->orderByDesc('spent_at')
            ->orderByDesc('id');

        if ($expenseScope === 'mine') {
            $expensesQuery->where('payer_id', $user->id);
        }

        if ($expenseMonth !== '') {
            [$year, $month] = array_map('intval', explode('-', $expenseMonth));
            $expensesQuery
                ->whereYear('spent_at', $year)
                ->whereMonth('spent_at', $month);
        }

        $expenses = $expensesQuery->limit(100)->get();

        $availableExpenseMonths = Expense::query()
            ->where('colocation_id', $colocation->id)
            ->orderByDesc('spent_at')
            ->get(['spent_at'])
            ->map(function ($row) {
                return \Illuminate\Support\Carbon::parse($row->spent_at)->format('Y-m');
            })
            ->unique()
            ->values();

        $activeMembers = $colocation->members->filter(function ($member) {
            return $member->pivot->left_at === null;
        })->values();

        $userBalance = (float) Debt::query()
            ->where('colocation_id', $colocation->id)
            ->where('amount', '>', 0)
            ->selectRaw('
                COALESCE(SUM(CASE WHEN to_user_id = ? THEN amount ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN from_user_id = ? THEN amount ELSE 0 END), 0) as balance
            ', [$user->id, $user->id])
            ->value('balance');

        return view('colocations.show', [
            'rolePrefix' => 'owner',
            'colocation' => $colocation,
            'activeMembers' => $activeMembers,
            'debts' => $debts,
            'expenses' => $expenses,
            'expenseScope' => $expenseScope,
            'expenseMonth' => $expenseMonth,
            'availableExpenseMonths' => $availableExpenseMonths,
            'payments' => $payments,
            'userBalance' => $userBalance,
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

    public function leave(Request $request, Colocation $colocation)
    {
        $owner = $request->user();

        $ownerMembership = $owner->colocations()
            ->where('colocations.id', $colocation->id)
            ->wherePivot('role_in_colocation', 'OWNER')
            ->wherePivotNull('left_at')
            ->exists();

        abort_if(! $ownerMembership, 403);

        $successor = $colocation->members()
            ->wherePivotNull('left_at')
            ->wherePivot('role_in_colocation', 'MEMBER')
            ->orderByDesc('reputation_score')
            ->orderBy('colocation_user.joined_at')
            ->orderBy('users.id')
            ->first();

        if (! $successor) {
            return back()->withErrors([
                'colocation' => 'Impossible de quitter: aucun membre actif disponible pour devenir owner.',
            ]);
        }

        $hasDebtBeforeLeave = Debt::query()
            ->where('colocation_id', $colocation->id)
            ->where('from_user_id', $owner->id)
            ->where('amount', '>', 0)
            ->exists();

        DB::transaction(function () use ($colocation, $owner, $successor, $hasDebtBeforeLeave) {
            $this->reassignMemberDebtsToReplacement($colocation->id, $owner->id, $successor->id);

            $colocation->members()->updateExistingPivot($successor->id, [
                'role_in_colocation' => 'OWNER',
            ]);

            $colocation->members()->updateExistingPivot($owner->id, [
                'left_at' => now(),
            ]);

            $this->applyLeaveReputation($owner, $colocation->id, $hasDebtBeforeLeave);
        });

        return redirect()
            ->route('dashboard')
            ->with('success', "Vous avez quitte la colocation. {$successor->name} est maintenant owner.");
    }

    public function cancel(Request $request, Colocation $colocation)
    {
        $owner = $request->user();

        $ownerMembership = $owner->colocations()
            ->where('colocations.id', $colocation->id)
            ->wherePivot('role_in_colocation', 'OWNER')
            ->wherePivotNull('left_at')
            ->exists();

        abort_if(! $ownerMembership, 403);

        if ($colocation->status === 'CANCELLED') {
            return back()->withErrors([
                'colocation' => 'Cette colocation est deja annulee.',
            ]);
        }

        DB::transaction(function () use ($colocation) {
            $colocation->update([
                'status' => 'CANCELLED',
                'cancelled_at' => now(),
            ]);

            DB::table('colocation_user')
                ->where('colocation_id', $colocation->id)
                ->whereNull('left_at')
                ->update([
                    'left_at' => now(),
                    'updated_at' => now(),
                ]);
        });

        return redirect()
            ->route('owner.dashboard')
            ->with('success', 'Colocation annulee avec succes.');
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
