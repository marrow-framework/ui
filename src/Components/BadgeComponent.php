<?php

declare(strict_types=1);

namespace Marrow\Ui\Components;

use Marrow\Template\Component;

/** {{ component('badge', {variant: 'success', slot: 'Active'}) }} */
class BadgeComponent extends Component
{
    public string $variant = 'default';
    public string $class = '';
    public array $attrs = [];
    public string $slot = '';

    private const VARIANTS = [
        'default' => 'bg-slate-800 text-slate-300',
        'success' => 'bg-emerald-400/10 text-emerald-400',
        'warning' => 'bg-amber-400/10 text-amber-400',
        'danger' => 'bg-red-400/10 text-red-400',
        'info' => 'bg-sky-400/10 text-sky-400',
    ];

    public function render(): string
    {
        return '@ui/components/badge';
    }

    public function data(): array
    {
        return array_merge(parent::data(), [
            'variantClasses' => self::VARIANTS[$this->variant] ?? self::VARIANTS['default'],
        ]);
    }
}
