<?php

declare(strict_types=1);

namespace Marrow\Ui\Components;

use Marrow\Template\Component;

/**
 * {{ component('button', {variant: 'primary', size: 'md', slot: 'Save'}) }}
 * {{ component('button', {href: '/posts', variant: 'ghost', slot: 'Cancel'}) }}
 *
 * `slot` is pre-rendered HTML (the button's label/icon), not auto-escaped —
 * build it with Twig's `{% set %}...{% endset %}` for anything beyond plain
 * text, and never feed it raw user input.
 */
class ButtonComponent extends Component
{
    public string $variant = 'primary';
    public string $size = 'md';
    public string $type = 'button';
    public ?string $href = null;
    public bool $disabled = false;
    public bool $loading = false;
    public string $class = '';
    public array $attrs = [];
    public string $slot = '';

    private const VARIANTS = [
        'primary' => 'bg-orange-500 text-slate-950 hover:bg-orange-400 focus-visible:outline-orange-400',
        'secondary' => 'bg-slate-800 text-slate-100 hover:bg-slate-700 focus-visible:outline-slate-500',
        'danger' => 'bg-red-600 text-white hover:bg-red-500 focus-visible:outline-red-400',
        'ghost' => 'bg-transparent text-slate-300 hover:bg-slate-800 focus-visible:outline-slate-500',
        'outline' => 'border border-slate-700 text-slate-100 hover:bg-slate-800 focus-visible:outline-slate-500',
    ];

    private const SIZES = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-5 py-2.5 text-base',
    ];

    public function render(): string
    {
        return '@ui/components/button';
    }

    public function data(): array
    {
        return array_merge(parent::data(), [
            'variantClasses' => self::VARIANTS[$this->variant] ?? self::VARIANTS['primary'],
            'sizeClasses' => self::SIZES[$this->size] ?? self::SIZES['md'],
        ]);
    }
}
