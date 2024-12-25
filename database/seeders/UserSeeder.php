<?php

namespace Database\Seeders;

use App\Models\ReferralLink;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;
use Hash;
use Illuminate\Support\Facades\Hash as FacadesHash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Admin User
         $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'username' =>'admin_11',
            'is_active' => true,
            'can_login' => true,
            'phone_verified' => true,
            'password' => FacadesHash::make('Google@123'), // Set a secure password
        ]);

        ReferralLink::create([
            'user_id' => $adminUser->id,
            'link' => $this->generateReferralCode()
        ]); 


        $adminUser->assignRole('admin');

        // Member User
        $editorUser = User::create([
            'name' => 'Member User',
            'email' => 'member@gmail.com',
            'username' =>'member_11',
            'is_active' => true,
            'phone_verified' => true,
            'password' => FacadesHash::make('Google@123'), // Set a secure password
        ]);
        $editorUser->assignRole('member');
        ReferralLink::create([
            'user_id' => $editorUser->id,
            'link' => $this->generateReferralCode()
        ]); 

         // Operator User
         $operator = User::create([
            'name' => 'Operator User',
            'email' => 'operatot@gmail.com',
            'username' =>'operator_11',
            'is_active' => true,
            'phone_verified' => true,
            'password' => FacadesHash::make('Google@123'), // Set a secure password
        ]);
        ReferralLink::create([
            'user_id' => $operator->id,
            'link' => $this->generateReferralCode()
        ]); 
        $operator->assignRole('operator');
    }

    protected function generateReferralCode()
    {
        // Generate a code with mix of letters and numbers
        $letters = Str::random(5); // 5 random letters
        $numbers = rand(10000, 99999); // 5 random numbers
        $code = strtoupper($letters . $numbers); // Combine and make uppercase
        
        // Check if code already exists and regenerate if needed
        while (ReferralLink::where('link', $code)->exists()) {
            $letters = Str::random(5);
            $numbers = rand(10000, 99999);
            $code = strtoupper($letters . $numbers);
        }
        
        return $code;
    }
}
