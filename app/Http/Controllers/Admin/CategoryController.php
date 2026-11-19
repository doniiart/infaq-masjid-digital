<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InfaqCategory;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        $categories = InfaqCategory::withCount(['allocations', 'expenses'])
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get();

        return Inertia::render('Admin/Categories/Index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:150', 'unique:infaq_categories,name'],
            'description'   => ['nullable', 'string', 'max:500'],
            'target_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $category = InfaqCategory::create([
            'slug'          => Str::slug($validated['name']),
            'name'          => $validated['name'],
            'description'   => $validated['description'] ?? null,
            'target_amount' => $validated['target_amount'] ?? null,
            'is_default'    => false,
            'is_active'     => true,
        ]);

        AuditLogger::log('CATEGORY_CREATED', $category, null, $category->toArray());

        return back()->with('success', 'Kategori infaq baru berhasil ditambahkan.');
    }

    public function update(Request $request, InfaqCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:150', 'unique:infaq_categories,name,' . $category->id],
            'description'   => ['nullable', 'string', 'max:500'],
            'target_amount' => ['nullable', 'numeric', 'min:0'],
            'is_active'     => ['nullable', 'boolean'],
        ]);

        $oldValues = $category->only(['name', 'description', 'target_amount', 'is_active']);

        $isActive = $category->is_default ? true : (bool) ($validated['is_active'] ?? false);

        $category->update([
            'name'          => $validated['name'],
            'description'   => $validated['description'] ?? null,
            'target_amount' => $validated['target_amount'] ?? null,
            'is_active'     => $isActive,
        ]);

        AuditLogger::log(
            'CATEGORY_UPDATED',
            $category,
            $oldValues,
            $category->only(['name', 'description', 'target_amount', 'is_active'])
        );

        return back()->with('success', 'Kategori "' . $category->name . '" berhasil diperbarui.');
    }
}