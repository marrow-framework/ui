<?php

declare(strict_types=1);

namespace Marrow\Ui\Components;

use Marrow\Template\Component;

/** {{ component('separator') }} / {{ component('separator', {orientation: 'vertical'}) }} */
class SeparatorComponent extends Component
{
    public string $orientation = 'horizontal'; // horizontal|vertical
    public string $class = '';

    public function render(): string
    {
        return '@ui/components/separator';
    }
}
