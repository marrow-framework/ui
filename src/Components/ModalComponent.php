<?php

declare(strict_types=1);

namespace Marrow\Ui\Components;

use Marrow\Template\Component;

/**
 *   {% set trigger %}{{ component('button', {slot: 'Delete account'}) }}{% endset %}
 *   {% set body %}<p>Are you sure? This cannot be undone.</p>{% endset %}
 *   {{ component('modal', {title: 'Confirm deletion', trigger: trigger, slot: body}) }}
 *
 * Self-contained: each instance owns its own Alpine `open` state (scoped to
 * its wrapping `x-data`), so more than one modal on the same page never
 * collides — no shared id/selector to keep unique.
 *
 * `trigger`/`slot` are pre-rendered HTML, not auto-escaped — see
 * ButtonComponent's note.
 *
 * Two named slots (`trigger`, `body`) means this is also usable directly
 * through Twig's native `{% embed %}` instead of the props above — no
 * custom tag needed for the 2-slot case, see this package's README:
 *
 *   {% embed '@ui/components/modal' with {title: 'Confirm deletion'} %}
 *       {% block trigger %}{{ component('button', {variant: 'danger', slot: 'Delete'}) }}{% endblock %}
 *       {% block body %}<p>Are you sure? This cannot be undone.</p>{% endblock %}
 *   {% endembed %}
 *
 * `maxWidth` (sm/md/lg/xl) is resolved to a Tailwind class inside the
 * template itself (a plain dict lookup), not computed here — deliberately,
 * so `{% embed %}` usage (which never instantiates this PHP class) still
 * gets the right class without needing to duplicate the mapping in two
 * places.
 */
class ModalComponent extends Component
{
    public ?string $title = null;
    public string $trigger = '';
    public string $slot = '';
    public string $maxWidth = 'md'; // sm|md|lg|xl
    public string $class = '';
    public array $attrs = [];

    public function render(): string
    {
        return '@ui/components/modal';
    }
}
