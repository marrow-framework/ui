<?php

declare(strict_types=1);

namespace Marrow\Ui\Components;

use Marrow\Template\Component;

/** {{ component('label', {for: 'email', required: true, slot: 'Email address'}) }} */
class LabelComponent extends Component
{
    public ?string $for = null;
    public bool $required = false;
    public string $class = '';
    public array $attrs = [];
    public string $slot = '';

    public function render(): string
    {
        return '@ui/components/label';
    }
}
