<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DebtController extends Controller
{
    public function markAsPaid(Request $request, Debt $debt)
    {
        $currentUser = $request->user();

        if ($debt->amount <= 0) {
            return back()->withErrors(['debt' => 'Cette dette est deja reglee.']);
        }

        if ((int) $debt->from_user_id !== (int) $currentUser->id) {
            abort(403);
        }

        DB::transaction(function () use ($debt, $currentUser) {
            Payment::create([
                'colocation_id' => $debt->colocation_id,
                'from_user_id' => $debt->from_user_id,
                'to_user_id' => $debt->to_user_id,
                'amount' => $debt->amount,
                'paid_at' => now(),
                'created_by' => $currentUser->id,
            ]);

            $debt->amount = 0;
            $debt->save();
        });

        return back()->with('success', 'Paiement enregistre et dette reglee.');
    }
}
