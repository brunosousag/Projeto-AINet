<?php

namespace App\Http\Controllers;

use App\Http\Requests\ColorFormRequest;
use App\Models\Color;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ColorController extends Controller
{
    private const BaseTemplate = 'tshirt_base/fafafa.jpg';

    private const MaskTemplate = 'tshirt_base/f3f46b.jpg';

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
            'color' => new Color,
        ]);
    }

    public function store(ColorFormRequest $request): RedirectResponse
    {
        $color = Color::create($request->safe()->only(['code', 'name']));
        $this->saveBaseImage($request, $color);

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
        $color->update($request->safe()->only(['name']));
        $this->saveBaseImage($request, $color);

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

    private function saveBaseImage(ColorFormRequest $request, Color $color): void
    {
        if ($file = $request->file('base_image_file')) {
            Storage::disk('public')->putFileAs('tshirt_base', $file, "{$color->code}.jpg");

            return;
        }

        if (! Storage::disk('public')->exists("tshirt_base/{$color->code}.jpg")) {
            $this->generateBaseImage($color);
        }
    }

    private function generateBaseImage(Color $color): void
    {
        $hex = $this->normalizeHexColor($color->code);

        if ($hex === null || ! Storage::disk('public')->exists(self::BaseTemplate)) {
            return;
        }

        $sourcePath = Storage::disk('public')->path(self::BaseTemplate);
        $targetPath = Storage::disk('public')->path("tshirt_base/{$color->code}.jpg");
        $image = imagecreatefromjpeg($sourcePath);
        $mask = Storage::disk('public')->exists(self::MaskTemplate)
            ? imagecreatefromjpeg(Storage::disk('public')->path(self::MaskTemplate))
            : false;

        if ($image === false) {
            return;
        }

        [$targetR, $targetG, $targetB] = sscanf($hex, '%02x%02x%02x');
        $width = imagesx($image);
        $height = imagesy($image);
        $background = $mask !== false && imagesx($mask) === $width && imagesy($mask) === $height
            ? $this->backgroundMask($mask, $width, $height)
            : $this->backgroundMask($image, $width, $height);

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                if ($background[$y * $width + $x] === "\1") {
                    continue;
                }

                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                if (max($r, $g, $b) < 45) {
                    continue;
                }

                $brightness = ((0.2126 * $r) + (0.7152 * $g) + (0.0722 * $b)) / 255;
                $shade = max(0.45, min(1.25, $brightness / 0.82));

                $newColor = imagecolorallocate(
                    $image,
                    min(255, (int) round($targetR * $shade)),
                    min(255, (int) round($targetG * $shade)),
                    min(255, (int) round($targetB * $shade)),
                );

                imagesetpixel($image, $x, $y, $newColor);
            }
        }

        Storage::disk('public')->makeDirectory('tshirt_base');
        imagejpeg($image, $targetPath, 90);
        imagedestroy($image);

        if ($mask !== false) {
            imagedestroy($mask);
        }
    }

    private function normalizeHexColor(string $code): ?string
    {
        $code = strtolower(preg_replace('/[^0-9a-f]/i', '', $code) ?? '');

        if (strlen($code) === 3) {
            return $code[0].$code[0].$code[1].$code[1].$code[2].$code[2];
        }

        if (strlen($code) >= 6) {
            return substr($code, 0, 6);
        }

        return null;
    }

    private function backgroundMask(\GdImage $image, int $width, int $height): string
    {
        $background = str_repeat("\0", $width * $height);
        $queue = new \SplQueue;

        for ($x = 0; $x < $width; $x++) {
            $this->queueBackgroundPixel($image, $background, $queue, $x, 0, $width);
            $this->queueBackgroundPixel($image, $background, $queue, $x, $height - 1, $width);
        }

        for ($y = 0; $y < $height; $y++) {
            $this->queueBackgroundPixel($image, $background, $queue, 0, $y, $width);
            $this->queueBackgroundPixel($image, $background, $queue, $width - 1, $y, $width);
        }

        while (! $queue->isEmpty()) {
            [$x, $y] = $queue->dequeue();

            foreach ([[1, 0], [-1, 0], [0, 1], [0, -1]] as [$dx, $dy]) {
                $nextX = $x + $dx;
                $nextY = $y + $dy;

                if ($nextX < 0 || $nextX >= $width || $nextY < 0 || $nextY >= $height) {
                    continue;
                }

                $this->queueBackgroundPixel($image, $background, $queue, $nextX, $nextY, $width);
            }
        }

        return $background;
    }

    private function queueBackgroundPixel(\GdImage $image, string &$background, \SplQueue $queue, int $x, int $y, int $width): void
    {
        $index = $y * $width + $x;

        if ($background[$index] === "\1") {
            return;
        }

        $rgb = imagecolorat($image, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;

        if (! $this->isNearWhitePixel($r, $g, $b)) {
            return;
        }

        $background[$index] = "\1";
        $queue->enqueue([$x, $y]);
    }

    private function isNearWhitePixel(int $r, int $g, int $b): bool
    {
        return min($r, $g, $b) > 226 && (max($r, $g, $b) - min($r, $g, $b)) < 24;
    }
}
