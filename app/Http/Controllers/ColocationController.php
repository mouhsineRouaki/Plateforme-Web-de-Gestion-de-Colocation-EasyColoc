<?php

namespace App\Http\Controllers;

use App\Models\Colocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ColocationController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'string', 'max:255'], // si tu veux url/path
        ]);

        $hasActive = $user->colocations()
            ->wherePivotNull('left_at')
            ->where('status', 'ACTIVE')
            ->exists();

        if ($hasActive) {
            return back()->withErrors([
                'colocation' => "Vous avez déjà une colocation active."
            ])->withInput();
        }

        return DB::transaction(function () use ($user, $data) {
            $colocation = Colocation::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'status' => 'ACTIVE',
                'image' => $data['image'] ?? '',
                'created_by' => $user->id,
            ]);

            $colocation->members()->attach($user->id, [
                'role_in_colocation' => 'OWNER',
                'joined_at' => now(),
            ]);

            return redirect()
                ->route('dashboard')
                ->with('success', 'Colocation créée avec succès.');
        });
    }
}