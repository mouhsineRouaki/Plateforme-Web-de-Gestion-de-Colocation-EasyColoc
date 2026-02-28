<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isGlobalAdmin = $user->role === 'GLOBAL_ADMIN';
        $activeColocations = collect();

        if ($isGlobalAdmin) {
            $activeColocations = $user->colocations()
                ->wherePivotNull('left_at')
                ->where('status', 'ACTIVE')
                ->with(['members' => function ($query) {
                    $query->wherePivotNull('left_at');
                }])
                ->orderByDesc('colocations.created_at')
                ->get();

            return view('dashboard', [
                'user' => $user,
                'activeColocation' => $activeColocations->first(),
                'activeColocations' => $activeColocations,
                'isGlobalAdmin' => true,
            ]);
        }

        $activeOwnerColocation = $user->colocations()
            ->wherePivot('role_in_colocation', 'OWNER')
            ->wherePivotNull('left_at')
            ->where('status', 'ACTIVE')
            ->first();

        if ($activeOwnerColocation) {
            return redirect()->route('owner.dashboard');
        }

        $activeMemberColocation = $user->colocations()
            ->wherePivot('role_in_colocation', 'MEMBER')
            ->wherePivotNull('left_at')
            ->where('status', 'ACTIVE')
            ->first();

        if ($activeMemberColocation) {
            return redirect()->route('member.dashboard');
        }

        return view('dashboard', [
            'user' => $user,
            'activeColocation' => null,
            'activeColocations' => collect(),
            'isGlobalAdmin' => false,
        ]);
    }
}
