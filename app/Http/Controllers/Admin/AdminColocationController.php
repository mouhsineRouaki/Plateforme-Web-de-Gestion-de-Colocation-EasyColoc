<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminColocationController extends Controller
{
    public function index(Request $request)
    {
        $admin = $request->user();

        $colocations = $admin->colocations()
            ->wherePivotNull('left_at')
            ->where('status', 'ACTIVE')
            ->with(['members' => function ($query) {
                $query->wherePivotNull('left_at');
            }])
            ->orderByDesc('colocations.created_at')
            ->get();

        return view('admin.colocations.index', [
            'colocations' => $colocations,
        ]);
    }
}
