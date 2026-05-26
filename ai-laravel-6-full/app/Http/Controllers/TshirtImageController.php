<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Color;
use App\Models\TshirtImage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\View\View;

class TshirtImageController extends Controller
{
    public function index(Request $request): View
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

        return view('tshirt-images.index', [
            'tshirtImages' => $tshirtImages,
            'categories' => Category::orderBy('name')->get(),
            'colors' => Color::orderBy('name')->get(),
            'sizes' => ['XS', 'S', 'M', 'L', 'XL'],
            'filters' => [
                'search' => $search,
                'category_id' => $categoryId,
            ],
        ]);
    }

    public function show(TshirtImage $tshirtImage): View
    {
        abort_if($tshirtImage->customer_id !== null, 404);

        return view('tshirt-images.show', [
            'tshirtImage' => $tshirtImage->load('category'),
            'colors' => Color::orderBy('name')->get(),
            'sizes' => ['XS', 'S', 'M', 'L', 'XL'],
        ]);
    }

    public function privateImage(Request $request, TshirtImage $tshirtImage): BinaryFileResponse
    {
        abort_if($tshirtImage->customer_id === null, 404);

        $user = $request->user();
        abort_unless(
            $user && ($user->isAdmin() || $user->isEmployee() || $user->id === $tshirtImage->customer_id),
            403
        );

        return response()->file(storage_path("app/private/tshirt_images_private/{$tshirtImage->image_url}"));
    }
}
