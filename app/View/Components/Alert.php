<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Alert extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public $type = 'info')
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.alert', [
            'backgroundColor' => match ($this->type) {
                'success' => 'bg-green-900',
                'danger' => 'bg-red-500',
                default => 'bg-blue-900',
            },
        ]);
    }
}
