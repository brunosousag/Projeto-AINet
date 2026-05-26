<?php

namespace App\Http\Controllers;

use App\Http\Requests\ColorFormRequest;
use App\Models\Color;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ColorController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('manage-catalog');

        $search = trim((string) $request->query('search', ''));

        $colors = Color::query()
            ->withCount('orderItems')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where('name', 'like', "%$search%")
                    ->orWhere('code', 'like', "%$search%");
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('colors.index', [
            'colors' => $colors,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): View
    {
        Gate::authorize('manage-catalog');

        return view('colors.create', [
            'color' => new Color(),
        ]);
    }

    public function store(ColorFormRequest $request): RedirectResponse
    {
        $color = Color::create($request->validated());

        return redirect()
            ->route('colors.index')
            ->with('alert-type', 'success')
            ->with('alert-msg', "Color '{$color->name}' has been created successfully.");
    }

    public function show(Color $color): View
    {
        Gate::authorize('manage-catalog');

        return view('colors.show', [
            'color' => $color->loadCount('orderItems'),
        ]);
    }

    public function edit(Color $color): View
    {
        Gate::authorize('manage-catalog');

        return view('colors.edit', [
            'color' => $color,
        ]);
    }

    public function update(ColorFormRequest $request, Color $color): RedirectResponse
    {
        $color->update($request->validated());

        return redirect()
            ->route('colors.index')
            ->with('alert-type', 'success')
            ->with('alert-msg', "Color '{$color->name}' has been updated successfully.");
    }

    public function destroy(Color $color): RedirectResponse
    {
        Gate::authorize('manage-catalog');

        $color->delete();

        return redirect()
            ->route('colors.index')
            ->with('alert-type', 'success')
            ->with('alert-msg', "Color '{$color->name}' has been deleted successfully.");
    }
}
