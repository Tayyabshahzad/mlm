<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Exports\TeamHierarchyExport;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{

    private function getTeamHierarchy($userId)
    {
        return \DB::table('referral_trees as rt')
            ->join('users as u', 'rt.descendant_id', '=', 'u.id')
            ->select('u.id', 'u.name', 'u.username','rt.ancestor_id', 'rt.level')
            ->where('rt.ancestor_id', $userId)
            ->orderBy('rt.level', 'asc') // Order by level for better readability
            ->get();
    }



    public function genealogyTree(Request $request){
         if($request->user){
            $user = User::find($request->user);
            $data = $this->getTeamHierarchy($user->id); 
         }else{
            $data = [];
         }
         
        $users = User::get();
        return view('reports.genealogy',compact('users','data'));
    }

    public function downloadTeamHierarchy($userId)
    {
        // Fetch the team hierarchy based on user ID
        $hierarchy = \DB::table('referral_trees as rt')
            ->join('users as u', 'rt.descendant_id', '=', 'u.id')
            ->select('u.id as UserID', 'u.name as Name', 'rt.ancestor_id as AncestorID', 'rt.level as Level')
            ->where('rt.ancestor_id', $userId)
            ->orderBy('rt.level', 'asc')
            ->get();

        // Convert the hierarchy to a Collection
        $hierarchyCollection = collect($hierarchy);

        // Pass the hierarchy collection to the export class
        return Excel::download(new TeamHierarchyExport($hierarchyCollection), 'team_hierarchy.xlsx');
    }

    private function formatHierarchyData($hierarchy)
    {
        $data = [];
        foreach ($hierarchy as $member) {
            $data[] = [ 
                'username' => $member->username,
                'Name' => $member->name, 
                'Level' => $member->level,
            ];
        }
        return $data;
    }

    public function treeView(Request $request)
    {
        $user = Auth::user();
        $nodeDataArray = [];
        $buildHierarchy = function ($parent, $descendants, $isRoot = false) use (&$nodeDataArray, &$buildHierarchy) {
            $descendants = $descendants ?? collect();
            foreach ($descendants as $descendant) { 
                    if($descendant->can_login ){
                        $label = 'Yes';
                    }else{
                        $label = 'No';
                    }
                    $nodeDataArray[] = [
                        'key' => $descendant->id,
                        'parent' => $parent->id,
                        'name' => $descendant->username . '  ('. $label .')',
                        'image' => $descendant->getFirstMediaUrl('user_profile_images', 'thumb') ?: asset('assets/custom-images/logo-50x50.jpeg'),
                    ];
                    $buildHierarchy($descendant, $descendant->children ?? collect()); 
            }
        };
        $nodeDataArray[] = [
            'key' => $user->id,
            'name' => $user->username ,
            'username' => $user->username . '--'. $user->can_login,
            'image' => $user->getFirstMediaUrl('user_profile_images', 'thumb') ?: asset('assets/custom-images/logo-50x50.jpeg'),
        ];
        $buildHierarchy($user, $user->children ?? collect(), true);
        return view('reports.treeview', compact('nodeDataArray'));
    }

}
