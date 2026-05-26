<?php

namespace App\Http\Controllers;

use App\Http\Requests\CatalogTshirtImageFormRequest;
use App\Models\Category;
use App\Models\TshirtImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CatalogTshirtImageController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('manage-catalog');

        $search = trim((string) $request->query('search', ''));
        $categoryId = $request->query('category_id');

        $tshirtImages = TshirtImage::query()
            ->with('category')
            ->withCount('orderItems')
            ->whereNull('customer_id')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%$search%")
                        ->orWhere('description', 'like', "%$search%");
                });
            })
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('catalog-tshirt-images.index', [
            'tshirtImages' => $tshirtImages,
            'categories' => Category::orderBy('name')->get(),
            'filters' => [
                'search' => $search,
                'category_id' => $categoryId,
            ],
        ]);
    }

    public function create(): View
    {
        Gate::authorize('manage-catalog');

        return view('catalog-tshirt-images.create', [
            'tshirtImage' => new TshirtImage(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(CatalogTshirtImageFormRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['image_file']);

        $tshirtImage = TshirtImage::create([
            'customer_id' => null,
            'category_id' => $data['category_id'] ?? null,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'image_url' => 'placeholder.png',
        ]);

        $this->storeImage($request, $tshirtImage);

        return redirect()
            ->route('catalog-images.index')
            ->with('alert-type', 'success')
            ->with('alert-msg', "Catalog image '{$tshirtImage->name}' has been created successfully.");
    }

    public function show(TshirtImage $catalogImage): View
    {
        Gate::authorize('manage-catalog');
        abort_unless($catalogImage->customer_id === null, 404);

        return view('catalog-tshirt-images.show', [
            'tshirtImage' => $catalogImage->load('category')->loadCount('orderItems'),
        ]);
    }

    public function edit(TshirtImage $catalogImage): View
    {
        Gate::authorize('manage-catalog');
        abort_unless($catalogImage->customer_id === null, 404);

        return view('catalog-tshirt-images.edit', [
            'tshirtImage' => $catalogImage,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(CatalogTshirtImageFormRequest $request, TshirtImage $catalogImage): RedirectResponse
    {
        abort_unless($catalogImage->customer_id === null, 404);

        $data = $request->validated();
        unset($data['image_file']);

        $catalogImage->update($data);
        $this->storeImage($request, $catalogImage);

        return redirect()
            ->route('catalog-images.index')
            ->with('alert-type', 'success')
            ->with('alert-msg', "Catalog image '{$catalogImage->name}' has been updated successfully.");
    }

    public function destroy(TshirtImage $catalogImage): RedirectResponse
    {
        Gate::authorize('manage-catalog');
        abort_unless($catalogImage->customer_id === null, 404);

        $catalogImage->delete();

        return redirect()
            ->route('catalog-images.index')
            ->with('alert-type', 'success')
            ->with('alert-msg', "Catalog image '{$catalogImage->name}' has been deleted successfully.");
    }

    private function storeImage(CatalogTshirtImageFormRequest $request, TshirtImage $tshirtImage): void
    {
        if (! $request->hasFile('image_file')) {
            return;
        }

        $file = $request->file('image_file');
        $filename = str_pad((string) $tshirtImage->id, 5, '0', STR_PAD_LEFT)
            .'_'.Str::random(10).'.'.$file->extension();

        Storage::disk('public')->putFileAs('tshirt_images', $file, $filename);
        $tshirtImage->update(['image_url' => $filename]);
    }
}
