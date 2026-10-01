<?php

declare(strict_types=1);

namespace Marrow\Ui\Components;

use Marrow\Template\Component;

/** {{ component('skeleton', {class: 'h-4 w-32'}) }} — loading placeholder; size is entirely controlled via `class`. */
class SkeletonComponent extends Component
{
    public string $class = '';

    public function render(): string
    {
        return '@ui/components/skeleton';
    }
}
