<?php

namespace App\Http\Controllers;

use App\Http\Requests\PersonalTshirtImageFormRequest;
use App\Models\Color;
use App\Models\TshirtImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PersonalTshirtImageController extends Controller
{
    public function index(Request $request): View
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

    public function create(): View
    {
        Gate::authorize('customer');

        return view('personal-tshirt-images.create');
    }

    public function store(PersonalTshirtImageFormRequest $request): RedirectResponse
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
        ]);

        return redirect()
            ->route('personal-tshirt-images.index')
            ->with('alert-type', 'success')
            ->with('alert-msg', "Personal image '{$tshirtImage->name}' uploaded successfully.");
    }

    public function destroy(Request $request, TshirtImage $personalTshirtImage): RedirectResponse
    {
        Gate::authorize('customer');
        abort_unless($personalTshirtImage->customer_id === $request->user()->id, 403);

        $personalTshirtImage->delete();

        return redirect()
            ->route('personal-tshirt-images.index')
            ->with('alert-type', 'success')
            ->with('alert-msg', "Personal image '{$personalTshirtImage->name}' deleted.");
    }
}
