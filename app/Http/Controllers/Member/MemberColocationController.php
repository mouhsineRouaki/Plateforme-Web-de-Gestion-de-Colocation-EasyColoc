<?php

namespace App\Http\Controllers\Member;

use App\Models\Colocation;
use App\Models\Debt;
use App\Models\Payment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
}
