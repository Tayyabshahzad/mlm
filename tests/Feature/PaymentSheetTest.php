<?php

use App\Models\InstalmentPaymentAudit;
use App\Models\InstalmentUploadBatch;
use App\Models\InstalmentUploadRow;
use App\Models\ReferralLink;
use App\Models\SavingInstalment;
use App\Models\Setting;
use App\Models\User;
use App\Services\InstalmentSheetUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

// ─── Helpers ─────────────────────────────────────────────────────────────────

function psUser(array $attrs = []): User
{
    static $n = 0;
    $n++;
    $user = User::factory()->create(array_merge([
        'username'       => 'psuser' . $n . '_' . uniqid(),
        'transaction_id' => 'TEST-' . uniqid(),
        'phone_number'   => '0322' . str_pad((string) $n, 7, '0', STR_PAD_LEFT),
        'can_login'      => true,
        'blocked'        => false,
    ], $attrs));
    ReferralLink::create(['user_id' => $user->id, 'link' => $user->username]);

    return $user;
}

/** Saving member: #1 confirmed, #2 and #3 pending ($19 each). */
function psMember(array $attrs = []): User
{
    $user = psUser(array_merge([
        'account_type'                  => 'saving',
        'saving_registration_completed' => true,
        'saving_plan_start_date'        => now()->toDateString(),
    ], $attrs));

    foreach ([1, 2, 3] as $i) {
        SavingInstalment::create([
            'user_id' => $user->id, 'instalment_number' => $i, 'amount' => 19,
            'due_date' => now()->addMonths($i - 1)->toDateString(),
            'status' => $i === 1 ? 'confirmed' : 'pending',
        ]);
    }

    return $user;
}

function psRole(string $role): User
{
    Role::findOrCreate($role);
    $u = psUser();
    $u->assignRole($role);

    return $u;
}

/** Build a CSV upload: rows of [username, email, txn, amount]. */
function psCsv(array $rows, string $header = 'Username,Email,Txn ID,Amount'): UploadedFile
{
    $lines = [$header];
    foreach ($rows as $r) {
        $lines[] = implode(',', $r);
    }

    return UploadedFile::fake()->createWithContent('payments.csv', implode("\n", $lines) . "\n");
}

function psStatus(User $u, int $n = 2): string
{
    return SavingInstalment::where('user_id', $u->id)->where('instalment_number', $n)->value('status');
}

beforeEach(function () {
    Notification::fake();
    Storage::fake('local');
    Storage::fake('public');
    expect(DB::connection()->getDatabaseName())->not->toBe('oracle_18');
    Setting::query()->insert(['created_at' => now(), 'updated_at' => now()]);
});

// ─── Happy path ──────────────────────────────────────────────────────────────

it('submits the next instalment of every member in the sheet; admin then confirms', function () {
    $op = psRole('operator');
    [$a, $b, $c] = [psMember(), psMember(), psMember()];

    $file = psCsv([
        [$a->username, $a->email, 'HBL-1001', '19'],
        [$b->username, '', '', '19.00'],
        [$c->username, '', 'MZN 2002', '$19'],
    ]);

    $this->actingAs($op)->post(route('payment-sheets.upload'), ['sheet' => $file])
        ->assertRedirect()->assertSessionHas('success');

    foreach ([$a, $b, $c] as $m) {
        expect(psStatus($m))->toBe('submitted')->and(psStatus($m, 3))->toBe('pending');
    }

    $batch = InstalmentUploadBatch::first();
    expect($batch->rows_count)->toBe(3)
        ->and((float) $batch->total_amount)->toBe(57.0)
        ->and($batch->uploaded_by)->toBe($op->id)
        ->and(Storage::disk('local')->exists($batch->stored_path))->toBeTrue();

    $instA = SavingInstalment::where('user_id', $a->id)->where('instalment_number', 2)->first();
    $instB = SavingInstalment::where('user_id', $b->id)->where('instalment_number', 2)->first();
    expect($instA->payment_method)->toBe('sheet_upload')
        ->and($instA->transaction_id)->toBe('HBL-1001')
        ->and((float) $instA->submitted_amount)->toBe(19.0)
        ->and($instA->notes)->toContain('@' . $op->username)
        ->and($instB->transaction_id)->toBe("SHEET-{$batch->id}-3"); // generated when blank (sheet row 3)

    expect(InstalmentPaymentAudit::where('action', 'payment_sheet_uploaded')->count())->toBe(1);

    // Admin confirms through the existing Pending Confirmations flow
    $admin = psRole('admin');
    $this->actingAs($admin)->get(route('admin.saving.pending'))->assertOk()->assertSee('Payment Sheet')->assertSee('Sheet #' . $batch->id);
    $this->actingAs($admin)->post(route('admin.saving.confirm', $instA))->assertSessionHas('success');
    expect($instA->fresh()->status)->toBe('confirmed');

    $this->actingAs($op)->get(route('payment-sheets.show', $batch))->assertOk()->assertSee('Confirmed')->assertSee('Awaiting admin');
});

