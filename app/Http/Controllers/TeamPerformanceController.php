<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeamPerformanceController extends Controller
{
    public function index(Request $request)
    {
        $authUser    = Auth::user();
        $isSuperAdmin = $authUser->hasRole('super-admin') || $authUser->hasRole('admin');

        // Super admin / admin can pick any user; others always see their own
        $targetUser = $authUser;
        if ($isSuperAdmin && $request->filled('user_id')) {
            $targetUser = User::find($request->user_id) ?? $authUser;
        }

        $users       = collect();
        $levelCounts = collect();
        $totalCount  = 0;

        if ($request->filled('from') && $request->filled('to') && $request->filled('levels')) {
            $from   = \Carbon\Carbon::parse($request->from)->startOfDay();
            $to     = \Carbon\Carbon::parse($request->to)->endOfDay();
            $levels = array_map('intval', (array) $request->levels);

            $users = DB::table('referral_trees')
                ->join('users', 'referral_trees.descendant_id', '=', 'users.id')
                ->leftJoin('users as sponsors', 'users.sponsor_id', '=', 'sponsors.id')
                ->where('referral_trees.ancestor_id', $targetUser->id)
                ->where('referral_trees.tree_type', 'standard')
                ->where('users.can_login', true)
                ->whereIn('referral_trees.level', $levels)
                ->whereBetween('users.created_at', [$from, $to])
                ->select(
                    'users.id',
                    'users.name',
                    'users.username',
                    'users.created_at',
                    'referral_trees.level',
                    'sponsors.name as sponsor_name',
                    'sponsors.username as sponsor_username'
                )
                ->orderBy('referral_trees.level')
                ->orderBy('users.created_at', 'desc')
                ->get();

            $levelCounts = $users->groupBy('level')->map->count()->sortKeys();
            $totalCount  = $users->count();
        }

        // For admin/super-admin: all users (any account type)
        $allUsers = $isSuperAdmin
            ? User::select('id', 'name', 'username')->orderBy('name')->get()
            : collect();

        return view('performance.index', compact(
            'targetUser', 'isSuperAdmin', 'allUsers',
            'users', 'levelCounts', 'totalCount'
        ));
    }
}
