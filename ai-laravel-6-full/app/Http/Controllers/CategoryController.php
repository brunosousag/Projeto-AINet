<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryFormRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('manage-catalog');

        $search = trim((string) $request->query('search', ''));

        $categories = Category::query()
            ->withCount('tshirtImages')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%$search%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('categories.index', [
            'categories' => $categories,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): View
    {
        Gate::authorize('manage-catalog');

        return view('categories.create', [
            'category' => new Category(),
        ]);
    }

    public function store(CategoryFormRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['image_file']);

        $category = Category::create($data);
        $this->storeImage($request, $category);

        return redirect()
            ->route('categories.index')
            ->with('alert-type', 'success')
            ->with('alert-msg', "Category '{$category->name}' has been created successfully.");
    }

    public function show(Category $category): View
    {
        Gate::authorize('manage-catalog');

        return view('categories.show', [
            'category' => $category->loadCount('tshirtImages'),
        ]);
    }

    public function edit(Category $category): View
    {
        Gate::authorize('manage-catalog');

        return view('categories.edit', [
            'category' => $category,
        ]);
    }

    public function update(CategoryFormRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();
        unset($data['image_file']);

        $category->update($data);
        $this->storeImage($request, $category);

        return redirect()
            ->route('categories.index')
            ->with('alert-type', 'success')
            ->with('alert-msg', "Category '{$category->name}' has been updated successfully.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        Gate::authorize('manage-catalog');

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('alert-type', 'success')
            ->with('alert-msg', "Category '{$category->name}' has been deleted successfully.");
    }

    public function destroyImage(Category $category): RedirectResponse
    {
        Gate::authorize('manage-catalog');

        if (! $category->image_url) {
            return redirect()
                ->back()
                ->with('alert-type', 'warning')
                ->with('alert-msg', "Category '{$category->name}' has no image to delete.");
        }

        $this->deleteImage($category);
        $category->update(['image_url' => null]);

        return redirect()
            ->back()
            ->with('alert-type', 'success')
            ->with('alert-msg', "Image of category '{$category->name}' has been deleted.");
    }

    private function storeImage(CategoryFormRequest $request, Category $category): void
    {
        if (! $request->hasFile('image_file')) {
            return;
        }

        $this->deleteImage($category);

        $file = $request->file('image_file');
        $filename = str_pad((string) $category->id, 5, '0', STR_PAD_LEFT)
            .'_'.Str::random(10).'.'.$file->extension();

        Storage::disk('public')->putFileAs('categories', $file, $filename);
        $category->update(['image_url' => $filename]);
    }

    private function deleteImage(Category $category): void
    {
        if (! $category->image_url || in_array($category->image_url, ['default_category.png', 'no_category.png'], true)) {
            return;
        }

        Storage::disk('public')->delete("categories/{$category->image_url}");
    }
}