it('accepts a real .xlsx file', function () {
    $op = psRole('operator');
    $m  = psMember();

    $xlsx = Excel::raw(new class([[$m->username, '', 'X-77', 19]]) implements FromArray, WithHeadings {
        public function __construct(private array $rows) {}
        public function headings(): array { return ['Username', 'Email', 'Txn ID', 'Amount']; }
        public function array(): array { return $this->rows; }
    }, \Maatwebsite\Excel\Excel::XLSX);

    $result = app(InstalmentSheetUploadService::class)
        ->upload(UploadedFile::fake()->createWithContent('payments.xlsx', $xlsx), $op);

    expect($result['ok'])->toBeTrue()->and(psStatus($m))->toBe('submitted');
});

// ─── All-or-nothing ──────────────────────────────────────────────────────────

it('rejects the whole sheet if even one of ten usernames is wrong — nothing is saved', function () {
    $op      = psRole('operator');
    $members = collect(range(1, 9))->map(fn () => psMember());

    $rows = $members->map(fn ($m) => [$m->username, '', '', '19'])->all();
    array_splice($rows, 4, 0, [['no_such_user_xyz', '', '', '19']]); // sheet row 6

    $this->actingAs($op)->post(route('payment-sheets.upload'), ['sheet' => psCsv($rows)])
        ->assertSessionHas('error')
        ->assertSessionHas('sheet_errors', fn ($errs) => count($errs) === 1 && $errs[0]['row'] === 6 && str_contains($errs[0]['message'], 'not found'));

    foreach ($members as $m) {
        expect(psStatus($m))->toBe('pending');
    }
    expect(InstalmentUploadBatch::count())->toBe(0)
        ->and(InstalmentUploadRow::count())->toBe(0)
        ->and(Storage::disk('local')->allFiles())->toBe([])
        ->and(InstalmentPaymentAudit::where('action', 'payment_sheet_rejected')->count())->toBe(1);
});

it('rolls everything back if a system error happens part-way through saving', function () {
    $op = psRole('operator');
    [$a, $b, $c] = [psMember(), psMember(), psMember()];
    $failId = SavingInstalment::where('user_id', $c->id)->where('instalment_number', 2)->value('id');

    // Simulate a crash while saving the 3rd row
    SavingInstalment::updating(function ($inst) use ($failId) {
        if ($inst->id === $failId) {
            throw new RuntimeException('Simulated database failure');
        }
    });

    $file = psCsv([[$a->username, '', '', '19'], [$b->username, '', '', '19'], [$c->username, '', '', '19']]);

    $this->actingAs($op)->post(route('payment-sheets.upload'), ['sheet' => $file])
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'nothing was saved'));

    expect(psStatus($a))->toBe('pending')
        ->and(psStatus($b))->toBe('pending')
        ->and(psStatus($c))->toBe('pending')
        ->and(InstalmentUploadBatch::count())->toBe(0)
        ->and(Storage::disk('local')->allFiles())->toBe([]);
});

// ─── Row validation ──────────────────────────────────────────────────────────

