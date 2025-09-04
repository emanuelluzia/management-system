<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\CategoryService;
use App\Http\Requests\CategoryRequest;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService
    ) {}

    
    public function index(): Response
    {
        $categories = $this->categoryService->getAllCategoriesWithRelations();

        return Inertia::render('Categories/Index', [
            'categories' => $categories, 
        ]);
    }

    public function create(): Response
    {
        
        $rootCategories = $this->categoryService->getRootCategories();

        return Inertia::render('Categories/CategoryForm', [
            'rootCategories' => $rootCategories,
        ]);
    }

   
    public function store(CategoryRequest $request)
    {
        $this->categoryService->create($request->validated());

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoria criada com sucesso!');
    }

    
    public function show(Category $category): Response
    {
        $category->load(['parent:id,name', 'children:id,name,parent_id']);

        $tasks = \App\Models\Task::query()
            ->slim()
            ->with(['category:id,name'])
            ->where('category_id', $category->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Categories/Show', [
            'category' => $category,
            'tasks'    => \App\Http\Resources\TaskResource::collection($tasks),
        ]);
    }

    
    public function edit(Category $category): Response
    {
        $category->load(['parent:id,name']);

        $rootCategories = $this->categoryService->getRootCategories()
            ->reject(fn ($c) => (int) $c->id === (int) $category->id)
            ->values();

        return Inertia::render('Categories/CategoryForm', [
            'category'       => $category,
            'rootCategories' => $rootCategories,
        ]);
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $this->categoryService->update($category, $request->validated());

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        $this->categoryService->delete($category);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category removed.');
    }

    public function restore(int $id)
    {
        $this->categoryService->restore($id);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category restored.');
    }

    public function statistics(): \Inertia\Response|\Illuminate\Http\JsonResponse
    {
        [$stats, $totals] = $this->categoryService->getStatistics();

        if (request()->wantsJson()) {
            return response()->json([
                'stats'  => $stats,
                'totals' => $totals,
            ]);
        }

        return \Inertia\Inertia::render('Categories/Statistics', [
            'stats'  => $stats,
            'totals' => $totals,
        ]);
}
}
