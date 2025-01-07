<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\FrontEndController;
use App\Http\Controllers\GenealogyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WithdrawalRequestController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\CheckUserStatus;
Route::get('/',[FrontEndController::class , 'index'])->name('index'); 
Route::get('/bulkRegisterUsers',[FrontEndController::class , 'bulkRegisterUsers'])->name('bulkRegisterUsers'); 
Route::middleware(['auth','verified',CheckUserStatus::class])->group(function () {
    Route::get('/dashboard',[FrontEndController::class , 'dashboard'])->name('dashboard');   
    Route::get('/profile/information', [FrontEndController::class, 'profile'])->name('profile.edit');
    Route::get('/account/information', [FrontEndController::class, 'accountInformation'])->name('account.information');
    Route::get('social/account/information', [FrontEndController::class, 'socialAccountInformation'])->name('social.account.information');
    Route::get('/profile/change-password', [FrontEndController::class, 'changePassword'])->name('profile.change.password'); 
    Route::get('/profile/bank-details', [FrontEndController::class, 'bankDetails'])->name('profile.bank.details'); 
    Route::put('/update-password', [FrontEndController::class, 'updatePassword'])->name('update.password'); 
    Route::put('/profile/update', [FrontEndController::class, 'updateProfile'])->name('user.profile.update');
    Route::get('/profile/agreement/request', [FrontEndController::class, 'agreementRequest'])->name('user.profile.agreement.request');
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
}); 
Route::controller(UserController::class)->middleware(['auth','verified','role:admin'],)->prefix('users')->group(function () {
    Route::get('index','index')->name('users.index'); 
    Route::put('status/update','updateStatus')->name('users.status.update');
    Route::get('details','userDetails')->name('users.details');
    Route::get('roi/payments','roiPayments')->name('roi.payments');
    Route::post('submit/roi/payments','submitRoiPayments')->name('submit.roi.payments');
}); 
Route::controller(WalletController::class)->middleware(['auth','verified'])->prefix('wallets')->group(function () {
    Route::get('online','online')->name('wallets.online');
    Route::get('direct-indirect','directIndirect')->name('wallets.direct.indirect');
    Route::get('rewards','rewards')->name('wallets.rewards');
    Route::get('return-on-investment','ROI')->name('wallets.roi');
    Route::get('profit-share','profitShare')->name('wallets.profit.share');
    Route::get('rank','rank')->name('wallets.rank');
    Route::post('transfer-to-online', [WalletController::class, 'transferToOnline'])->name('wallet.transfer.to.online');
    Route::get('show-transaction-history', [WalletController::class, 'showTransactionHistory'])->name('show.transaction.history');
}); 


Route::controller(WithdrawalRequestController::class)->middleware(['auth','verified'])->group(function () {
    Route::get('/withdrawals','index')->name('withdrawals.index');
    Route::get('/withdrawals/create','create')->name('withdrawals.create');
    Route::post('/withdrawals','store')->name('withdrawals.store'); 
    Route::post('/withdrawals/delete','delete')->name('withdrawals.delete'); 
}); 

Route::controller(WithdrawalRequestController::class)->middleware(['auth','verified','role:admin'])->group(function () {
    Route::get('/withdrawals/requests/','requests')->name('withdrawals.requests'); 
    Route::get('/withdraw-request/{id}','getWithdrawalRequest')->name('withdraw.request.details');
    Route::post('/withdraw-request/update','updateWithdrawalRequest')->name('withdraw.request.update');

}); 


Auth::routes(['verify' => true]);

require __DIR__.'/auth.php';