it('reports every problem with its row number', function () {
    $op       = psRole('operator');
    $ok       = psMember();
    $wrongAmt = psMember();
    $wrongMail = psMember(['email' => 'real@example.com']);
    $dup      = psMember();
    $awaiting = psMember();
    SavingInstalment::where('user_id', $awaiting->id)->where('instalment_number', 2)->update(['status' => 'submitted']);
    $standard = psUser();                      // no saving plan
    $blocked  = psMember(['blocked' => true]);
    $usedTxn  = psMember();
    SavingInstalment::where('user_id', $usedTxn->id)->where('instalment_number', 1)->update(['transaction_id' => 'OLD-TXN-1']);
    $other    = psMember();

    $rows = [
        [$ok->username, '', 'SAME-1', '19'],          // 2  dup txn in sheet
        [$wrongAmt->username, '', '', '18'],          // 3  amount
        [$wrongMail->username, 'fake@example.com', '', '19'], // 4 email
        [$dup->username, '', '', '19'],               // 5  dup username
        [$dup->username, '', '', '19'],               // 6  dup username
        [$awaiting->username, '', '', '19'],          // 7  awaiting
        [$standard->username, '', '', '19'],          // 8  no plan
        [$blocked->username, '', '', '19'],           // 9  blocked
        ['', '', '', '19'],                           // 10 username missing
        [$other->username, '', 'SAME-1', 'abc'],      // 11 dup txn + bad amount
        [$usedTxn->username, '', 'old-txn-1', '19'],  // 12 txn already used
    ];

    $result = app(InstalmentSheetUploadService::class)->upload(psCsv($rows), $op);
    $byRow  = collect($result['errors'])->groupBy('row')->map(fn ($g) => $g->pluck('message')->implode(' | '));

    expect($result['ok'])->toBeFalse()
        ->and($byRow[2])->toContain('more than once')
        ->and($byRow[3])->toContain('does not match')
        ->and($byRow[4])->toContain('Email does not match')
        ->and($byRow[5])->toContain('more than once')
        ->and($byRow[6])->toContain('more than once')
        ->and($byRow[7])->toContain('awaiting admin confirmation')
        ->and($byRow[8])->toContain('not on a saving instalment plan')
        ->and($byRow[9])->toContain('blocked')
        ->and($byRow[10])->toContain('Username is required')
        ->and($byRow[11])->toContain('not a valid positive number')
        ->and($byRow[12])->toContain('already used');

    expect(SavingInstalment::where('status', 'submitted')->count())->toBe(1); // only the pre-existing awaiting one
});

it('rejects a sheet with missing columns or no rows', function () {
    $op  = psRole('operator');
    $svc = app(InstalmentSheetUploadService::class);

    $r1 = $svc->upload(psCsv([['someone', '19']], 'Name,Paid'), $op);
    expect($r1['ok'])->toBeFalse()->and($r1['errors'][0]['message'])->toContain('Missing required column(s): Username, Amount');

    $r2 = $svc->upload(psCsv([]), $op);
    expect($r2['ok'])->toBeFalse()->and($r2['errors'][0]['message'])->toContain('no data rows');
});

it('only accepts spreadsheet files', function () {
    $this->actingAs(psRole('operator'))
        ->post(route('payment-sheets.upload'), ['sheet' => UploadedFile::fake()->image('photo.jpg')])
        ->assertSessionHasErrors('sheet');
});

// ─── Access ──────────────────────────────────────────────────────────────────

it('lets operators and admins use payment sheets, and nobody else', function () {
    $op     = psRole('operator');
    $admin  = psRole('admin');
    $office = psRole('office-admin');
    $member = psMember();

    $this->actingAs($op)->get(route('payment-sheets.index'))->assertOk()->assertSee('Upload Payment Sheet');
    $this->actingAs($admin)->get(route('payment-sheets.index'))->assertOk();
    $this->actingAs($op)->get(route('payment-sheets.template'))->assertOk()->assertDownload('payment-sheet-template.xlsx');

    $this->actingAs($member)->get(route('payment-sheets.index'))->assertForbidden();
    $this->actingAs($office)->get(route('payment-sheets.index'))->assertForbidden();
    $this->actingAs($member)->post(route('payment-sheets.upload'), ['sheet' => psCsv([[$member->username, '', '', '19']])])->assertForbidden();
    expect(psStatus($member))->toBe('pending');

    // Operators cannot reach admin pages or bank reconciliation
    $this->actingAs($op)->get(route('admin.saving.pending'))->assertForbidden();
    $this->actingAs($op)->get(route('admin.saving.index'))->assertForbidden();
    $this->actingAs($op)->get(route('reconciliation.index'))->assertForbidden();
    $this->actingAs($op)->get(route('admin.office-admins.index'))->assertForbidden();
});

