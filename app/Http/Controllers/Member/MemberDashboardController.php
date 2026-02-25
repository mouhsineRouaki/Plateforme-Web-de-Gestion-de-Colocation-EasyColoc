<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Debt;
use Illuminate\Http\Request;

class MemberDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $activeColocation = $user->colocations()
            ->wherePivot('role_in_colocation', 'MEMBER')
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

        return view('member.dashboard', [
            'user' => $user,
            'activeColocation' => $activeColocation,
            'debts' => $debts,
        ]);
    }
}
