<?php

namespace App\Http\Controllers;

use App\Models\AccountVoucher;
use App\Models\Attendance;
use App\Models\ChartOfAccount;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\SalaryPayment;
use App\Models\User;
use App\Services\PartyAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function index(): View
    {
        return view('admin.payrolls.index', ['payrolls' => Payroll::query()->withCount('items')->latest('period_start')->paginate(15)]);
    }

    public function create(Request $request): View
    {
        $month = Carbon::parse($request->query('month', today()->startOfMonth()->toDateString()))->startOfMonth();
        return view('admin.payrolls.create', ['month' => $month, 'preview' => $this->calculate($month)]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['month' => ['required', 'date_format:Y-m']]);
        $month = Carbon::createFromFormat('Y-m', $data['month'])->startOfMonth();

        $payroll = DB::transaction(function () use ($month, $request): Payroll {
            $payroll = Payroll::query()->firstOrCreate(['period_start' => $month->toDateString()], ['period_end' => $month->copy()->endOfMonth()->toDateString(), 'status' => 'draft', 'created_by' => $request->user()->id]);
            if ($payroll->items()->exists()) return $payroll;
            $items = $this->calculate($month)->map(fn (array $row) => [...collect($row)->except('name')->all(), 'payroll_id' => $payroll->id]);
            $payroll->items()->createMany($items->all());
            $payroll->update(['total_net_salary' => $items->sum('net_salary')]);
            return $payroll;
        });

        return redirect()->route('payrolls.show', $payroll)->with('success', 'Attendance-based payroll sheet generated successfully.');
    }

    public function show(Payroll $payroll): View
    {
        $payroll->load(['items.employee', 'items.payments.cashAccount', 'voucher.entries.account']);
        return view('admin.payrolls.show', compact('payroll'));
    }

    public function post(Request $request, Payroll $payroll): RedirectResponse
    {
        DB::transaction(function () use ($payroll, $request) {
            $payroll = Payroll::query()->lockForUpdate()->with('items.employee')->findOrFail($payroll->id);
            if ($payroll->status !== 'draft') throw ValidationException::withMessages(['payroll' => 'Only a draft payroll can be posted.']);
            if ($payroll->items->isEmpty()) throw ValidationException::withMessages(['payroll' => 'Payroll has no employee items.']);
            $salaryExpense = ChartOfAccount::query()->where('code', '5000202')->where('is_active', true)->where('is_transactional', true)->firstOrFail();
            $amount = round((float) $payroll->items->sum('net_salary'), 2);
            $voucher = AccountVoucher::create(['voucher_number' => $this->voucherNumber('journal'), 'voucher_type' => 'journal', 'voucher_date' => $payroll->period_end, 'narration' => 'Salary accrual for '.$payroll->period_start->format('F Y'), 'amount' => $amount, 'user_id' => $request->user()->id]);
            $entries = [['chart_of_account_id' => $salaryExpense->id, 'debit' => $amount, 'credit' => 0, 'note' => 'Salary expense']];
            foreach ($payroll->items as $item) {
                $account = app(PartyAccountService::class)->forEmployee($item->employee);
                $entries[] = ['chart_of_account_id' => $account->id, 'debit' => 0, 'credit' => (float) $item->net_salary, 'note' => 'Salary payable: '.$item->employee->name];
            }
            $voucher->entries()->createMany($entries);
            $payroll->update(['status' => 'posted', 'account_voucher_id' => $voucher->id, 'posted_by' => $request->user()->id, 'posted_at' => now(), 'total_net_salary' => $amount]);
        });

        return redirect()->route('payrolls.show', $payroll)->with('success', 'Payroll posted. Salary expense and employee payables were recorded.');
    }

    public function paymentCreate(PayrollItem $item): View
    {
        abort_unless($item->payroll->status === 'posted' && $item->due > 0, 422, 'This payroll item has no payable salary.');
        return view('admin.payrolls.payment-create', ['item' => $item->load('employee', 'payroll'), 'cashAccounts' => ChartOfAccount::query()->whereIn('code', ['1000101', '1000102', '1000103'])->where('is_active', true)->where('is_transactional', true)->orderBy('code')->get()]);
    }

    public function paymentStore(Request $request, PayrollItem $item): RedirectResponse
    {
        $data = $request->validate(['payment_date' => ['required', 'date'], 'cash_account_id' => ['required', 'exists:chart_of_accounts,id'], 'amount' => ['required', 'numeric', 'min:0.01'], 'notes' => ['nullable', 'string', 'max:1000']]);
        $payment = DB::transaction(function () use ($data, $item, $request): SalaryPayment {
            $item = PayrollItem::query()->lockForUpdate()->with(['employee', 'payroll'])->findOrFail($item->id);
            if ($item->payroll->status !== 'posted' || (float) $data['amount'] > $item->due + 0.001) throw ValidationException::withMessages(['amount' => 'Payment cannot exceed the remaining salary payable.']);
            $cash = ChartOfAccount::query()->whereKey($data['cash_account_id'])->whereIn('code', ['1000101', '1000102', '1000103'])->where('is_active', true)->where('is_transactional', true)->firstOrFail();
            $employeeAccount = app(PartyAccountService::class)->forEmployee($item->employee);
            $amount = round((float) $data['amount'], 2);
            $voucher = AccountVoucher::create(['voucher_number' => $this->voucherNumber('debit'), 'voucher_type' => 'debit', 'voucher_date' => $data['payment_date'], 'narration' => 'Salary payment to '.$item->employee->name, 'amount' => $amount, 'user_id' => $request->user()->id]);
            $voucher->entries()->createMany([['chart_of_account_id' => $employeeAccount->id, 'debit' => $amount, 'credit' => 0], ['chart_of_account_id' => $cash->id, 'debit' => 0, 'credit' => $amount]]);
            $item->increment('paid_amount', $amount);
            return SalaryPayment::create(['payment_number' => $this->paymentNumber(), 'payroll_item_id' => $item->id, 'cash_account_id' => $cash->id, 'account_voucher_id' => $voucher->id, 'payment_date' => $data['payment_date'], 'amount' => $amount, 'notes' => blank($data['notes'] ?? null) ? null : $data['notes'], 'user_id' => $request->user()->id]);
        });

        return redirect()->route('salary-payments.show', $payment)->with('success', 'Salary payment saved and employee payable settled.');
    }

    public function paymentShow(SalaryPayment $payment): View
    {
        $payment->load(['item.employee', 'item.payroll', 'cashAccount', 'voucher.entries.account', 'creator']);
        return view('admin.payrolls.payment-show', compact('payment'));
    }

    private function calculate(Carbon $month)
    {
        $days = $month->daysInMonth;
        $attendance = Attendance::query()->whereBetween('attendance_date', [$month->toDateString(), $month->copy()->endOfMonth()->toDateString()])->get()->groupBy('user_id');
        return User::query()->where('is_active', true)->whereNotNull('employee_code')->orderBy('name')->get()->map(function (User $employee) use ($attendance, $days) {
            $records = $attendance->get($employee->id, collect());
            $paidDays = $records->isEmpty() ? $days : $records->sum(fn ($record) => match ($record->status) { 'present', 'leave' => 1, 'half_day' => .5, default => 0 });
            $gross = round((float) $employee->salary / $days * $paidDays, 2);
            return ['user_id' => $employee->id, 'name' => $employee->name, 'calendar_days' => $days, 'paid_days' => $paidDays, 'monthly_salary' => (float) $employee->salary, 'gross_salary' => $gross, 'deduction' => 0, 'net_salary' => $gross, 'paid_amount' => 0];
        });
    }

    private function voucherNumber(string $type): string { $prefix = $type === 'debit' ? 'DV' : 'JV'; return sprintf('%s-%s-%05d', $prefix, now()->format('Ym'), AccountVoucher::query()->where('voucher_type', $type)->lockForUpdate()->count() + 1); }
    private function paymentNumber(): string { return sprintf('SALPAY-%s-%05d', now()->format('Ym'), SalaryPayment::query()->lockForUpdate()->count() + 1); }
}
