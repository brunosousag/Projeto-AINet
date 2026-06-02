@props([
    'imageUrl',
    'alt',
    'colorCode' => 'fafafa',
    'dynamic' => false,
    'settings' => [],
    'dynamicPlacement' => false,
    'dynamicImage' => false,
])

@php
    $baseUrl = asset('storage/tshirt_base');
    $previewTop = $settings['preview_top'] ?? 25;
    $previewWidth = $settings['preview_width'] ?? 48;
    $previewHeight = $settings['preview_height'] ?? 50;
    $previewOpacity = $settings['preview_opacity'] ?? 100;
@endphp

<div {{ $attributes->class('relative aspect-[13/14] overflow-hidden bg-white') }}>
    <img src="{{ "{$baseUrl}/{$colorCode}.jpg" }}"
         @if ($dynamic)
             x-bind:src="'{{ $baseUrl }}/' + selectedColor + '.jpg'"
         @endif
         alt=""
         class="h-full w-full object-contain"
         loading="lazy">

    <div data-print-area class="absolute inset-x-[23%] top-[18%] bottom-[10%] overflow-hidden">
        <img src="{{ $imageUrl ?: asset('storage/tshirt_images/placeholder.png') }}"
             alt="{{ $alt }}"
             style="top: {{ $previewTop }}%; width: {{ $previewWidth }}%; height: {{ $previewHeight }}%; opacity: {{ $previewOpacity / 100 }}"
             @if ($dynamicImage)
                 x-bind:src="previewImage"
                 x-show="previewImage"
             @endif
             @if ($dynamicPlacement)
                 x-bind:style="'top: ' + previewTop + '%; width: ' + previewWidth + '%; height: ' + previewHeight + '%; opacity: ' + (previewOpacity / 100)"
             @endif
             class="absolute left-1/2 -translate-x-1/2 object-contain"
             loading="lazy">
    </div>
</div>
