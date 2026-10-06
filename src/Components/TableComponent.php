<?php

declare(strict_types=1);

namespace Marrow\Ui\Components;

use Marrow\Template\Component;

/**
 *   {{ component('table', {
 *       headers: ['Name', 'Email'],
 *       rows: [['Jane', 'jane@example.com'], ['Bob', 'bob@example.com']],
 *   }) }}
 *
 * Unlike most other components here, cell values ARE auto-escaped (plain
 * strings, no `|raw`) — ordinary tabular data is the common case, and
 * escaping-by-default is the safer one. Build something richer (links,
 * badges) per cell by putting pre-rendered HTML in `rows` yourself only
 * when you've deliberately chosen to trust that content.
 */
class TableComponent extends Component
{
    /** @var string[] */
    public array $headers = [];
    /** @var array<int, string[]> */
    public array $rows = [];
    public string $class = '';
    public array $attrs = [];

    public function render(): string
    {
        return '@ui/components/table';
    }
}
