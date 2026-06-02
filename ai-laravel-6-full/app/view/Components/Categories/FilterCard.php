<?php

namespace App\View\Components\Categories;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FilterCard extends Component
{
    public function __construct(
        public string $filterAction,
        public string $resetUrl,
        public ?string $search = null,
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.categories.filter-card');
    }
}
