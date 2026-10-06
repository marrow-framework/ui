<?php

declare(strict_types=1);

namespace Marrow\Ui\Components;

use Marrow\Template\Component;

/**
 *   {{ component('accordion', {
 *       items: [
 *           {title: 'Is it accessible?', content: '<p>Yes, keyboard and ARIA-friendly.</p>'},
 *           {title: 'Can I style it?', content: '<p>Yes, pass your own `class`.</p>'},
 *       ],
 *   }) }}
 *
 * Single-open (classic accordion behaviour) — opening one item closes any
 * other, matching shadcn's default `type="single"` Accordion rather than
 * its `type="multiple"` variant. Each `content` is pre-rendered HTML, not
 * auto-escaped — see ButtonComponent's note.
 */
class AccordionComponent extends Component
{
    /** @var array<int, array{title: string, content: string}> */
    public array $items = [];
    public string $class = '';
    public array $attrs = [];

    public function render(): string
    {
        return '@ui/components/accordion';
    }
}
