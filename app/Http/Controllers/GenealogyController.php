<?php

namespace App\Http\Controllers;

use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class GenealogyController extends Controller
{
    public function team()
    {
        $user = Auth::user();
    
        $nodeDataArray = [];
    
        $buildHierarchy = function ($parent, $descendants, $isRoot = false) use (&$nodeDataArray, &$buildHierarchy) {
            $descendants = $descendants ?? collect(); // Ensure it's an iterable collection
    
            foreach ($descendants as $descendant) {
                if ($descendant->can_login == true) {
                    $status = 'Y';
                }else{
                    $status = 'N';
                }
                // Check if the user's status is active
                if ($descendant->can_login == true) {
                    $nodeDataArray[] = [
                        'key' => $descendant->id,
                        'parent' => $parent->id,
                        'name' => $descendant->name . ' ('. $status . ')',
                        'image' => $descendant->getFirstMediaUrl('user_profile_images', 'thumb') ?: asset('assets/custom-images/logo-50x50.jpeg'),
                    ];
    
                    // Recursively process this descendant's children
                    $buildHierarchy($descendant, $descendant->children ?? collect());
               }
            }
        };
    
        // Add the authenticated user to the hierarchy
        $nodeDataArray[] = [
            'key' => $user->id,
            'name' => $user->name . '-'. $user->can_login ,
            'username' => $user->username,
            'image' => $user->getFirstMediaUrl('user_profile_images', 'thumb') ?: asset('assets/custom-images/logo-50x50.jpeg'),
        ];
    
        // Start building the hierarchy from the authenticated user
        $buildHierarchy($user, $user->children ?? collect(), true);
    
        return view('genealogy.team', compact('user', 'nodeDataArray'));
    }
    

    
    
    


    public function teamMembers(){ 
        $teamMembers = auth()->user()->team; 
        return view('genealogy.team-members',compact('teamMembers')); 
    }
 

    
}
