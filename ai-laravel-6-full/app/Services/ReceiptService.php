<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReceiptService
{
    private const FirstPageItems = 6;

    private const FollowingPageItems = 10;

    /**
     * @var array<string, array{name:string,bytes:string,width:int,height:int,color_space:string}>
     */
    private array $images = [];

    private int $imageSequence = 0;

    public function generate(Order $order): string
    {
        $order->loadMissing(['customer.user', 'items.tshirtImage', 'items.color']);

        $filename = "receipt_{$order->id}.pdf";
        Storage::disk('local')->makeDirectory('pdf_receipts');
        Storage::disk('local')->put("pdf_receipts/{$filename}", $this->buildPdf($order));

        return $filename;
    }

    private function buildPdf(Order $order): string
    {
        $this->images = [];
        $this->imageSequence = 0;

        $pageItems = $this->paginateItems($order->items);
        $pageCount = count($pageItems);
        $pages = [];

        foreach ($pageItems as $pageIndex => $items) {
            $pages[] = $this->renderPage($order, $items, $pageIndex + 1, $pageCount);
        }

        return $this->assemblePdf($pages);
    }

    /**
     * @return list<Collection<int, OrderItem>>
     */
    private function paginateItems(Collection $items): array
    {
        $pages = [$items->take(self::FirstPageItems)->values()];

        foreach ($items->skip(self::FirstPageItems)->chunk(self::FollowingPageItems) as $chunk) {
            $pages[] = $chunk->values();
        }

        return $pages;
    }

    /**
     * @param  Collection<int, OrderItem>  $items
     * @return array{content:string,images:list<string>}
     */
    private function renderPage(Order $order, Collection $items, int $page, int $pageCount): array
    {
        $commands = [];
        $usedImages = [];
        $firstPage = $page === 1;

        $this->rectangle($commands, 0, 755, 595, 87, [0.09, 0.11, 0.14]);
        $this->text($commands, 38, 807, 'FUNSHIRT', 21, true, [1, 1, 1]);
        $this->text($commands, 38, 785, 'Personalized T-shirt store', 9, false, [0.79, 0.84, 0.9]);
        $this->text($commands, 425, 808, "RECEIPT #{$order->id}", 13, true, [1, 1, 1]);
        $this->text($commands, 425, 789, 'Date: '.$order->date?->format('Y-m-d'), 9, false, [0.79, 0.84, 0.9]);
        $this->text($commands, 425, 773, 'Status: '.Str::headline($order->status), 9, false, [0.79, 0.84, 0.9]);

        if ($firstPage) {
            $this->informationPanels($commands, $order);
            $tableTop = 535;
        } else {
            $this->text($commands, 38, 719, 'ITEMS - CONTINUED', 12, true, [0.1, 0.12, 0.15]);
            $tableTop = 696;
        }

        $this->tableHeader($commands, $tableTop);
        $rowTop = $tableTop - 15;

        foreach ($items as $item) {
            $rowBottom = $rowTop - 52;
            $shirtImage = $this->registerImage($this->tshirtBasePath($item));
            $designImage = $this->registerImage($this->designImagePath($item));

            if ($shirtImage) {
                $usedImages[] = $shirtImage;
                $this->image($commands, $shirtImage, 42, $rowBottom + 8, 34, 34);
            } else {
                $this->imagePlaceholder($commands, 42, $rowBottom + 8, 34, 34, 'shirt');
            }

            if ($designImage) {
                $usedImages[] = $designImage;
                $this->image($commands, $designImage, 82, $rowBottom + 8, 34, 34);
            } else {
                $this->imagePlaceholder($commands, 82, $rowBottom + 8, 34, 34, 'design');
            }

            $this->text($commands, 126, $rowTop - 22, $this->shorten($item->design_name, 27), 9, true);
            $this->text(
                $commands,
                298,
                $rowTop - 22,
                $this->shorten(($item->color?->name ?? $item->color_code).' / '.$item->size, 20),
                9
            );
            $this->text($commands, 414, $rowTop - 22, (string) $item->qty, 9);
            $this->text($commands, 446, $rowTop - 22, $this->money($item->unit_price), 8);
            $this->text($commands, 509, $rowTop - 22, $this->money($item->sub_total), 8, true);
            $this->line($commands, 38, $rowBottom, 557, $rowBottom, [0.86, 0.88, 0.9]);

            $rowTop = $rowBottom;
        }

        if ($page === $pageCount) {
            $totalY = max(83, $rowTop - 50);
            $this->rectangle($commands, 389, $totalY, 168, 38, [0.93, 0.95, 0.97]);
            $this->text($commands, 405, $totalY + 14, 'TOTAL', 10, true, [0.22, 0.28, 0.35]);
            $this->text($commands, 486, $totalY + 13, $this->money($order->total_price), 11, true, [0.08, 0.1, 0.13]);
        }

        $this->text($commands, 38, 33, 'Thank you for choosing FunShirt.', 8, false, [0.4, 0.45, 0.5]);
        $this->text($commands, 495, 33, "Page {$page} / {$pageCount}", 8, false, [0.4, 0.45, 0.5]);

        return [
            'content' => implode("\n", $commands),
            'images' => array_values(array_unique($usedImages)),
        ];
    }

    /**
     * @param  list<string>  $commands
     */
    private function informationPanels(array &$commands, Order $order): void
    {
        $this->rectangle($commands, 38, 680, 250, 56, [0.96, 0.97, 0.98]);
        $this->rectangle($commands, 306, 680, 251, 56, [0.96, 0.97, 0.98]);
        $this->text($commands, 50, 718, 'CUSTOMER', 8, true, [0.35, 0.42, 0.5]);
        $this->text($commands, 50, 701, $this->shorten($order->customer?->user?->name ?? '-', 37), 9, true);
        $this->text($commands, 50, 686, $this->shorten($order->customer?->user?->email ?? '-', 42), 8);
        $this->text($commands, 318, 718, 'PAYMENT', 8, true, [0.35, 0.42, 0.5]);
        $this->text($commands, 318, 701, $order->payment_type ?: '-', 9, true);
        $this->text($commands, 318, 686, 'Reference: '.($order->payment_ref ?: '-'), 8);

        $this->rectangle($commands, 38, 615, 519, 48, [0.96, 0.97, 0.98]);
        $this->text($commands, 50, 646, 'DELIVERY', 8, true, [0.35, 0.42, 0.5]);
        $this->text($commands, 50, 629, 'NIF: '.($order->nif ?: '-'), 8, true);

        foreach ($this->wrap($order->address ?: '-', 73, 2) as $lineIndex => $addressLine) {
            $this->text($commands, 148, 629 - ($lineIndex * 13), $addressLine, 8);
        }

        $this->text($commands, 38, 591, 'NOTES', 8, true, [0.35, 0.42, 0.5]);
        foreach ($this->wrap($order->notes ?: 'No notes.', 102, 2) as $lineIndex => $notesLine) {
            $this->text($commands, 38, 576 - ($lineIndex * 13), $notesLine, 8);
        }
    }

    /**
     * @param  list<string>  $commands
     */
    private function tableHeader(array &$commands, float $top): void
    {
        $this->rectangle($commands, 38, $top, 519, 16, [0.2, 0.24, 0.3]);
        $this->text($commands, 42, $top + 5, 'SHIRT', 7, true, [1, 1, 1]);
        $this->text($commands, 82, $top + 5, 'DESIGN', 7, true, [1, 1, 1]);
        $this->text($commands, 126, $top + 5, 'ITEM', 7, true, [1, 1, 1]);
        $this->text($commands, 298, $top + 5, 'COLOR / SIZE', 7, true, [1, 1, 1]);
        $this->text($commands, 406, $top + 5, 'QUANTITY', 7, true, [1, 1, 1]);
        $this->text($commands, 446, $top + 5, 'UNIT', 7, true, [1, 1, 1]);
        $this->text($commands, 509, $top + 5, 'SUBTOTAL', 7, true, [1, 1, 1]);
    }

    private function tshirtBasePath(OrderItem $item): ?string
    {
        $code = preg_replace('/[^a-f0-9]/i', '', $item->color_code);
        $path = Storage::disk('public')->path("tshirt_base/{$code}.jpg");

        return is_file($path) ? $path : null;
    }

    private function designImagePath(OrderItem $item): ?string
    {
        $filename = data_get($item->custom, 'design.image_url', $item->tshirtImage?->image_url);
        $isPersonal = data_get($item->custom, 'design.is_personal', $item->tshirtImage?->customer_id !== null);

        if ($filename && basename($filename) === $filename) {
            $path = $isPersonal
                ? Storage::disk('local')->path("tshirt_images_private/{$filename}")
                : Storage::disk('public')->path("tshirt_images/{$filename}");

            if (is_file($path)) {
                return $path;
            }
        }

        $placeholder = Storage::disk('public')->path('tshirt_images/placeholder.png');

        return is_file($placeholder) ? $placeholder : null;
    }

    private function registerImage(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (isset($this->images[$path])) {
            return $this->images[$path]['name'];
        }

        $image = $this->pdfImage($path);
        if (! $image) {
            return null;
        }

        $name = 'Im'.(++$this->imageSequence);
        $this->images[$path] = [
            'name' => $name,
            ...$image,
        ];

        return $name;
    }

    /**
     * Images pass through a Base64 data URI so private and public sources
     * are embedded in the PDF without exposing filesystem paths or URLs.
     *
     * @return array{bytes:string,width:int,height:int,color_space:string}|null
     */
    private function pdfImage(string $path): ?array
    {
        $dataUri = $this->base64DataUri($path);
        if (! $dataUri) {
            return null;
        }

        [, $encoded] = explode(',', $dataUri, 2);
        $bytes = base64_decode($encoded, true);
        if ($bytes === false) {
            return null;
        }

        $info = @getimagesizefromstring($bytes);
        if (! $info) {
            return null;
        }

        if (($info['mime'] ?? null) !== 'image/jpeg') {
            $bytes = $this->convertToJpeg($bytes);
            if (! $bytes) {
                return null;
            }

            $info = @getimagesizefromstring($bytes);
            if (! $info) {
                return null;
            }
        }

        $colorSpace = match ($info['channels'] ?? 3) {
            1 => '/DeviceGray',
            4 => '/DeviceCMYK',
            default => '/DeviceRGB',
        };

        return [
            'bytes' => $bytes,
            'width' => (int) $info[0],
            'height' => (int) $info[1],
            'color_space' => $colorSpace,
        ];
    }

    private function base64DataUri(string $path): ?string
    {
        $bytes = @file_get_contents($path);
        if ($bytes === false) {
            return null;
        }

        $mime = function_exists('mime_content_type') ? (mime_content_type($path) ?: null) : null;
        $mime ??= match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            default => 'image/png',
        };

        return "data:{$mime};base64,".base64_encode($bytes);
    }

    private function convertToJpeg(string $bytes): ?string
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        $source = @imagecreatefromstring($bytes);
        if (! $source) {
            return null;
        }

        $canvas = imagecreatetruecolor(imagesx($source), imagesy($source));
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);
        imagealphablending($canvas, true);
        imagecopy($canvas, $source, 0, 0, 0, 0, imagesx($source), imagesy($source));

        ob_start();
        imagejpeg($canvas, null, 88);
        $jpeg = ob_get_clean();

        imagedestroy($canvas);
        imagedestroy($source);

        return $jpeg === false ? null : $jpeg;
    }

    /**
     * @param  list<array{content:string,images:list<string>}>  $pages
     */
    private function assemblePdf(array $pages): string
    {
        $objects = [
            1 => '',
            2 => '',
            3 => "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\n",
            4 => "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>\n",
            5 => "<< /Title (FunShirt receipt) /Producer (FunShirt) /Subject (Images embedded from Base64 data URIs) >>\n",
        ];
        $nextObject = 6;
        $imageObjects = [];

        foreach ($this->images as $image) {
            $imageObjects[$image['name']] = $nextObject;
            $objects[$nextObject++] = "<< /Type /XObject /Subtype /Image /Width {$image['width']} /Height {$image['height']} /ColorSpace {$image['color_space']} /BitsPerComponent 8 /Filter /DCTDecode /Length ".strlen($image['bytes'])." >>\nstream\n{$image['bytes']}\nendstream\n";
        }

        $pageObjects = [];
        foreach ($pages as $page) {
            $contentObject = $nextObject++;
            $pageObject = $nextObject++;
            $objects[$contentObject] = '<< /Length '.strlen($page['content'])." >>\nstream\n{$page['content']}\nendstream\n";

            $xObjects = collect($page['images'])
                ->map(fn (string $name): string => "/{$name} {$imageObjects[$name]} 0 R")
                ->implode(' ');
            $resources = '/Font << /F1 3 0 R /F2 4 0 R >>';
            if ($xObjects !== '') {
                $resources .= " /XObject << {$xObjects} >>";
            }

            $objects[$pageObject] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << {$resources} >> /Contents {$contentObject} 0 R >>\n";
            $pageObjects[] = $pageObject;
        }

        $kids = collect($pageObjects)->map(fn (int $object): string => "{$object} 0 R")->implode(' ');
        $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>\n";
        $objects[2] = "<< /Type /Pages /Kids [{$kids}] /Count ".count($pageObjects)." >>\n";

        ksort($objects);
        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $number => $object) {
            $offsets[$number] = strlen($pdf);
            $pdf .= "{$number} 0 obj\n{$object}endobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R /Info 5 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF\n";

        return $pdf;
    }

    /**
     * @param  list<string>  $commands
     * @param  array{float, float, float}  $color
     */
    private function text(array &$commands, float $x, float $y, string $text, int $size, bool $bold = false, array $color = [0.1, 0.12, 0.15]): void
    {
        $font = $bold ? 'F2' : 'F1';
        $commands[] = sprintf(
            'q %.2F %.2F %.2F rg BT /%s %d Tf %.1F %.1F Td (%s) Tj ET Q',
            $color[0],
            $color[1],
            $color[2],
            $font,
            $size,
            $x,
            $y,
            $this->escapePdfText(Str::ascii($text)),
        );
    }

    /**
     * @param  list<string>  $commands
     * @param  array{float, float, float}  $color
     */
    private function rectangle(array &$commands, float $x, float $y, float $width, float $height, array $color): void
    {
        $commands[] = sprintf('q %.2F %.2F %.2F rg %.1F %.1F %.1F %.1F re f Q', $color[0], $color[1], $color[2], $x, $y, $width, $height);
    }

    /**
     * @param  list<string>  $commands
     * @param  array{float, float, float}  $color
     */
    private function line(array &$commands, float $x1, float $y1, float $x2, float $y2, array $color): void
    {
        $commands[] = sprintf('q %.2F %.2F %.2F RG %.1F %.1F m %.1F %.1F l S Q', $color[0], $color[1], $color[2], $x1, $y1, $x2, $y2);
    }

    /**
     * @param  list<string>  $commands
     */
    private function image(array &$commands, string $name, float $x, float $y, float $width, float $height): void
    {
        $image = collect($this->images)->firstWhere('name', $name);
        $scale = min($width / $image['width'], $height / $image['height']);
        $renderWidth = $image['width'] * $scale;
        $renderHeight = $image['height'] * $scale;
        $left = $x + (($width - $renderWidth) / 2);
        $bottom = $y + (($height - $renderHeight) / 2);
        $commands[] = sprintf('q %.2F 0 0 %.2F %.2F %.2F cm /%s Do Q', $renderWidth, $renderHeight, $left, $bottom, $name);
    }

    /**
     * @param  list<string>  $commands
     */
    private function imagePlaceholder(array &$commands, float $x, float $y, float $width, float $height, string $label): void
    {
        $this->rectangle($commands, $x, $y, $width, $height, [0.93, 0.94, 0.95]);
        $this->text($commands, $x + 4, $y + 15, $label, 6, false, [0.46, 0.5, 0.55]);
    }

    /**
     * @return list<string>
     */
    private function wrap(string $text, int $length, int $limit): array
    {
        return array_slice(explode("\n", wordwrap(Str::ascii($text), $length, "\n", true)), 0, $limit);
    }

    private function shorten(string $text, int $length): string
    {
        return Str::limit(Str::ascii($text), $length, '...');
    }

    private function escapePdfText(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    private function money(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '').' EUR';
    }
}
