<?php

namespace App\Http\Controllers;

use App\Http\Requests\ViolationCategoryRequest;
use App\Models\ViolationCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ViolationCategoryController extends Controller
{
    public function index(): View
    {
        $categories = ViolationCategory::query()
            ->withCount('reports')
            ->orderByDesc('is_active') // active first
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('categories.create', ['category' => new ViolationCategory(['is_active' => true])]);
    }

    public function store(ViolationCategoryRequest $request): RedirectResponse
    {
        ViolationCategory::create([
            ...$request->validated(),
            'is_active' => true,
        ]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Violation category added.');
    }

    public function edit(ViolationCategory $category): View
    {
        return view('categories.edit', compact('category'));
    }

    public function update(ViolationCategoryRequest $request, ViolationCategory $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()
            ->route('categories.index')
            ->with('success', 'Violation category updated.');
    }

    /**
     * Activate / deactivate. Categories are never deleted, so existing reports
     * keep their category. Deactivated ones simply stop appearing in the report form.
     */
    public function toggle(ViolationCategory $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        return redirect()
            ->route('categories.index')
            ->with('success', $category->is_active
                ? "\"{$category->name}\" is now active."
                : "\"{$category->name}\" has been deactivated.");
    }
}