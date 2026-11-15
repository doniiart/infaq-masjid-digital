<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\FundAllocation;
use App\Models\InfaqCategory;
use App\Models\InfaqTransaction;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinancialReportController extends Controller
{
    public function index(Request $request): View
    {
        $report = $this->compileReport($request);

        return view('admin.reports.index', $report);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $report = $this->compileReport($request);

        AuditLogger::log('REPORT_EXPORTED_CSV', null, null, [
            'period' => $report['period'],
            'start'  => $report['startDate']->toDateString(),
            'end'    => $report['endDate']->toDateString(),
        ]);

        $filename = 'laporan-keuangan-masjid-' . $report['startDate']->format('Ymd') . '-' . $report['endDate']->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($report) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['LAPORAN KEUANGAN INFAQ MASJID']);
            fputcsv($handle, ['Periode', $report['startDate']->format('d M Y') . ' s/d ' . $report['endDate']->format('d M Y')]);
            fputcsv($handle, ['Total Pemasukan (Settlement)', $report['totalIncome']]);
            fputcsv($handle, ['Total Pengeluaran', $report['totalExpense']]);
            fputcsv($handle, ['Surplus / Defisit Periode', $report['netBalance']]);
            fputcsv($handle, []);
            fputcsv($handle, ['REKAP PER KATEGORI']);
            fputcsv($handle, ['Kategori', 'Pemasukan Dialokasikan', 'Pengeluaran', 'Selisih']);

            foreach ($report['categorySummary'] as $row) {
                fputcsv($handle, [$row['name'], $row['income'], $row['expense'], $row['balance']]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function printView(Request $request): View
    {
        $report = $this->compileReport($request);

        AuditLogger::log('REPORT_PRINTED_PDF', null, null, [
            'period' => $report['period'],
            'start'  => $report['startDate']->toDateString(),
            'end'    => $report['endDate']->toDateString(),
        ]);

        return view('admin.reports.print', $report);
    }

    private function compileReport(Request $request): array
    {
        $now = Carbon::now('Asia/Jakarta');
        $period = $request->input('period', 'monthly');

        [$startDate, $endDate] = match ($period) {
            'daily'   => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'weekly'  => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'yearly'  => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'custom'  => [
                Carbon::parse($request->input('start_date', $now->toDateString()), 'Asia/Jakarta')->startOfDay(),
                Carbon::parse($request->input('end_date', $now->toDateString()), 'Asia/Jakarta')->endOfDay(),
            ],
            default   => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };

        $transactions = InfaqTransaction::with('allocations.category')
            ->where('transaction_status', InfaqTransaction::STATUS_SETTLEMENT)
            ->whereBetween('transaction_time', [$startDate, $endDate])
            ->orderByDesc('transaction_time')
            ->get();

        $expenses = Expense::with('category')
            ->whereBetween('expense_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderByDesc('expense_date')
            ->get();

        $totalIncome = (float) $transactions->sum('gross_amount');
        $totalExpense = (float) $expenses->sum('amount');

        $categorySummary = InfaqCategory::where('is_active', true)->get()->map(function (InfaqCategory $category) use ($startDate, $endDate) {
            $income = (float) FundAllocation::where('infaq_category_id', $category->id)
                ->whereHas('transaction', function ($q) use ($startDate, $endDate) {
                    $q->where('transaction_status', InfaqTransaction::STATUS_SETTLEMENT)
                      ->whereBetween('transaction_time', [$startDate, $endDate]);
                })
                ->sum('allocated_amount');

            $expense = (float) Expense::where('infaq_category_id', $category->id)
                ->whereBetween('expense_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->sum('amount');

            return [
                'name'    => $category->name,
                'income'  => $income,
                'expense' => $expense,
                'balance' => $income - $expense,
            ];
        });

        return [
            'period'          => $period,
            'startDate'       => $startDate,
            'endDate'         => $endDate,
            'transactions'    => $transactions,
            'expenses'        => $expenses,
            'totalIncome'     => $totalIncome,
            'totalExpense'    => $totalExpense,
            'netBalance'      => $totalIncome - $totalExpense,
            'categorySummary' => $categorySummary,
        ];
    }
}