<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\FundAllocation;
use App\Models\InfaqCategory;
use App\Models\InfaqTransaction;
use Illuminate\View\View;

class PublicPortalController extends Controller
{
    public function home(): View
    {
        $summary = $this->getFinancialSummary();

        $recentTransactions = InfaqTransaction::query()
            ->where('transaction_status', InfaqTransaction::STATUS_SETTLEMENT)
            ->orderByDesc('transaction_time')
            ->limit(5)
            ->get();

        $recentExpenses = Expense::query()
            ->with('category')
            ->orderByDesc('expense_date')
            ->limit(4)
            ->get();

        return view('public.home', compact('summary', 'recentTransactions', 'recentExpenses'));
    }

    public function profile(): View
    {
        return view('public.profile');
    }

    public function development(): View
    {
        $summary = $this->getFinancialSummary();

        $developmentExpenses = Expense::query()
            ->whereHas('category', fn ($q) => $q->where('slug', 'infaq-pembangunan'))
            ->orderByDesc('expense_date')
            ->limit(10)
            ->get();

        return view('public.development', compact('summary', 'developmentExpenses'));
    }

    public function transparency(): View
    {
        $summary = $this->getFinancialSummary();

        $categories = InfaqCategory::query()
            ->where('is_active', true)
            ->get()
            ->map(function (InfaqCategory $category) {
                $allocatedIncome = (float) FundAllocation::query()
                    ->where('infaq_category_id', $category->id)
                    ->whereHas('transaction', fn ($q) => $q->where('transaction_status', InfaqTransaction::STATUS_SETTLEMENT))
                    ->sum('allocated_amount');

                $totalExpense = (float) Expense::query()
                    ->where('infaq_category_id', $category->id)
                    ->sum('amount');

                return [
                    'name'          => $category->name,
                    'slug'          => $category->slug,
                    'description'   => $category->description,
                    'target_amount' => $category->target_amount ? (float) $category->target_amount : null,
                    'income'        => $allocatedIncome,
                    'expense'       => $totalExpense,
                    'balance'       => $allocatedIncome - $totalExpense,
                ];
            });

        $transactions = InfaqTransaction::query()
            ->with('allocations.category')
            ->where('transaction_status', InfaqTransaction::STATUS_SETTLEMENT)
            ->orderByDesc('transaction_time')
            ->paginate(15, ['*'], 'trx_page');

        $expenses = Expense::query()
            ->with('category')
            ->orderByDesc('expense_date')
            ->paginate(15, ['*'], 'exp_page');

        return view('public.transparency', compact('summary', 'categories', 'transactions', 'expenses'));
    }

    private function getFinancialSummary(): array
    {
        $totalIncome = (float) InfaqTransaction::query()
            ->where('transaction_status', InfaqTransaction::STATUS_SETTLEMENT)
            ->sum('gross_amount');

        $totalExpense = (float) Expense::query()->sum('amount');

        $developmentCategory = InfaqCategory::where('slug', 'infaq-pembangunan')->first();
        $developmentTarget = $developmentCategory && $developmentCategory->target_amount
            ? (float) $developmentCategory->target_amount
            : 500000000.0;

        $developmentCollected = 0.0;
        if ($developmentCategory) {
            $developmentCollected = (float) FundAllocation::query()
                ->where('infaq_category_id', $developmentCategory->id)
                ->whereHas('transaction', fn ($q) => $q->where('transaction_status', InfaqTransaction::STATUS_SETTLEMENT))
                ->sum('allocated_amount');
        }

        $progressPercent = $developmentTarget > 0
            ? min(100, round(($developmentCollected / $developmentTarget) * 100, 1))
            : 0;

        return [
            'total_income'          => $totalIncome,
            'total_expense'         => $totalExpense,
            'current_balance'       => $totalIncome - $totalExpense,
            'settled_count'         => InfaqTransaction::where('transaction_status', InfaqTransaction::STATUS_SETTLEMENT)->count(),
            'development_target'    => $developmentTarget,
            'development_collected' => $developmentCollected,
            'progress_percent'      => $progressPercent,
        ];
    }
}