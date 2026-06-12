@props([
    'imageUrl',
    'alt',
    'colorCode' => 'fafafa',
    'dynamic' => false,
    'settings' => [],
    'dynamicPlacement' => false,
    'dynamicImage' => false,
    'colorProperty' => 'selectedColor',
    'imageProperty' => 'previewImage',
    'topProperty' => 'previewTop',
    'widthProperty' => 'previewWidth',
    'heightProperty' => 'previewHeight',
    'opacityProperty' => 'previewOpacity',
])

@php
    $baseUrl = asset('storage/tshirt_base');
    $fallbackBaseUrl = asset('storage/tshirt_base/fafafa.jpg');
    $previewTop = $settings['preview_top'] ?? 25;
    $previewWidth = $settings['preview_width'] ?? 48;
    $previewHeight = $settings['preview_height'] ?? 50;
    $previewOpacity = $settings['preview_opacity'] ?? 100;
@endphp

<div {{ $attributes->class('relative aspect-[13/14] overflow-hidden bg-white') }}>
    <img src="{{ "{$baseUrl}/{$colorCode}.jpg" }}"
         @if ($dynamic)
             x-bind:src="@js($baseUrl) + '/' + {{ $colorProperty }} + '.jpg'"
         @endif
         onerror="this.onerror=null; this.src='{{ $fallbackBaseUrl }}';"
         alt=""
         class="h-full w-full object-contain"
         loading="lazy">

    <div data-print-area
         class="absolute overflow-hidden"
         style="left: 23%; right: 23%; top: 18%; bottom: 10%;">
        <img src="{{ $imageUrl ?: asset('storage/tshirt_images/placeholder.png') }}"
             alt="{{ $alt }}"
             style="left: 50%; transform: translateX(-50%); top: {{ $previewTop }}%; width: {{ $previewWidth }}%; height: {{ $previewHeight }}%; opacity: {{ $previewOpacity / 100 }}"
             @if ($dynamicImage)
                 x-bind:src="{{ $imageProperty }}"
                 x-show="{{ $imageProperty }}"
             @endif
             @if ($dynamicPlacement)
                 x-bind:style="'left: 50%; transform: translateX(-50%); top: ' + {{ $topProperty }} + '%; width: ' + {{ $widthProperty }} + '%; height: ' + {{ $heightProperty }} + '%; opacity: ' + ({{ $opacityProperty }} / 100)"
             @endif
             class="absolute object-contain"
             loading="lazy">
    </div>
</div>
