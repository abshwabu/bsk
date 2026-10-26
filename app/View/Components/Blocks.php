<?php

namespace App\View\Components;

use App\Models\Page;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Blocks extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  array<int, array<string, mixed>>|null  $blocks
     */
    public function __construct(
        public ?Page $page = null,
        public ?array $blocks = null
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.blocks', [
            'blocks' => $this->blocks ?? ($this->page?->blocks ?? []),
        ]);
    }
}