it('shows operators only their own sheets; admins see all', function () {
    $op1   = psRole('operator');
    $op2   = psRole('operator');
    $admin = psRole('admin');
    $svc   = app(InstalmentSheetUploadService::class);

    $b1 = $svc->upload(psCsv([[psMember()->username, '', '', '19']]), $op1)['batch'];
    $b2 = $svc->upload(psCsv([[psMember()->username, '', '', '19']]), $op2)['batch'];

    $this->actingAs($op1)->get(route('payment-sheets.show', $b1))->assertOk();
    $this->actingAs($op1)->get(route('payment-sheets.show', $b2))->assertForbidden();
    $this->actingAs($op1)->get(route('payment-sheets.download', $b2))->assertForbidden();
    $this->actingAs($admin)->get(route('payment-sheets.show', $b2))->assertOk();
    $this->actingAs($admin)->get(route('payment-sheets.download', $b1))->assertOk();
});

it('lets an admin make someone a data entry operator from Staff Roles', function () {
    Role::findOrCreate('operator');
    Role::findOrCreate('office-admin');
    $admin = psRole('admin');
    $staff = psUser(['name' => 'Usman Operator']);

    $this->actingAs($admin)->post(route('admin.office-admins.assign'), ['account' => $staff->username, 'role' => 'operator'])
        ->assertSessionHas('success');
    expect($staff->fresh()->hasRole('operator'))->toBeTrue()->and($staff->fresh()->hasRole('office-admin'))->toBeFalse();

    $this->actingAs($admin)->get(route('admin.office-admins.index'))->assertOk()->assertSee('Usman Operator')->assertSee('Data Entry Operator');
    $this->actingAs($staff->fresh())->get(route('payment-sheets.index'))->assertOk();

    $this->actingAs($admin)->delete(route('admin.office-admins.remove', $staff), ['role' => 'operator'])->assertSessionHas('success');
    expect($staff->fresh()->hasRole('operator'))->toBeFalse();
    expect(InstalmentPaymentAudit::where('action', 'office_admin_assigned')->first()->details['role'])->toBe('operator');
});

// ─── Staff accounts: create, activate/deactivate, password ───────────────────

function psStaffPayload(array $o = []): array
{
    return array_merge([
        'name' => 'Ayesha Entry', 'username' => 'ayesha_entry', 'email' => 'ayesha@office.test',
        'phone_number' => '03001112233', 'password' => 'Secret#123', 'password_confirmation' => 'Secret#123', 'role' => 'operator',
    ], $o);
}

it('lets an admin create a staff login that works immediately and lands on its tool', function () {
    Role::findOrCreate('operator');
    Role::findOrCreate('office-admin');
    Role::findOrCreate('member');
    $admin = psRole('admin');

    $this->actingAs($admin)->post(route('admin.office-admins.create'), psStaffPayload())->assertSessionHas('success');

    $staff = User::where('username', 'ayesha_entry')->first();
    expect($staff)->not->toBeNull()
        ->and($staff->hasRole('operator'))->toBeTrue()
        ->and($staff->hasRole('member'))->toBeFalse()
        ->and((bool) $staff->can_login)->toBeTrue()
        ->and($staff->email_verified_at)->not->toBeNull()
        ->and($staff->reflink)->toBeNull()                       // not part of the MLM
        ->and($staff->sponsor_id)->toBeNull()
        ->and(InstalmentPaymentAudit::where('action', 'staff_account_created')->where('user_id', $staff->id)->exists())->toBeTrue();

    // Real login through the login form
    auth()->logout();
    $this->post('/login', ['login' => 'ayesha_entry', 'password' => 'Secret#123'])->assertRedirect(route('dashboard', absolute: false));
    $this->get(route('dashboard'))->assertOk()->assertSee('Data Entry Dashboard');
    $this->get(route('payment-sheets.index'))->assertOk()->assertSee('Upload Payment Sheet')->assertDontSee('Copy Referral Link');
});

