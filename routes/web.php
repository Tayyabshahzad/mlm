<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\FrontEndController;
use App\Http\Controllers\GenealogyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WalletController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\CheckUserStatus;
Route::get('/',[FrontEndController::class , 'index'])->name('index'); 
Route::middleware(['auth','verified',CheckUserStatus::class])->group(function () {
    Route::get('/dashboard',[FrontEndController::class , 'dashboard'])->name('dashboard');   
    Route::get('/profile/information', [FrontEndController::class, 'profile'])->name('profile.edit');
    Route::get('/account/information', [FrontEndController::class, 'accountInformation'])->name('account.information');
    Route::get('social/account/information', [FrontEndController::class, 'socialAccountInformation'])->name('social.account.information');
    Route::get('/profile/change-password', [FrontEndController::class, 'changePassword'])->name('profile.change.password'); 
    Route::get('/profile/bank-details', [FrontEndController::class, 'bankDetails'])->name('profile.bank.details'); 
    Route::put('/update-password', [FrontEndController::class, 'updatePassword'])->name('update.password'); 
    Route::put('/profile/update', [FrontEndController::class, 'updateProfile'])->name('user.profile.update');
    Route::get('verify/phone', [FrontEndController::class, 'verifyPhone'])->name('phone.verify');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/generate/otp', [FrontEndController::class, 'generateOtp'])->name('generate.otp'); 
    Route::post('/verify/otp', [FrontEndController::class, 'verifyOtp'])->name('verify.otp'); 

});

Route::middleware(['auth','verified',CheckUserStatus::class])->group(function () {
    Route::get('/waiting-for-approval',[ApprovalController::class , 'index'])->name('approval.waiting');   
});


Route::controller(GenealogyController::class)->middleware(['auth','verified'])->prefix('genealogy')->group(function () {
    Route::get('team','team')->name('genealogy.team');
    Route::get('team/members','teamMembers')->name('genealogy.team.members');
    Route::put('team/members/status/update','updateTeamMemberStatus')->name('genealogy.team.member.status.update');
});



Route::controller(WalletController::class)->middleware(['auth','verified'])->prefix('wallets')->group(function () {
    Route::get('online','online')->name('wallets.online');
    Route::get('direct-indirect','directIndirect')->name('wallets.direct.indirect');
    Route::get('rewards','rewards')->name('wallets.rewards');
    Route::get('return-on-investment','ROI')->name('wallets.roi');
    Route::get('profit-share','profitShare')->name('wallets.profit.share');
    Route::get('rank','rank')->name('wallets.rank');
});


 
 
Auth::routes(['verify' => true]);

require __DIR__.'/auth.php';
