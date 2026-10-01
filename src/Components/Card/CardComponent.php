<?php

declare(strict_types=1);

namespace Marrow\Ui\Components\Card;

use Marrow\Template\Component;

/**
 * Plain shortcut:
 *   {% set body %}<p>Some rich content.</p>{% endset %}
 *   {{ component('card', {title: 'Account', slot: body}) }}
 *
 * Compositional — for a header/footer with more than a plain string (an
 * icon, a badge, an action button next to the title), pass `composed: true`
 * and build the body out of `card-header`/`card-title`/`card-description`/
 * `card-content`/`card-footer` yourself (see CardHeaderComponent):
 *
 *   {% component 'card' with {composed: true} %}
 *       {% component 'card-header' %}
 *           {% component 'card-title' %}Account{% endcomponent %}
 *       {% endcomponent %}
 *       {% component 'card-content' %}...{% endcomponent %}
 *   {% endcomponent %}
 *
 * `composed: true` is what tells `card` to skip its own title/footer/padding
 * conveniences and just render `slot` as-is inside the outer shell — without
 * it, those sub-pieces' own padding would be nested *inside* `card`'s own
 * padded body wrapper, doubling up.
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
    public bool $composed = false;

    public function render(): string
    {
        return '@ui/components/card/card';
    }
}