it('sends a new office admin straight to bank reconciliation', function () {
    Role::findOrCreate('operator');
    Role::findOrCreate('office-admin');
    $admin = psRole('admin');

    $this->actingAs($admin)->post(route('admin.office-admins.create'), psStaffPayload(['username' => 'bilal_office', 'email' => 'b@office.test', 'phone_number' => null, 'role' => 'office-admin']));
    $staff = User::where('username', 'bilal_office')->first();

    $this->actingAs($staff)->get(route('dashboard'))->assertRedirect(route('reconciliation.index'));
    $this->actingAs($staff)->get(route('reconciliation.index'))->assertOk();
});

it('validates new staff accounts', function () {
    Role::findOrCreate('operator');
    $admin = psRole('admin');
    $taken = psUser(['username' => 'taken_name', 'email' => 'taken@x.test']);

    $this->actingAs($admin)->post(route('admin.office-admins.create'), psStaffPayload(['username' => 'taken_name']))->assertSessionHasErrors('username');
    $this->actingAs($admin)->post(route('admin.office-admins.create'), psStaffPayload(['email' => 'taken@x.test']))->assertSessionHasErrors('email');
    $this->actingAs($admin)->post(route('admin.office-admins.create'), psStaffPayload(['password_confirmation' => 'nope1234']))->assertSessionHasErrors('password');
    $this->actingAs($admin)->post(route('admin.office-admins.create'), psStaffPayload(['role' => 'admin']))->assertSessionHasErrors('role');
    expect(User::where('username', 'ayesha_entry')->exists())->toBeFalse();

    // Members cannot create staff
    $this->actingAs(psMember())->post(route('admin.office-admins.create'), psStaffPayload())->assertForbidden();
});

it('deactivates and re-activates a staff account', function () {
    $admin = psRole('admin');
    $op    = psRole('operator');

    $this->actingAs($admin)->post(route('admin.office-admins.toggle', $op))->assertSessionHas('success');
    expect((bool) $op->fresh()->can_login)->toBeFalse();
    $this->actingAs($op->fresh())->get(route('payment-sheets.index'))->assertRedirect(route('approval.waiting'));

    $this->actingAs($admin)->post(route('admin.office-admins.toggle', $op))->assertSessionHas('success');
    expect((bool) $op->fresh()->can_login)->toBeTrue();
    $this->actingAs($op->fresh())->get(route('payment-sheets.index'))->assertOk();

    expect(InstalmentPaymentAudit::where('user_id', $op->id)->whereIn('action', ['staff_account_deactivated', 'staff_account_activated'])->count())->toBe(2);
});

it('changes a staff password without logging it', function () {
    $admin = psRole('admin');
    $op    = psRole('operator');

    $this->actingAs($admin)->post(route('admin.office-admins.password', $op), ['password' => 'NewPass#2026', 'password_confirmation' => 'NewPass#2026'])
        ->assertSessionHas('success');

    expect(\Illuminate\Support\Facades\Hash::check('NewPass#2026', $op->fresh()->password))->toBeTrue();
    $log = InstalmentPaymentAudit::where('action', 'staff_password_changed')->first();
    expect($log->actor_id)->toBe($admin->id)
        ->and(json_encode($log->details) . $log->description)->not->toContain('NewPass');

    auth()->logout();
    $this->post('/login', ['login' => $op->username, 'password' => 'NewPass#2026'])->assertRedirect();
    $this->assertAuthenticatedAs($op->fresh());
});

it('does not let the staff page change admins or ordinary members', function () {
    Role::findOrCreate('operator');
    $admin  = psRole('admin');
    $admin2 = psRole('admin');
    $admin2->assignRole('operator');            // an admin who also has a staff role
    $member = psMember();

    $this->actingAs($admin)->post(route('admin.office-admins.toggle', $admin2))->assertForbidden();
    $this->actingAs($admin)->post(route('admin.office-admins.password', $admin2), ['password' => 'xxxxxxxx', 'password_confirmation' => 'xxxxxxxx'])->assertForbidden();
    $this->actingAs($admin)->post(route('admin.office-admins.toggle', $member))->assertNotFound();
    $this->actingAs($admin)->post(route('admin.office-admins.password', $member), ['password' => 'xxxxxxxx', 'password_confirmation' => 'xxxxxxxx'])->assertNotFound();

    expect((bool) $admin2->fresh()->can_login)->toBeTrue()->and((bool) $member->fresh()->can_login)->toBeTrue();

    // Operators cannot reach the staff page at all
    $this->actingAs(psRole('operator'))->post(route('admin.office-admins.create'), psStaffPayload())->assertForbidden();
});

