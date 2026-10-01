<?php

declare(strict_types=1);

namespace Marrow\Ui\Components;

use Marrow\Template\Component;

/**
 *   {% set trigger %}{{ component('button', {variant: 'outline', slot: 'Options'}) }}{% endset %}
 *   {% set items %}
 *       <a href="/profile" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-800">Profile</a>
 *       <a href="/logout" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-800">Logout</a>
 *   {% endset %}
 *   {{ component('dropdown', {trigger: trigger, slot: items}) }}
 *
 * `trigger`/`slot` are pre-rendered HTML, not auto-escaped — see
 * ButtonComponent's note.
 *
 * Two named slots (`trigger`, `body`) means this is also usable directly
 * through Twig's native `{% embed %}` instead of the props above:
 *
 *   {% embed '@ui/components/dropdown' %}
 *       {% block trigger %}{{ component('button', {variant: 'outline', slot: 'Options'}) }}{% endblock %}
 *       {% block body %}<a href="/profile" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-800">Profile</a>{% endblock %}
 *   {% endembed %}
 *
 * `align` is resolved to a Tailwind class inside the template itself (a
 * plain ternary), not computed here — see ModalComponent's docblock for
 * why (so `{% embed %}`, which never instantiates this PHP class, still
 * gets it right).
 */
class DropdownComponent extends Component
{
    public string $trigger = '';
    public string $slot = '';
    public string $align = 'right'; // left|right

    public function render(): string
    {
        return '@ui/components/dropdown';
    }
}
