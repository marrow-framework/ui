<?php

declare(strict_types=1);

namespace Marrow\Ui\Components;

use Marrow\Template\Component;

/**
 *   {% set body %}<p>Some rich content.</p>{% endset %}
 *   {{ component('card', {title: 'Account', slot: body}) }}
 *
 * `slot`/`footer` are pre-rendered HTML, not auto-escaped — see
 * ButtonComponent's note.
 */
class CardComponent extends Component
{
    public ?string $title = null;
    public string $footer = '';
    public string $class = '';
    public string $slot = '';

    public function render(): string
    {
        return '@ui/components/card';
    }
}