// ─── Data Entry Operator: restricted menu, URLs and dashboard ────────────────

function psSidebar($test, User $u): array
{
    $html = $test->actingAs($u)->get(route('dashboard'))->assertOk()->getContent();
    preg_match_all('/<(?:span|h4) class="menu-text">\s*([^<]+?)\s*</', $html, $m);

    return array_values(array_unique($m[1]));
}

it('shows a data entry operator only Dashboard, Upload Payment Sheet and Logout in the sidebar', function () {
    $menu = psSidebar($this, psRole('operator'));

    expect($menu)->toContain('Dashboard')->toContain('Upload Payment Sheet')->toContain('Logout');
    foreach (['Genealogy Details', 'Genealogy', 'Product', 'Digital Cards', 'Wallets', 'Saving Wallet', 'Standard Wallet',
              'Team Performance', 'Profile Setting', 'Bank Reconciliation', 'Staff Roles'] as $hidden) {
        expect($menu)->not->toContain($hidden);
    }
});

it('blocks a data entry operator from every other page by URL', function () {
    $op = psRole('operator');

    foreach ([
        'wallets.online', 'wallets.investment', 'wallets.direct.indirect', 'wallets.saving.account', 'show.transaction.history',
        'cards.index', 'cards.received', 'genealogy.team', 'buy.products', 'activation.code', 'team.performance',
        'profile.edit', 'profile.change.password', 'saving.user.instalments', 'kyc.show', 'withdrawals.index',
    ] as $route) {
        $this->actingAs($op)->get(route($route))->assertForbidden();
    }
    $this->actingAs($op)->post(route('cards.store'), ['amount' => 5])->assertForbidden();

    // Its own tool keeps working
    $this->actingAs($op)->get(route('payment-sheets.index'))->assertOk();
    $this->actingAs($op)->get(route('payment-sheets.template'))->assertOk();
    $m = psMember();
    $this->actingAs($op)->post(route('payment-sheets.upload'), ['sheet' => psCsv([[$m->username, '', '', '19']])])->assertSessionHas('success');
    $this->actingAs($op)->post(route('logout'))->assertRedirect();
});

it('shows the operator dashboard with their own sheet figures', function () {
    $op    = psRole('operator');
    $other = psRole('operator');
    $admin = psRole('admin');
    $svc   = app(InstalmentSheetUploadService::class);
    [$a, $b, $c] = [psMember(), psMember(), psMember(['name' => 'Rejected Person'])];

    $batch = $svc->upload(psCsv([[$a->username, '', '', '19'], [$b->username, '', '', '19'], [$c->username, '', '', '19']]), $op)['batch'];
    $svc->upload(psCsv([['ghost_user', '', '', '19']]), $op);                         // rejected upload
    $svc->upload(psCsv([[psMember()->username, '', '', '19']]), $other);              // someone else's sheet

    $instA = SavingInstalment::where('user_id', $a->id)->where('instalment_number', 2)->first();
    $instC = SavingInstalment::where('user_id', $c->id)->where('instalment_number', 2)->first();
    $this->actingAs($admin)->post(route('admin.saving.confirm', $instA));
    $this->actingAs($admin)->post(route('admin.saving.reject', $instC), ['notes' => 'Not in owner account']);

    // The member later pays the rejected instalment himself — still not this sheet's success
    $instC->refresh()->update(['status' => 'submitted', 'transaction_id' => 'OWN-1', 'payment_method' => 'bank', 'submitted_amount' => 19, 'submitted_at' => now()]);
    $this->actingAs($admin)->post(route('admin.saving.confirm', $instC->fresh()));

    $stats = $svc->statsFor($op);
    expect($stats['sheets'])->toBe(1)
        ->and($stats['records'])->toBe(3)
        ->and($stats['amount'])->toBe(57.0)
        ->and($stats['confirmed'])->toBe(1)
        ->and($stats['awaiting'])->toBe(1)
        ->and($stats['rejected'])->toBe(1)
        ->and($stats['rejected_uploads'])->toBe(1)
        ->and($stats['success_rate'])->toBe(33.3);

    $this->actingAs($op)->get(route('dashboard'))->assertOk()
        ->assertSee('Data Entry Dashboard')
        ->assertSee('Rejected Person')
        ->assertSee('Not in owner account')
        ->assertSee('#' . $batch->id)
        ->assertDontSee('Copy Referral Link');

    $this->actingAs($op)->get(route('payment-sheets.show', $batch))->assertOk()->assertSee('Rejected by admin')->assertSee('Confirmed')->assertSee('Awaiting admin');
});

