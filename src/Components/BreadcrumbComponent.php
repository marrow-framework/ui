<?php

declare(strict_types=1);

namespace Marrow\Ui\Components;

use Marrow\Template\Component;

/**
 *   {{ component('breadcrumb', {items: [
 *       {label: 'Dashboard', href: '/'},
 *       {label: 'Settings', href: '/settings'},
 *       {label: 'Profile'},
 *   ]}) }}
 *
 * The last item (or any item without `href`) renders as plain text — the
 * current page, not a link.
 */
class BreadcrumbComponent extends Component
{
    /** @var array<int, array{label: string, href?: string}> */
    public array $items = [];
    public string $class = '';

    public function render(): string
    {
        return '@ui/components/breadcrumb';
    }
}
