<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Colocation;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $usersCount = User::count();
        $bannedCount = User::where('is_banned', true)->count();

        $colocationsCount = Colocation::count();
        $activeColocationsCount = Colocation::where('status', 'ACTIVE')->count();
        $cancelledColocationsCount = Colocation::where('status', 'CANCELLED')->count();

        $expensesCount = Expense::count();
        $totalExpenses = Expense::sum('amount');

        $topSpenders = Expense::select('payer_id', DB::raw('SUM(amount) as total'))
            ->groupBy('payer_id')
            ->orderByDesc('total')
            ->with('payer:id,name')
            ->limit(5)
            ->get();

        $recentUsers = User::latest()->limit(6)->get(['id', 'name', 'email', 'role', 'is_banned', 'created_at']);

        return view('admin.dashboard', compact(
            'usersCount',
            'bannedCount',
            'colocationsCount',
            'activeColocationsCount',
            'cancelledColocationsCount',
            'expensesCount',
            'totalExpenses',
            'topSpenders',
            'recentUsers',
        ));
    }
}