it('leaves other roles untouched', function () {
    Role::findOrCreate('member');
    Role::findOrCreate('operator');

    // A member who was also given the operator role keeps the full member portal
    $memberOp = psMember();
    $memberOp->assignRole('member');
    $memberOp->assignRole('operator');
    expect($memberOp->isDataEntryOperatorOnly())->toBeFalse();
    $this->actingAs($memberOp)->get(route('wallets.online'))->assertOk();
    $this->actingAs($memberOp)->get(route('payment-sheets.index'))->assertOk();

    // Ordinary members still see their menus
    $plain = psMember();
    $plain->assignRole('member');
    expect(psSidebar($this, $plain))->toContain('Wallets')->toContain('Digital Cards')->not->toContain('Upload Payment Sheet');

    // Office admins still land on bank reconciliation
    $office = psRole('office-admin');
    $this->actingAs($office)->get(route('dashboard'))->assertRedirect(route('reconciliation.index'));

    // Admins keep their dashboard
    $admin = psRole('admin');
    $this->actingAs($admin)->get(route('dashboard'))->assertOk()->assertDontSee('Data Entry Dashboard');
});

// ─── Staff account that loses its role must not become a member ──────────────

function psCreateStaffViaAdmin($test, User $admin, string $username = 'staffuser', string $role = 'operator'): User
{
    $test->actingAs($admin)->post(route('admin.office-admins.create'), psStaffPayload([
        'username' => $username, 'email' => "{$username}@office.test", 'phone_number' => null, 'role' => $role,
    ]))->assertSessionHas('success');

    return User::where('username', $username)->firstOrFail();
}

