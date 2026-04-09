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

        if (!$rootId || !($root = User::find($rootId))) {
            return back()->with('error', 'Saving tree root has not been configured.');
        }

        $nodeDataArray = [];

        $nodeDataArray[] = [
            'key'      => $root->id,
            'name'     => $root->name,
            'username' => $root->username,
            'image'    => $root->getFirstMediaUrl('user_profile_images', 'thumb') ?: asset('assets/custom-images/fav-icon.png'),
        ];

        $allSavingUsers = User::where('account_type', 'saving')->get()->keyBy('id');

        $savingIds = DB::table('referral_trees')
            ->where('ancestor_id', $rootId)
            ->where('tree_type', 'saving')
            ->pluck('descendant_id');

        foreach ($savingIds as $descendantId) {
            $descendant = $allSavingUsers->get($descendantId);
            if (!$descendant) continue;

            $parentRow = DB::table('referral_trees')
                ->where('descendant_id', $descendantId)
                ->where('tree_type', 'saving')
                ->where('level', 1)
                ->first();

            $parentId = $parentRow?->ancestor_id ?? $rootId;

            $nodeDataArray[] = [
                'key'      => $descendant->id,
                'parent'   => $parentId,
                'name'     => $descendant->name,
                'username' => $descendant->username,
                'image'    => $descendant->getFirstMediaUrl('user_profile_images', 'thumb') ?: asset('assets/custom-images/fav-icon.png'),
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
