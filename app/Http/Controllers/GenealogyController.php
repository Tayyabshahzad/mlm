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
        $user = Auth::user();

        $nodeDataArray = [];

        $nodeDataArray[] = [
            'key'      => $user->id,
            'name'     => $user->username,
            'username' => $user->username,
            'image'    => $user->getFirstMediaUrl('user_profile_images', 'thumb') ?: asset('assets/custom-images/fav-icon.png'),
        ];

        // Always use the standard referral tree regardless of the logged-in user's account type
        $descendantIds = DB::table('referral_trees')
            ->where('ancestor_id', $user->id)
            ->where('descendant_id', '!=', $user->id)
            ->where('tree_type', 'standard')
            ->pluck('descendant_id')
            ->unique();

        $allTreeUsers = User::whereIn('id', $descendantIds)
            ->where('can_login', true)
            ->where('account_type', '!=', 'saving') // standard tree never includes saving users
            ->get()
            ->keyBy('id');

        // Fetch all direct-parent rows in one query, ordered by level so parents come before children
        $parentRows = DB::table('referral_trees')
            ->whereIn('descendant_id', $descendantIds->toArray())
            ->where('tree_type', 'standard')
            ->where('level', 1)
            ->get()
            ->keyBy('descendant_id');

        $includedKeys = [$user->id => true];

        // Sort by level ascending so parent nodes are added before their children
        $orderedDescendants = DB::table('referral_trees')
            ->where('ancestor_id', $user->id)
            ->where('descendant_id', '!=', $user->id)
            ->where('tree_type', 'standard')
            ->orderBy('level')
            ->pluck('descendant_id')
            ->unique();

        foreach ($orderedDescendants as $descendantId) {
            $descendant = $allTreeUsers->get($descendantId);
            if (!$descendant) continue;

            $parentRow = $parentRows->get($descendantId);
            $parentId  = $parentRow?->ancestor_id ?? $user->id;

            if (!isset($includedKeys[$parentId])) continue;

            $nodeDataArray[] = [
                'key'    => $descendant->id,
                'parent' => $parentId,
                'name'   => $descendant->username,
                'image'  => $descendant->getFirstMediaUrl('user_profile_images', 'thumb') ?: asset('assets/custom-images/fav-icon.png'),
            ];
            $includedKeys[$descendant->id] = true;
        }

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

    /**
     * Personal saving tree for any user who is saving_enrolled (or account_type=saving).
     * Shows the logged-in user as root with their direct/indirect saving downline.
     */
    public function mySavingTree()
    {
        $user = Auth::user();

        abort_unless($user->account_type === 'saving' || ($user->saving_enrolled && $user->saving_enrollment_activated), 403);

        $nodeDataArray = [];

        // The logged-in user is always the root of their own view
        $nodeDataArray[] = [
            'key'          => $user->id,
            'name'         => $user->name,
            'username'     => $user->username,
            'account_type' => $user->account_type,
            'image'        => $user->getFirstMediaUrl('user_profile_images', 'thumb') ?: asset('assets/custom-images/fav-icon.png'),
        ];

        // All saving-tree descendants of this user (exclude self to prevent duplicate root node)
        $descendantIds = DB::table('referral_trees')
            ->where('ancestor_id', $user->id)
            ->where('descendant_id', '!=', $user->id)
            ->where('tree_type', 'saving')
            ->pluck('descendant_id')
            ->unique();

        $allTreeUsers = User::whereIn('id', $descendantIds)
            ->where('can_login', true)
            ->where('account_type', 'saving') // saving tree only shows saving account users
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

            $parentId = $parentRow?->ancestor_id ?? $user->id;

            $nodeDataArray[] = [
                'key'          => $descendant->id,
                'parent'       => $parentId,
                'name'         => $descendant->name,
                'username'     => $descendant->username,
                'account_type' => $descendant->account_type,
                'image'        => $descendant->getFirstMediaUrl('user_profile_images', 'thumb') ?: asset('assets/custom-images/fav-icon.png'),
            ];
        }

        $root = $user;
        return view('genealogy.saving-tree', compact('root', 'nodeDataArray'));
    }

    /**
     * Admin: view any user's personal saving subtree.
     */
    public function adminUserSavingTree(User $user)
    {
        $nodeDataArray = [];

        $nodeDataArray[] = [
            'key'          => $user->id,
            'name'         => $user->name,
            'username'     => $user->username,
            'account_type' => $user->account_type,
            'image'        => $user->getFirstMediaUrl('user_profile_images', 'thumb') ?: asset('assets/custom-images/fav-icon.png'),
        ];

        $descendantIds = DB::table('referral_trees')
            ->where('ancestor_id', $user->id)
            ->where('tree_type', 'saving')
            ->pluck('descendant_id')
            ->unique();

        $allTreeUsers = User::whereIn('id', $descendantIds)->get()->keyBy('id');

        foreach ($descendantIds as $descendantId) {
            $descendant = $allTreeUsers->get($descendantId);
            if (!$descendant) continue;

            $parentRow = DB::table('referral_trees')
                ->where('descendant_id', $descendantId)
                ->where('tree_type', 'saving')
                ->where('level', 1)
                ->first();

            $parentId = $parentRow?->ancestor_id ?? $user->id;

            $nodeDataArray[] = [
                'key'          => $descendant->id,
                'parent'       => $parentId,
                'name'         => $descendant->name,
                'username'     => $descendant->username,
                'account_type' => $descendant->account_type,
                'image'        => $descendant->getFirstMediaUrl('user_profile_images', 'thumb') ?: asset('assets/custom-images/fav-icon.png'),
            ];
        }

        $root = $user;
        return view('genealogy.saving-tree', compact('root', 'nodeDataArray'));
    }

    public function teamMembers()
    {
        $user = Auth::user();

        // Always show standard tree members — saving members are in the saving tree
        $descendantIds = DB::table('referral_trees')
            ->where('ancestor_id', $user->id)
            ->where('tree_type', 'standard')
            ->where('level', 1)
            ->pluck('descendant_id');

        $teamMembers = User::whereIn('id', $descendantIds)
            ->where('account_type', '!=', 'saving')
            ->get();

        return view('genealogy.team-members', compact('teamMembers'));
    }
}
