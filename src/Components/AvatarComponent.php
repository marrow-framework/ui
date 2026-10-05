<?php

declare(strict_types=1);

namespace Marrow\Ui\Components;

use Marrow\Template\Component;

/**
 * {{ component('avatar', {src: user.avatarUrl, alt: user.name}) }}
 * {{ component('avatar', {initials: 'JD'}) }}  {# no src — falls back to initials #}
 */
class AvatarComponent extends Component
{
    public ?string $src = null;
    public string $alt = '';
    public string $initials = '';
    public string $size = 'md'; // sm|md|lg
    public string $class = '';
    public array $attrs = [];

    private const SIZES = [
        'sm' => 'h-8 w-8 text-xs',
        'md' => 'h-10 w-10 text-sm',
        'lg' => 'h-14 w-14 text-base',
    ];

    public function render(): string
    {
        return '@ui/components/avatar';
    }

    public function data(): array
    {
        return array_merge(parent::data(), [
            'sizeClasses' => self::SIZES[$this->size] ?? self::SIZES['md'],
        ]);
    }
}
