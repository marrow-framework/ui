<?php

declare(strict_types=1);

namespace Marrow\Ui\Components;

use Marrow\Template\Component;

/**
 * {{ component('alert', {type: 'success', title: 'Saved', slot: 'Your changes were saved.'}) }}
 * {{ component('alert', {type: 'danger', dismissible: true, slot: errors('email')[0]}) }}
 *
 * `slot` is pre-rendered HTML, not auto-escaped — see ButtonComponent's note.
 */
class AlertComponent extends Component
{
    public string $type = 'info';
    public ?string $title = null;
    public bool $dismissible = false;
    public string $class = '';
    public string $slot = '';

    private const STYLES = [
        'info' => ['box' => 'border-sky-500/30 bg-sky-400/10', 'text' => 'text-sky-300', 'icon' => 'text-sky-400'],
        'success' => ['box' => 'border-emerald-500/30 bg-emerald-400/10', 'text' => 'text-emerald-300', 'icon' => 'text-emerald-400'],
        'warning' => ['box' => 'border-amber-500/30 bg-amber-400/10', 'text' => 'text-amber-300', 'icon' => 'text-amber-400'],
        'danger' => ['box' => 'border-red-500/30 bg-red-400/10', 'text' => 'text-red-300', 'icon' => 'text-red-400'],
    ];

    public function render(): string
    {
        return '@ui/components/alert';
    }

    public function data(): array
    {
        $style = self::STYLES[$this->type] ?? self::STYLES['info'];

        return array_merge(parent::data(), [
            'boxClasses' => $style['box'],
            'textClasses' => $style['text'],
            'iconClasses' => $style['icon'],
        ]);
    }
}
