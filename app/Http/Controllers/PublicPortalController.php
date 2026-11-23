<?php

namespace App\Http\Controllers;

use App\Models\FundAllocation;
use App\Models\InfaqCategory;
use App\Models\InfaqTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\DevelopmentMedia;
use App\Models\SiteSetting;

class PublicPortalController extends Controller
{
    public function home(): Response
    {
        $totalIncome = (float) InfaqTransaction::where('transaction_status', InfaqTransaction::STATUS_SETTLEMENT)
            ->sum('gross_amount');

        $totalExpense = (float) DB::table('expenses')->sum('amount');

        $developmentCategory = InfaqCategory::where('slug', 'pembangunan-masjid')
            ->orWhere('name', 'ilike', '%pembangunan%')
            ->first();

        $developmentIncome = $developmentCategory
            ? (float) FundAllocation::where('infaq_category_id', $developmentCategory->id)
                ->whereHas('transaction', fn ($q) => $q->where('transaction_status', InfaqTransaction::STATUS_SETTLEMENT))
                ->sum('allocated_amount')
            : 0.0;

        $recentTransactions = InfaqTransaction::with('allocations.category')
            ->where('transaction_status', InfaqTransaction::STATUS_SETTLEMENT)
            ->orderByDesc('transaction_time')
            ->limit(8)
            ->get()
            ->map(fn (InfaqTransaction $tx) => [
                'id'               => $tx->id,
                'reference'        => substr($tx->provider_transaction_id, 0, 12) . '...',
                'gross_amount'     => (float) $tx->gross_amount,
                'acquirer'         => strtoupper((string) ($tx->acquirer ?: $tx->payment_type)),
                'transaction_time' => $tx->transaction_time?->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB',
                'categories'       => $tx->allocations->map(fn ($a) => $a->category?->name)->filter()->values(),
            ]);

        return Inertia::render('Public/Home', [
            'stats' => [
                'total_income'       => $totalIncome,
                'total_expense'      => $totalExpense,
                'net_balance'        => $totalIncome - $totalExpense,
                'development_income' => $developmentIncome,
            ],
            'recentTransactions' => $recentTransactions,
            'hasOfficialQris'    => file_exists(public_path('images/qris-resmi-masjid.png')),
        ]);
    }

    public function transparency(Request $request): Response
    {
        $categories = InfaqCategory::orderBy('id')->get()->map(function (InfaqCategory $cat) {
            $income = (float) FundAllocation::where('infaq_category_id', $cat->id)
                ->whereHas('transaction', fn ($q) => $q->where('transaction_status', InfaqTransaction::STATUS_SETTLEMENT))
                ->sum('allocated_amount');

            $expense = (float) DB::table('expenses')
                ->where('infaq_category_id', $cat->id)
                ->sum('amount');

            return [
                'id'          => $cat->id,
                'name'        => $cat->name,
                'description' => $cat->description,
                'is_default'  => (bool) $cat->is_default,
                'income'      => $income,
                'expense'     => $expense,
                'balance'     => $income - $expense,
            ];
        });

        $transactions = InfaqTransaction::with('allocations.category')
            ->where('transaction_status', InfaqTransaction::STATUS_SETTLEMENT)
            ->orderByDesc('transaction_time')
            ->limit(25)
            ->get()
            ->map(fn (InfaqTransaction $tx) => [
                'id'               => $tx->id,
                'reference'        => substr($tx->provider_transaction_id, 0, 14) . '...',
                'gross_amount'     => (float) $tx->gross_amount,
                'acquirer'         => strtoupper((string) ($tx->acquirer ?: $tx->payment_type)),
                'transaction_time' => $tx->transaction_time?->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB',
                'categories'       => $tx->allocations->map(fn ($a) => $a->category?->name)->filter()->values(),
            ]);

        $expenses = DB::table('expenses')
            ->leftJoin('infaq_categories', 'expenses.infaq_category_id', '=', 'infaq_categories.id')
            ->select('expenses.*', 'infaq_categories.name as category_name')
            ->orderByDesc('expenses.expense_date')
            ->limit(25)
            ->get();

        return Inertia::render('Public/Transparency', [
            'categories'   => $categories,
            'transactions' => $transactions,
            'expenses'     => $expenses,
        ]);
    }

    public function development(): Response
    {
        $devCategories = InfaqCategory::where('slug', 'like', '%pembangunan%')
            ->orWhere('name', 'ilike', '%pembangunan%')
            ->get();

        $catIds = $devCategories->pluck('id');

        $collected = $catIds->isNotEmpty()
            ? (float) FundAllocation::whereIn('infaq_category_id', $catIds)
                ->whereHas('transaction', fn ($q) => $q->where('transaction_status', InfaqTransaction::STATUS_SETTLEMENT))
                ->sum('allocated_amount')
            : 0.0;

        $spent = $catIds->isNotEmpty()
            ? (float) DB::table('expenses')->whereIn('infaq_category_id', $catIds)->sum('amount')
            : 0.0;

        return Inertia::render('Public/Development', [
            'mediaList' => DevelopmentMedia::query()
                ->where('is_published', true)
                ->orderBy('sort_order')
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    public function profile(): Response
    {
        return Inertia::render('Public/Profile', [
            'tagline' => SiteSetting::getValue(
                'profile_tagline',
                "Pusat ibadah, pendidikan Al-Qur'an, dan pemberdayaan ekonomi umat."
            ),
            'history' => SiteSetting::getValue(
                'profile_history',
                'Masjid At Taqwa didirikan atas swadaya masyarakat dan wakaf tanah warga setempat sebagai sarana ibadah berjamaah serta pusat pembinaan keislaman umat. Seiring bertambahnya jumlah jamaah, takmir bersama panitia pembangunan menginisiasi perluasan ruang ibadah dan menerapkan pencatatan infaq berbasis QRIS digital agar pengelolaan dana umat dapat diaudit secara terbuka.'
            ),
            'committeeMembers' => SiteSetting::getCommitteeMembers(),
        ]);
    }
}