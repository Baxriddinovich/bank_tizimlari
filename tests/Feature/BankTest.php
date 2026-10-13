<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\LoanApplication;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankTest extends TestCase
{
    use RefreshDatabase;

    private int $n = 0;

    private function user(string $role = 'customer', int $balance = 0): User
    {
        $this->n++;
        $u = User::create([
            'name' => "User{$this->n}", 'email' => "u{$this->n}@t.uz", 'phone' => '+99890000'.str_pad((string) $this->n, 4, '0', STR_PAD_LEFT),
            'password' => 'Secret123', 'role' => $role,
        ]);
        Account::create(['user_id' => $u->id, 'number' => Account::generateNumber(), 'balance' => $balance]);
        return $u;
    }

    public function test_registration_then_otp_login(): void
    {
        $this->post('/register', [
            'name' => 'Ali', 'email' => 'ali@t.uz', 'phone' => '+998901112233',
            'password' => 'Secret123', 'password_confirmation' => 'Secret123',
        ])->assertRedirect('/otp');
        $code = session('demo_otp');
        $this->assertNotNull($code);
        $this->post('/otp', ['code' => $code])->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertSame(1, User::first()->accounts()->count());
    }

    public function test_weak_password_and_bad_phone_rejected(): void
    {
        $this->post('/register', [
            'name' => 'A', 'email' => 'a@t.uz', 'phone' => '123', 'password' => 'short', 'password_confirmation' => 'short',
        ])->assertSessionHasErrors(['phone', 'password']);
    }

    public function test_wrong_otp_does_not_login(): void
    {
        $u = $this->user();
        $this->post('/login', ['email' => $u->email, 'password' => 'Secret123'])->assertRedirect('/otp');
        $this->post('/otp', ['code' => '000000'])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_account_locks_after_five_failed_logins(): void
    {
        $u = $this->user();
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $u->email, 'password' => 'bad']);
        }
        $this->post('/login', ['email' => $u->email, 'password' => 'Secret123'])->assertSessionHasErrors('email');
        $this->assertNotNull($u->fresh()->locked_until);
    }

    public function test_internal_transfer_moves_money_and_notifies(): void
    {
        $a = $this->user('customer', 1_000_00);
        $b = $this->user();
        $this->actingAs($a)->post('/transfer', [
            'from_account_id' => $a->accounts->first()->id, 'kind' => 'internal',
            'target' => $b->accounts->first()->number, 'amount' => '300',
        ])->assertRedirect();
        $this->assertSame(700_00, $a->accounts->first()->fresh()->balance);
        $this->assertSame(300_00, $b->accounts->first()->fresh()->balance);
        $this->assertSame(1, Transaction::count());
        $this->assertSame(1, UserNotification::where('user_id', $a->id)->count());
        $this->assertSame(1, UserNotification::where('user_id', $b->id)->count());
    }

    public function test_transfer_with_insufficient_funds_fails(): void
    {
        $a = $this->user('customer', 100_00);
        $b = $this->user();
        $this->actingAs($a)->post('/transfer', [
            'from_account_id' => $a->accounts->first()->id, 'kind' => 'internal',
            'target' => $b->accounts->first()->number, 'amount' => '500',
        ])->assertSessionHasErrors('amount');
        $this->assertSame(100_00, $a->accounts->first()->fresh()->balance);
        $this->assertSame(0, Transaction::count());
    }

    public function test_cannot_transfer_from_someone_elses_account(): void
    {
        $a = $this->user('customer', 1_000_00);
        $b = $this->user('customer', 1_000_00);
        $this->actingAs($a)->post('/transfer', [
            'from_account_id' => $b->accounts->first()->id, 'kind' => 'internal',
            'target' => $a->accounts->first()->number, 'amount' => '100',
        ])->assertSessionHasErrors();
        $this->assertSame(1_000_00, $b->accounts->first()->fresh()->balance);
    }

    public function test_external_transfer_debits_sender_only(): void
    {
        $a = $this->user('customer', 1_000_00);
        $this->actingAs($a)->post('/transfer', [
            'from_account_id' => $a->accounts->first()->id, 'kind' => 'external', 'target' => '9860123412341234', 'amount' => '250',
        ])->assertRedirect();
        $this->assertSame(750_00, $a->accounts->first()->fresh()->balance);
        $this->assertSame('9860123412341234', Transaction::first()->external_target);
    }

    public function test_transaction_history_is_private_and_exportable(): void
    {
        $a = $this->user('customer', 1_000_00);
        $b = $this->user('customer', 1_000_00);
        $c = $this->user();
        $tx = Transaction::create([
            'reference' => 'TXTEST', 'from_account_id' => $a->accounts->first()->id, 'to_account_id' => $b->accounts->first()->id,
            'kind' => 'internal', 'amount' => 100_00,
        ]);
        $this->actingAs($a)->get("/transactions/{$tx->id}")->assertOk();
        $this->actingAs($b)->get("/transactions/{$tx->id}")->assertOk();
        $this->actingAs($c)->get("/transactions/{$tx->id}")->assertForbidden();
        $this->actingAs($a)->get('/transactions/export')->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->actingAs($a)->get('/transactions?kind=internal&min=50')->assertOk()->assertSee('100,00', false);
        $this->actingAs($a)->get('/transactions?min=500')->assertOk()->assertDontSee('100,00', false);
    }

    public function test_loan_application_and_officer_review(): void
    {
        $cust = $this->user();
        $officer = $this->user('officer');
        $this->actingAs($cust)->post('/loans', [
            'type' => 'consumer', 'amount' => '5000000', 'term_months' => 12, 'monthly_income' => '3000000',
        ])->assertRedirect('/loans');
        $loan = LoanApplication::first();
        $this->assertSame('pending', $loan->status);

        $this->actingAs($cust)->post("/officer/loans/{$loan->id}/review", ['decision' => 'approved'])->assertForbidden();
        $this->actingAs($officer)->post("/officer/loans/{$loan->id}/review", ['decision' => 'approved', 'review_note' => 'OK'])->assertRedirect();
        $this->assertSame('approved', $loan->fresh()->status);
        $this->assertSame(1, UserNotification::where('user_id', $cust->id)->count());
        $this->actingAs($officer)->post("/officer/loans/{$loan->id}/review", ['decision' => 'rejected'])->assertStatus(422);
    }

    public function test_loan_amount_limits(): void
    {
        $cust = $this->user();
        $this->actingAs($cust)->post('/loans', [
            'type' => 'consumer', 'amount' => '100', 'term_months' => 12, 'monthly_income' => '3000000',
        ])->assertSessionHasErrors('amount');
    }

    public function test_role_based_access(): void
    {
        $cust = $this->user();
        $admin = $this->user('admin');
        $this->actingAs($cust)->get('/admin/users')->assertForbidden();
        $this->actingAs($cust)->get('/officer/loans')->assertForbidden();
        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get('/dashboard')->assertForbidden();
        $this->app['auth']->forgetGuards();
        $this->flushSession();
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_admin_can_block_user_and_blocked_user_is_logged_out(): void
    {
        $cust = $this->user();
        $admin = $this->user('admin');
        $this->actingAs($admin)->post("/admin/users/{$cust->id}/block")->assertRedirect();
        $this->assertTrue($cust->fresh()->is_blocked);
        $this->actingAs($cust->fresh())->get('/dashboard')->assertRedirect('/login');
        $this->actingAs($admin)->post("/admin/users/{$admin->id}/block")->assertStatus(422);
    }

    public function test_admin_changes_role(): void
    {
        $cust = $this->user();
        $admin = $this->user('admin');
        $this->actingAs($admin)->post("/admin/users/{$cust->id}/role", ['role' => 'officer'])->assertRedirect();
        $this->assertSame('officer', $cust->fresh()->role);
    }

    public function test_notifications_page_and_mark_read(): void
    {
        $u = $this->user();
        UserNotification::notify($u, 'Salom', 'Test');
        $this->actingAs($u)->get('/notifications')->assertOk()->assertSee('Salom');
        $this->actingAs($u)->post('/notifications/read')->assertRedirect();
        $this->assertSame(0, UserNotification::whereNull('read_at')->count());
    }

    public function test_open_account_limit(): void
    {
        $u = $this->user();
        for ($i = 0; $i < 4; $i++) {
            $this->actingAs($u)->post('/accounts', ['type' => 'card'])->assertRedirect();
        }
        $this->actingAs($u)->post('/accounts', ['type' => 'card'])->assertStatus(422);
        $this->assertSame(5, $u->accounts()->count());
    }

    public function test_admin_dashboard_and_all_transactions(): void
    {
        $a = $this->user('customer', 1_000_00);
        $b = $this->user();
        $admin = $this->user('admin');
        Transaction::create([
            'reference' => 'TXADMIN1', 'from_account_id' => $a->accounts->first()->id, 'to_account_id' => $b->accounts->first()->id,
            'kind' => 'internal', 'amount' => 100_00,
        ]);
        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('Boshqaruv paneli');
        $this->actingAs($admin)->get('/admin/transactions')->assertOk()->assertSee('TXADMIN1');
        $this->actingAs($admin)->get('/admin/transactions?q=NOPE')->assertOk()->assertDontSee('TXADMIN1');
        $this->actingAs($admin)->get('/admin/transactions?kind=internal&min=50&max=500')->assertOk()->assertSee('TXADMIN1');
        $this->actingAs($a)->get('/admin')->assertForbidden();
        $this->actingAs($a)->get('/admin/transactions')->assertForbidden();
    }
}
