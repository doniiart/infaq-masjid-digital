<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Expense;
use App\Models\FundAllocation;
use App\Models\InfaqCategory;
use App\Models\InfaqTransaction;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $now = Carbon::now('Asia/Jakarta');

        $totalIncome = (float) InfaqTransaction::where('transaction_status', InfaqTransaction::STATUS_SETTLEMENT)
            ->sum('gross_amount');

        $settledCount = InfaqTransaction::where('transaction_status', InfaqTransaction::STATUS_SETTLEMENT)
            ->count();

        $todayCount = InfaqTransaction::where('transaction_status', InfaqTransaction::STATUS_SETTLEMENT)
            ->whereBetween('transaction_time', [$now->copy()->startOfDay(), $now->copy()->endOfDay()])
            ->count();

        $monthCount = InfaqTransaction::where('transaction_status', InfaqTransaction::STATUS_SETTLEMENT)
            ->whereBetween('transaction_time', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->count();

        $devCategory = InfaqCategory::where('slug', 'infaq-pembangunan')->first();
        $developmentFund = $devCategory
            ? (float) FundAllocation::where('infaq_category_id', $devCategory->id)
                ->whereHas('transaction', fn ($q) => $q->where('transaction_status', InfaqTransaction::STATUS_SETTLEMENT))
                ->sum('allocated_amount')
            : 0.0;

        $totalExpense = (float) Expense::sum('amount');
        $balance = $totalIncome - $totalExpense;

        // Grafik Transaksi Harian (7 Hari Terakhir)
        $dailyChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i);
            $sum = (float) InfaqTransaction::where('transaction_status', InfaqTransaction::STATUS_SETTLEMENT)
                ->whereBetween('transaction_time', [$day->copy()->startOfDay(), $day->copy()->endOfDay()])
                ->sum('gross_amount');

            $dailyChart[] = [
                'label'  => $day->format('d M'),
                'amount' => $sum,
            ];
        }

        // Grafik Pemasukan vs Pengeluaran Bulanan (6 Bulan Terakhir)
        $monthlyChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $incomeMonth = (float) InfaqTransaction::where('transaction_status', InfaqTransaction::STATUS_SETTLEMENT)
                ->whereBetween('transaction_time', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->sum('gross_amount');

            $expenseMonth = (float) Expense::whereBetween('expense_date', [
                $month->copy()->startOfMonth()->toDateString(),
                $month->copy()->endOfMonth()->toDateString(),
            ])->sum('amount');

            $monthlyChart[] = [
                'label'   => $month->format('M Y'),
                'income'  => $incomeMonth,
                'expense' => $expenseMonth,
            ];
        }

        $recentTransactions = InfaqTransaction::with('allocations.category')
            ->orderByDesc('transaction_time')
            ->limit(6)
            ->get();

        $recentActivities = AuditLog::with('user')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return Inertia::render('Dashboard', compact(
            'totalIncome',
            'settledCount',
            'todayCount',
            'monthCount',
            'developmentFund',
            'totalExpense',
            'balance',
            'dailyChart',
            'monthlyChart',
            'recentTransactions',
            'recentActivities'
        ));
    }
}