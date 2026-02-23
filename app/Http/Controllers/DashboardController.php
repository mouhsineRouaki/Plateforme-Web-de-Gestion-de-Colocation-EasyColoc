<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $activeColocation = $user->colocations()
            ->wherePivotNull('left_at')
            ->where('status', 'ACTIVE')
            ->with(['members' => function ($q) {
                $q->wherePivotNull('left_at');
            }])
            ->first();

        return view('dashboard', compact('user', 'activeColocation'));
    }
}