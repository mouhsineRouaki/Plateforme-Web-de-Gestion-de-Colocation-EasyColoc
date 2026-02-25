<?php

namespace App\Http\Controllers;

use App\Models\Debt;

class SettlementController extends Controller
{
    public function index($colocationId)
    {
        $debts = Debt::with(['fromUser', 'toUser'])
            ->where('colocation_id', $colocationId)
            ->where('amount', '>', 0)
            ->orderByDesc('amount')
            ->get();

        return view('settlements.index', compact('debts', 'colocationId'));
    }
}