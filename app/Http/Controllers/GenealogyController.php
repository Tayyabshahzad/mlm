<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GenealogyController extends Controller
{
    public function team()
    {
        $user      = Auth::user();
        $treeType  = $user->account_type === 'saving' ? 'saving' : 'standard';

        $nodeDataArray = [];

        // Add the authenticated user as root
        $nodeDataArray[] = [
            'key'      => $user->id,
            'name'     => $user->username,
            'username' => $user->username,
            'image'    => $user->getFirstMediaUrl('user_profile_images', 'thumb') ?: asset('assets/custom-images/fav-icon.png'),
        ];

        // Build hierarchy using the correct tree
        $buildHierarchy = function ($parent, $descendants) use (&$nodeDataArray, &$buildHierarchy, $treeType) {
            $descendants = $descendants ?? collect();
            foreach ($descendants as $descendant) {
                // Only include active users who belong to the same tree type
                if ($descendant->can_login && $descendant->account_type === ($treeType === 'saving' ? 'saving' : 'standard_investment')) {
                    $nodeDataArray[] = [
                        'key'    => $descendant->id,
                        'parent' => $parent->id,
                        'name'   => $descendant->username,
                        'image'  => $descendant->getFirstMediaUrl('user_profile_images', 'thumb') ?: asset('assets/custom-images/fav-icon.png'),
                    ];
                    $buildHierarchy($descendant, $descendant->children ?? collect());
                }
            }
        };

        $buildHierarchy($user, $user->children ?? collect());

        return view('genealogy.team', compact('user', 'nodeDataArray'));
    }

    public function savingTree()
    {
        $setting = Setting::first();
        $rootId  = $setting?->saving_parent_user_id;

        // Fall back to the logged-in user if no root is configured (admin viewing their own tree)
        if (!$rootId || !($root = User::find($rootId))) {
            $root   = Auth::user();
            $rootId = $root->id;

            // Auto-save so future visits don't fall back again
            if ($setting) {
                $setting->update(['saving_parent_user_id' => $rootId]);
            }
        }

        $nodeDataArray = [];

        $nodeDataArray[] = [
            'key'          => $root->id,
            'name'         => $root->name,
            'username'     => $root->username,
            'account_type' => $root->account_type,
            'image'        => $root->getFirstMediaUrl('user_profile_images', 'thumb') ?: asset('assets/custom-images/fav-icon.png'),
        ];

        // Get all unique descendant IDs under the root in the saving tree
        $descendantIds = DB::table('referral_trees')
            ->where('ancestor_id', $rootId)
            ->where('tree_type', 'saving')
            ->pluck('descendant_id')
            ->unique();

        // Only show admin-activated users (can_login = true) in the tree.
        // Standard sponsors (e.g. the root itself) are always included via the root node above.
        $allTreeUsers = User::whereIn('id', $descendantIds)
            ->where('can_login', true)
            ->get()
            ->keyBy('id');

        foreach ($descendantIds as $descendantId) {
            $descendant = $allTreeUsers->get($descendantId);
            if (!$descendant) continue;

            $parentRow = DB::table('referral_trees')
                ->where('descendant_id', $descendantId)
                ->where('tree_type', 'saving')
                ->where('level', 1)
                ->first();

            $parentId = $parentRow?->ancestor_id ?? $rootId;

            $nodeDataArray[] = [
                'key'          => $descendant->id,
                'parent'       => $parentId,
                'name'         => $descendant->name,
                'username'     => $descendant->username,
                'account_type' => $descendant->account_type,
                'image'        => $descendant->getFirstMediaUrl('user_profile_images', 'thumb') ?: asset('assets/custom-images/fav-icon.png'),
            ];
        }

        return view('genealogy.saving-tree', compact('root', 'nodeDataArray'));
    }

    public function teamMembers()
    {
        $user     = Auth::user();
        $treeType = $user->account_type === 'saving' ? 'saving' : 'standard';

        // Get descendants from the correct tree
        $descendantIds = DB::table('referral_trees')
            ->where('ancestor_id', $user->id)
            ->where('tree_type', $treeType)
            ->where('level', 1)
            ->pluck('descendant_id');

        $teamMembers = User::whereIn('id', $descendantIds)->get();

        return view('genealogy.team-members', compact('teamMembers'));
    }
}
