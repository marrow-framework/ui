<?php

declare(strict_types=1);

namespace Marrow\Ui\Components\Breadcrumb;

use Marrow\Template\Component;

/**
 * Plain shortcut:
 *   {{ component('breadcrumb', {items: [
 *       {label: 'Dashboard', href: '/'},
 *       {label: 'Settings', href: '/settings'},
 *       {label: 'Profile'},
 *   ]}) }}
 *
 * The last item (or any item without `href`) renders as plain text — the
 * current page, not a link.
 *
 * Compositional — build each item yourself with `breadcrumb-item` (see its
 * own docblock) instead of the `items` array, e.g. to put an icon next to a
 * label:
 *
 *   {% component 'breadcrumb' %}
 *       {% component 'breadcrumb-item' with {href: '/'} %}Dashboard{% endcomponent %}
 *       {% component 'breadcrumb-item' %}Settings{% endcomponent %}
 *   {% endcomponent %}
 *
 * `items` and compositional `breadcrumb-item` children are mutually
 * exclusive — when `items` is non-empty it wins and `slot` is ignored.
 */
class BreadcrumbComponent extends Component
{
    /** @var array<int, array{label: string, href?: string}> */
    public array $items = [];
    public string $class = '';
    public array $attrs = [];
    public string $slot = '';

    public function render(): string
    {
        return '@ui/components/breadcrumb/breadcrumb';
    }
}
