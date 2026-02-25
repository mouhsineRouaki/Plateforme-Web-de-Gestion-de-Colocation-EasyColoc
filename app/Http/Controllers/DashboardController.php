<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'GLOBAL_ADMIN') {
            return redirect()->route('admin.dashboard');
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
        ]);
    }
}
