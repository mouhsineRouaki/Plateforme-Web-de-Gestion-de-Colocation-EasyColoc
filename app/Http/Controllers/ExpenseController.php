<?php

namespace App\Http\Controllers;

use App\Models\Colocation;
use App\Models\Debt;
use App\Models\Expense;
use App\Models\ExpenseSplit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'colocation_id' => 'required|integer|exists:colocations,id',
            'category_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'spent_at' => 'required|date',
        ]);
        $colocation = Colocation::query()->with(['members' =>function ($query){$query->wherePivotNull('left_at');}])->findOrFail($request->colocation_id);

        $payerId = auth()->id();
        $colocationId = $colocation->id;
        $amount = $request->amount;

        $isActiveMember = $colocation->members->contains(function ($member) use ($payerId) {
            return $member->id === $payerId;
        });

        abort_if(! $isActiveMember, 403);

        $categoryInColocation = $colocation->categories()->where('id', $request->category_id) ->exists();

        if (! $categoryInColocation) {
            return back()->withErrors([
                'category_id' => 'La categorie choisie ne fait pas partie de cette colocation.',
            ])->withInput();
        }

        $activeMemberIds = $colocation->members
            ->pluck('id')
            ->values();

        if ($activeMemberIds->isEmpty()) {
            return back()->withErrors([
                'amount' => 'Aucun membre actif dans cette colocation.',
            ])->withInput();
        }

        $montantEgale = $this->diviseAmount($activeMemberIds->all(), $amount);

        DB::transaction(function () use ($request, $colocationId, $payerId, $amount, $montantEgale) {
            $expense = Expense::create([
                'colocation_id' => $colocationId,
                'category_id' => (int) $request->category_id,
                'payer_id' => $payerId,
                'title' => $request->title,
                'amount' => $amount,
                'spent_at' => $request->spent_at,
                'note' => $request->note ?? null,
            ]);

            foreach ($montantEgale as $userId => $share) {
                ExpenseSplit::create([
                    'expense_id' => $expense->id,
                    'user_id' => (int) $userId,
                    'share_amount' => $share,
                ]);

                if ((int) $userId === (int) $payerId) {
                    continue;
                }
                $this->addDebtMembers($colocationId, $userId, $payerId, $share);
            }
        });

        return back()->with('success', 'Depense ajoutee avec repartition egale automatique. Dettes mises a jour.');
    }

    private function diviseAmount(array $memberIds, float $amount): array{
        $count = count($memberIds);
        if ($count === 0) {
            return [];
        }
        $part = $amount / $count ;
        $shares = [];   

        foreach ($memberIds as $memberId) {
            $shares[$memberId] = round($part , 2) ;
        }

        return $shares;
    }

    private function addDebtMembers(int $colocationId, int $debtorId, int $creditorId, float $amount): void
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
