<?php

namespace App\Http\Controllers;

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
            'colocation_id' => 'required|integer',
            'category_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'spent_at' => 'required|date',
            'splits' => 'required|array|min:1',
            'splits.*.user_id' => 'required|integer',
            'splits.*.share_amount' => 'required|numeric|min:0',
        ]);

        $colocationId = (int) $request->colocation_id;
        $payerId = (int) auth()->id();
        $amount = (float) $request->amount;

        $sum = 0;
        foreach ($request->splits as $split) {
            $sum += (float) $split['share_amount'];
        }

        if (round($sum, 2) !== round($amount, 2)) {
            return back()->withErrors([
                'splits' => "La somme des parts ($sum) doit etre egale au montant ($amount).",
            ]);
        }

        DB::transaction(function () use ($request, $colocationId, $payerId) {
            $expense = Expense::create([
                'colocation_id' => $colocationId,
                'category_id' => (int) $request->category_id,
                'payer_id' => $payerId,
                'title' => $request->title,
                'amount' => (float) $request->amount,
                'spent_at' => $request->spent_at,
                'note' => $request->note ?? null,
            ]);

            foreach ($request->splits as $split) {
                $userId = (int) $split['user_id'];
                $share = (float) $split['share_amount'];

                ExpenseSplit::create([
                    'expense_id' => $expense->id,
                    'user_id' => $userId,
                    'share_amount' => $share,
                ]);

                if ($userId === $payerId) {
                    continue;
                }

                // userId doit payer payerId avec compensation des dettes inverses
                $this->addNetDebt($colocationId, $userId, $payerId, $share);
            }
        });

        return back()->with('success', 'Depense ajoutee. Dettes mises a jour.');
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
