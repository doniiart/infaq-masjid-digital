<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\InfaqCategory;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseController extends Controller
{
    public function index(Request $request): Response
    {
        $expenses = Expense::with(['category', 'creator'])
            ->when($request->filled('category_id'), fn ($q) => $q->where('infaq_category_id', $request->input('category_id')))
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $categories = InfaqCategory::where('is_active', true)
            ->where('is_default', false)
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Expenses/Index', compact('expenses', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'infaq_category_id' => ['required', 'exists:infaq_categories,id'],
            'expense_date'      => ['required', 'date'],
            'amount'            => ['required', 'numeric', 'min:1'],
            'title'             => ['required', 'string', 'max:200'],
            'description'       => ['nullable', 'string', 'max:1000'],
            'receipt'           => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        }

        $expense = Expense::create([
            'infaq_category_id' => $validated['infaq_category_id'],
            'expense_date'      => $validated['expense_date'],
            'amount'            => $validated['amount'],
            'title'             => $validated['title'],
            'description'       => $validated['description'] ?? null,
            'receipt_path'      => $receiptPath,
            'created_by'        => Auth::id(),
        ]);

        AuditLogger::log('EXPENSE_CREATED', $expense, null, $expense->toArray());

        return back()->with('success', 'Pengeluaran "' . $expense->title . '" berhasil dicatat.');
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $validated = $request->validate([
            'infaq_category_id' => ['required', 'exists:infaq_categories,id'],
            'expense_date'      => ['required', 'date'],
            'amount'            => ['required', 'numeric', 'min:1'],
            'title'             => ['required', 'string', 'max:200'],
            'description'       => ['nullable', 'string', 'max:1000'],
            'receipt'           => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        $oldValues = $expense->only(['infaq_category_id', 'expense_date', 'amount', 'title', 'description', 'receipt_path']);

        $receiptPath = $expense->receipt_path;
        if ($request->hasFile('receipt')) {
            if ($receiptPath) {
                Storage::disk('public')->delete($receiptPath);
            }
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        }

        $expense->update([
            'infaq_category_id' => $validated['infaq_category_id'],
            'expense_date'      => $validated['expense_date'],
            'amount'            => $validated['amount'],
            'title'             => $validated['title'],
            'description'       => $validated['description'] ?? null,
            'receipt_path'      => $receiptPath,
        ]);

        AuditLogger::log('EXPENSE_UPDATED', $expense, $oldValues, $expense->fresh()->only(array_keys($oldValues)));

        return back()->with('success', 'Data pengeluaran berhasil diperbarui.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $snapshot = $expense->toArray();

        if ($expense->receipt_path) {
            Storage::disk('public')->delete($expense->receipt_path);
        }

        $expense->delete();

        AuditLogger::log('EXPENSE_DELETED', null, $snapshot, null);

        return back()->with('success', 'Catatan pengeluaran berhasil dihapus dan tercatat di Audit Log.');
    }
}