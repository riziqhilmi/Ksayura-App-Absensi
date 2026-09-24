<?php

namespace Tests\Feature;

use App\Models\DailyRecap;
use App\Models\DailyRecapExpense;
use App\Models\DailyRecapExpenseSession;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyRecapExpenseDuplicateValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_expense_create_rejects_exact_normalized_duplicate_names_only(): void
    {
        [$user, $employee, $recap, $session] = $this->openExpenseSession();

        DailyRecapExpense::create([
            'daily_recap_id' => $recap->id,
            'daily_recap_expense_session_id' => $session->id,
            'created_by_employee_id' => $employee->id,
            'name' => 'Beras',
            'amount' => 10000,
            'sort_order' => 0,
        ]);

        $this->actingAs($user)
            ->from(route('employee.daily-recap-expenses.index', ['date' => $session->recap_date->toDateString()]))
            ->post(route('employee.daily-recap-expenses.store'), [
                'expense_date' => $session->recap_date->toDateString(),
                'name' => '  beRAS   ',
                'amount' => 12000,
            ])
            ->assertSessionHasErrors([
                'name' => 'Barang/pengeluaran tersebut sudah terinput pada rekap shift ini.',
            ]);

        $this->actingAs($user)
            ->post(route('employee.daily-recap-expenses.store'), [
                'expense_date' => $session->recap_date->toDateString(),
                'name' => 'Beras Ketan',
                'amount' => 12000,
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($user)
            ->post(route('employee.daily-recap-expenses.store'), [
                'expense_date' => $session->recap_date->toDateString(),
                'name' => 'Beras Impor',
                'amount' => 13000,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('daily_recap_expenses', 3);
    }

    public function test_expense_update_ignores_itself_but_rejects_other_normalized_duplicates(): void
    {
        [$user, $employee, $recap, $session] = $this->openExpenseSession();

        $beras = DailyRecapExpense::create([
            'daily_recap_id' => $recap->id,
            'daily_recap_expense_session_id' => $session->id,
            'created_by_employee_id' => $employee->id,
            'name' => 'Beras',
            'amount' => 10000,
            'sort_order' => 0,
        ]);

        $minyak = DailyRecapExpense::create([
            'daily_recap_id' => $recap->id,
            'daily_recap_expense_session_id' => $session->id,
            'created_by_employee_id' => $employee->id,
            'name' => 'Minyak',
            'amount' => 15000,
            'sort_order' => 1,
        ]);

        $this->actingAs($user)
            ->put(route('employee.daily-recap-expenses.update', $beras), [
                'name' => '  BERAS  ',
                'amount' => 11000,
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($user)
            ->from(route('employee.daily-recap-expenses.index', ['date' => $session->recap_date->toDateString()]))
            ->put(route('employee.daily-recap-expenses.update', $minyak), [
                'name' => 'beras',
                'amount' => 16000,
            ])
            ->assertSessionHasErrors([
                'name' => 'Barang/pengeluaran tersebut sudah terinput pada rekap shift ini.',
            ]);

        $this->assertSame('BERAS', $beras->fresh()->name);
        $this->assertSame('Minyak', $minyak->fresh()->name);
    }

    private function openExpenseSession(): array
    {
        $user = User::factory()->create(['role' => 'employee']);
        $employee = Employee::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-001',
            'position' => 'Staff',
            'status' => 'active',
        ]);

        $recap = DailyRecap::create([
            'employee_id' => $employee->id,
            'recap_date' => today()->toDateString(),
            'total_expense_amount' => 0,
            'remaining_cash_amount' => 0,
            'total_qris_amount' => 0,
            'capital_amount' => 0,
        ]);

        $session = DailyRecapExpenseSession::create([
            'daily_recap_id' => $recap->id,
            'recap_date' => today()->toDateString(),
            'opened_by_employee_id' => $employee->id,
            'opened_at' => now(),
        ]);

        return [$user, $employee, $recap, $session];
    }
}
