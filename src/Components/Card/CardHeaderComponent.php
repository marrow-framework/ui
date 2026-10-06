<?php

declare(strict_types=1);

namespace Marrow\Ui\Components\Card;

use Marrow\Template\Component;

/**
 * Compositional alternative to `card`'s `title`/`footer` props — for a
 * header with more than a plain string (an icon, a badge, an action
 * button next to the title):
 *
 *   {% component 'card' %}
 *       {% component 'card-header' %}
 *           {% component 'card-title' %}Account{% endcomponent %}
 *           {% component 'card-description' %}Manage your account settings.{% endcomponent %}
 *       {% endcomponent %}
 *       {% component 'card-content' %}...{% endcomponent %}
 *       {% component 'card-footer' %}...{% endcomponent %}
 *   {% endcomponent %}
 *
 * Plain `card` with `title`/`footer` props is still the simpler choice for
 * a plain-string header/footer — this family exists for when it isn't one.
 */
class CardHeaderComponent extends Component
{
    public string $class = '';
    public array $attrs = [];
    public string $slot = '';

    public function render(): string
    {
        return '@ui/components/card/card-header';
    }
}
