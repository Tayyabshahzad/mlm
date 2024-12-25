<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GenealogyController extends Controller
{
     
    public function team(){ 
        return view('Genealogy.Team');
        //return Inertia::render('Genealogy.Team');
    }


    public function teamMembers(){ 
        $teamMembers = auth()->user()->team; 
        return view('genealogy.team-members',compact('teamMembers')); 
    }

    public function updateTeamMemberStatus(Request $request){ 
        $user = User::find($request->member_id);
        $user->can_login = true;
        $user->save();
        return redirect()->back()->with('success','Member Status has been Updated');
    }

    
}