it('logs a staff-only account out when its last role is removed instead of opening the member portal', function () {
    Role::findOrCreate('operator');
    Role::findOrCreate('office-admin');
    $admin = psRole('admin');
    $staff = psCreateStaffViaAdmin($this, $admin);
    expect((bool) $staff->is_staff_account)->toBeTrue();

    // Staff is logged in and working
    auth()->logout();
    $this->post('/login', ['login' => 'staffuser', 'password' => 'Secret#123']);
    $this->get(route('dashboard'))->assertOk()->assertSee('Data Entry Dashboard');

    // Admin removes the role (in another session)
    $this->actingAs($admin)->delete(route('admin.office-admins.remove', $staff), ['role' => 'operator'])
        ->assertSessionHas('success', fn ($m) => str_contains($m, 'deactivated'));

    $staff->refresh();
    expect($staff->roles()->count())->toBe(0)->and((bool) $staff->can_login)->toBeFalse();
    expect(InstalmentPaymentAudit::where('action', 'staff_account_deactivated')->where('user_id', $staff->id)->exists())->toBeTrue();

    // Staff reloads: logged out with a clear message — never the member dashboard
    $this->actingAs($staff)->get(route('dashboard'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'deactivated'));
    $this->assertGuest();

    foreach (['wallets.online', 'cards.index', 'genealogy.team', 'payment-sheets.index'] as $r) {
        $this->actingAs($staff->fresh())->get(route($r))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    // Admin sees the account as "No role" and cannot simply re-activate it without a role
    $this->actingAs($admin)->get(route('admin.office-admins.index'))->assertOk()->assertSee('staffuser')->assertSee('No role');
    $this->actingAs($admin)->post(route('admin.office-admins.toggle', $staff))->assertSessionHas('error');
    expect((bool) $staff->fresh()->can_login)->toBeFalse();
});

it('also logs out a role-less staff account even if its login was still switched on', function () {
    Role::findOrCreate('operator');
    $admin = psRole('admin');
    $staff = psCreateStaffViaAdmin($this, $admin, 'leftover_staff');

    // Simulate the old bug's state: role gone but login still on (e.g. data from before this fix)
    $staff->removeRole('operator');
    $staff->forceFill(['can_login' => true])->save();

    $this->actingAs($staff->fresh())->get(route('dashboard'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'access has been removed'));
    $this->assertGuest();
});

it('restores a staff account when a role is given back', function () {
    Role::findOrCreate('operator');
    Role::findOrCreate('office-admin');
    $admin = psRole('admin');
    $staff = psCreateStaffViaAdmin($this, $admin);
    $this->actingAs($admin)->delete(route('admin.office-admins.remove', $staff), ['role' => 'operator']);

    $this->actingAs($admin)->post(route('admin.office-admins.assign'), ['account' => 'staffuser', 'role' => 'office-admin'])
        ->assertSessionHas('success');

    $staff->refresh();
    expect((bool) $staff->can_login)->toBeTrue()->and($staff->hasRole('office-admin'))->toBeTrue();
    $this->actingAs($staff)->get(route('dashboard'))->assertRedirect(route('reconciliation.index'));
    $this->actingAs($staff)->get(route('reconciliation.index'))->assertOk();
});

it('logs out a deactivated staff account instead of showing the member waiting page', function () {
    Role::findOrCreate('operator');
    $admin = psRole('admin');
    $staff = psCreateStaffViaAdmin($this, $admin, 'paused_staff');

    $this->actingAs($admin)->post(route('admin.office-admins.toggle', $staff))->assertSessionHas('success');

    $this->actingAs($staff->fresh())->get(route('payment-sheets.index'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'deactivated'));
    $this->assertGuest();
});

it('does not touch a real member who had the operator role removed', function () {
    Role::findOrCreate('member');
    Role::findOrCreate('operator');
    $admin  = psRole('admin');
    $member = psMember();
    $member->assignRole('member');
    $member->assignRole('operator');

    $this->actingAs($admin)->delete(route('admin.office-admins.remove', $member), ['role' => 'operator'])->assertSessionHas('success');

    expect((bool) $member->fresh()->can_login)->toBeTrue();
    $this->actingAs($member->fresh())->get(route('wallets.online'))->assertOk();
    $this->actingAs($member->fresh())->get(route('payment-sheets.index'))->assertForbidden();
});

// ─── Windows/fcgid: uploaded temp file where realpath() fails ────────────────

it('uploads even when the server cannot resolve the temp file realpath (Windows C:\Windows\Temp)', function () {
    $op = psRole('operator');
    $m  = psMember();

    $xlsx = Excel::raw(new class([[$m->username, '', 'EASY-PAS-1', 19]]) implements FromArray, WithHeadings {
        public function __construct(private array $rows) {}
        public function headings(): array { return ['Username', 'Email', 'Txn ID', 'Amount']; }
        public function array(): array { return $this->rows; }
    }, \Maatwebsite\Excel\Excel::XLSX);
    $tmp = tempnam(sys_get_temp_dir(), 'php');
    file_put_contents($tmp, $xlsx);

    // Behaves like the real upload on the user's server: getRealPath() === false
    $file = new class($tmp, 'payment-sheet-template.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', UPLOAD_ERR_OK, true) extends UploadedFile {
        public function getRealPath(): string|false { return false; }
    };

    $result = app(InstalmentSheetUploadService::class)->upload($file, $op);

    expect($result['ok'])->toBeTrue()
        ->and(psStatus($m))->toBe('submitted')
        ->and(Storage::disk('local')->exists($result['batch']->stored_path))->toBeTrue()
        ->and(Storage::disk('local')->allFiles('payment-sheets/incoming'))->toBe([]);   // staging copy moved, nothing left behind
});
