<?php

declare(strict_types=1);

namespace Marrow\Ui\Components;

use Marrow\Template\Component;

/** {{ component('progress', {value: 72}) }} */
class ProgressComponent extends Component
{
    public float $value = 0;
    public float $max = 100;
    public string $class = '';

    public function render(): string
    {
        return '@ui/components/progress';
    }

    public function data(): array
    {
        $percent = $this->max > 0 ? max(0, min(100, ($this->value / $this->max) * 100)) : 0;

        return array_merge(parent::data(), [
            'percent' => round($percent, 2),
        ]);
    }
}
