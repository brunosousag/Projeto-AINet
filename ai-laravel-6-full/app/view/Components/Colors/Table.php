<?php

namespace App\View\Components\Colors;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Table extends Component
{
    public function __construct(
        public object $colors,
        public bool $showView = true,
        public bool $showEdit = true,
        public bool $showDelete = true,
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.colors.table');
    }
}
