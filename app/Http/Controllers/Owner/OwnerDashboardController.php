<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Debt;
use Illuminate\Http\Request;

class OwnerDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $activeColocation = $user->colocations()
            ->wherePivot('role_in_colocation', 'OWNER')
            ->wherePivotNull('left_at')
            ->where('status', 'ACTIVE')
            ->with(['members' => function ($query) {
                $query->wherePivotNull('left_at');
            }])
            ->orderByDesc('colocations.created_at')
            ->first();

        $debts = collect();
        if ($activeColocation) {
            $debts = Debt::with(['fromUser', 'toUser'])
                ->where('colocation_id', $activeColocation->id)
                ->where('amount', '>', 0)
                ->orderByDesc('amount')
                ->get();
        }

        return view('owner.dashboard', [
            'user' => $user,
            'activeColocation' => $activeColocation,
            'debts' => $debts,
        ]);
    }
}
