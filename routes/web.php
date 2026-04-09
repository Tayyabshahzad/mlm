<?php

use App\Http\Controllers\{
    ActivationCodeController,
    ApprovalController,
    Auth\ForgotPasswordController,
    Auth\ResetPasswordController,
    BinarySystemController,
    BlockedUserController,
    CommissionController,
    FrontEndController,
    GenealogyController,
    ProductController,
    ProfileController,
    ReportController,
    SavingInstalmentController,
    ScheduleRoiController,
    SettingController,
    TopupController,
    UserController,
    WalletController,
    WithdrawalRequestController,
    ROIMonitoringController,
    ROISubmissionMonitoringController
}; 
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\{Route, Auth, Artisan};
use App\Http\Middleware\{CheckUserStatus, CheckBlockedUser};

// Public Routes
// Look Busy Do noting new ipdate/////
Route::get('log-viewer', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index')->middleware('auth');

Route::get('/', [FrontEndController::class, 'index'])->name('index');
Route::get('api-test', [FrontEndController::class, 'apiTest'])->name('api-test');
Route::get('api-test-get', [FrontEndController::class, 'apiTestGet'])->name('api-test-get');


Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
Route::get('/bulkRegisterUsers', [FrontEndController::class, 'bulkRegisterUsers'])->name('bulkRegisterUsers');
Route::get('/update/all', [FrontEndController::class, 'processUsersUsdtInvestment'])->name('processUsersUsdtInvestment');
Route::get('/update/50', [FrontEndController::class, 'processUsersUsdtInvestment50User'])->name('processUsersUsdtInvestment50User');
// Authenticated and Verified Routes
Route::middleware(['auth', 'verified', CheckUserStatus::class])->group(function () {
    // Dashboard and Product Routes
    Route::get('/dashboard', [FrontEndController::class, 'dashboard'])->name('dashboard');
    Route::get('/products', [FrontEndController::class, 'buyProduct'])->name('buy.products');
    Route::get('/waiting-for-approval', [ApprovalController::class, 'index'])->name('approval.waiting');
    Route::get('/activation-code', [ActivationCodeController::class, 'index'])->name('activation.code');
    Route::post('/generate-code', [ActivationCodeController::class, 'generateCode'])->middleware('auth');
    Route::delete('/delete-code/{id}', [ActivationCodeController::class, 'destroy']);
    Route::prefix('profile')->group(function () {
        Route::get('/info', [FrontEndController::class, 'profile'])->name('profile.edit');
        Route::get('/account', [FrontEndController::class, 'accountInformation'])->name('account.information');
        Route::get('/social', [FrontEndController::class, 'socialAccountInformation'])->name('social.account.information');
        Route::get('/change-password', [FrontEndController::class, 'changePassword'])->name('profile.change.password');
        Route::get('/bank-details', [FrontEndController::class, 'bankDetails'])->name('profile.bank.details');
        Route::put('/update-password/{id?}', [FrontEndController::class, 'updatePassword'])->name('update.password');
        Route::put('/profile/update', [FrontEndController::class, 'updateProfile'])->name('user.profile.update');
        Route::get('/agreement/request', [FrontEndController::class, 'agreementRequest'])->name('user.profile.agreement.request');
        Route::get('/verify/phone', [FrontEndController::class, 'verifyPhone'])->name('phone.verify');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::get('/generate/otp', [FrontEndController::class, 'generateOtp'])->name('generate.otp');
        Route::post('/verify/otp', [FrontEndController::class, 'verifyOtp'])->name('verify.otp');
    });

    Route::prefix('genealogy')->controller(GenealogyController::class)->group(function () {
        Route::get('team', 'team')->name('genealogy.team');
        Route::get('team/members', 'teamMembers')->name('genealogy.team.members');
        Route::get('saving-tree', 'savingTree')->name('genealogy.saving.tree');
    });

    Route::prefix('wallets')->controller(WalletController::class)->group(function () {
        Route::get('online', 'online')->name('wallets.online');
        Route::get('investment', 'investment')->name('wallets.investment');
        Route::get('direct-indirect', 'directIndirect')->name('wallets.direct.indirect');
        Route::get('rewards', 'rewards')->name('wallets.rewards');
        Route::get('return-on-investment', 'ROI')->name('wallets.roi');
        Route::get('profit-share', 'profitShare')->name('wallets.profit.share');
        Route::get('rank', 'rank')->name('wallets.rank');
        Route::get('incentive-wallets', 'incentiveWallets')->name('wallets.incentive');
        Route::get('saving-account', 'savingAccount')->name('wallets.saving.account');
        Route::post('transfer-to-online', 'transferToOnline')->name('wallet.transfer.to.online');
        Route::get('show-transaction-history', 'showTransactionHistory')->name('show.transaction.history');
        Route::post('clear-negative-points', 'clearNegativePoints')->name('clear.negative.points');
    });

    Route::controller(WithdrawalRequestController::class)->group(function () {
        Route::get('/withdrawals', 'index')->name('withdrawals.index');
        Route::get('/withdrawals/create', 'create')->name('withdrawals.create');
        Route::post('/withdrawals', 'store')->name('withdrawals.store');
        Route::post('/withdrawals/member', 'memberTransfer')->name('withdrawals.member.transfer');
        Route::post('/withdrawals/delete', 'delete')->name('withdrawals.delete');
    });

    Route::get('recalculateCommissions', [UserController::class, 'recalculateCommissions'])->name('recalculateCommissions');

    // Saving Account — user-facing
    Route::prefix('saving')->controller(SavingInstalmentController::class)->group(function () {
        Route::get('instalments', 'userIndex')->name('saving.user.instalments');
        Route::post('instalments/submit', 'userSubmit')->name('saving.user.submit');
    });
});

// Admin Routes
Route::middleware(['auth', 'verified', 'role:admin'])->group(function () { 
    Route::prefix('users')->controller(UserController::class)->group(function () {
        Route::get('index', 'index')->name('users.index');
        Route::put('status/update', 'updateStatus')->name('users.status.update');
        Route::get('details', 'userDetails')->name('users.details');
        Route::get('roi/payments', 'roiPayments')->name('roi.payments');
        Route::post('submit/roi/payments', 'submitRoiPayments')->name('submit.roi.payments');
        Route::get('info/{id}', 'userInfo')->name('user.info');
        Route::put('info/{user}/update', 'userInfoUpdate')->name('user.info.update');
        Route::get('team/{id}', 'adminUserTeam')->name('admin.user.team');
        Route::post('user/delete', 'userDelete')->name('user.delete');
        Route::get('deleted/user', 'deletedUser')->name('deleted.users');
        Route::get('activation-code','activationCode')->name('user.activation.code');
        Route::post('/admin/activation-code/update-status','updateActivationCode')->name('admin.activation-code.update-status');
        Route::get('download/contacts', 'downloadContacts')->name('download.contacts');
        Route::get('incentive-wallet', 'incentiveWallet')->name('admin.incentive.wallet');
        Route::post('incentive-wallet/send', 'sendIncentive')->name('admin.incentive.send');
        Route::get('change-parent', 'changeParent')->name('admin.change.parent');
        Route::post('change-parent/process', 'processChangeParent')->name('admin.change.parent.process');
      // Route::post('admin/topup','storeTopup')->name('user.topup.store');

    });

    Route::prefix('rental')->controller(UserController::class)->group(function () {
        Route::get('percentage', 'rentalPercentage')->name('rental.percentage');
        Route::post('add/percentage', 'addRentalPercentage')->name('add.rental.percentage');
        Route::put('percentage/{id}/update', 'updateRentalPercentage')->name('rental.percentage.update');
        Route::delete('percentage/{id}/delete', 'deleteRentalPercentage')->name('rental.percentage.delete');
    });
    Route::prefix('product')->controller(ProductController::class)->group(function () {
        Route::get('index', 'index')->name('product.index');
        Route::get('create', 'create')->name('product.create');
        Route::post('store', 'store')->name('product.store');
        Route::post('purchase', 'purchase')->name('product.purchase');
        Route::get('update/{id}', 'update')->name('product.update.view');
        Route::post('update/{id}', 'updateProcess')->name('product.update');
        Route::delete('delete', 'delete')->name('product.delete');
    });
    Route::prefix('report')->controller(ReportController::class)->group(function () {
        Route::get('genealogy/{id?}', 'genealogyTree')->name('report.genealogy.tree');
        Route::get('genealogy/download/{id}', 'downloadTeamHierarchy')->name('report.genealogy.tree.download');
        Route::get('genealogy/tree', 'treeView')->name('report.genealogy.tree.view');
    }); 
    Route::controller(ScheduleRoiController::class)->group(function () {
        Route::get('run-schedule-manually', 'schedule')->name('run.schedule.manually');
    });
    Route::controller(WithdrawalRequestController::class)->group(function () {
        Route::get('/withdrawals/requests', 'requests')->name('withdrawals.requests');
        Route::get('/withdrawals/analytics', 'analytics')->name('withdrawals.analytics');
        Route::get('/withdrawals/export', 'exportWithdrawals')->name('withdrawals.export');
        Route::get('/withdraw-request/{id}', 'getWithdrawalRequest')->name('withdraw.request.details');
        Route::post('/withdraw-request/update', 'updateWithdrawalRequest')->name('withdraw.request.update');
    });

    Route::prefix('setting')->controller(SettingController::class)->group(function () {
        Route::get('/basic', 'index')->name('setting.basic');
        Route::post('/update', 'update')->name('setting.update');
        Route::get('update.usdt', 'updateUSDT')->name('rate.manual.update');
        Route::get('/saving-account', 'savingSettings')->name('setting.saving');
        Route::post('/saving-account/update', 'updateSavingSettings')->name('setting.saving.update');
    });

    Route::controller(ROIMonitoringController::class)->group(function () {
        Route::get('/roi-monitoring', 'index')->name('users.roi.monitoring');
        Route::post('/users/roi/stop/{user}', 'stopAccount')->name('users.roi.stop');
        Route::post('/users/roi/reactivate/{user}', 'reactivateAccount')->name('users.roi.reactivate');
    });

    // ROI Submission Monitoring Routes
    Route::controller(ROISubmissionMonitoringController::class)->group(function () {
        Route::get('/roi-submission-monitoring', 'index')->name('roi.submission.monitoring');
        Route::post('/roi-submission/generate/{user}', 'generateManualROI')->name('roi.submission.generate');
        Route::post('/roi-submission/bulk-generate', 'bulkGenerateROI')->name('roi.submission.bulk.generate');
    });

    // Pending Rewards Management Routes
    Route::prefix('pending-rewards')->controller(App\Http\Controllers\Admin\PendingRewardController::class)->group(function () {
        Route::get('/', 'index')->name('admin.pending-rewards.index');
        Route::get('/{pendingReward}', 'show')->name('admin.pending-rewards.show');
        Route::post('/{pendingReward}/approve', 'approve')->name('admin.pending-rewards.approve');
        Route::post('/{pendingReward}/deny', 'deny')->name('admin.pending-rewards.deny');
        Route::post('/bulk-approve', 'bulkApprove')->name('admin.pending-rewards.bulk-approve');
        Route::get('/{pendingReward}/reverify', 'reverify')->name('admin.pending-rewards.reverify');
    });

    // Reward Settings Management Routes
    Route::prefix('reward-settings')->controller(App\Http\Controllers\Admin\RewardSettingsController::class)->group(function () {
        Route::get('/', 'index')->name('admin.reward-settings.index');
        Route::get('/create', 'create')->name('admin.reward-settings.create');
        Route::post('/', 'store')->name('admin.reward-settings.store');
        Route::get('/{rewardSetting}/edit', 'edit')->name('admin.reward-settings.edit');
        Route::put('/{rewardSetting}', 'update')->name('admin.reward-settings.update');
        Route::delete('/{rewardSetting}', 'destroy')->name('admin.reward-settings.destroy');
        Route::get('/{rewardSetting}/toggle', 'toggleStatus')->name('admin.reward-settings.toggle');
        Route::get('/reset-defaults', 'resetToDefaults')->name('admin.reward-settings.reset');
        Route::get('/export', 'export')->name('admin.reward-settings.export');
    });

    // Temporary Reward Review Routes (for identifying incorrect reward assignments)
    Route::prefix('reward-review')->controller(App\Http\Controllers\Admin\RewardReviewController::class)->group(function () {
        Route::get('/', 'index')->name('admin.reward-review.index');
        Route::get('/{user}', 'show')->name('admin.reward-review.show');
        Route::post('/reverse', 'reverseReward')->name('admin.reward-review.reverse');
        Route::post('/assign', 'assignReward')->name('admin.reward-review.assign');
        Route::post('/record-only', 'recordRewardOnly')->name('admin.reward-review.record-only');
        Route::get('/export/csv', 'export')->name('admin.reward-review.export');
    });

    // Admin User Impersonation Routes
    Route::prefix('impersonation')->controller(\App\Http\Controllers\Admin\ImpersonationController::class)->group(function () {
        Route::get('/', 'index')->name('admin.impersonation.index');
        Route::post('/start/{user}', 'impersonate')->name('admin.impersonation.start');
        Route::post('/stop', 'stopImpersonation')->name('admin.impersonation.stop');
    });

    // Binary System Management Routes (2x/7x)
    Route::prefix('binary-system')->controller(BinarySystemController::class)->group(function () {
        Route::get('/', 'index')->name('binary-system.index');
        Route::post('/initialize', 'initializeSystem')->name('binary-system.initialize');
        Route::post('/progress-level', 'progressLevel')->name('binary-system.progress');
        Route::post('/toggle-auto-progress', 'toggleAutoProgress')->name('binary-system.toggle-auto');
        Route::get('/history/{systemId}', 'getSystemHistory')->name('binary-system.history');
        Route::post('/upgrade-rank', 'upgradeRank')->name('binary-system.upgrade-rank');
        Route::post('/fix-online-income', 'fixOnlineIncome')->name('binary-system.fix-online-income');

        // Admin routes for binary systems
        Route::prefix('admin')->group(function () {
            Route::get('/', 'adminIndex')->name('admin.binary-system.index');
            Route::get('/{system}', 'adminShow')->name('admin.binary-system.show');
            Route::post('/process-earnings', 'adminProcessEarnings')->name('admin.binary-system.process-earnings');
        });
    });

    // ROI Settings Management Routes (VIP/Standard Plans)
    Route::prefix('roi-settings')->controller(App\Http\Controllers\Admin\ROISettingsController::class)->group(function () {
        Route::get('/', 'index')->name('admin.roi-settings.index');
        Route::post('/update', 'update')->name('admin.roi-settings.update');
        Route::get('/user-plans', 'userPlans')->name('admin.roi-settings.user-plans');
        Route::post('/user-plans/update', 'updateUserPlan')->name('admin.roi-settings.update-user-plan');
        Route::post('/user-plans/bulk-migrate', 'bulkMigrateUsers')->name('admin.roi-settings.bulk-migrate-users');
        Route::post('/user-plans/assign-all', 'assignAllUsers')->name('admin.roi-settings.assign-all-users');
        Route::post('/user-plans/bulk-assign-selected', 'bulkAssignSelected')->name('admin.roi-settings.bulk-assign-selected');
        Route::get('/commission-bonuses', 'commissionBonuses')->name('admin.roi-settings.commission-bonuses');
        Route::post('/commission-bonuses/update', 'updateCommissionBonuses')->name('admin.roi-settings.update-commission-bonuses');
        Route::get('/profit-share-settings', 'profitShareSettings')->name('admin.roi-settings.profit-share-settings');
        Route::post('/profit-share-settings/update', 'updateProfitShare')->name('admin.roi-settings.update-profit-share');
    });

    // ROI Reversal Management Routes
    Route::prefix('roi-reversal')->controller(App\Http\Controllers\Admin\ROIReversalController::class)->group(function () {
        Route::get('/', 'index')->name('admin.roi-reversal.index');
        Route::post('/preview', 'preview')->name('admin.roi-reversal.preview');
        Route::post('/execute', 'execute')->name('admin.roi-reversal.execute');
    });

    // Saving Account — admin management (specific routes before wildcard)
    Route::prefix('saving-accounts')->controller(SavingInstalmentController::class)->group(function () {
        Route::get('/', 'adminIndex')->name('admin.saving.index');
        Route::get('/pending', 'adminPendingSubmissions')->name('admin.saving.pending');
        Route::get('/create-user', 'adminCreateUserForm')->name('admin.saving.create-user');
        Route::post('/create-user', 'adminCreateUser')->name('admin.saving.create-user.store');
        Route::post('/set-parent', 'setSavingParent')->name('admin.saving.set-parent');
        Route::post('/instalment/{instalment}/confirm', 'adminConfirm')->name('admin.saving.confirm');
        Route::post('/instalment/{instalment}/reject', 'adminReject')->name('admin.saving.reject');
        Route::post('/{user}/activate', 'adminActivate')->name('admin.saving.activate');
        Route::get('/{user}', 'adminShow')->name('admin.saving.show');
    });

});

Route::prefix('account')->controller(TopupController::class)->middleware(['auth', 'verified'])->group(function () {
    Route::get('top-ups', 'index')->name('account.topups.index');
    Route::post('top-ups', 'store')->name('account.topups.store');
    Route::post('top-ups/process', 'topUp')->name('account.topups.process');
    Route::get('top-ups/analytics', 'analytics')->name('account.topups.analytics');
    Route::get('top-ups/export', 'exportTopups')->name('account.topups.export');
});


Route::controller(BlockedUserController::class)->middleware(['auth'])->group(function () {
    Route::get('blocked', 'index')->name('blocked.index');
});

// Run Schedule Command
Route::get('/run-schedule', function () {
    Artisan::call('schedule:run');
    return redirect()->back()->with('success', 'Schedule command executed');
})->name('run-schedule');

// ── TEST ROUTE: Create Saving Account Root Admin ──────────────────────────────
// Visit: /create-saving-admin
// Remove this route in production after use.
Route::get('/create-saving-admin', function () {
    $existing = \App\Models\User::where('username', 'saving-admin')->first();
    if ($existing) {
        // Already exists — just make sure it is set as the saving parent
        \App\Models\Setting::first()->update(['saving_parent_user_id' => $existing->id]);

        // Ensure referral link exists
        \App\Models\ReferralLink::firstOrCreate(
            ['user_id' => $existing->id],
            ['link' => 'saving-admin']
        );

        return response()->json([
            'message'        => 'Saving admin already exists — set as saving parent.',
            'user_id'        => $existing->id,
            'username'       => $existing->username,
            'referral_link'  => $existing->username,
            'login_url'      => url('/login'),
        ]);
    }

    \Illuminate\Support\Facades\DB::beginTransaction();
    try {
        $user = \App\Models\User::create([
            'name'                          => 'Saving Admin',
            'email'                         => 'saving-admin@gmail.com',
            'password'                      => \Illuminate\Support\Facades\Hash::make('saving@admin123'),
            'username'                      => 'saving-admin',
            'is_active'                     => true,
            'phone_verified'                => true,
            'can_login'                     => true,   // root user is always active
            'phone_number'                  => '00000000000',
            'transaction_id'                => 'SAVING-ROOT-' . strtoupper(\Illuminate\Support\Str::random(6)),
            'account_type'                  => 'saving',
            'saving_plan_start_date'        => now()->toDateString(),
            'saving_registration_completed' => true,
            'saving_total_deposited'        => 0,
            'user_plan'                     => 'standard',
        ]);

        $user->assignRole('member');

        // Give this user a referral link so others can register under them
        \App\Models\ReferralLink::create([
            'user_id' => $user->id,
            'link'    => 'saving-admin',
        ]);

        // Set as the saving tree root in settings
        \App\Models\Setting::first()->update(['saving_parent_user_id' => $user->id]);

        \Illuminate\Support\Facades\DB::commit();

        return response()->json([
            'message'        => 'Saving admin created and set as saving tree root.',
            'user_id'        => $user->id,
            'username'       => $user->username,
            'email'          => $user->email,
            'password'       => 'saving@admin123',
            'referral_link'  => 'saving-admin',
            'register_url'   => url('/register/ref/saving-admin'),
            'login_url'      => url('/login'),
        ]);

    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\DB::rollBack();
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
// ─────────────────────────────────────────────────────────────────────────────

// Auth Routes
Auth::routes(['verify' => true]);
require __DIR__ . '/auth.php';
