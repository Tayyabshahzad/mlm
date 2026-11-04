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

        $nodeDataArray = [$this->orgNode($user, null)];

        // Collect descendants from BOTH trees so saving-account referrals are visible
        $descendantIds = DB::table('referral_trees')
            ->where('ancestor_id', $user->id)
            ->where('descendant_id', '!=', $user->id)
            ->whereIn('tree_type', ['standard', 'saving'])
            ->pluck('descendant_id')
            ->unique();

        $allTreeUsers = User::with('media')->whereIn('id', $descendantIds)
            ->where('can_login', true)
            ->get()
            ->keyBy('id');

        // Use sponsor_id as the direct parent (correct regardless of tree type).
        // Building a children-map and doing BFS ensures every parent is added
        // before its children, preventing the cascade-skip problem.
        $childrenMap = [];
        foreach ($allTreeUsers as $u) {
            $childrenMap[$u->sponsor_id][] = $u->id;
        }

        $includedKeys = [$user->id => true];
        $queue = [$user->id];

        while (!empty($queue)) {
            $parentId = array_shift($queue);

            foreach ($childrenMap[$parentId] ?? [] as $childId) {
                $child = $allTreeUsers->get($childId);
                if (!$child || isset($includedKeys[$childId])) continue;

                $nodeDataArray[] = $this->orgNode($child, $parentId);
                $includedKeys[$childId] = true;
                $queue[] = $childId;
            }
        }

        return view('genealogy.team', compact('user', 'nodeDataArray'));
    }

    /** One card on the My Team org chart */
    private function orgNode(User $u, ?int $parentId): array
    {
        $initials = collect(preg_split('/\s+/', trim($u->name ?: $u->username)))
            ->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');

        return [
            'key'      => $u->id,
            'parent'   => $parentId,
            'name'     => $u->name ?: $u->username,
            'username' => $u->username,
            'initials' => $initials ?: '?',
            'image'    => $u->getFirstMediaUrl('user_profile_images', 'thumb') ?: null,
            'plan'     => $this->planLabel($u),
            'status'   => $u->blocked ? 'blocked' : ((float) $u->roi_eligible_investment_amount > 0 || (float) $u->saving_total_deposited > 0 ? 'active' : 'inactive'),
        ];
    }

    private function planLabel(User $u): string
    {
        if ($u->account_type === 'saving') return 'Saving';

        return ($u->user_plan === 'vip' ? 'VIP' : 'Standard') . ($u->saving_enrolled ? ' + Saving' : '');
    }

    /**
     * Details for the side panel when a member card is clicked (JSON).
     * Members can open themselves and their own downline; admins can open anyone.
     */
    public function teamMemberDetails(User $member)
    {
        $viewer  = Auth::user();
        $isAdmin = $viewer->hasRole('admin') || $viewer->hasRole('super-admin');

        $saving  = request('tree') === 'saving';

        $level = $member->id === $viewer->id ? 0 : DB::table('referral_trees')
            ->where('ancestor_id', $viewer->id)->where('descendant_id', $member->id)
            ->when($saving, fn ($q) => $q->where('tree_type', 'saving'))
            ->min('level');
        abort_if(!$isAdmin && $level === null, 403);

        // active members per level (standard tree, holding an investment) — same rule as profit share
        $required = \App\Services\ProfitShareAuditService::REQ;
        $perLevel = DB::table('referral_trees')
            ->join('users', 'users.id', '=', 'referral_trees.descendant_id')
            ->where('referral_trees.ancestor_id', $member->id)
            ->where('referral_trees.tree_type', 'standard')
            ->where('referral_trees.level', '<=', 7)
            ->whereNull('users.deleted_at')
            ->where('users.roi_eligible_investment_amount', '>', 0)
            ->where(fn ($q) => $q->where('users.account_type', 'standard_investment')->orWhereNull('users.account_type'))
            ->groupBy('referral_trees.level')
            ->selectRaw('referral_trees.level, COUNT(DISTINCT referral_trees.descendant_id) n')
            ->pluck('n', 'level');

        // saving team per level (members who can log in)
        $savingPerLevel = $saving ? DB::table('referral_trees')
            ->join('users', 'users.id', '=', 'referral_trees.descendant_id')
            ->where('referral_trees.ancestor_id', $member->id)
            ->where('referral_trees.tree_type', 'saving')
            ->where('referral_trees.level', '<=', 7)
            ->where('users.can_login', true)
            ->groupBy('referral_trees.level')
            ->selectRaw('referral_trees.level, COUNT(DISTINCT referral_trees.descendant_id) n')
            ->pluck('n', 'level') : collect();

        $sum = fn (string $type, ?\Closure $extra = null) => round((float) DB::table('wallets')
            ->where('user_id', $member->id)->where('wallet_type', $type)
            ->when($extra, $extra)->sum('total_amount'), 2);

        $notSaving = fn ($q) => $q->where(fn ($i) => $i->whereNull('source_type')->orWhere('source_type', '!=', 'saving_instalment'));

        return response()->json([
            'basic' => [
                'name'     => $member->name,
                'username' => $member->username,
                'initials' => $this->orgNode($member, null)['initials'],
                'image'    => $member->getFirstMediaUrl('user_profile_images', 'thumb') ?: null,
                'joined'   => optional($member->created_at)->format('d M Y'),
                'sponsor'  => optional(User::withTrashed()->find($member->sponsor_id))->username,
                'plan'     => $this->planLabel($member),
                'status'   => $member->blocked ? 'Blocked' : ($member->can_login ? 'Active' : 'Inactive'),
                'roi'      => $member->roi_status ? ucfirst($member->roi_status) : null,
                'level'    => $level,
            ],
            'investment' => [
                'standard' => round((float) $member->roi_eligible_investment_amount, 2),
                'saving'   => round((float) $member->saving_total_deposited, 2),
            ],
            'team' => [
                'direct' => User::where('sponsor_id', $member->id)->where('can_login', true)->count(),
                'total'  => DB::table('referral_trees')->join('users', 'users.id', '=', 'referral_trees.descendant_id')
                    ->where('referral_trees.ancestor_id', $member->id)->where('users.can_login', true)
                    ->distinct()->count('referral_trees.descendant_id'),
                'tree'   => $saving ? 'saving' : 'standard',
                'levels' => $saving
                    ? collect(range(1, 7))->map(fn ($L) => ['level' => $L, 'have' => (int) ($savingPerLevel[$L] ?? 0), 'need' => null])
                    : collect($required)->map(fn ($need, $L) => ['level' => $L, 'have' => (int) ($perLevel[$L] ?? 0), 'need' => $need])->values(),
            ],
            'earnings' => [
                'ROI'                      => $sum('roi'),
                'Direct / Indirect'        => $sum('direct_indirect', $notSaving),
                'Profit Share'             => round($sum('profit_share') + $sum('profit_share_reversal'), 2),
                'Rewards'                  => $sum('reward'),
                'Saving ROI'               => $sum('saving_roi'),
                'Saving Commission'        => $sum('direct_indirect', fn ($q) => $q->where('source_type', 'saving_instalment')),
            ],
            'contact' => $isAdmin ? ['email' => $member->email, 'phone' => $member->phone_number] : null,
        ]);
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

        // The logged-in user is always the root of their own view
        $nodeDataArray = [$this->orgNode($user, null)];

        // All saving-tree descendants of this user (exclude self to prevent duplicate root node)
        $descendantIds = DB::table('referral_trees')
            ->where('ancestor_id', $user->id)
            ->where('descendant_id', '!=', $user->id)
            ->where('tree_type', 'saving')
            ->pluck('descendant_id')
            ->unique();

        $allTreeUsers = User::with('media')
            ->whereIn('id', $descendantIds)
            ->where('can_login', true)
            ->get()
            ->keyBy('id');

        // direct saving parent of every descendant, in one query
        $parentOf = DB::table('referral_trees')
            ->whereIn('descendant_id', $descendantIds)
            ->where('tree_type', 'saving')
            ->where('level', 1)
            ->pluck('ancestor_id', 'descendant_id');

        // parents before children, so every card hangs under a card that is shown
        $childrenMap = [];
        foreach ($allTreeUsers as $u) {
            $childrenMap[$parentOf[$u->id] ?? $user->id][] = $u->id;
        }
        $queue = [$user->id];
        $seen  = [$user->id => true];
        while ($queue) {
            $parentId = array_shift($queue);
            foreach ($childrenMap[$parentId] ?? [] as $childId) {
                if (isset($seen[$childId])) continue;
                $nodeDataArray[] = $this->orgNode($allTreeUsers[$childId], $parentId);
                $seen[$childId] = true;
                $queue[] = $childId;
            }
        }

        return view('genealogy.my-saving-team', compact('user', 'nodeDataArray'));
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

        // Include directs from both trees so saving-account referrals are visible
        $descendantIds = DB::table('referral_trees')
            ->where('ancestor_id', $user->id)
            ->whereIn('tree_type', ['standard', 'saving'])
            ->where('level', 1)
            ->pluck('descendant_id')
            ->unique();

        $teamMembers = User::whereIn('id', $descendantIds)->get();

        return view('genealogy.team-members', compact('teamMembers'));
    }
}
