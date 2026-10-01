<?php

declare(strict_types=1);

namespace Marrow\Ui\Components\Breadcrumb;

use Marrow\Template\Component;

/**
 * One item inside a compositional `breadcrumb` (see its own docblock).
 * Renders a link when `href` is given, plain current-page text otherwise —
 * same rule as the `items`-array shortcut: the last/current item has no
 * `href`.
 *
 *   {% component 'breadcrumb-item' with {href: '/'} %}Dashboard{% endcomponent %}
 *   {% component 'breadcrumb-item' %}Settings{% endcomponent %}  {# current page — no href #}
 */
class BreadcrumbItemComponent extends Component
{
    public ?string $href = null;
    public string $class = '';
    public string $slot = '';

    public function render(): string
    {
        return '@ui/components/breadcrumb/breadcrumb-item';
    }
}
