<?php

declare(strict_types=1);

namespace Marrow\Ui\Components;

use Marrow\Template\Component;

/**
 *   {% set trigger %}{{ component('button', {variant: 'ghost', slot: '?'}) }}{% endset %}
 *   {{ component('tooltip', {trigger: trigger, slot: 'More information', side: 'top'}) }}
 *
 * Pure CSS (`group`/`group-hover`) — no Alpine needed. Fixed `side`, not
 * auto-flipping near viewport edges like a Floating-UI-backed tooltip
 * (Radix, which shadcn's Tooltip is built on) would — a reasonable
 * simplification for a v1 without pulling in a positioning library.
 *
 * `trigger`/`slot` are pre-rendered HTML, not auto-escaped — see
 * ButtonComponent's note.
 */
class TooltipComponent extends Component
{
    public string $trigger = '';
    public string $slot = '';
    public string $side = 'top'; // top|bottom|left|right
    public string $class = '';
    public array $attrs = [];

    private const SIDES = [
        'top' => 'bottom-full left-1/2 mb-2 -translate-x-1/2',
        'bottom' => 'top-full left-1/2 mt-2 -translate-x-1/2',
        'left' => 'right-full top-1/2 mr-2 -translate-y-1/2',
        'right' => 'left-full top-1/2 ml-2 -translate-y-1/2',
    ];

    public function render(): string
    {
        return '@ui/components/tooltip';
    }

    public function data(): array
    {
        return array_merge(parent::data(), [
            'sideClasses' => self::SIDES[$this->side] ?? self::SIDES['top'],
        ]);
    }
}
