<?php

namespace App\Http\Controllers\Member;

use App\Models\Colocation;
use App\Models\Debt;
use App\Models\Expense;
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
        return redirect()->route('colocations.history');
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

        $userBalance = Debt::query()
            ->where('colocation_id', $colocation->id)
            ->where('amount', '>', 0)
            ->selectRaw('
                COALESCE(SUM(CASE WHEN to_user_id = ? THEN amount ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN from_user_id = ? THEN amount ELSE 0 END), 0) as balance
            ', [$user->id, $user->id])
            ->value('balance');

        return view('colocations.show', [
            'rolePrefix' => 'member',
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

        $ilaDejaDebt = Debt::query()
            ->where('colocation_id', $colocation->id)
            ->where('from_user_id', $member->id)
            ->where('amount', '>', 0)
            ->exists();

        DB::transaction(function () use ($colocation, $member, $activeOwner, $ilaDejaDebt) {
            $this->reassignMemberDebtsToReplacement($colocation->id, $member->id, $activeOwner->id);

            $colocation->members()->updateExistingPivot($member->id, [
                'left_at' => now(),
            ]);

            $this->applyLeaveReputation($member, $colocation->id, $ilaDejaDebt);
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
        $reput = $hasDebt ? -1 : 1;

        $user->increment('reputation_score', $reput);

        ReputationEvent::create([
            'user_id' => $user->id,
            'colocation_id' => $colocationId,
            'type' => $hasDebt ? 'Debt' : 'NoDebt',
            'delta' => $reput,
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

        $sameAmount = $sameDirection->amount;
        $reverseAmount = $reverseDirection->amount;

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
