<?php

namespace App\Http\Controllers;

use App\Http\Requests\PersonalTshirtImageFormRequest;
use App\Http\Requests\TshirtImageFormRequest;
use App\Models\Category;
use App\Models\Color;
use App\Models\TshirtImage;
use App\Services\TshirtImageSnapshotService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TshirtImageController extends Controller
{
    public function shopIndex(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $categoryId = $request->query('category_id');

        $tshirtImages = TshirtImage::query()
            ->with('category')
            ->whereNull('customer_id')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%$search%")
                        ->orWhere('description', 'like', "%$search%");
                });
            })
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('shop.index', [
            'tshirtImages' => $tshirtImages,
            'categories' => Category::orderBy('name')->get(),
            'filters' => [
                'search' => $search,
                'category_id' => $categoryId,
            ],
        ]);
    }

    public function shopShow(TshirtImage $tshirtImage): View
    {
        abort_if($tshirtImage->customer_id !== null, 404);

        return view('shop.tshirt-details', [
            'tshirtImage' => $tshirtImage->load('category'),
            'colors' => Color::orderBy('name')->get(),
            'sizes' => ['XS', 'S', 'M', 'L', 'XL'],
        ]);
    }

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', TshirtImage::class);

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

        return view('tshirt-images.index', [
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
        Gate::authorize('create', TshirtImage::class);

        return view('tshirt-images.create', [
            'tshirtImage' => new TshirtImage,
            'categories' => Category::orderBy('name')->get(),
            'colors' => Color::orderBy('name')->get(),
        ]);
    }

    public function store(TshirtImageFormRequest $request): RedirectResponse
    {
        Gate::authorize('create', TshirtImage::class);

        $data = $request->validated();
        unset($data['image_file']);

        $tshirtImage = TshirtImage::create([
            'customer_id' => null,
            'category_id' => $data['category_id'] ?? null,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'image_url' => 'placeholder.png',
            'custom' => $this->previewSettings($request),
        ]);

        $this->storeImage($request, $tshirtImage);

        return redirect()
            ->route('tshirt-images.index')
            ->with('alert-type', 'success')
            ->with('alert-msg', "Catalog image '{$tshirtImage->name}' has been created successfully.");
    }

    public function show(TshirtImage $tshirtImage): View
    {
        Gate::authorize('view', $tshirtImage);

        return view('tshirt-images.show', [
            'tshirtImage' => $tshirtImage->load('category')->loadCount('orderItems'),
        ]);
    }

    public function edit(TshirtImage $tshirtImage): View
    {
        Gate::authorize('update', $tshirtImage);

        return view('tshirt-images.edit', [
            'tshirtImage' => $tshirtImage,
            'categories' => Category::orderBy('name')->get(),
            'colors' => Color::orderBy('name')->get(),
        ]);
    }

    public function update(
        TshirtImageFormRequest $request,
        TshirtImage $tshirtImage,
        TshirtImageSnapshotService $snapshotService
    ): RedirectResponse {
        Gate::authorize('update', $tshirtImage);
        $snapshotService->preserveExistingOrders($tshirtImage);

        $data = $request->validated();
        unset($data['image_file'], $data['preview_top'], $data['preview_width'], $data['preview_height'], $data['preview_opacity']);
        $data['custom'] = $this->previewSettings($request);

        $tshirtImage->update($data);
        $this->storeImage($request, $tshirtImage);

        return redirect()
            ->route('tshirt-images.index')
            ->with('alert-type', 'success')
            ->with('alert-msg', "Catalog image '{$tshirtImage->name}' has been updated successfully.");
    }

    public function destroy(TshirtImage $tshirtImage, TshirtImageSnapshotService $snapshotService): RedirectResponse
    {
        Gate::authorize('delete', $tshirtImage);
        $snapshotService->preserveExistingOrders($tshirtImage);

        $tshirtImage->delete();

        return redirect()
            ->route('tshirt-images.index')
            ->with('alert-type', 'success')
            ->with('alert-msg', "Catalog image '{$tshirtImage->name}' has been deleted successfully.");
    }

    public function personalIndex(Request $request): View
    {
        Gate::authorize('customer');

        $search = trim((string) $request->query('search', ''));
        $tshirtImages = TshirtImage::query()
            ->where('customer_id', $request->user()->id)
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%$search%")
                        ->orWhere('description', 'like', "%$search%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('personal-tshirt-images.index', [
            'tshirtImages' => $tshirtImages,
            'colors' => Color::orderBy('name')->get(),
            'sizes' => ['XS', 'S', 'M', 'L', 'XL'],
            'filters' => ['search' => $search],
        ]);
    }

    public function personalCreate(): View
    {
        Gate::authorize('customer');

        return view('personal-tshirt-images.create', [
            'colors' => Color::orderBy('name')->get(),
        ]);
    }

    public function personalStore(PersonalTshirtImageFormRequest $request): RedirectResponse
    {
        $file = $request->file('image_file');
        $filename = str_pad((string) $request->user()->id, 5, '0', STR_PAD_LEFT)
            .'_'.Str::random(10).'.'.$file->extension();

        Storage::disk('local')->putFileAs('tshirt_images_private', $file, $filename);

        $tshirtImage = TshirtImage::create([
            'customer_id' => $request->user()->id,
            'category_id' => null,
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'image_url' => $filename,
            'custom' => $this->personalPreviewSettings($request),
        ]);

        return redirect()
            ->route('personal-tshirt-images.index')
            ->with('alert-type', 'success')
            ->with('alert-msg', "Personal image '{$tshirtImage->name}' uploaded successfully.");
    }

    public function personalEdit(Request $request, TshirtImage $personal_tshirt_image): View
    {
        $this->authorizeOwnership($request, $personal_tshirt_image);

        return view('personal-tshirt-images.edit', [
            'tshirtImage' => $personal_tshirt_image,
            'colors' => Color::orderBy('name')->get(),
        ]);
    }

    public function personalUpdate(
        PersonalTshirtImageFormRequest $request,
        TshirtImage $personal_tshirt_image,
        TshirtImageSnapshotService $snapshotService
    ): RedirectResponse {
        $this->authorizeOwnership($request, $personal_tshirt_image);
        $snapshotService->preserveExistingOrders($personal_tshirt_image);

        $data = [
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'custom' => $this->personalPreviewSettings($request),
        ];

        if ($file = $request->file('image_file')) {
            $filename = str_pad((string) $request->user()->id, 5, '0', STR_PAD_LEFT)
                .'_'.Str::random(10).'.'.$file->extension();
            Storage::disk('local')->putFileAs('tshirt_images_private', $file, $filename);
            $data['image_url'] = $filename;
        }

        $personal_tshirt_image->update($data);

        return redirect()
            ->route('personal-tshirt-images.index')
            ->with('alert-type', 'success')
            ->with('alert-msg', "Personal image '{$personal_tshirt_image->name}' updated successfully.");
    }

    public function personalDestroy(
        Request $request,
        TshirtImage $personal_tshirt_image,
        TshirtImageSnapshotService $snapshotService
    ): RedirectResponse {
        $this->authorizeOwnership($request, $personal_tshirt_image);
        $snapshotService->preserveExistingOrders($personal_tshirt_image);

        $personal_tshirt_image->delete();

        return redirect()
            ->route('personal-tshirt-images.index')
            ->with('alert-type', 'success')
            ->with('alert-msg', "Personal image '{$personal_tshirt_image->name}' deleted.");
    }

    public function privateImage(Request $request, TshirtImage $tshirtImage): BinaryFileResponse
    {
        abort_if($tshirtImage->customer_id === null, 404);

        $user = $request->user();
        abort_unless($user && $user->id === $tshirtImage->customer_id, 403);

        $filename = $tshirtImage->image_url;
        abort_unless($filename && basename($filename) === $filename, 404);

        $path = Storage::disk('local')->path("tshirt_images_private/{$filename}");
        abort_unless(is_file($path), 404);

        return response()->file($path);
    }

    private function storeImage(TshirtImageFormRequest $request, TshirtImage $tshirtImage): void
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

    /**
     * @return array<string, int>
     */
    private function previewSettings(TshirtImageFormRequest $request): array
    {
        return [
            'preview_top' => (int) $request->validated('preview_top'),
            'preview_width' => (int) $request->validated('preview_width'),
            'preview_height' => (int) $request->validated('preview_height'),
            'preview_opacity' => (int) $request->validated('preview_opacity'),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function personalPreviewSettings(PersonalTshirtImageFormRequest $request): array
    {
        return [
            'preview_top' => (int) $request->validated('preview_top'),
            'preview_width' => (int) $request->validated('preview_width'),
            'preview_height' => (int) $request->validated('preview_height'),
            'preview_opacity' => (int) $request->validated('preview_opacity'),
        ];
    }

    private function authorizeOwnership(Request $request, TshirtImage $personalTshirtImage): void
    {
        Gate::authorize('customer');
        abort_unless($personalTshirtImage->customer_id === $request->user()->id, 403);
    }
}
