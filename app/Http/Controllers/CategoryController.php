<?php

namespace App\Http\Controllers;

use App\Models\DocumentCategory;
use App\Models\Office;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $categories = DocumentCategory::query()
            ->withCount(['documents' => fn ($q) => $q->visibleTo($user)])
            ->orderBy('name')
            ->get();

        $offices = Office::query()
            ->with(['departments' => fn ($q) => $q->withCount(['documents' => fn ($dq) => $dq->visibleTo($user)])->orderBy('name')])
            ->withCount([
                'users',
                'documents' => fn ($q) => $q->visibleTo($user),
            ])
            ->orderBy('name')
            ->get();

        $activeTab = $request->query('tab') === 'offices' ? 'offices' : 'categories';

        return view('categories.index', compact('categories', 'offices', 'activeTab'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $request->user()->hasRole(['system_admin', 'records_manager'])) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'color_code' => ['required', 'string', 'max:20'],
            'retention_years' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $slug = Str::slug($validated['name']);
        $category = DocumentCategory::create([
            'slug' => DocumentCategory::where('slug', $slug)->exists() ? $slug.'-'.uniqid() : $slug,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'color_code' => $validated['color_code'],
            'retention_years' => (int) $validated['retention_years'],
            'is_active' => true,
        ]);

        $this->auditService->log(
            'category.created',
            "Created document category: {$category->name}",
            null,
            ['category_id' => $category->id],
            'info',
            $request->user()
        );

        return redirect()
            ->route('categories.index', ['tab' => 'categories'])
            ->with('success', "Category '{$category->name}' created.");
    }
}
