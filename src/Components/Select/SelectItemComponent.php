<?php

declare(strict_types=1);

namespace Marrow\Ui\Components\Select;

use Marrow\Template\Component;

/**
 * One `<option>` inside a compositional `select` — the plain `options`
 * dict shortcut is simpler for a static list; use this when an item needs
 * something the dict can't express (a disabled option, an explicit
 * `selected` you compute yourself, a value that differs from its label in
 * a way a flat dict gets awkward for):
 *
 *   {% component 'select' with {name: 'role'} %}
 *       {% component 'select-item' with {value: 'admin', selected: role == 'admin'} %}Administrator{% endcomponent %}
 *       {% component 'select-item' with {value: 'editor'} %}Editor{% endcomponent %}
 *   {% endcomponent %}
 *
 * Not coordinated with `select`'s own `value` prop automatically — a native
 * `<select>` only has one source of truth for "which option is selected"
 * (the `selected` attribute on the matching `<option>`), and independently
 * rendered items have no shared context to compute that from; pass
 * `selected` explicitly per item instead, as shown above.
 */
class SelectItemComponent extends Component
{
    public string $value = '';
    public bool $selected = false;
    public bool $disabled = false;
    public string $class = '';
    public array $attrs = [];
    public string $slot = '';

    public function render(): string
    {
        return '@ui/components/select/select-item';
    }
}
