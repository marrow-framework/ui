<?php

declare(strict_types=1);

namespace Marrow\Ui\Components;

use Marrow\Template\Component;

/**
 *   {% set profileBody %}<p>Profile settings...</p>{% endset %}
 *   {% set accountBody %}<p>Account settings...</p>{% endset %}
 *   {{ component('tabs', {
 *       tabs: [
 *           {id: 'profile', label: 'Profile', content: profileBody},
 *           {id: 'account', label: 'Account', content: accountBody},
 *       ],
 *   }) }}
 *
 * Each `content` is pre-rendered HTML, not auto-escaped — see
 * ButtonComponent's note. Not a compound-component API (no separate
 * `<Tabs.Trigger>`/`<Tabs.Content>`) — `component()` renders one template
 * per call, so the tab set is passed as a single structured array instead.
 */
class TabsComponent extends Component
{
    /** @var array<int, array{id: string, label: string, content: string}> */
    public array $tabs = [];
    public string $class = '';

    public function render(): string
    {
        return '@ui/components/tabs';
    }

    public function data(): array
    {
        return array_merge(parent::data(), [
            'defaultTab' => $this->tabs[0]['id'] ?? '',
        ]);
    }
}
