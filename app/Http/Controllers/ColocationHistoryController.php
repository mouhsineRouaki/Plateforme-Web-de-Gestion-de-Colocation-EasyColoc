<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ColocationHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $ownerHistory = $user->colocations()
            ->wherePivot('role_in_colocation', 'OWNER')
            ->with(['members' => function ($query) {
                $query->wherePivotNull('left_at');
            }])
            ->orderByDesc('colocations.created_at')
            ->get();

        $memberHistory = $user->colocations()
            ->wherePivot('role_in_colocation', 'MEMBER')
            ->with(['members' => function ($query) {
                $query->wherePivotNull('left_at');
            }])
            ->orderByDesc('colocations.created_at')
            ->get();

        return view('colocations.history', [
            'ownerHistory' => $ownerHistory,
            'memberHistory' => $memberHistory,
        ]);
    }
}
