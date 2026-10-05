<?php

declare(strict_types=1);

namespace Marrow\Ui\Components;

use Marrow\Template\Component;

/** {{ component('spinner', {size: 'md'}) }} — the same spinner Button uses internally for `loading`, usable standalone. */
class SpinnerComponent extends Component
{
    public string $size = 'md'; // sm|md|lg
    public string $class = '';
    public array $attrs = [];

    private const SIZES = [
        'sm' => 'h-4 w-4',
        'md' => 'h-6 w-6',
        'lg' => 'h-8 w-8',
    ];

    public function render(): string
    {
        return '@ui/components/spinner';
    }

    public function data(): array
    {
        return array_merge(parent::data(), [
            'sizeClasses' => self::SIZES[$this->size] ?? self::SIZES['md'],
        ]);
    }
}
